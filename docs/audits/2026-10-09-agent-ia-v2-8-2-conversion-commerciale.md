# OLING.FR - Agent IA commercial V2.8.2

Date : 2026-10-09
Environnement : local uniquement, aucun deploiement.

## Verdict

**GO technique local. NO-GO production sans recette LLM reelle.**

## Cause

La dynamique commerciale avait ete affaiblie par deux garde-fous trop stricts :

- la proposition de contact attendait souvent deux messages ou plus ;
- `isReadyForLead()` exigeait encore trop de champs de qualification avant d'autoriser une offre commerciale.

Resultat : l'agent pouvait bien conseiller, mais laissait des demandes professionnelles explicites sans CTA de contact.

## Modifications

### Prompt systeme

Le prompt donne maintenant la priorite a la conversion commerciale :

1. comprendre la demande ;
2. repondre avec expertise ;
3. montrer comment OLING peut accompagner ;
4. proposer un premier echange des qu'une opportunite existe.

Il precise que le budget, le delai, la taille ou l'interlocuteur ne doivent pas bloquer une proposition de contact.

### Orchestration backend

- Contact propose des le premier message si la demande est professionnelle et explicite.
- `contact_offer` n'ouvre pas automatiquement le formulaire.
- `lead_request` ouvre le formulaire uniquement en cas d'intention explicite.
- Bouton de contact contextualise : **Etre recontacte par OLING**.
- Refus explicite de contact respecte.
- Correction de detection pour `demonstration MAPSI`.

### Qualification

Assouplissement controle :

- `identified_need` + besoin + intention commerciale suffisent pour proposer le contact.
- Ajout de signaux : DORA, NIS2, conformite, prestataire, consultant, demonstration, DSI de transition.
- Normalisation texte renforcée pour eviter les erreurs de transliteration accentuee.

## Regles CTA

- Demande professionnelle explicite : CTA contact affiche.
- Demande de contact/devis/rendez-vous : fiche projet ouverte.
- Question purement informative : pas de CTA systematique.
- Refus explicite : pas de relance contact.
- Note PDF et diagnostic restent secondaires.

## Tests premier tour

Scenarios couverts :

- DORA societe de gestion d'actifs financiers ;
- AMOA ERP ;
- DPO externalise ;
- integrateur SAP en difficulte ;
- consultation CRM ;
- ISO 27001 ;
- RFE ;
- DSI de transition ;
- GMAO services publics ;
- NIS2 ;
- demonstration MAPSI.

Le scenario DORA produit bien une reponse avec CTA `open_lead_form`, sans envoi automatique.

## Resultats

- `php -l` : OK
- JSON i18n : OK
- `./vendor/bin/phpunit tests/ChatResponderTest.php ...` : OK, 102 tests ChatResponder, 333 assertions
- `node --check assets/js/chat-widget.js` : OK
- `php bin/console lint:twig ...` : OK
- `npm run test:e2e:chat` : OK, 12 tests Playwright passes

## Limites

- Aucun appel LLM reel OpenAI/Mistral n'a ete certifie dans ce lot.
- Les clics CTA ne sont pas encore persistés serveur de façon distincte ; les formulaires ouverts et leads transmis restent mesurables.
- Validation metier/RGPD finale requise avant production.

## Decision

**GO local pour la dynamique commerciale.**
**NO-GO production publique au 2026-10-09** sans recette LLM reelle et validation metier.
