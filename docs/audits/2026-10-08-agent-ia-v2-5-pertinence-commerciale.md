# OLING.FR — Agent IA commercial V2.5

Date : 2026-10-08

## Objectif

Améliorer la pertinence documentaire, l’efficacité commerciale et le design conversationnel, local uniquement, sans modifier l’architecture LLM ni le mécanisme `open_lead_form`.

## Design conversationnel

- Fond conversation repassé en blanc pur `#FFFFFF`.
- Suppression de la bulle/carte autour des réponses IA : plus de bordure, ombre, fond coloré ou radius sur le corps assistant.
- Texte IA renforcé : `#101820`, titres `#0B1220`, liens `#155A91`, séparateurs `#E5E7EB`.
- Largeur desktop V2.4 conservée autour de 520 px.
- Message visiteur conservé en bulle discrète.

## Pertinence des liens

- Les documents RAG restent transmis au LLM.
- Les liens visibles au visiteur passent désormais par un score de recommandation séparé.
- Le filtre distingue :
  - sources documentaires internes ;
  - liens utiles affichés ;
  - action commerciale `open_lead_form`.
- Maximum 2 liens visibles.
- Aucun lien est accepté si le score sémantique est insuffisant.
- Gestion des négations simples, ex. `pas de sujet CRM`.
- Correction de cas accentués translittérés par PHP, ex. `recontacté`, `accompagné`.

## Scoring ajouté

Signaux utilisés :

- besoin principal qualifié ;
- thèmes commerciaux détectés : ERP, CRM, GMAO, SIRH, finance, RFE, RGPD, cyber, QSE, MAPSI, DSI, IA ;
- type du document ;
- score RAG initial ;
- intention référence/expert ;
- blocages sémantiques simples, ex. DPO/RGPD vs cybersécurité générale, ERP vs CRM hors intention.

## Conversion commerciale

- `open_lead_form` conservé.
- Le contact fort ne débouche pas sur des liens documentaires : la prochaine étape reste le contact/formulaire.
- Pas de modification du formulaire lead.
- Pas de réponse métier PHP préécrite.

## Banc de tests

Ajout de 40 scénarios de pertinence documentaire dans `tests/ChatResponderTest.php`.

Couverture :

- ERP, CRM, GMAO, SIRH, finance, facturation électronique, RGPD, DPO, ISO 27001, NIS2, QSE, MAPSI, DSI, transformation, IA.
- Situations mixtes.
- Contact direct.
- Demandes vagues.
- Objections.
- Cas sans lien pertinent.

Critères vérifiés :

- 0 à 2 liens maximum.
- Aucun lien hors liste sémantiquement attendue.
- Aucun `127.0.0.1`.
- Capacité à n’afficher aucun lien.
- Conservation des tests historiques ChatResponder.

## UX/UI Playwright

Les tests V2.4 ont été adaptés au rendu V2.5 :

- panneau blanc ;
- réponse IA transparente ;
- absence de bordure autour de la réponse IA ;
- largeur desktop ;
- sources compactes ;
- CTA visible ;
- formulaire prérempli Maison&Objet.

Captures générées :

- `test-results/chat-v2-4-chromium-1920.png`
- `test-results/chat-v2-4-chromium-1366.png`
- `test-results/chat-v2-4-chromium-tablet.png`
- `test-results/chat-v2-4-chromium-mobile.png`

## Résultats

- `php -l src/Service/Chat/ChatResponder.php` : OK.
- `php -l tests/ChatResponderTest.php` : OK.
- `node --check assets/js/chat-widget.js` : OK.
- `./vendor/bin/phpunit tests/ChatResponderTest.php` : OK, 58 tests, 239 assertions.
- `npm run build` : OK.
- `npm run test:e2e:chat` : OK, 8 tests.

## Limites

- Le scoring reste explicite et conservateur, sans recours à un second appel LLM.
- La bibliothèque de preuves commerciales complète reste à enrichir côté contenu indexé.
- Les indicateurs analytics avancés ne sont pas ajoutés ici pour éviter tout risque RGPD ou collecte non cadrée.
- Le build conserve l’avertissement existant `Browserslist: caniuse-lite is outdated`.

## Décision

GO local V2.5.

La présentation est revenue à une conversation ouverte, les liens visibles sont mieux filtrés, et les parcours V2.3/V2.4 restent validés.
