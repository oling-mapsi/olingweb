# Cyber Prod Deployment - Phase 2 Etape 1

Date: 2026-09-03.

## A. BUSINESS FIXES REVIEW

P0 BLOCKING PROD: aucun.

P1 NON BLOCKING:
- Preuves experts cyber encore limitees.
- Reference DORA conservee anonymisee car publication nominative non validee.
- Publication DB prod non realisee: pas d'API admin automatisable et pas d'autorisation SQL prod separee.

P2 FUTURE IMPROVEMENT:
- Enrichir preuves experts cyber.
- Ajouter captures visuelles 1440/390 avec outil navigateur disponible.

## B. PROD BEFORE

Prod avant DB:
- commit: `1234111ed2ea6b1152bb9818a228c2fffaf02ae5`
- title: `Conseil cyber securite | Gouvernance SSI, risques et conformite | OLING`
- meta: `Conseil cyber securite pour secteur public, PME et ETI : gouvernance SSI, reduction des risques, continuite d activite et conformite ISO 27001 NIS2 DORA.`
- hero_badge: `Cyber securite`
- hero_title: `Conseil cyber securite : proteger les SI critiques et la conformite`
- hero_side_html: len `3596`, sha `ac74efc926042343d356cd4f82d8612a19c1d9bf3edd729de2bb907d800f496c`
- body_html: len `575`, sha `37ef5730c1241031d913e0514343bf66b85f4d41a953756f87170d4224bab37a`

## C. LOCAL TARGET

Local cible:
- title: `Cybersécurité : gouvernance, ISO 27001, NIS2 & DORA | OLING`
- meta: `OLING accompagne les organisations dans la gouvernance cybersécurité, analyse des risques, ISO 27001, NIS2, DORA, PCA/PRA et mise en oeuvre des plans de sécurité.`
- hero_badge: `Cybersécurité & gouvernance SSI`
- hero_title: `Conseil cybersécurité : gouvernance, risques et conformité`
- canonical: `https://oling.fr/cyber-securite`
- hero_side_html: len `5740`, sha `69308ba6ea098a5a81b761c65f073891eab66f3098651eb0d88c7c269cc2976b`
- body_html: len `1308`, sha `7cc3d01e4005331a0abe49b55c18092861e7aa59803bbe04756fedd5a7884c0f`

## D. BACKUP

CYBER BACKUP: VERIFIED.

Backup prod:
- `/home/ubuntu/backups/oling/cyber-phase2/20260903-131222/site_page.sql`
- `/home/ubuntu/backups/oling/cyber-phase2/20260903-131222/site_page_revision.sql`
- `/home/ubuntu/backups/oling/cyber-phase2/20260903-131222/site_page.cyber-securite.sql`
- SHA256 verification: OK.

## E. CODE COMMIT

Commit code pousse et deploye:
`e4ed9dc1547bc08b612bc1ed9b0384d795679c95` - `SEO phase 2 cyber landing`.

## F. DEPLOY METHOD

Prod code:
`sudo -n -u deploy git pull --ff-only origin main`

Resultat: fast-forward OK, prod HEAD `e4ed9dc1547bc08b612bc1ed9b0384d795679c95`, worktree clean.

## G. DB PUBLICATION METHOD

Publication DB prod stoppee.

Raison: source de verite DB/admin; aucune API admin automatisable identifiee; pas d'autorisation SQL prod separee. Payload exact prepare hors Git:
`/private/tmp/oling-cyber-site-page-target.json`

SHA256:
`45ec48fdaa264499203242bbcefe00ed9b948287f12017821929420aecbec855`

## H. DB AFTER

DB prod non modifiee. Donc:
LOCAL CYBER != PROD CYBER.

## I. SEO BEFORE / AFTER

Apres deploiement code seul:
- HTTP `/cyber-securite`: 200.
- Canonical: `https://oling.fr/cyber-securite`.
- Title/meta/H1 restent ceux de prod avant DB.
- CTA code visible: `Évaluer mon projet cybersécurité`.
- FAQ visible et schema FAQ present.

## J. DESKTOP

Controle navigateur 1440 non execute: Playwright absent du projet et aucun navigateur connecte utilisable dans ce run.

## K. MOBILE

Controle navigateur 390 non execute pour la meme raison.

## L. FAQ

FAQ prod visible, schema FAQ present. Contenu enrichi non publie tant que DB prod non alignee.

## M. CTA

CTA prod visible et cliquable vers `/contact`: `Évaluer mon projet cybersécurité`.

## N. INTERNAL LINKS

Liens internes prod existants OK. Liens enrichis cibles non publies tant que DB prod non alignee.

## O. STRUCTURED DATA

Prod `/cyber-securite`: 3 scripts JSON-LD. Organization / Service / FAQ presents.

## P. SITEMAP

`presta:sitemap:dump --env=prod` execute avec utilisateur `deploy`: OK.
`https://oling.fr/cyber-securite` present une seule fois.

## Q. CHAT

Smoke chat prod:
- `POST /api/chat/conversations`: `302` vers `/login`.
- Message non teste car creation non disponible publiquement sans session.
- Aucun fichier Chat modifie.

## R. NON REGRESSION

HTTP 200 confirme:
`/`, `/amoa-si`, `/erp-progiciel`, `/crm`, `/gmao`, `/si-finance`, `/facturation-electronique-amoa`, `/rgpd`, `/conseil-qualite`, `/mapsi-progiciel`, `/cyber-securite`.

Title/meta/canonical/H1 inchanges sur les pages controlees, hors effet CTA template cyber.

## S. REMAINING P1/P2

P1:
- Publier la DB via admin ou autoriser explicitement un SQL prod controle.
- Rejouer comparaison checksum local/prod apres publication DB.
- Refaire smoke chat avec session ou corriger exposition API si elle doit etre publique.

P2:
- Captures navigateur desktop/mobile.
- Renforcer preuves experts cyber.

CYBER PROD DEPLOYMENT:
PASS WITH FIXES

LOCAL/PROD CYBER CONTENT:
NOT ALIGNED

CYBER BUSINESS READINESS:
PASS WITH FIXES

SAFE TO START NEXT LANDING:
NO
