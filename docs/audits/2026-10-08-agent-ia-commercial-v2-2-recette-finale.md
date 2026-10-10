# OLING.fr — Agent IA commercial V2.2 — Recette finale

Date : 2026-10-08

## 1. Synthèse

V2.2 consolide l’existant V2/V2.1 sans refonte du widget :

- transmission commerciale depuis le chat sans ressaisie du besoin ;
- fiche projet préremplie et éditable avant consentement ;
- téléphone rendu optionnel ;
- observabilité commerciale côté `dataLayer`, sans contenu de message ni donnée personnelle ;
- architecture multi-provider réelle : OpenAI primaire, Mistral secondaire si configuré ;
- budget global de latence configuré à 28 s ;
- continuité visible si panne LLM/API.

## 2. Fichiers modifiés

- `assets/js/chat-widget.js`
- `templates/includes/_chat_widget_v2.html.twig`
- `src/Service/Chat/ChatConversationManager.php`
- `src/Service/Chat/ChatResponder.php`
- `src/Service/Chat/Ai/MistralChatProvider.php`
- `config/services.yaml`
- `data/i18n/ai_consultant.fr.json`
- `public/build/entrypoints.json`
- `public/build/manifest.json`
- `public/build/app.7040b617.js`
- `public/build/app.7040b617.js.LICENSE.txt`
- `tests/MistralChatProviderTest.php`

## 3. Architecture finale

- Réponses métier : uniquement providers IA taggés `app.chat_ai_provider`.
- OpenAI : priorité 100, endpoint Responses API.
- Mistral : priorité 50, endpoint `/chat/completions`, désactivé sans `MISTRAL_API_KEY`.
- Heuristique : conservée hors réponses commerciales, non taggée.
- Panne totale : réponse de continuité `technical_unavailable` + retry côté widget.
- Lead : envoi via le système existant `ChatConversationManager::submitLead()`.

Variables :

- `OPENAI_API_KEY`
- `CHAT_AI_OPENAI_MODEL`
- `CHAT_AI_OPENAI_BASE_URL`
- `MISTRAL_API_KEY`
- `MISTRAL_MODEL`
- `MISTRAL_BASE_URL`
- `chat_ai_global_latency_budget_ms = 28000`

## 4. Tests PHPUnit

OK :

- `php bin/phpunit tests/ChatResponderTest.php` : 17 tests, 46 assertions.
- `php bin/phpunit tests/ChatCommercialSectorRegressionTest.php` : 25 tests, 74 assertions.
- `php bin/phpunit tests/ChatQualificationServiceTest.php` : 3 tests, 18 assertions.
- `php bin/phpunit tests/ContactControllerTest.php` : 5 tests, 17 assertions.
- `php bin/phpunit tests/MistralChatProviderTest.php` : 3 tests, 7 assertions.

Contrôles OK :

- `php -l` sur PHP modifiés.
- `node --check assets/js/chat-widget.js`.
- `php bin/console lint:yaml --parse-tags config/services.yaml`.
- `npm run build` OK, avertissement non bloquant `Browserslist: caniuse-lite is outdated`.

## 5. Tests Playwright

Non exécutés : Playwright n’est pas installé dans le dépôt (`require('playwright')` échoue) et aucun `playwright.config.*` n’est présent.

Recette navigateur à exécuter dès installation :

- desktop, tablette, mobile ;
- ouverture widget ;
- premier message ;
- réponse IA ;
- Markdown/liens/sources ;
- multi-tour ;
- navigation et restauration historique ;
- formulaire prérempli ;
- retry après panne ;
- erreurs backend/réseau/JSON/timeout.

## 6. Matrice pannes

| Cas | Résultat local |
|---|---|
| OpenAI indisponible | réponse continuité visible |
| OpenAI KO puis secondaire configuré | bascule prévue vers Mistral |
| Mistral absent | provider désactivé |
| Deux providers KO | `llm_unavailable` |
| JSON invalide provider | exception capturée, provider suivant |
| Budget global dépassé | arrêt boucle, continuité |
| Erreur API frontend | message local de continuité + retry |
| Soumission lead KO | confirmation non affichée |

## 7. 60 scénarios

Commande exécutée : `php bin/console app:chat:audit`.

Résultat local : OpenAI inaccessible DNS (`Could not resolve host: api.openai.com`). Les scénarios métier retournent donc `llm_unavailable`; les gardes contact/confidentialité restent OK.

Conclusion : la recette réelle LLM n’est pas certifiable dans cet environnement réseau. Le comportement de continuité est certifié localement.

## 8. 15 parcours multi-tours

Non certifiés en navigateur faute de Playwright. La persistance conversationnelle et la continuité sont couvertes par le flux backend/widget existant, mais la recette multi-tour réelle doit être rejouée avec accès LLM.

## 9. Maison&Objet

Non certifié avec modèle réel à cause DNS OpenAI. Le prompt V2/V2.1 interdit l’invention de montant et demande une orientation AMOA indépendante. À rejouer avec le scénario :

- CRM ;
- plans salon ;
- facturation ;
- outils digitaux ;
- diagnostic déjà fait ;
- demande intervention/budget.

## 10. Chat vers formulaire

Implémenté :

- CTA : “Transmettre mon projet à OLING”.
- Fiche préremplie avec objet, expertise, intention, contexte, objectifs, situation, urgence, prochaine étape, page source.
- Fiche éditable avant envoi.
- Champs obligatoires : nom, email professionnel, organisation, fiche projet, consentement.
- Téléphone optionnel.
- Confirmation affichée seulement après réponse serveur OK.
- Canal d’envoi existant conservé.

## 11. Résilience multi-LLM

Implémenté :

- `OpenAiResponsesProvider` primaire.
- `MistralChatProvider` secondaire.
- Pas de clé en dur.
- Mistral inactif sans `MISTRAL_API_KEY`.
- Provider visible dans le conteneur taggé `app.chat_ai_provider`.
- Budget global : 28 s.

## 12. RAG

Amélioration structurelle déjà présente : sélection par documents publics, déduplication URL, filtrage sources affichées, priorité références/secteurs selon intention.

Reste à faire : recette documentaire par domaine avec corpus réel et scores exportés :

- AMOA ERP, CRM, SI Finance, SIRH, GMAO ;
- facturation électronique ;
- DSI externalisée ;
- RGPD/DPO ;
- ISO 27001, NIS2/DORA ;
- PCA/PRA ;
- QSE ;
- MAPSI.

## 13. Sécurité/RGPD

OK local :

- CSRF conservé.
- Consentement RGPD obligatoire.
- Anti-spam existant conservé.
- Pas de tracking des messages complets dans `dataLayer`.
- Pas de fuite client nommé : garde confidentialité.
- Logs techniques sans clé API.

## 14. Problèmes résiduels

- Pas de certification modèle réel sans DNS/API.
- Pas de screenshots Playwright faute dépendance.
- Mistral non testé contre API réelle sans clé.
- Score commercial 85/100 non calculable sans réponses LLM réelles.
- RAG à auditer sur corpus de production complet.

## 15. Backlog

- Installer Playwright et ajouter suite E2E widget.
- Exporter les 60 réponses réelles en JSON horodaté.
- Ajouter scoring automatique commercial.
- Ajouter ingestion contrôlée de sources publiques autorisées avec métadonnées droits/fraîcheur.

## 16. Décision

GO technique local pour V2.2 : continuité, lead sans ressaisie, multi-provider configurable, build et tests ciblés OK.

NO-GO pour certification commerciale finale tant que la recette navigateur Playwright et les 60 scénarios avec modèle réel ne sont pas exécutés dans un environnement ayant accès aux APIs LLM.
