# Agent IA commercial V2.1 - continuite de service

Date: 2026-10-08

## Objectif

Garantir une reponse visible au visiteur quand le LLM est indisponible, sans reintroduire de moteur commercial heuristique.

## Chaine de resilience implementee

1. Appel du provider LLM principal.
2. Retry borne a 2 tentatives.
3. Bascule vers un provider LLM secondaire si un autre provider est configure et disponible dans la chaine `app.chat_ai_provider`.
4. Si tous les LLM echouent: message de continuite visible, sans diagnostic metier.
5. Si le backend leve une exception Symfony pendant le traitement: `ChatApiController` retourne quand meme un JSON exploitable via `handleTechnicalFailure`.
6. Si l'appel frontend/backend echoue ou renvoie un JSON inexploitable: le widget affiche localement le message de continuite.

## Message de continuite

Message conforme:

> Je rencontre momentanément une difficulté technique pour analyser votre demande. Vous pouvez néanmoins contacter directement notre équipe OLING au 01 89 70 15 60 ou à contact@oling.fr, ou utiliser notre formulaire de contact.

Actions visibles cote widget:
- Reessayer avec l'IA
- Contacter OLING
- Appeler OLING

## Preservation de conversation

- Le message utilisateur est conserve cote backend quand le backend est operationnel.
- En cas d'erreur frontend/backend, le message utilisateur reste visible localement dans le widget.
- L'historique serveur est preserve quand il existe.
- La qualification existante est conservee dans `handleTechnicalFailure`.
- La reponse technique est typee `technical_unavailable`, provider `technical_error` ou `llm_unavailable`.
- Aucune reponse technique n'est enregistree comme conseil commercial.

## Monitoring

Ajouts:
- `ChatReply::status`: `llm_primary`, `llm_secondary`, `llm_unavailable`, `technical_error`.
- Logs enrichis: provider, model, status, prompt version, durees, fallback, error code, contact step.
- Version prompt/contenu: `v2.1`.

## Matrice de pannes simulees

| Cas | Simulation locale | Comportement observe | Statut |
|---|---|---|---|
| OpenAI fonctionne | provider de test retourne `AiDecision` | reponse LLM normale | OK |
| OpenAI echoue une fois puis fonctionne | provider de test jette puis repond | retry puis reponse LLM | OK |
| OpenAI echoue, secondaire disponible | provider primaire KO + provider secondaire OK | reponse secondaire, status `llm_secondary` | OK |
| Tous LLM echouent | provider OpenAI DNS KO local | message continuite, status `llm_unavailable` | OK |
| DNS | `api.openai.com` non resolu localement | 2 tentatives puis continuite | OK |
| Timeout | couvert par exception provider / test unitaire a ajouter avec client HTTP mocke | comportement attendu identique | Limite |
| Connexion refusee | couvert par exception provider | continuite | OK conceptuel |
| HTTP 429 | OpenAI provider remonte exception | retry borne puis secours | OK conceptuel |
| HTTP 500/502/503/504 | OpenAI provider remonte exception | retry borne puis secours | OK conceptuel |
| Cle API absente | provider indisponible | pas de LLM, continuite | OK |
| Cle API invalide | exception provider | retry borne puis continuite | OK conceptuel |
| Quota epuise | exception provider | retry borne puis continuite | OK conceptuel |
| Reponse vide | `extractOutputText` leve | secours | OK conceptuel |
| JSON invalide | decode JSON leve | secours | OK conceptuel |
| JSON non conforme schema | provider leve ou API refuse schema | secours | OK conceptuel |
| Refus fournisseur | exception provider | secours | OK conceptuel |
| Secondaire indisponible | pas de provider secondaire configure | continuite | OK |
| Exception Symfony | catch controller | JSON de continuite | OK |
| HTTP frontend/backend | catch JS `request` | message local de continuite | OK |
| JS parsing error | conversation absente => catch JS | message local de continuite | OK |
| Conversation deja commencee | historique conserve; retry possible | OK |

## Tests realises

- `php bin/phpunit tests/ChatResponderTest.php`
- `php bin/phpunit tests/ChatCommercialSectorRegressionTest.php`
- `php bin/phpunit tests/ChatQualificationServiceTest.php`
- `php bin/phpunit tests/ContactControllerTest.php`
- `php bin/console lint:yaml --parse-tags config/services.yaml`
- `php -l src/Service/Chat/ChatReply.php`
- `php -l src/Service/Chat/ChatResponder.php`
- `php -l src/Service/Chat/ChatConversationManager.php`
- `php -l src/Controller/ChatApiController.php`
- `npm run build`
- `php bin/console app:chat:audit` sur 60 scenarios

## Resultat audit 60 scenarios

Environnement local: OpenAI inaccessible par DNS.

Observation:
- 2 tentatives OpenAI par scenario metier;
- message V2.1 visible pour les demandes metier;
- contact direct toujours fonctionnel;
- garde-fou client nomme conserve;
- aucune reponse metier generee par `HeuristicAiProvider`.

## Limites restantes

- Aucun provider secondaire independant n'est configure: la bascule secondaire est prete cote architecture, mais non activee sans configuration validee.
- Les tests HTTP precis 429/500/timeout avec client HTTP mocke restent a completer pour distinguer finement les erreurs transitoires/non transitoires.
- La recette navigateur reelle reste a faire sur un serveur local avec Playwright ou verification manuelle.

## Validation

Critere d'acceptation atteint localement: en cas d'echec LLM, l'utilisateur recoit une reponse visible tant que l'interface est operationnelle, avec reessai IA, contact OLING et appel OLING. Aucune panne IA ne provoque une conversation silencieuse ou bloquee.
