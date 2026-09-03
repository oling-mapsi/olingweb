# OLING.FR - Cyber Phase 2

Date: 2026-09-03. Perimetre: local uniquement. Aucun deploiement production.

## A. CURRENT STATE

Baseline confirmee: `1234111ed2ea6b1152bb9818a228c2fffaf02ae5`. La page `/cyber-securite` existait, indexable, avec title/meta/canonical coherents, mais contenu trop peu profond pour porter le positionnement cyber.

## B. EXISTING URL MAP

| URL | Role | Statut | Source |
| --- | --- | --- | --- |
| `/cyber-securite` | Master cyber conseil/gouvernance/conformite | 200 | `site_page` |
| `/expertises-audit/si` | Offre service SSI/ISO/NIS2/DORA | 200 | `services` |
| `/rgpd` | Soutien data/privacy | 200 | `site_page` |
| `/mapsi-progiciel` | Soutien outil GRC/pilotage | 200 | `site_page` |
| `/ressource-feuille-route-cyber-pme-eti` | Ressource cyber | published | `site_page` |
| `/ressource-nis2-dora-par-ou-commencer` | Ressource conformite | published | `site_page` |
| `/ressource-pca-pra-structurer-la-continuit-viter-les-illusions` | Ressource resilience | published | `site_page` |

## C. CANNIBALIZATION

Risque faible si `/cyber-securite` reste master. `/expertises-audit/si` doit rester page service, pas page pilier. Pas de 301 appliquee.

## D. MASTER URL

Master retenue: `/cyber-securite`.

## E. COMPETITOR BENCHMARK

Sources consultees: Netsystem `projet-cybersecurite`, `NIS2`, `RSSI externalise`, `TRUST`, `ISO 27001`; Shift home, strategie/gouvernance SI, data/IA.

| Dimension | OLING apres | Netsystem | Shift |
| --- | ---: | ---: | ---: |
| Clarity | 8 | 9 | 8 |
| Expertise | 8 | 9 | 8 |
| Proof | 7 | 9 | 8 |
| Method | 8 | 8 | 8 |
| Deliverables | 8 | 8 | 7 |
| Experts | 6 | 9 | 8 |
| CTA | 8 | 9 | 7 |
| SEO depth | 8 | 9 | 7 |
| AI readability | 8 | 8 | 8 |
| Credibility | 8 | 9 | 8 |

Conclusion: OLING doit assumer un angle plus conseil, gouvernance, conformite, risques, continuite, AMOA, sans imiter SOC/pentest.

## F. SEARCH INTENTS

Couverture ajoutee: conseil cybersecurite, cabinet cybersecurite, gouvernance SSI, SMSI, PSSI, ISO 27001, NIS2, DORA, PCA/PRA, BIA, RTO/RPO, plan de traitement, preuves de conformite.

## G. POSITIONING

Message applique: OLING securise les systemes d'information et les organisations face aux risques cyber et aux exigences reglementaires.

## H. TITLE / META / H1

- Title: `Cybersécurité : gouvernance, ISO 27001, NIS2 & DORA | OLING`
- H1: `Conseil cybersécurité : gouvernance, risques et conformité`
- Meta: `OLING accompagne les organisations dans la gouvernance cybersécurité, analyse des risques, ISO 27001, NIS2, DORA, PCA/PRA et mise en oeuvre des plans de sécurité.`

## I. PAGE STRUCTURE

Structure locale: hero, problemes traites, positionnement OLING, interventions, methode, livrables, preuves/terrains, interventions complementaires, parcours, CTA, zones, FAQ.

## J. OFFER COVERAGE

Couverture: diagnostic, maturite, risques, gouvernance SSI, PSSI, SMSI, ISO 27001, NIS2, DORA, fournisseurs, incidents, PCA/PRA, pilotage MAPSI.

## K. ISO 27001

OLING est presentee comme certifiee ISO 27001:2022. La page ne dit pas qu'OLING certifie ses clients.

## L. NIS2

Traitement operationnel ajoute: ecarts, gouvernance, risques, fournisseurs, incidents, preuves, plan d'action.

## M. DORA

Traitement operationnel ajoute: actifs, risques, resilience numerique, fournisseurs, documentation, plan de conformite.

## N. PCA / PRA

Couverture renforcee: BIA, PCA, PRA, RTO, RPO, crise, strategie de reprise, tests, retour a la normale.

## O. RISKS / EBIOS

Risques renforces. EBIOS RM non ajoute comme promesse car non suffisamment documente dans les sources locales.

## P. RSSI EXTERNALISE

Non positionne comme offre. La page cible les DG, DSI et RSSI et parle de gouvernance SSI sans vendre un RSSI externalise.

## Q. DELIVERABLES

Livrables visibles: diagnostic maturite, rapport d'ecarts, cartographie risques, plan de traitement, roadmap securite, PSSI, politiques, procedures, dossier SMSI, tableaux de bord SSI, preuves, PCA/PRA, crise, plan de tests.

## R. PROJECT PROOFS

References publiables citees de facon generale: GPMG, CCIIG, SAGPC, gouvernance SI, RGPD, telecoms, plans d'action MAPSI. Reference DORA conservee anonymisee car statut `A valider avant publication nominative`.

## S. TEAM PROOFS

Base locale: Jean Claude VATI, Dorothee Maitrias. Preuves cyber expertes encore faibles comparees a Netsystem/Shift.

## T. MAPSI

MAPSI est mentionne comme support de pilotage risques, audits, actions, controles, documents, incidents et preuves. Pas de transformation de la page en fiche produit.

## U. CTA / CONVERSION

CTA modifie: `Évaluer mon projet cybersécurité` vers `/contact`. Pas de nouveau formulaire.

## V. INTERNAL LINKING

Liens ajoutes/renforces: `/expertises-audit/si`, `/rgpd`, `/mapsi-progiciel`, `/ressources`.

## W. STRUCTURED DATA

`Service` et `FAQPage` restent rendus via `templates/seo/_db-landing.html.twig`.

## X. AI READINESS

Entites, offres, livrables, methodes, referentiels, preuves, zones et limites explicites. Lisible pour moteurs IA sans vocabulaire artificiel visible.

## Y. TESTS

Resultats:

- `php -l`: non applicable, aucun fichier PHP modifie.
- `php bin/console lint:twig templates`: OK, 125 fichiers Twig valides.
- `php bin/console lint:yaml config --parse-tags`: OK, 24 fichiers YAML valides.
- `php bin/console doctrine:migrations:status`: OK, aucune migration nouvelle.
- `php bin/console cache:clear --env=prod`: OK, avec deprecations Symfony/Doctrine existantes.
- `php bin/console debug:router`: OK, route `seo_cyber_securite` presente sur `/cyber-securite`.
- `php bin/console presta:sitemap:dump --env=prod`: OK. Timestamps sitemap generes puis restaures pour eviter du bruit Git.
- Smoke local: `/cyber-securite`, `/expertises-audit/si`, `/rgpd`, `/conseil-qualite`, `/amoa-si`, `/mapsi-progiciel`: HTTP 200.

## Z. REMAINING RISKS

- La modification principale est en DB locale `site_page`; elle devra etre rejouee ou publiee via le workflow admin avant prod.
- Preuves experts cyber encore limitees.
- Ne pas publier nominativement les references DORA non validees.
- Rapport Phase 1 preexistant non suivi conserve: `docs/audits/2026-08-30-seo-phase1-deployment.md`.

CYBER MASTER PAGE:
`/cyber-securite`

CYBER SEO READINESS:
PASS

CYBER BUSINESS READINESS:
PASS WITH FIXES

CYBER AI READINESS:
PASS

READY FOR PROD REVIEW:
YES
