# Agent IA commercial V2 - implementation locale

Date: 2026-10-08

## 1. Diagnostic architecture actuelle

Avant modification, `ChatResponder` appelait les providers LLM puis basculait sur `HeuristicAiProvider` si OpenAI etait indisponible. Ce provider redigeait des reponses metier/commerciales a partir de regles PHP et de textes preecrits. C'etait contraire a la cible "100 % IA generative" pour les reponses commerciales.

Les garde-fous non redactionnels etaient deja utiles: confidentialite client nomme, routage contact, RAG via `PublicContentCatalog`, filtrage sources, validation JSON.

## 2. Composants heuristiques retires ou limites

- `HeuristicAiProvider` n'est plus tague `app.chat_ai_provider`.
- `ChatResponder` ne l'injecte plus et ne l'appelle plus en fallback.
- Les reponses contact direct et refus de confirmation client nomme restent codees: ce sont des actions/garde-fous, pas du conseil commercial.
- En cas d'echec LLM, aucune reponse metier n'est fabriquee.

## 3. Architecture cible implementee

- LLM: `OpenAiResponsesProvider` reste responsable de la generation finale.
- RAG: `PublicContentCatalog` conserve la recuperation documentaire.
- Contexte: historique, source page, qualification et snippets restent transmis au prompt utilisateur.
- Controles: confidentialite, contact direct, retry borne, indisponibilite transparente.
- Actions: contact OLING direct conserve.

## 4. Nouveau system prompt

Mis a jour dans `data/i18n/ai_consultant.fr.json`, version `v2-generative-sales`.

Principes ajoutes:
- reponses metier/commerciales toujours redigees par le modele;
- LLM + RAG + contexte + connaissance commerciale;
- qualification dynamique non rigide;
- MAPSI seulement si besoin logiciel GRC/conformite;
- prix traite sans inventer;
- objections traitees par raisonnement;
- CTA contextualises, non mecaniques;
- interdiction hallucinations.

## 5. Connaissance OLING

Taxonomie enrichie:
- `crm`, `gmao`, `si_finance`, `rfe`, `pca_pra`, `qse`, `grc_mapsi`.

Intentions enrichies:
- `information`, `demo_logiciel`, `devis`, `contact`.

Le RAG existant est conserve. L'ingestion exhaustive externe reste a industrialiser dans un lot separe: elle necessite un job de synchronisation avec metadata source/date/fiabilite/confidentialite.

## 6. Modifications applicatives

- `src/Service/Chat/ChatResponder.php`: suppression du fallback commercial heuristique, retry LLM x2, indisponibilite transparente.
- `src/Service/Chat/Ai/OpenAiResponsesProvider.php`: log corrige, plus de mention fallback heuristique.
- `config/services.yaml`: `HeuristicAiProvider` non tague comme provider conversationnel.
- `src/Service/Chat/ChatQualificationService.php`: taxonomie commerciale plus fine.
- `data/i18n/ai_consultant.fr.json`: prompt V2 + message d'indisponibilite.
- `src/Command/ChatAuditCommand.php`: banc etendu a 60 scenarios.
- Tests adaptes pour valider "pas de fallback metier".

## 7. Tests 60 scenarios

Commande: `php bin/console app:chat:audit`

Resultat local: OpenAI indisponible par DNS dans l'environnement courant. Le banc 60 scenarios confirme le comportement cible en mode degrade:
- retry borne: 2 tentatives par message;
- provider final: `llm_unavailable` pour les demandes metier;
- contact direct: `contact_router`;
- confidentialite client nomme: `confidentiality_guard`;
- aucune reponse commerciale heuristique generee.

La recette qualitative des 60 scenarios avec modele reel reste a executer lorsque l'acces OpenAI est disponible.

## 8. Avant / apres

Avant:
- echec LLM => reponse heuristique commerciale;
- risque de ton scripté et de conseil approximatif;
- 20 scenarios seulement.

Apres:
- echec LLM => message technique bref + contact OLING;
- aucune redaction metier sans LLM;
- 60 scenarios d'audit;
- prompt V2 oriente consultant avant-vente generatif.

## 9. Validation explicite

Valide localement: aucun fallback heuristique ne produit une reponse metier ou commerciale dans `ChatResponder`.

Commandes executees:
- `php bin/phpunit tests/ChatResponderTest.php`
- `php bin/phpunit tests/ChatCommercialSectorRegressionTest.php`
- `php bin/phpunit tests/ChatQualificationServiceTest.php`
- `php bin/console lint:yaml --parse-tags config/services.yaml`
- `php -l src/Service/Chat/ChatResponder.php`
- `php -l src/Command/ChatAuditCommand.php`
- `php bin/console app:chat:audit`
