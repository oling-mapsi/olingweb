# SEO Phase 1 - Cleanup P0

Date: 2026-08-30
Prod vérifiée: `https://oling.fr`
Backup prod propre: `/home/ubuntu/backups/oling/seo-phase1/20260830-194719`

## A. Executive Summary

Nettoyage P0 appliqué sans changement de slug, route, canonical, formulaire ou chat. Les pages prioritaires restent centrées AMOA SI, ERP, CRM, GMAO, SI Finance, RFE, RGPD, cyber et QSE.

Verdict principal: les textes internes visibles ont été retirés des surfaces auditées, les CTA business sont spécialisés, et la production DB a été sauvegardée avant écriture.

## B. Textes Internes Supprimés

- `Maillage` public remplacé par `Expertises complémentaires`.
- `Maillage strategique` remplacé par `Interventions associées`.
- `Hiérarchie éditoriale` remplacé par `Parcours d'expertise`.
- `Cette landing` remplacé par `Cette page`.
- `IA utile et gouvernée` remplacé par `IA cadrée et gouvernée`.

## C. ISO 27001 Cleanup

Recherche code/templates: pas de `IS027001` ni `ISO27001` public détecté. Forme publique conservée: `ISO 27001` / `ISO 27001:2022`.

## D. Old Wording Cleanup

Nettoyés dans code/fallback/DB: `transformation digitale` générique sur références, métiers, équipe, à-propos et fallbacks. Les occurrences restantes concernent une page explicitement dédiée `Transformation digitale, automatisation et IA` et sont laissées hors périmètre P0.

## E. Homepage

Homepage conforme au positionnement Phase 1: AMOA SI, ERP, CRM, GMAO, SIRH, SI Finance, RGPD, cyber, QSE, MAPSI, diagnostic, cadrage, choix, pilotage, recette, déploiement. Pas de réécriture.

## F. AMOA SI

Page `/amoa-si` conservée. Couverture correcte: cadrage, gouvernance, pilotage, ERP, conformité, cyber. Pas de nouveau slug.

## G. ERP

Pages comparées: `/business-apps/erp`, `/erp-progiciel`, `/consulting/assistance-a-maitrise-douvrage`. `/business-apps/erp` reste la page master ERP/progiciels. `/erp-progiciel` joue un rôle secondaire AMOA ERP éditorial.

## H. ERP Cannibalization

Pas de 301 maintenant. Recommandation Phase 2: consolider title/H1/intention entre master ERP et page secondaire, puis décider si `/erp-progiciel` devient support éditorial ou page de conversion dédiée.

## I. CRM

Nettoyage public appliqué. CTA ajouté: `Échanger sur mon projet CRM`. Contenu spécifique conservé: processus commerciaux, relation client, données, reporting, recette, adoption, interfaces ERP.

## J. GMAO

CTA ajouté: `Cadrer mon projet GMAO`. Spécificités maintenues: préventif/correctif, actifs, stocks, achats, ordonnancement, interventions, données, ERP, reprise, recette, déploiement.

## K. SI Finance

CTA ajouté: `Parler de mon SI Finance`. H1/title déjà orientés `AMOA SI Finance`. Couverture P2P/O2C/R2R, clôture, contrôle interne, reporting, ERP, RFE conservée.

## L. RFE

CTA ajouté: `Sécuriser mon projet de facturation électronique`. Nettoyage `Maillage strategique`.

## M. RFE Cannibalization

Pages comparées: `/facturation-electronique-amoa` et `/consulting/reforme-facturation-electronique-amoa`. Master recommandé: `/facturation-electronique-amoa`. La page consulting doit rester secondaire tant qu’aucune fusion/redirection n’est décidée.

## N. Cyber

Page prioritaire conservée sans refonte. Présence confirmée: cybersécurité, ISO 27001, NIS2, DORA, risques, continuité/PCA-PRA selon contenus. Phase 2 recommandée: structure cyber plus profonde autour SMSI/PSSI/risques/incidents/fournisseurs.

## O. RGPD

Page stable. CTA ajouté: `Échanger avec un DPO`. Couverture: DPO/DPD, audit, registre, AIPD/DPIA, CNIL, violations, plan d’action, gouvernance.

## P. QSE

Page stable. CTA ajouté: `Échanger sur ma démarche QSE`. Couverture non limitée à certification: processus, ISO, audit, management, plans d’action.

## Q. CTA / Conversion

Ajout d’un bloc de cadrage vers `/contact` pour: ERP, CRM, GMAO, SI Finance, RFE, RGPD, cyber, QSE. Aucun nouveau formulaire.

## R. Project Shortlist

Candidats Phase suivante, non publiés sans validation nominative: Ville de Fort-de-France GMAO AS400, GPMG DPO/RGPD MAPSI, Roannaise de l’Eau GMAO, Aéroport Montpellier RFE, Ville de Goyave GED/SAE, Routes de Guadeloupe SIG, SOCOMECO ERP Divalto, GPMGuyane DPO, GPM Martinique QSEE, Institut Pasteur Guadeloupe RFE.

## S. Team

Page équipe accessible. Bloc faible remplacé par un texte factuel orienté AMOA SI, conformité, cyber, qualité et pilotage projet.

## T. Hexagone / DROM

Perception actuelle: France + DROM. Agences DB: Boulogne-Billancourt et Baie-Mahault. Pas de nouvelle landing géographique.

## U. Entity / Address

Incohérence à traiter hors P0: pages légales statiques mentionnent encore `40 rue Alexandre DUMAS 75011 Paris` ou `rue René RABAT - 97122 Baie-Mahault`, alors que la DB agence affiche `14 rue Marcel Bontemps, 92100 Boulogne-Billancourt` et `SDC SUD JARRY, ZAC DE HOUELBOURG, Rue René RABAT, 97122 BAIE MAHAULT`.

## V. Canonical / WWW

Baseline avant créé dans `docs/audits/2026-08-30-seo-phase1-before.md`. Cible confirmée à maintenir: HTTPS non-www `https://oling.fr`.

## W. Robots / OAI-SearchBot

À contrôler après déploiement final avec `/robots.txt`. Pas de modification GPTBot dans cette étape.

## X. Sitemap

`php bin/console presta:sitemap:dump --env=prod` OK en local. Fichiers générés non versionnés dans ce cleanup. Pas de 404 sitemap traitée dans cette étape.

## Y. Structured Data

Correction `serviceType` public: `AMOA SI, progiciels et conformité`. Pas de faux schema ajouté.

## Z. Performance / Accessibility

Contrôle léger: aucun changement image/JS lourd. CTA ajoutés avec lien standard vers `/contact`. Chat non modifié.

## Comparaison Before / After

- CRM: CTA avant générique/support links, après `Échanger sur mon projet CRM`; H1/title/canonical inchangés.
- GMAO: après `Cadrer mon projet GMAO`; H1/title/canonical inchangés.
- SI Finance: après `Parler de mon SI Finance`; H1/title/canonical inchangés.
- RFE: après `Sécuriser mon projet de facturation électronique`; H1/title/canonical inchangés.
- RGPD: après `Échanger avec un DPO`; H1/title/canonical inchangés.
- Cyber: après `Évaluer mon projet cyber`; H1/title/canonical inchangés.
- QSE: après `Échanger sur ma démarche QSE`; H1/title/canonical inchangés.

## Tests

- `php -l src/Controller/SeoLandingController.php`: OK
- `php -l src/Service/PublicSiteConfig.php`: OK
- `php bin/console lint:twig templates`: OK
- `php bin/console lint:yaml config --parse-tags`: OK
- `php bin/console doctrine:migrations:status`: OK, 58/58
- `php bin/console cache:clear --env=prod`: OK avec dépréciations existantes
- `php bin/console debug:router`: OK
- `php bin/console presta:sitemap:dump --env=prod`: OK avec dépréciations existantes
- `php bin/console doctrine:schema:validate`: mapping OK, schema DB non synchronisé à cause de drift déjà identifié lors de l’audit Phase 1
- Smoke HTTP local: pages modifiées 200, CTA visibles, termes internes retirés

## Roadmap Phase Suivante

P0: cohérence légale/adresse, consolidation ERP, consolidation RFE.
P1: landing SIRH, landing SI Client/facturation, refonte cyber, cas clients premium.
P2: hubs DROM, diagnostics/conversion avancés, extraction des narratives commerciales de `SeoLandingController` vers DB.

SEO PHASE 1 CLEANUP: PASS WITH FIXES

READY FOR NEW BUSINESS LANDINGS: YES
