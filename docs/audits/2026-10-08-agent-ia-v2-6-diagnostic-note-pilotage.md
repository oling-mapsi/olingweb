# OLING.FR — Agent IA commercial V2.6

## 1. Architecture V2.6

Parcours conservé dans le widget existant : conversation IA, actions structurées, génération LLM, formulaire lead RGPD et admin interne.

## 2. Modifications réalisées

- Actions : `start_diagnostic`, `generate_scoping_note`, `download_scoping_note`, `open_lead_form`.
- API message : renvoi des actions structurées du backend.
- Widget : rendu des boutons, prompts cadrés, PDF local, formulaire prérempli.
- Admin : dashboard commercial agrégé dans `/admin/chat`.
- Tests : 30 scénarios commerciaux V2.6 et parcours Maison&Objet.

## 3. Mini-diagnostic IA

Le diagnostic reste piloté par le LLM. Le clic `start_diagnostic` envoie une consigne de cadrage demandant des questions utiles, sans reposer les informations déjà fournies.

## 4. Note de cadrage

Le clic `generate_scoping_note` demande au LLM une note structurée : contexte, objectifs, périmètre, risques, scénarios, recommandations, rôle OLING et prochaines étapes.

## 5. Génération PDF

Le bouton `download_scoping_note` produit un PDF local côté navigateur à partir de la dernière note IA. Pas d’URL publique permanente.

## 6. Transmission commerciale

La transmission passe toujours par `open_lead_form`, avec relecture, coordonnées obligatoires et consentement RGPD. Aucun envoi automatique.

## 7. Dashboard interne

Ajout d’indicateurs agrégés dans l’admin : conversations engagées, besoins, diagnostics, notes, formulaires ouverts, leads, taux de conversion, domaines et performance LLM.

## 8. Dictionnaire des événements

Événements sans texte libre : `chat_opened`, `need_identified`, `diagnostic_started`, `scoping_note_generated`, `scoping_note_downloaded`, `lead_form_opened`, `lead_form_submitted`, `lead_submission_confirmed`, `chat_cta_clicked`, `chat_source_clicked`, `chat_llm_unavailable`.

## 9. Analyse RGPD

Aucune conversation complète n’est envoyée vers GA/GTM. Les événements front ne contiennent pas de texte libre. Les données admin restent internes et agrégées.

## 10. Résultats des tests

- `php -l` OK.
- `node --check assets/js/chat-widget.js` OK.
- `./vendor/bin/phpunit tests/ChatResponderTest.php` : 88 tests, 269 assertions.
- `npm run build` OK.
- `npm run test:e2e:chat` : 12 tests passés.

## 11. Captures desktop/mobile

Les captures Playwright sont générées dans `test-results/` par la recette E2E.

## 12. Limites et risques

Le PDF est sobre et local. Il ne stocke pas encore une version serveur sécurisée rattachée à un identifiant temporaire.

## 13. Backlog résiduel

- PDF serveur charté complet avec logo et purge dédiée.
- Journalisation dédiée des accès au dashboard.
- Événement explicite `diagnostic_completed` si un état conversationnel dédié est ajouté.

## 14. Décision GO/NO-GO

GO local pour recette V2.6. NO-GO production tant que le PDF serveur sécurisé et la validation RGPD finale ne sont pas arbitrés.
