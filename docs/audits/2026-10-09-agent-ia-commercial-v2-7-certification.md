# OLING.FR - Agent IA commercial V2.7

Date : 2026-10-09
Perimetre : finalisation PDF, securite RGPD, dashboard, recette E2E, certification avant production.

## Verdict

**NON CERTIFIE PRODUCTION.**

Le socle local V2.7 est fonctionnel et les tests automatises passes valident les points techniques principaux. Le passage en production reste bloque tant que les controles suivants ne sont pas faits en conditions reelles : recette qualitative multi-LLM avec appels fournisseurs actifs, validation RGPD/DPO, revue finale des acces au dashboard, et validation metier des notes de cadrage generees.

## Synthese

- PDF serveur : **OK local**.
- Stockage prive des notes : **OK local**.
- Lien de telechargement borne a la conversation et au jeton de note : **OK local**.
- Purge des PDF expires : **OK local**.
- Widget et actions structurees : **OK local**.
- Dashboard admin : **partiel**, indicateurs presents mais controle d'acces final non certifie dans cette recette.
- Email commercial reel : **non declenche**, conforme a la contrainte de recette locale.
- Production : **NO-GO**.

## Corrections V2.7 realisees

### 1. Generation PDF cote serveur

Ajout de `App\Service\Chat\ScopingNotePdfService`.

La note de cadrage est maintenant generee cote serveur en PDF via un template Twig dedie :

- `templates/chat/scoping_note_pdf.html.twig`
- stockage dans `var/private/chat_scoping_notes/`
- nommage telechargeable `note-cadrage-oling-{besoin}.pdf`
- contenu structure en sections projet, contexte, objectifs, SI cible, risques, trajectoire et prochaines etapes
- disclaimer integre : document exploratoire, non contractuel, a valider par OLING

Le frontend ne fabrique plus de faux PDF en JavaScript.

### 2. Telechargement securise

Ajout des routes :

- `POST /api/chat/conversations/{token}/scoping-note`
- `GET /api/chat/conversations/{token}/scoping-notes/{noteToken}/download`

Le telechargement verifie :

- existence de la conversation
- jeton de note aleatoire
- association note/conversation
- date d'expiration
- absence de cache navigateur
- stockage hors repertoire public

Un acces croise entre conversations est rejete par test unitaire.

### 3. Integration widget

Le bouton `Telecharger la note de cadrage` appelle maintenant le backend, puis ouvre l'URL temporaire de telechargement renvoyee par le serveur.

Le mecanisme conserve les actions structurees deja introduites en V2.6 :

- `start_diagnostic`
- `generate_scoping_note`
- `download_scoping_note`
- `open_lead_form`

### 4. Purge

La commande `app:chat:purge-expired` purge aussi les notes PDF expirees et leurs metadonnees.

### 5. Donnees personnelles et RGPD

Points valides localement :

- pas de donnees personnelles injectees dans une URL de contact
- PDF stocke hors `public/`
- telechargement soumis a jetons serveur
- pas d'envoi automatique au commercial
- formulaire de contact toujours soumis a validation explicite et consentement RGPD

Points restant a valider avant production :

- duree de conservation cible a confirmer juridiquement
- mention RGPD finale a relire par DPO/conseil
- politique d'acces admin dashboard a certifier
- journalisation/audit des telechargements a arbitrer

## Recette scenarios commerciaux

### Scenario 1 - Maison&Objet

Objectif : transformation SI commercial, remplacement CRM, architecture cible, besoin d'AMOA independante.

Statut : **OK automatise local**, via tests Playwright du parcours widget desktop/mobile et tests backend.
Limite : contenu LLM reel non certifie pendant cette recette.

### Scenario 2 - Sage X3

Objectif : besoin ERP/Sage X3, cadrage metier et orientation expertise.

Statut : **OK automatise local** sur lisibilite widget, actions et parcours.
Limite : qualification commerciale reelle a rejouer avec fournisseur LLM actif.

### Scenario 3 - Diagnostic IA / note de cadrage

Objectif : mini-diagnostic, generation note, telechargement PDF serveur.

Statut : **OK local**.
Limite : validation humaine requise sur la qualite du document et sa conformite commerciale.

## Tests executes

- `php -l src/Service/Chat/ScopingNotePdfService.php` : OK
- `php -l src/Controller/ChatApiController.php` : OK
- `php -l src/Command/PurgeChatConversationsCommand.php` : OK
- `./vendor/bin/phpunit tests/ScopingNotePdfServiceTest.php` : OK, 3 tests, 5 assertions
- `./vendor/bin/phpunit tests/ChatResponderTest.php` : OK, 88 tests, 269 assertions
- `node --check assets/js/chat-widget.js` : OK
- `php bin/console lint:twig templates/chat/scoping_note_pdf.html.twig templates/includes/_chat_widget_v2.html.twig` : OK
- `php bin/console debug:router api_chat_conversation_scoping_note` : OK
- `npm run build` : OK
- `npm run test:e2e:chat` : OK, 12 tests Playwright passes

## Reserves

1. La generation PDF repose sur la derniere note de cadrage produite par l'agent ; elle ne remplace pas une validation humaine.
2. Le PDF n'est pas encore attache automatiquement a un lead transmis au commercial.
3. La recette n'a pas declenche d'email commercial reel.
4. La recette n'a pas certifie la qualite de sortie d'un appel OpenAI/Mistral reel.
5. Le dashboard admin existe, mais la certification production doit inclure une revue d'habilitation.

## Decision

**GO technique local pour poursuivre la pre-production.**
**NO-GO production publique au 2026-10-09.**

Conditions minimales de levee du NO-GO :

- rejouer les trois scenarios avec LLM reel et traces conservees
- valider les mentions RGPD et la duree de conservation
- confirmer les droits d'acces au dashboard
- valider metierement un echantillon de PDF
- tester l'envoi lead complet sans envoi automatique non consenti
