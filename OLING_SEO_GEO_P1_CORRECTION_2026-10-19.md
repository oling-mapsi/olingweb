# OLING — Lot correctif SEO/GEO P1
**19 octobre 2026 — candidat local, aucun déploiement.**

## A. Baseline

- Worktree propre créé et attaché : `/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw`.
- Fetch effectué avant création. Le checkout initial sale n’a pas servi de base aux corrections et reste intact.
- Release effectivement servie au début du lot : `/var/www/releases/oling-home-method-image-layout-20261018`.
- **La release n’a pas de .git et ne correspond pas à un commit de déploiement unique identifiable.** Le dépôt historique serveur est à `d082c55d72c8b4911db7428a05d3c2f38451405f`, avec des écarts ; ce commit n’est donc pas présenté comme l’état exact de production.
- Base de travail : **`8ad79eb703adf6d17ef788efeff32e9f6789fd12`**, snapshot Git créé localement à partir de **4 800 fichiers applicatifs vérifiés par SHA-256** contre la release active. Le commit historique est son parent. Aucun push.
- Périmètre du snapshot : code, configuration applicative, gabarits, traductions, données éditoriales versionnées et assets statiques. Secrets, base, caches et uploads ne font pas partie de cette empreinte applicative. Les tests et dépendances de recette sont locaux.
- Le worktree était vide de modifications avant préparation de la recette et du candidat.
- Copie éditoriale de recette : **40 tables / 1 279 lignes**, sans comptes, contacts ou demandes ; SQLite privée. Export distant limité à SELECT/SHOW.
- Les secrets et services externes sont remplacés par une configuration locale ignorée. Le routeur privé adapte JSON_UNQUOTE à SQLite. Aucun de ces éléments de recette n’est livré dans le diff.
- Le HTML de baseline a été capturé avant correction sur les dix URL demandées, plus les owners RGPD et Cyber.

**Limite de provenance explicite :** BASE COMMIT est un snapshot local vérifié des fichiers déployés, pas un SHA prétendument déjà déployé. Cela évite de corriger depuis un ancien arbre Git ou le checkout sale.

Preuves locales : [manifest de baseline](var/seo-p1/baseline-manifest.json), [HTML avant](var/seo-p1/before.json), [HTML après](var/seo-p1/after.json).

## B. AMOA SI avant/après

| Élément | Avant | Après |
|---|---|---|
| /amoa-si | 200, owner commerciale | Inchangée : 200, title/H1/canonical conservés |
| /practice/consulting | 200, même title/H1, contenu presque identique | **301 directe → /amoa-si** |
| Sitemap practice | Alias présent | Alias retiré du fichier et du générateur |
| Liens générés par l’indexeur interne | URL practice consulting | URL /amoa-si |
| Ancres éditoriales historiques | Quelques liens de supports ERP dispersés | Liens commerciaux ERP réorientés vers l’owner |

### Inventaire ayant justifié la 301

- Les deux routes chargeaient la même entité Practice `consulting` et le même rendu `renderPracticeHome`. Aucune intention de hub réellement distincte n’a été démontrée.
- Le contenu propre à la practice, ses services, son équipe et ses associations restent en place : **aucune suppression d’entité ou de données**.
- Les compléments spécifiques à l’owner AMOA SI, notamment ses réponses SI Client/SIRH, restent sur /amoa-si. Rien d’utile à cette owner n’a été retiré.
- Avant correction : canonical autonome de chaque route, même title/H1 et schemas de même nature. La duplication du rendu est confirmée par la baseline et l’audit.
- Les alternates EN/ES de /amoa-si restent inchangés. L’alias FR n’a pas révélé de contenu linguistique autonome à préserver.
- La navigation commerciale employait déjà /amoa-si. L’indexeur interne générait encore l’alias ; il est corrigé.
- Backlinks externes : aucun inventaire autoritatif disponible dans ce lot. La 301 conserve une destination utile pour les liens externes existants.
- Le crawl final des 116 pages françaises ne trouve **aucun lien public vers /practice/consulting**.

Le title reste **AMOA SI : conseil, cadrage et pilotage de projets IT | OLING** et le H1 **AMOA SI : cadrer, arbitrer et piloter les projets IT**.

## C. ERP avant/après

### Owner conservée : /business-apps/erp

Title, H1 et canonical conservés. La méthode existante, les livrables, les cas, les intervenants, la reprise, les interfaces, la recette et l’hypercare sont préservés. Le libellé de carte/H2 est recentré sur **AMOA ERP**, sans ajout de texte à la chaîne AMOA.

### /erp-progiciel : cadrage du besoin avant choix

- Nouveau title : **Choisir son ERP : besoins, cadrage et aide au choix | OLING**.
- Nouveau H1 : **Cadrer vos besoins avant de choisir un ERP**.
- Introduction, questions, livrables et CTA centrés sur la qualification des besoins et des critères de sélection.
- Lien explicite **Notre accompagnement AMOA ERP** → /business-apps/erp.
- Le questionnaire structuré, son URL et ses boutons sont conservés. Aucun appel IA ni envoi de questionnaire réalisé.
- Les blocs historiques qui répliquaient une offre complète ne sont plus affichés sur cette entrée ; leurs données sources ne sont pas détruites.
- La page reste 200, auto-canonique et dans le sitemap.
- JSON-LD **WebPage**, sans déclaration concurrente de Service AMOA ERP.

### /expertises/amoa-erp-applications-metiers : hub applications métiers

- Nouveau title : **Applications métiers : ERP, CRM, GMAO et SI Finance | OLING**.
- Nouveau H1 : **Cadrer la transformation de vos applications métiers**.
- Introduction et situations recentrées sur les périmètres applicatifs et leurs interfaces.
- Quatre liens de spécialisation visibles : ERP → /business-apps/erp ; CRM → /crm ; GMAO → /gmao ; Finance → /si-finance.
- CTA final d’orientation applicative. Slug, images, associations et données de preuve conservés.
- Les métadonnées du sous-payload historique landingNarrative sont également alignées, afin de ne pas conserver une ancienne promesse AMOA ERP dans les données CMS.
- Pas de Service AMOA ERP ajouté au hub ; la structure d’entité globale reste intacte.

### /consulting/assistance-a-maitrise-douvrage : support AMOA métier

L’offre ancienne reste accessible : son contenu métier, ses phases et ses usages ne justifient pas une redirection. Title/H1, résumé de carte et description la présentent désormais comme **AMOA métier : besoins, cahier des charges et recette**. Deux liens explicites orientent vers les owners AMOA SI et AMOA ERP. Le service ne se présente plus comme une quatrième landing ERP principale.

Les corrections éditoriales ne reposent pas sur un changement de JSON source qui serait invisible en production : elles sont portées par la migration CMS ciblée décrite ci-dessous.

## D. QSE / ISO 9001 avant/après

| Intention | Owner | Signal principal après correction |
|---|---|---|
| Conseil / pilotage QSE | /expertises-audit/qse | Système intégré, animation, gouvernance, indicateurs, reporting et plans d’action |
| Qualité / ISO 9001 | /conseil-qualite | Management de la qualité, processus, audits internes et revue de direction |

**QSE :**
- Title : **Pilotage QSE et système de management intégré | OLING**.
- H1 : **Pilotage QSE : structurer, animer et améliorer votre système de management**.
- Les premiers écrans précisent le rôle de la direction et du responsable QSE, les activités récurrentes, les tableaux de bord et le suivi des actions.
- Les phases et livrables intègrent animation, reporting et vérification d’efficacité.
- ISO 9001, ISO 14001 et ISO 45001 restent cités comme référentiels applicables ; aucune certification unique « QSE » n’est inventée.
- CTA et libellé de carte recentrés sur le pilotage QSE.
- Le lien vers qualité dit **Accompagnement ISO 9001 et management de la qualité**, sans qualifier l’owner de simple synthèse.

**Qualité :**
- Title/H1/canonical ISO 9001 conservés.
- La FAQ de diagnostic est recentrée sur ISO 9001 ; le lien QSE distingue bien le système intégré.
- Deux entrées séparées dans le menu et les hubs : **QSE / pilotage QSE** et **Qualité / ISO 9001**.
- Traductions des nouveaux libellés de navigation ajoutées en EN/ES ; aucune restructuration éditoriale internationale.

### Matrice finale de différenciation

| URL | Intention principale après correction | Title | H1 | Owner/support | Risque résiduel |
|---|---|---|---|---|---|
| /amoa-si | AMOA SI | AMOA SI : conseil, cadrage et pilotage de projets IT \| OLING | AMOA SI : cadrer, arbitrer et piloter les projets IT | Owner | Faible, alias consolidé |
| /practice/consulting | Redirection AMOA SI | — (301) | — | Alias 301 | Recrawl nécessaire |
| /business-apps/erp | AMOA ERP complète | AMOA ERP : cadrage, choix et pilotage de projet ERP \| OLING | AMOA ERP : cadrage, choix et pilotage de votre projet ERP | Owner | Recouvrements naturels avec supports |
| /erp-progiciel | Qualification des besoins et préparation du choix ERP | Choisir son ERP : besoins, cadrage et aide au choix \| OLING | Cadrer vos besoins avant de choisir un ERP | Support | Faible après spécialisation |
| /expertises/amoa-erp-applications-metiers | Orientation vers les applications métiers | Applications métiers : ERP, CRM, GMAO et SI Finance \| OLING | Cadrer la transformation de vos applications métiers | Hub | Faible, chemins spécialisés explicites |
| /consulting/assistance-a-maitrise-douvrage | Besoins, cahier des charges et recette métier | AMOA métier : besoins, cahier des charges et recette \| OLING | Assistance à maîtrise d’ouvrage métier : besoins et recette | Support | Quelques domaines ERP restent cités |
| /expertises-audit/qse | Conseil et pilotage du système QSE intégré | Pilotage QSE et système de management intégré \| OLING | Pilotage QSE : structurer, animer et améliorer votre système de management | Owner | Recouvrement normatif naturel |
| /conseil-qualite | ISO 9001 et management de la qualité | Conseil ISO 9001 et qualité \| Processus, audits et certification \| OLING | Conseil ISO 9001 et qualité : fiabiliser les processus et la performance durable | Owner | Recouvrement normatif naturel |
| /gmao | AMOA GMAO | AMOA GMAO : cadrage, choix et déploiement de votre GMAO \| OLING | AMOA GMAO : cadrer, choisir et réussir votre projet de maintenance | Owner conservée | Hors correction |
| /si-finance | AMOA SI Finance | AMOA SI Finance : cadrage et pilotage de transformation \| OLING | AMOA SI Finance : cadrer et piloter votre transformation Finance | Owner conservée | Hors correction |


Le risque résiduel ERP est **LOW dans l’architecture locale corrigée** : des mots et des étapes peuvent naturellement être communs, mais les rôles principaux sont distincts. Cela ne prétend pas mesurer une nouvelle répartition des résultats Google avant déploiement.

## E. llms.txt

Une seule section **Primary Expertise Paths**, avec les huit associations attendues :

| Spécialité | Owner |
|---|---|
| AMOA SI | /amoa-si |
| AMOA ERP | /business-apps/erp |
| AMOA GMAO | /gmao |
| AMOA SI Finance | /si-finance |
| QSE / integrated management system | /expertises-audit/qse |
| ISO 9001 / Quality management | /conseil-qualite |
| RGPD / GDPR compliance | /expertises-audit/rgpd |
| Cybersecurity / governance | /cyber-securite |

Le second bloc contradictoire est supprimé. Les pages support sont listées séparément, avec leur rôle. SI Client est explicitement décrit comme réparti entre AMOA SI, CRM, Finance et facturation, **sans owner dédiée**.

Aucune règle robots ni configuration de crawler n’est modifiée. Le fichier ne promet aucun gain automatique de visibilité IA.

## F. Maillage interne

- Menu global, desktop et mobile : deux entrées distinctes QSE et qualité.
- Hubs /expertises et /services : deux destinations et deux descriptions distinctes.
- QSE → qualité : ancre spécialisée ISO 9001 ; qualité → QSE : ancre de pilotage intégré.
- Hub applications métiers → les quatre owners applicatives, avec descriptions du besoin couvert.
- Cadrage ERP et support AMOA métier → **Notre accompagnement AMOA ERP**, cible unique /business-apps/erp.
- Les ancres commerciales anciennes « ERP », « ERP/progiciel », « AMOA ERP » ou « AMOA ERP et progiciels » pointant vers /erp-progiciel sont réorientées lorsqu’elles sont reconnues exactement dans les contenus français. Les liens d’aide au choix réellement distincts ne sont pas remplacés indistinctement.
- Les anciens liens /practice/consulting sont remplacés dans les contenus et les documents de l’indexeur interne.
- Des liens commerciaux ERP sont corrigés sur AMOA SI, les pages locales, la synthèse conformité, l’infrastructure et Finance. Sur Finance, cela concerne une URL dans le HTML éditorial historique : **title, H1, canonical, rôle et texte principal rendu inchangés**.
- Aucun lien artificiel ajouté aux projets ; les liens qualité existants sont conservés et la distribution globale est renforcée par la navigation et les hubs.

La migration finale prépare **17 UPDATE de lignes françaises** depuis la baseline ; elle ne modifie ni slug, ni publication, ni image, ni association de services/projets. Le second passage prépare zéro UPDATE.

## G. Redirects éventuels

| URL | Action |
|---|---|
| /practice/consulting | 301 directe vers /amoa-si, paramètres de requête préservés |
| /consulting | Alias existant raccourci vers /amoa-si, sans passage intermédiaire par la practice |

Tests : destination 200, un seul saut sur l’alias principal, aucun cycle ; utm_source et utm_campaign conservés. /amoa-si garde sa canonical autonome.

**CANONICAL CHANGES : aucune valeur d’owner modifiée.** La canonical de l’ancienne landing practice disparaît avec son rendu 200, remplacé par la réponse 301.

## H. Sitemaps

Génération locale effectuée avec Presta, dans le dossier privé de recette :

`php bin/console presta:sitemaps:dump var/seo-p1/generated-sitemaps --base-url=https://oling.fr --no-debug`

- Delta français par rapport aux 117 URL de l’audit : **suppression de /practice/consulting uniquement** ; zéro ajout.
- **116 URL françaises générées et crawlées : toutes 200.**
- /amoa-si, /business-apps/erp, /expertises-audit/qse et /conseil-qualite présentes.
- Les deux supports ERP restent présents et indexables.
- Le sitemap practice statique et le générateur ont été corrigés, pour éviter le retour de l’alias à la prochaine génération.

Preuves : [delta des URL](var/seo-p1/sitemap-delta.json), [log de génération](var/seo-p1/sitemaps.log).

## I. Structured data

- JSON-LD parsé sur toutes les pages HTML de la recette ciblée.
- ProfessionalService global conservé ; aucun bloc d’entité concurrent ajouté.
- Service AMOA ERP conservé sur /business-apps/erp.
- /erp-progiciel émet WebPage pour son rôle de préparation, sans Service AMOA ERP concurrent.
- QSE émet un Service de conseil/pilotage QSE.
- Le support ancien émet un Service AMOA métier, avec nom et type cohérents.
- Hub applications métiers : pas de nouveau schema Service ; seulement la structure globale existante.
- GMAO, Finance, RGPD et Cyber : schemas et contenus métier hors correction.
- Breadcrumbs visibles conservés. Pas d’ajout de schema secondaire hors périmètre.

Il s’agit d’un contrôle de syntaxe et de cohérence du HTML local, pas d’une validation Google Rich Results ni d’une mesure d’éligibilité effective.

## J. Contrôles techniques

| Contrôle | Résultat |
|---|---|
| Propreté initiale / base identifiée | PASS, avec provenance du snapshot explicitée en A |
| PHP des fichiers modifiés et migration | PASS |
| JSON | PASS : 43 fichiers data et 3 fichiers config |
| Twig | PASS : 187 fichiers |
| YAML | PASS : 3 fichiers de traductions |
| git diff --check | PASS |
| Routes | PASS ; table exportée, alias et destinations testés |
| Sitemaps | PASS ; 116 URL FR, seul alias retiré |
| HTML rendu | PASS sur les 10 URL demandées + RGPD/Cyber |
| Placeholders / clés i18n brutes | Aucun détecté dans les textes principaux des pages ciblées |
| Questionnaire ERP | Présent, URL conservée ; aucune soumission/API testée |
| Migration depuis baseline réelle de recette | PASS : 17 UPDATE ; deuxième passage : 0 |
| Garde de dérive éditoriale | PASS : modification CMS inattendue refusée avant application |
| Tests nouveaux migration | PASS : 2 tests, 9 assertions |
| Tests indexeur interne | PASS : 3 tests, 19 assertions |
| Tests existants ciblés | 13 réussis ; 1 échec MAPSI préexistant, décrit ci-dessous |
| Visuel desktop / mobile | PASS aux tailles 1440 × 1000 et 390 × 844 |
| Non-régression des clusters protégés | PASS : métadonnées et texte principal rendu identiques |

**Échec préexistant :** CorePagesSourceTest::testMapsiPracticeLinksToInternalMapsiServicePages attend /mapsi/automatisation-et-workflow dans une liste source où elle n’est pas présente. Le fichier du test et data/i18n/practice_narratives.fr.json sont strictement identiques à HEAD de la baseline. Aucune correction MAPSI n’a été entreprise. Bilan des tests distincts exécutés : **18 réussis, 1 échec préexistant hors lot**.

### Vérification visuelle

Sept pages contrôlées en desktop et mobile : AMOA SI, owner ERP, cadrage ERP, hub applications métiers, support AMOA métier, QSE et qualité. Quatorze captures conservées. Pas de débordement horizontal ni d’image principale cassée aux deux tailles homologuées. Le menu mobile expose les deux entrées QSE / ISO 9001 séparées.

À 1280 px, un débordement de 49 px du bandeau existant a été observé. Une comparaison avec le HTML de baseline donne **1329 px avant et 1329 px après**, donc aucune régression de ce lot. Aucun chantier CSS hors périmètre n’a été ouvert.

![QSE desktop — recette locale](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/visual/expertises-audit_qse-desktop.jpg)

![QSE mobile — recette locale](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/visual/expertises-audit_qse-mobile.jpg)

Preuves : [contrôles HTML](var/seo-p1/rendered-checks.json), [tests de migration](var/seo-p1/tests-migration.log), [tests existants](var/seo-p1/tests-existing.log), [contrôles visuels](var/seo-p1/visual/checks.json).

## K. Risques résiduels

1. **Aucun effet moteur encore observable** : le candidat n’est pas déployé. La réindexation, les positions et les conversions ne sont pas présumées.
2. La release est un assemblage sans SHA unique de déploiement. La baseline par empreintes est conservée ; elle devra être recontrôlée avant un éventuel lot de production.
3. Les contenus CMS sont autoritaires : livrer uniquement les gabarits/JSON sources ne suffit pas. La migration **Version20261019220000**, son plan **data/seo/p1-corrections-20261019.fr.json** et leurs gardes font partie du candidat à relire.
4. Les gardes refusent une valeur éditoriale différente de la baseline et de la correction attendue. Une dérive depuis le relevé doit provoquer une nouvelle revue, jamais un écrasement forcé.
5. Retour éditorial : pas de down aveugle. La migration refuse un rollback destructeur et impose de restaurer seulement les champs concernés depuis une sauvegarde, en tenant compte d’éventuelles éditions ultérieures.
6. Recette de contenu sous SQLite avec adaptation privée de JSON_UNQUOTE ; pas de recette MySQL complète dans ce lot. La migration utilise des SELECT/UPDATE paramétrés classiques, sans fonction JSON spécifique à SQLite.
7. Les contenus EN/ES des supports ne sont pas réécrits. Seuls les nouveaux libellés de navigation disposent de traductions ; leur spécialisation éditoriale internationale reste hors lot.
8. Les textes RGPD/Cyber, les paramètres d’owner GMAO/Finance et les sources protégées restent inchangés. Le menu commun évolue pour toutes les pages ; cela ne constitue pas une restructuration de ces clusters.
9. Aucun nouvel outil, nouvelle landing SI Client, nouveau slug, aucune modification de formulaire, de tracking ou de configuration serveur.
10. L’échec MAPSI existant et le débordement à 1280 px sont documentés, sans correction opportuniste.

**Prêt pour revue du candidat local, pas pour déploiement automatique.** Prochaine décision : relire le diff et le plan éditorial gardé ; autoriser séparément toute intervention ultérieure en production.

```text
OLING SEO/GEO P1 CORRECTION

LOCAL: PASS
PROD TOUCHED: NO
DEPLOYED: NO

BASE COMMIT: 8ad79eb703adf6d17ef788efeff32e9f6789fd12 (snapshot local vérifié de la release)

AMOA SI OWNER: /amoa-si
/practice/consulting ACTION: 301 vers /amoa-si
AMOA SI DUPLICATION: RESOLVED

ERP OWNER: /business-apps/erp
ERP-PROGICIEL ROLE: cadrage du besoin et préparation du choix ERP
ERP EXPERTISE HUB ROLE: orientation applications métiers
ERP CANNIBALIZATION: LOW

QSE OWNER: /expertises-audit/qse
ISO9001 OWNER: /conseil-qualite
QSE / ISO9001 SEPARATION: PASS

LLMS.TXT CONSISTENCY: PASS

GMAO UNCHANGED: YES
SI FINANCE UNCHANGED: YES
RGPD UNCHANGED: YES
CYBER UNCHANGED: YES
SI CLIENT NEW PAGE CREATED: NO

REDIRECTS: 301 /practice/consulting et /consulting vers /amoa-si
CANONICAL CHANGES: aucune sur les owners
SITEMAP CHANGES: retrait de /practice/consulting uniquement
INTERNAL LINK CHANGES: concentration ERP et deux entrées QSE / ISO 9001

JSON: PASS
TWIG: PASS
DIFF CHECK: PASS
RENDERED HTML: PASS
VISUAL CHECK: PASS

READY FOR PROD REVIEW: YES

NEXT ACTION: Relire le candidat et la migration ciblée ; décider séparément de toute intervention en production.
```
