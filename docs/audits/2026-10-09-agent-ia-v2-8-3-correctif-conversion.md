# OLING.FR - Correctif conversion V2.8.3

Date : 2026-10-09
Portee : local uniquement, aucun deploiement.

## Cause exacte

La reponse backend directe contenait bien l'action `open_lead_form`, mais le widget ne rend pas `reply.actions` : il rend `conversation.messages[].actions`.

Or `ChatConversationManager::serializeConversation()` recalculait les actions a partir du message stocke et ne considerait pas le type `contact_offer` comme une intention de contact. Resultat : une reponse pouvait dire "Je vous propose un premier echange..." sans bouton utilisable dans le DOM.

## Correctifs appliques

- `contact_offer` serialise maintenant une action `open_lead_form` libellee **"Etre recontacte par OLING"**.
- Le CTA reste affiche immediatement sous la reponse agent, avant les sources.
- Les demandes professionnelles explicites DPO externalise et SI Finance declenchent un `contact_offer` des le premier tour.
- Les demandes pedagogiques "DORA, c'est quoi ?" et "Je suis etudiant, definition AMOA" ne declenchent plus automatiquement de CTA.
- Le formulaire chat enrichit le pre-remplissage DORA : domaine, secteur, besoin, objet, contexte.
- Les sources generiques `/projets` sont evitees hors intention reference/secteur explicite.

## Essai live DORA

Essai effectue avec vrai widget, vrai backend et OpenAI `gpt-5.6-sol`.

Resultat :

- `reply.type` : `contact_offer`
- `reply.actions` : `open_lead_form`, "Etre recontacte par OLING"
- `conversation.messages[-1].actions` : `open_lead_form`, "Etre recontacte par OLING"
- provider : `openai`
- statut : `llm_primary`
- CTA visible : oui
- sources affichees : 2
- formulaire ouvert au clic : oui
- telephone : vide
- consentement RGPD : non coche
- aucun envoi automatique

Captures :

- `test-results/live-dora-cta.png`
- `test-results/live-dora-form.png`

## Pre-remplissage DORA verifie

Le formulaire contient :

- Domaine : DORA / resilience operationnelle numerique
- Secteur : societe de gestion d'actifs financiers
- Besoin : accompagnement a la conformite DORA
- Objet : premier echange avec un consultant OLING
- Contexte : cadrer le perimetre et la demarche de mise en conformite

Identite, taille, urgence et budget ne sont pas inventes.

## Tests

- `php -l src/Service/Chat/ChatConversationManager.php`
- `php -l src/Service/Chat/ChatResponder.php`
- `node --check assets/js/chat-widget.js`
- `node --check tests/e2e/chat-conversion.spec.js`
- `./vendor/bin/phpunit tests/ChatResponderTest.php tests/ChatConversationManagerTest.php tests/MistralChatProviderTest.php` : OK, 107 tests, 346 assertions
- `npm run build` : OK, navigateur charge `public/build/app.d3bb2cf8.js`
- `npm run test:e2e:chat` : OK, 20 tests Playwright

## Limites restantes

L'essai live a necessite de masquer la banniere cookies dans le navigateur de test pour pouvoir cliquer le widget. Ce n'est pas un defaut du CTA chat, mais un point d'ergonomie a garder en tete pour les tests automatises hors parcours cookies.
