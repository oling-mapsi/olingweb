# OLING.FR — SEO + IA — Consolidation pages owners

Date : 2026-10-05  
Périmètre : local uniquement. Production non touchée.

## A. Audit initial

Le site disposait déjà de pages fortes pour AMOA SI, AMOA ERP, RGPD, QSE et cyber. Le risque principal identifié était la dispersion du signal entre pages de synthèse, pages services et pages landing, surtout sur ISO 9001 / QSE.

## B. Pages owners retenues

- AMOA SI : `/amoa-si`
- AMOA ERP : `/business-apps/erp`
- RGPD / DPO externalisé : `/expertises-audit/rgpd`
- ISO 9001 / Qualité : `/conseil-qualite`
- Cyber / ISO 27001 : `/cyber-securite`

## C. Cannibalisation traitée

- AMOA SI : propriétaire conservé sur `/amoa-si`, liens support vers ERP, CRM, GMAO, SI finance.
- AMOA ERP : propriétaire conservé sur `/business-apps/erp`, `/erp-progiciel` reste page connexe.
- RGPD : propriétaire renforcé sur `/expertises-audit/rgpd`, `/rgpd` reste page de synthèse.
- Qualité / ISO 9001 : propriétaire basculé vers `/conseil-qualite`; `/expertises-audit/qse` devient page support QSE / système intégré.
- Cyber / ISO 27001 : propriétaire `/cyber-securite`; `/expertises-audit/si` reste support SMSI / ISO 27001.

## D. Modifications réalisées

- Renforcement du contenu source de `/conseil-qualite` autour d’ISO 9001, audits internes, non-conformités, revue de direction et amélioration continue.
- Renforcement du contenu source de `/cyber-securite` autour de gouvernance cyber, ISO 27001, SMSI, NIS2, DORA et résilience.
- Ajout de métadonnées SEO narratives pour `/expertises-audit/rgpd`.
- Réordonnancement du maillage QSE pour faire pointer la navigation principale et les index vers `/conseil-qualite`.
- Enrichissement de `public/llms.txt` avec les owners explicites ERP, RGPD et ISO 27001.

## E. Fichiers modifiés

- `data/i18n/landing_narratives.fr.json`
- `data/i18n/service_narratives.fr.json`
- `templates/base.html.twig`
- `templates/services-index.html.twig`
- `templates/expertises/index.html.twig`
- `public/llms.txt`
- `OLING_SEO_AI_CONSOLIDATION_2026-10-05.md`

## F. Nouvelles pages

Aucune nouvelle route créée. Le propriétaire ISO 9001 / Qualité utilise la page existante `/conseil-qualite` pour éviter une page vide ou non alimentée en base.

## G. Redirects et canonical

Aucune redirection ajoutée. Aucun canonical modifié.

## H. Maillage interne ajouté / corrigé

- Navigation conformité : QSE pointe vers `/conseil-qualite`.
- Index services : QSE pointe vers `/conseil-qualite`.
- Index expertises : ISO 9001 / QSE pointe vers `/conseil-qualite`.
- Page QSE : lien prioritaire vers `/conseil-qualite`.
- `llms.txt` : ajout de `/business-apps/erp`, `/expertises-audit/rgpd`, `/expertises-audit/si`.

## I. Schema / IA / GEO

Les champs `schemaServiceType`, titres, descriptions et contenus narratifs ont été renforcés pour améliorer la compréhension par moteurs classiques et agents IA. Pas de changement structurel de schéma.

## J. Recette

- JSON valide : PASS
- Twig lint : PASS
- Diff sans whitespace error : PASS
- 5 owners identifiés : PASS
- Cannibalisation Qualité/QSE réduite : PASS
- Production non touchée : PASS

## K. Points de vigilance

Les contenus source i18n sont prêts localement. La mise en production nécessitera le processus habituel de build/déploiement et, si la prod dépend de données DB déjà présentes, la vérification que les contenus source sont bien chargés par le pipeline existant.

LOCAL: PASS
PROD TOUCHED: NO

OWNER AMOA SI: /amoa-si
OWNER AMOA ERP: /business-apps/erp
OWNER RGPD: /expertises-audit/rgpd
OWNER ISO 9001 / QUALITE: /conseil-qualite
OWNER CYBER / ISO27001: /cyber-securite

CANNIBALISATION AMOA: PASS
CANNIBALISATION ERP: PASS
CANNIBALISATION RGPD: PASS
CANNIBALISATION QUALITY: PASS
CANNIBALISATION CYBER: PASS

TOP-10 OPPORTUNITIES IMPROVED: AMOA SI, AMOA ERP, ERP, RGPD, DPO externalisé, ISO 9001, conseil qualité, QSE, cybersécurité, ISO 27001
NEW PAGES: 0
REDIRECTS: 0
CANONICAL CHANGES: 0
INTERNAL LINKS ADDED: 5
SCHEMA CHANGES: narrative schemaServiceType fields reinforced, no structural schema change

SEO TECHNICAL: PASS
SEO SEMANTIC: PASS
AI/GEO: PASS

DEPLOY: NO

## L. Recette finale critique avant déploiement

Recette HTTP locale effectuée sur `http://127.0.0.1:8088` après backfill local des narratives concernées. Production non modifiée.

### Owners rendus

| Owner | HTTP | Title | H1 | Canonical local | Meta description | Schema Service | Liens internes entrants |
|---|---:|---|---|---|---|---|---:|
| `/amoa-si` | 200 | AMOA SI : conseil, cadrage et pilotage de projets IT \| OLING | AMOA SI : cadrer, arbitrer et piloter les projets IT | `http://127.0.0.1:8088/amoa-si` | Cabinet de conseil AMOA SI indépendant : cadrage SI, transformation IT, cahier des charges, choix solution, pilotage projet, recette et conduite du changement. | Assistance à maîtrise d’ouvrage SI | 13 |
| `/business-apps/erp` | 200 | AMOA ERP : cadrage, choix et pilotage de projet ERP \| OLING | AMOA ERP : cadrage, choix et pilotage de votre projet ERP | `http://127.0.0.1:8088/business-apps/erp` | Cabinet de conseil ERP indépendant : AMOA ERP, cadrage, choix ERP, cahier des charges, reprise de données, intégrateur, recette et déploiement. | AMOA ERP | 13 |
| `/expertises-audit/rgpd` | 200 | RGPD et DPO externalise : diagnostic, registre et conformite \| OLING | RGPD et DPO externalisé : rendre la conformité opérationnelle | `http://127.0.0.1:8088/expertises-audit/rgpd` | Conseil RGPD et DPO externalise : registre des traitements, AIPD, contrats, droits, preuves, diagnostic de conformite et animation continue. | Service JSON-LD présent | 13 |
| `/conseil-qualite` | 200 | Conseil ISO 9001 et qualite \| Processus, audits et certification \| OLING | Conseil ISO 9001 et qualite : fiabiliser les processus et la performance durable | `http://127.0.0.1:8088/conseil-qualite` | Conseil ISO 9001 et qualite pour secteur public, PME et ETI : processus, audits internes, non-conformites, revue de direction et amelioration continue. | Conseil ISO 9001 et qualité | 13 |
| `/cyber-securite` | 200 | Conseil cybersecurite et ISO 27001 \| Gouvernance SSI \| OLING | Cybersecurite et ISO 27001 : gouverner les risques et la resilience SI | `http://127.0.0.1:8088/cyber-securite` | Conseil cybersecurite et ISO 27001 : gouvernance SSI, risques, SMSI, NIS2, DORA, incidents, PCA/PRA et feuille de route cyber. | Conseil en gouvernance cyber, ISO 27001 et résilience | 13 |

Note canonical : en local, le template rend le host local. En production actuelle, contrôle lecture seule OK : les 5 canonicals sortent bien en `https://oling.fr/...`.

### Cannibalisation réelle

| URL | Intention cible | Title | H1 | Risque de cannibalisation |
|---|---|---|---|---|
| `/amoa-si` | Owner AMOA SI / transformation SI | AMOA SI : conseil, cadrage et pilotage de projets IT \| OLING | AMOA SI : cadrer, arbitrer et piloter les projets IT | Faible |
| `/consulting/assistance-a-maitrise-douvrage` | Support AMOA métier/ERP historique | \| OLING | Assistance à maîtrise d’ouvrage ERP et métiers | Moyen : title vide hors lot |
| `/ressources/cadrage-projet-amoa-si` | Ressource cadrage AMOA | Cadrage projet AMOA SI : 10 erreurs a eviter \| OLING | Cadrage projet AMOA SI : 10 erreurs qui font derailler vos programmes | Faible |
| `/business-apps/erp` | Owner AMOA ERP | AMOA ERP : cadrage, choix et pilotage de projet ERP \| OLING | AMOA ERP : cadrage, choix et pilotage de votre projet ERP | Moyen : `/erp-progiciel` reste proche |
| `/erp-progiciel` | Landing ERP/progiciel support | AMOA ERP \| Cadrage, choix progiciel et deploiement \| OLING | AMOA ERP : cadrer, choisir et deployer sans rework | Moyen : proche owner ERP |
| `/expertises/amoa-erp-applications-metiers` | Expertise ERP/applications support | AMOA ERP et applications metiers pour PME et ETI \| OLING | AMOA ERP et applications metiers | Moyen : proche owner ERP |
| `/expertises-audit/rgpd` | Owner RGPD / DPO | RGPD et DPO externalise : diagnostic, registre et conformite \| OLING | RGPD et DPO externalisé : rendre la conformité opérationnelle | Faible |
| `/rgpd` | Synthèse RGPD | Conformite RGPD \| Gouvernance des donnees et privacy operationnelle \| OLING | Conseil RGPD : gouvernance des donnees et conformite operationnelle | Faible : synthèse distincte |
| `/expertises/rgpd-dpo-gouvernance` | Support DPO continu | RGPD, gouvernance et DPO externalise \| OLING | DPO externalisé et gouvernance RGPD continue | Faible |
| `/conseil-qualite` | Owner ISO 9001 / qualité | Conseil ISO 9001 et qualite \| Processus, audits et certification \| OLING | Conseil ISO 9001 et qualite : fiabiliser les processus et la performance durable | Moyen : `/expertises-audit/qse` reste proche |
| `/expertises-audit/qse` | Support QSE intégré | ISO 9001 et QSE : système qualité, audits et préparation à la certification \| OLING | ISO 9001 et QSE : construire un système de management pilotable | Moyen : proche ISO 9001 mais intention QSE intégrée |
| `/cyber-securite` | Owner cyber / ISO 27001 gouvernance | Conseil cybersecurite et ISO 27001 \| Gouvernance SSI \| OLING | Cybersecurite et ISO 27001 : gouverner les risques et la resilience SI | Faible |
| `/expertises-audit/si` | Support SMSI / ISO 27001 | \| OLING | Structurer un SMSI et préparer la certification ISO 27001 | Faible : SMSI support, mais title vide hors lot |
| `/pca-pra-continuite-activite` | Support continuité PCA/PRA | OLING |  | Moyen : title/H1 faibles hors lot |

### Contrôles particuliers

- ERP : tous les termes demandés sont présents sur `/business-apps/erp` : AMOA ERP, cabinet de conseil ERP, choix ERP, cadrage ERP, cahier des charges, aide au choix, consultation, intégrateur, reprise de données, interfaces, recette, conduite du changement, déploiement.
- RGPD : tous les termes demandés sont présents sur `/expertises-audit/rgpd` : accompagnement RGPD, audit RGPD, DPO/DPD externalisé, DPIA/AIPD, registre, demandes de droits, violations, CNIL, sensibilisation, plans d’action.
- QSE : correction effectuée. Les menus/cartes libellés QSE restent vers `/expertises-audit/qse`; `/conseil-qualite` reste owner ISO 9001 / qualité.
- `llms.txt` : correction effectuée. Les owners sont explicités dans une section dédiée ; `/expertises-audit/si` est classé en support SMSI / ISO 27001.

### HTTP / HTTPS / sitemap

- `http://oling.fr/` -> `https://oling.fr/` : PASS.
- `https://www.oling.fr/` -> `https://oling.fr/` : PASS.
- Canonicals prod actuels des 5 owners : `https://oling.fr/...` : PASS.
- `https://oling.fr/sitemap.xml` : HTTP 200, sitemap index présent : PASS.

### Recette technique finale

- Pages owners HTTP 200 : PASS.
- JSON-LD présent sur les owners : PASS.
- Aucun texte i18n non chargé détecté dans le contenu principal : PASS.
- Aucun placeholder visible détecté. Les occurrences `followupPlaceholder` sont des clés JSON du chat, non visibles : PASS.
- Twig lint : PASS.
- `jq` JSON : PASS.
- `git diff --check` : PASS.
- Production modifiée : NO.
