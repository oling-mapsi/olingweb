# OLING.FR - Agent IA commercial V2.8

Date : 2026-10-09
Environnement : local uniquement, aucun deploiement.

## Verdict

**GO technique local. NO-GO production tant que la recette LLM reelle et la validation RGPD/metier ne sont pas faites.**

## Cause du comportement trop passif

L'agent attendait une qualification trop complete avant de proposer un contact : besoin, urgence, maturite, type d'organisation et intention commerciale. Cela bloquait des opportunites deja exploitables, notamment quand le prospect avait decrit un besoin metier mais pas encore son budget ou son organisation.

Deux autres causes ont ete corrigees :

- les demandes explicites de contact etaient traitees comme une simple demande d'information ;
- le RAG et les liens etaient evalues surtout sur le dernier message, ce qui pouvait faire perdre le contexte principal.

## Modifications principales

- Ajout d'une progression commerciale : `exploration`, `identified_need`, `qualified_opportunity`, `intent_contact`.
- Assouplissement du seuil de lead : une opportunite qualifiee n'a plus besoin de tous les champs de qualification.
- Demande explicite de contact : ouverture immediate de la fiche projet via `open_lead_form`.
- Proposition commerciale proactive : note PDF + transmission OLING lorsque le besoin est assez caracterise.
- Contact proactif : le formulaire n'est plus ouvert automatiquement ; le visiteur choisit l'action.
- Le RAG exploite maintenant le contexte conversationnel, pas seulement le dernier message.
- Les liens RFE privilegient `/facturation-electronique-amoa`, puis `/si-finance`.
- Le prompt systeme demande une decision commerciale structuree sans exposer les donnees techniques.
- Le schema OpenAI accepte `commercial_progression` avec action recommandee.

## Scenario RFE corrige

Conversation testee :

- `accompagnement amoa SI finance ?`
- `un cadrage en amont`
- `la rfe`
- `salesforce`
- `dolibarr`

Resultat attendu obtenu :

- besoin principal conserve : `si_finance` ;
- pas de requalification CRM malgre Salesforce ;
- reponse centree RFE, flux, donnees, interfaces, plateforme agreee ;
- actions affichees : `Préparer ma note de cadrage PDF` et `Transmettre mon projet à OLING` ;
- lien prioritaire : `/facturation-electronique-amoa`.

Si le visiteur ecrit ensuite `Je veux vous contacter`, la fiche projet est ouverte directement et la synthese mentionne AMOA SI Finance, cadrage RFE, Salesforce, Dolibarr, flux/interfaces et trajectoire de mise en conformite.

## Sources RFE verifiees

Controle effectue sur les sources DGFiP :

- `https://www.impots.gouv.fr/facturation-electronique-et-plateformes-agreees`
- `https://www.impots.gouv.fr/specifications-externes-b2b`

Points retenus :

- la terminologie actuelle est `plateforme agréée` ;
- les plateformes agréées assurent les fonctions centrales de facturation électronique et e-reporting ;
- les specifications externes B2B V3.2 du 30/04/2026 couvrent annuaire, declaration, donnees de facturation, transaction et paiement ;
- le PPF ne doit plus etre presente comme un choix equivalent a une plateforme agreee pour les entreprises.

## Dashboard

Ajout de metriques agregees minimales :

- opportunites qualifiees ;
- propositions de contact ;
- propositions de note PDF ;
- notes generees ;
- formulaires ouverts ;
- leads transmis ;
- moyenne d'echanges avant premiere proposition de contact ;
- taux opportunite qualifiee vers lead.

Aucune donnee personnelle ni contenu complet de message n'est ajoute aux evenements analytiques.

## Tests executes

- `php -l` sur les fichiers modifies : OK
- `json_decode(data/i18n/ai_consultant.fr.json)` : OK
- `./vendor/bin/phpunit tests/ChatResponderTest.php tests/ChatQualificationServiceTest.php tests/ScopingNotePdfServiceTest.php` : OK, 90 tests ChatResponder + 4 qualification
- `./vendor/bin/phpunit tests/MistralChatProviderTest.php` : OK
- `./vendor/bin/phpunit tests/HeuristicAiProviderTest.php tests/ChatCommercialSectorRegressionTest.php` : OK
- `node --check assets/js/chat-widget.js` : OK
- `php bin/console lint:twig templates/admin/chat/index.html.twig templates/includes/_chat_widget_v2.html.twig` : OK
- `npm run test:e2e:chat` : OK, 12 tests Playwright passes

## Limites

- Pas de recette OpenAI/Mistral live executee dans ce lot local.
- Pas de deploiement production.
- Validation RGPD/DPO et validation metier des formulations RFE encore necessaires avant production.
- Les tests de progression commerciale sont majoritairement mockes/heuristiques ; une recette conversationnelle reelle reste requise.

## Decision

**GO local pour poursuivre.**
**NO-GO production publique au 2026-10-09** sans recette LLM reelle, validation RGPD et validation commerciale finale.
