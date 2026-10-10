# OLING.FR — Agent IA commercial V2.6

## Réalisé localement

- Ajout des actions structurées `start_diagnostic`, `generate_scoping_note`, `download_scoping_note`, `open_lead_form`.
- Le widget rend ces actions en boutons, sans URL technique visible.
- Le mini-diagnostic et la note restent générés par l'IA via messages cadrés, sans questionnaire PHP figé.
- La note de cadrage peut être téléchargée en PDF local, généré côté navigateur depuis la session.
- La fiche projet du formulaire reprend aussi la dernière synthèse diagnostic/note, pour éviter la ressaisie.
- Tracking étendu avec événements sans texte libre : ouverture chat, diagnostic, note, lead form, source.
- Tests ajoutés : 30 scénarios V2.6 et parcours E2E Maison&Objet desktop/mobile/tablet.

## Sécurité

Aucune donnée personnelle n'est placée dans l'URL. Aucun envoi automatique n'est ajouté ; la transmission passe toujours par le formulaire RGPD existant.

## Recette

- `php -l src/Service/Chat/ChatReply.php`
- `php -l src/Service/Chat/ChatResponder.php`
- `php -l src/Service/Chat/ChatConversationManager.php`
- `node --check assets/js/chat-widget.js`
- `./vendor/bin/phpunit tests/ChatResponderTest.php` : 88 tests, 269 assertions
- `npm run build`
- `npm run test:e2e:chat` : 12 tests passés
