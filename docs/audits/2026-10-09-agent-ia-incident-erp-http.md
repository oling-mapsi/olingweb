# Incident agent IA ERP HTTP - 2026-10-09

## Verdict

Incident reproduit dans le vrai widget navigateur, puis corrigé en local.

Validation finale : 3 essais ERP industriels consécutifs dans le widget avec OpenAI réel retournent HTTP 200, `llm_primary`, provider `openai`, type `proposal_request`, CTA `Recevoir une proposition OLING` et formulaire prérempli.

## Cause racine démontrée

Deux causes en chaîne :

1. Le serveur HTTP local PHP coupait la requête vers 30 s (`Maximum execution time of 30 seconds exceeded`) pendant l'attente OpenAI.
2. Certaines réponses OpenAI 200 contenaient un `output_text` JSON métier avec caractères de contrôle ou échappement imparfait dans le champ `reply`. Le décodage strict échouait, puis le fallback technique pouvait planter sur `ChatConversationManager::normalize()` manquant.

## Différences CLI / HTTP

- CLI : `max_execution_time=0`, donc pas de coupure PHP à 30 s.
- HTTP widget : serveur PHP local à 30 s par défaut avant correction.
- Navigateur : pas d'`AbortController` identifié dans `assets/js/chat-widget.js`; le widget attendait bien la réponse backend.
- API Symfony : après timeout/fallback, renvoyait parfois HTTP 500 ou `llm_unavailable`.

## Timeouts effectifs

Avant correction :

- OpenAI provider : `timeout=20`, sans `max_duration`.
- Budget global IA : `28000 ms`.
- Serveur PHP HTTP local : 30 s.
- Navigateur : pas de timeout JS explicite.

Après correction locale :

- OpenAI provider : `timeout=35`, `max_duration=35`.
- Budget global IA : `55000 ms`.
- Endpoint `/api/chat/conversations/{token}/messages` : `set_time_limit(75)`.
- Navigateur : inchangé.

## Erreurs observées

- HTTP 500 navigateur à environ 30.1 s.
- Log serveur : `Maximum execution time of 30 seconds exceeded in vendor/symfony/http-client/Response/CurlResponse.php`.
- OpenAI HTTP 200 suivi de `OpenAI structured output JSON decode failed: Control character error`.
- Fallback technique : `Call to undefined method App\Service\Chat\ChatConversationManager::normalize()`.

## Correctifs appliqués

- Lecture brute du corps OpenAI avant décodage, au lieu de `Response::toArray()`.
- Nettoyage des caractères de contrôle au niveau du corps OpenAI et de `output_text`.
- Parseur de secours limité au champ `reply` si le JSON métier OpenAI reste imparfait, afin de conserver la réponse réellement générée.
- Alignement des timeouts HTTP/provider/budget.
- Ajout de `ChatConversationManager::normalize()` pour éviter le crash de fallback technique.

## Fichiers modifiés

- `config/services.yaml`
- `src/Controller/ChatApiController.php`
- `src/Service/Chat/Ai/OpenAiResponsesProvider.php`
- `src/Service/Chat/ChatConversationManager.php`
- `tests/OpenAiResponsesProviderTest.php`

## Validation ERP navigateur

Message testé : PME industrielle, 40 utilisateurs ERP, études, achats, approvisionnements, stocks, production, qualité, ventes, pilotage, mission courte de cadrage et aide au choix, méthodologie, jours, livrables, références, coût.

| Essai | HTTP | Statut | Provider | Type | Latence | CTA | Préremplissage |
|---|---:|---|---|---|---:|---|---|
| 1 | 200 | `llm_primary` | `openai` | `proposal_request` | 18.379 s | OK | OK |
| 2 | 200 | `llm_primary` | `openai` | `proposal_request` | 18.193 s | OK | OK |
| 3 | 200 | `llm_primary` | `openai` | `proposal_request` | 18.416 s | OK | OK |

Les 5 attentes commerciales sont couvertes sur les 3 essais : méthodologie, estimation en jours/charge, livrables, références industrielles, coût/proposition.

## Validation DORA / RFE

| Scénario | HTTP | Statut | Provider | Type | Latence | CTA |
|---|---:|---|---|---|---:|---|
| DORA | 200 | `llm_primary` | `openai` | `contact_offer` | 14.002 s | `Être recontacté par OLING` |
| RFE | 200 | `llm_primary` | `openai` | `contact_offer` | 15.130 s | `Être recontacté par OLING` |

## Tests de non-régression

- `php -l src/Service/Chat/Ai/OpenAiResponsesProvider.php` : OK.
- `php -l src/Controller/ChatApiController.php` : OK.
- `php -l src/Service/Chat/ChatConversationManager.php` : OK.
- `./vendor/bin/phpunit tests/OpenAiResponsesProviderTest.php` : OK, 1 test, 4 assertions.
- `./vendor/bin/phpunit tests/ChatResponderTest.php tests/ChatConversationManagerTest.php` : OK, 109 tests, 361 assertions.
- `npx playwright test -c playwright.chat.config.js tests/e2e/chat-conversion.spec.js --project=chromium-1920 -g "Demande de proposition AMOA ERP"` : OK, 2 tests.

## Limites restantes

- La réponse OpenAI peut encore arriver avec un JSON métier imparfait dans `output_text`; le correctif conserve la réponse générée via extraction de secours du champ `reply`.
- Le parseur de secours ne réinterprète pas toute la qualification OpenAI ; la qualification applicative existante reprend alors le relais à partir de la conversation.
- Aucun déploiement production effectué.
