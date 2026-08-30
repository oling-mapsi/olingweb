# Audit local / production / donnees OLING.fr - 2026-08-30

## A. Executive summary

- Architecture comprise : PARTIAL.
- Risque actuel : MEDIUM.
- Cause principale : le code local est propre et aligne avec `origin/main`, mais l'acces SSH trouve un dossier `/home/ubuntu/bkoling/olingfr` qui contient des dumps historiques, pas le code Symfony de production. L'acces applicatif/DB production reste donc incomplet.
- Aucun changement prod, aucune migration, aucun SQL d'ecriture, aucun deploy effectue.

Verdict court : le rendu public prod et local a les memes `title`, H1 et meta descriptions sur les pages critiques testees, mais il manque encore la comparaison DB prod directe.

## B. Git local

| Item | Valeur |
| --- | --- |
| Path | `/Users/florestanrouet/myweb/olingw` |
| Branch | `main` |
| Commit | `885a81293fb3fb0f24f0668f16eb717521ff004a` |
| Origin main | `885a81293fb3fb0f24f0668f16eb717521ff004a` |
| Ahead / behind | 0 / 0 |
| Dirty files | none |
| Untracked files | none |

Derniers commits : `885a8129 fix(chat): clamp mobile panel width`, `360f3cae fix(chat): stabilize iPhone composer focus`, `a6421d6b feat(chat): improve mobile ergonomics`.

## C. Environnements

| Env | Host / path | PHP | DB | Etat |
| --- | --- | --- | --- | --- |
| Local | machine locale, `/Users/florestanrouet/myweb/olingw` | PHP 8.2.28 | MySQL local, base `olingw` | OK |
| Preprod | non identifiee dans le code courant | unknown | unknown | ACCESS BLOCKED |
| Prod publique | `https://oling.fr` / `https://www.oling.fr` | unknown | unknown | HTML public OK |
| SSH trouve | `ovh-vps-mapsi-gpmlm`, host `vps-550038e6`, `/home/ubuntu/bkoling/olingfr` | PHP CLI absent du PATH | non verifiee | ne semble pas etre le code Symfony |

Secrets masques volontairement. Les fichiers `.env*` contiennent des identifiants et ne sont pas reproduits.

## D. Role de MAPSI

Constat code :

- MAPSI apparait comme practice `mapsi`, page SEO `mapsi-progiciel`, liens externes vers `https://mapsi.fr/` et `https://web.mapsi.fr/`.
- `config/oling/software_taxonomy.php` declare MAPSI comme produit OLING, famille `GRC / RGPD / QSE / PCA`.
- Aucun appel API MAPSI, aucun connecteur, aucun import automatique MAPSI -> OLING trouve dans le code local.
- Les cross-links Oling -> MAPSI sont en base/code ; les backlinks MAPSI -> Oling sont documentes comme action cote MAPSI, pas comme synchro automatique.

Flux reel constate :

```text
MAPSI
    -> pas de flux technique constate vers OLING DB
OLING DB
    -> Controllers / repositories / resolvers
    -> Twig
    -> HTML public
```

Conclusion : MAPSI n'alimente pas techniquement OLING.fr dans le code local observe. Role actuel : produit, practice, source de liens, referentiel editorial.

## E. Sources de contenu

| Source | Usage public | Admin |
| --- | --- | --- |
| `site_page` | pages SEO, pages editoriales, ressources, home structurée | `/admin/pages`, `/admin/home/*` |
| `practice` | `/amoa-si`, `/practice/*`, menu, home cards | `/admin/practices` |
| `services` | `/business-apps/erp`, services par practice, menu | `/admin/services` |
| `projet` | `/projets`, cartes references, liens services | `/admin/projets` |
| `metier` | secteurs, hero home, projets | `/admin/metiers` |
| `team` | equipe, rattachements services | `/admin/teams` |
| `home_section` | sections home `hero`, `practices`, `projects`, `awards` | `/admin/home/*` |
| `legal_page` | RGPD, securite, mentions legales historiques | `/admin/legal-pages` |
| `PublicSiteConfig.php` | fallback structurel/editorial | non |
| `SeoLandingController.php` | narratives hardcodees CRM/GMAO/SI finance | non |
| Twig | structure HTML, quelques libelles | non |

Counts DB locale : `site_page=70`, `practice=4`, `services=34`, `projet=263`, `metier=11`, `team=7`, `home_section=4`, `legal_page=3`.

## F. Ordre de priorite des donnees

| Page | Priorite constatee |
| --- | --- |
| `/` | `site_page.slug=home` colonnes SEO/hero > JSON `body_html` merge > `PublicSiteConfig::getHome()` > `index.html.twig` |
| `/amoa-si` | route alias > `practice.slug=consulting` + `services` rattaches > `PublicSiteConfig::getPracticeNarrative()` > `practice-home.html.twig` |
| `/business-apps/erp` | `services.slug=erp` > `practice` rattachee > `PublicSiteConfig::getServiceNarrative()` > `services.html.twig` |
| `/crm`, `/gmao`, `/si-finance`, `/rgpd`, `/cyber-securite`, `/conseil-qualite`, `/mapsi-progiciel` | `site_page.slug` pour title/meta/hero/body > `SeoLandingController::getLandingNarrative()` pour blocs narratifs > Twig SEO |
| `/projets` | `site_page.slug=projets` pour hero/SEO > `projet` DB pour cartes > fallback `PublicSiteConfig` si champs vides |
| pages legales | `legal_page.slug` > `LegalPageDefaults` si seed/admin absent |

Reponses operationnelles :

- Modifier le PHP est visible seulement pour fallback, structure Twig, narratives hardcodees et logique.
- Modifier la DB locale est visible localement si la page lit l'entite concernee.
- Migrer la DB prod serait visible si la prod a le meme code et applique la migration, mais non verifie sans DB prod.
- Si l'admin modifie une donnee apres migration, l'admin gagne tant qu'aucune migration future ne re-ecrase le meme champ.

## G. Schema drift

Local :

- `doctrine:schema:validate` : mapping OK, DB non synchronisee.
- Drift detecte uniquement sur noms d'indexes et indexes `site_page_revision` :
  - renommages indexes `projet`, `projet_team`, `app_user`, `legal_page`, `site_page`.
  - suppression proposee de `uniq_site_page_revision_number` et `idx_site_page_revision_lookup`.

Prod : ACCESS BLOCKED pour schema direct.

## H. Migration drift

Local :

- 58 migrations disponibles, 58 appliquees.
- Derniere locale : `DoctrineMigrations\Version20260825103000`.
- Nouvelle migration locale : 0.

Prod :

- Impossible a verifier : le chemin SSH identifie ne contient pas `bin/console` et PHP CLI est absent.
- Ne pas conclure que la prod est a jour sans acces applicatif ou DB.

Migrations recentes a risque contenu :

| Migration | Type | Risque |
| --- | --- | --- |
| `20260603173000` | data, cross-links MAPSI dans `site_page.hero_side_html` | faible a moyen, idempotence par marqueur |
| `20260809143000` | data, nouvelles pages expertise IA | moyen si rejouee sur prod modifiee |
| `20260809150000` | data, nouveaux services IA | moyen |
| `20260824113000` | data home/practices | eleve, ecrit wording editorial |
| `20260824143000` | structure projets SEO referential | moyen structure, impact rendu projet |
| `20260824170000` | data alignement editorial | eleve, ecrit plusieurs champs |
| `20260824173000` | data nettoyage overrides IA | eleve sur contenu admin |
| `20260824174000` | data force alignment site_page IA | eleve car force SQL |
| `20260824225729` | data services/meta SEO | eleve sur wording |
| `20260825103000` | structure chat index | faible editorial |

## I. Data drift

Comparaison DB prod : ACCESS BLOCKED.

Comparaison HTML public prod/local :

| URL | Title | H1 | Meta | Drift |
| --- | --- | --- | --- | --- |
| `/` | identique | identique | identique | hash HTML different |
| `/amoa-si` | identique | identique | identique | hash HTML different |
| `/business-apps/erp` | identique | identique | identique | hash HTML different |
| `/crm` | identique | identique | identique | hash HTML different |
| `/gmao` | identique | identique | identique | hash HTML different |
| `/si-finance` | identique | identique | identique | hash HTML different |
| `/rgpd` | identique | identique | identique | hash HTML different |
| `/cyber-securite` | identique | identique | identique | hash HTML different |
| `/conseil-qualite` | identique | identique | identique | hash HTML different |
| `/projets` | identique | identique | identique | hash HTML different |
| `/mapsi-progiciel` | identique | identique | identique | hash HTML different |

Classement : EXPECTED/PARTIAL. Les differences de hash peuvent venir des assets, URLs absolues, environnement local/debug ou contenu secondaire. Pas de drift SEO majeur constate sur les champs compares.

## J. Projets

Local :

- `projet=263`.
- `external_id IS NOT NULL=193`.
- `featured_projects=12`, mais le frontend limite a 6.
- `software_tags` presents sur 64 projets.
- Statuts : 167 `A valider avant publication nominative`, 26 `Deja cite sur OLING.fr`, 70 vides/historiques.
- Relations : `projet_services=112`, `projet_team=0`, `projets_with_metier=70`.

Prod DB : ACCESS BLOCKED. Ne pas synchroniser automatiquement `projet`.

## K. Progiciels / competences

Sources constatees :

- Referentiel canonique : `config/oling/software_taxonomy.php`.
- Donnees projet : `projet.software_tags`, `software_families`, `software_relation`, `software_priority`, `metadata`.
- Pages/service : `services` et `site_page` contiennent ERP/CRM/GMAO/SIRH/SI finance.
- Fallback code : `PublicSiteConfig`, `SeoLandingController`, templates.

Progiciels identifies : Sage X3, Divalto Infinity, MAPSI, Power BI, Advantage Finance/SIGMA, Proginov, Cegid, Dolibarr, Odoo, SAP S/4HANA, IFS, CIRIL Finance/RH, MyReport, EBP, Chorus Pro, So'Wave, IBM Maximo, CARL Source, Coswin 8i, HxGN EAM, QGIS, Business Geo, TOPKAPI, Maarch.

Risque : information dupliquee entre code, DB `projet`, DB `services`, pages SEO et docs.

## L. Source of truth matrix

| Type de donnee | Local | Prod | MAPSI/Admin | Code fallback | Source recommandee |
| --- | --- | --- | --- | --- | --- |
| Code | oui | deploye inconnu | non | n/a | Git |
| Schema DB | migrations | inconnu | non | non | Doctrine migrations |
| Homepage | DB locale + fallback | HTML aligne | admin home | oui | DB admin, fallback minimal |
| SEO title/meta | `site_page` | HTML aligne | admin pages | oui partiel | DB `site_page` |
| Practices | `practice` | HTML partiel aligne | admin practices | narratives | DB admin |
| Services | `services` | HTML partiel aligne | admin services | narratives | DB admin |
| Projets | `projet` local riche | inconnu | admin projets | tri/fallback image | MERGE MANUEL |
| Team | `team` | inconnu | admin team | non | DB admin |
| Secteurs/metiers | `metier` + fallback secteur | inconnu | admin metiers | oui | DB admin + fallback structurel |
| Progiciels | config + projet tags | inconnu | admin projet indirect | oui | referentiel code + rattachement DB |
| Ressources | `site_page` external/publishing | inconnu | API growth/admin pages | templates | DB production apres validation |
| Menus/header/footer | Twig + DB menu indirect | HTML public | non dedie | oui | code + DB entities |
| Images/uploads | filesystem + DB paths | inconnu | admin uploads | fallback assets | backup uploads + DB paths |
| robots/sitemap | code/config + bundle | public OK non audite complet | non | oui | code + commande sitemap |

## M. Sens de synchronisation

| Ensemble | Sens |
| --- | --- |
| Code | LOCAL/GIT -> PROD |
| Schema | migrations validees -> PROD |
| Migrations de contenu | MERGE MANUEL / cas par cas |
| Homepage | MERGE MANUEL puis admin ou migration idempotente ciblee |
| Practices | PROD -> local avant changement si admin prod utilise ; sinon DB ciblee |
| Services | MERGE MANUEL |
| Projets | MERGE MANUEL obligatoire |
| Ressources | PROD -> local pour contenus publies, puis workflow editorial |
| MAPSI | NO SYNC technique constate |
| Uploads | backup avant modification, sync ciblee seulement |

## N. Risques

P0 :

- DB prod non auditee directement : impossible de garantir les migrations absentes/presentes.
- Migrations de contenu recentes peuvent ecraser des edits admin prod si rejouees sans comparaison champ par champ.

P1 :

- `SitePageAdminController::ensureManagedPages()` peut creer des pages manquantes lors de l'ouverture admin.
- Fallbacks importants dans `PublicSiteConfig` et `SeoLandingController` maintiennent du contenu editorial hors DB.
- `projet_team=0` alors que la relation existe : relation non exploitee ou non alimentee.

P2 :

- Drift schema local sur noms d'indexes.
- `featured_projects=12` en DB mais rendu limite a 6.
- Hash HTML prod/local different malgre champs SEO identiques.

## O. Workflow recommande

1. Identifier le vrai host/path prod Symfony et obtenir acces read-only DB.
2. Capturer `git rev-parse`, `doctrine:migrations:status/list`, `schema:validate`, counts et hashes DB prod.
3. Faire backup prod complet DB + backup cible tables editoriales + backup uploads si images.
4. Comparer par slugs/business keys, pas par ids.
5. Decider champ par champ : prod gagne, local gagne, merge manuel.
6. Modifier en local uniquement.
7. Pour structure : migration Doctrine.
8. Pour editorial courant : admin DB ou import controle, pas migration wording systematique.
9. Tester local, deploy code, appliquer migrations structurelles, clear cache controle, smoke tests.
10. Post-deploy : verifier HTML public, sitemap, robots, title/meta/H1/canonical.

## P. Rollback

- Code : revenir au commit/release precedent.
- Schema : `down` seulement si migration reversible et testee ; sinon restore DB cible.
- Contenu : restaurer lignes sauvegardees des tables `site_page`, `practice`, `services`, `projet`, `metier`, `team`, `home_section`, `legal_page`.
- Images : restaurer uploads modifies/supprimes.
- Objectif futur : chaque intervention doit avoir un rollback < 15 min avec backup cible horodate hors Git.

## Q. Actions avant phase SEO

1. Trouver le vrai chemin applicatif production Symfony.
2. Confirmer PHP CLI production et commande console.
3. Obtenir acces DB production read-only ou lancer commandes SELECT via console prod.
4. Comparer migrations local/prod.
5. Comparer schema local/prod.
6. Comparer tables editoriales par slug/external_id.
7. Isoler les modifications prod plus recentes.
8. Definir quels contenus passent par admin vs migration.
9. Geler une politique anti-ecrasement pour migrations de contenu.
10. Executer un backup prod avant toute phase SEO.

## SEO issues discovered

Non corrige dans cette etape :

- Plusieurs contenus SEO et progiciels sont dupliques entre DB, code fallback et templates.
- Les pages CRM/GMAO/SI finance ont une narrative hardcodee dans `SeoLandingController`, donc une partie du SEO n'est pas administrable.
- Les projets sont riches localement mais doivent etre valides nominativement avant publication.

## Reponses aux questions de pilotage

1. Source de verite actuelle : mix DB admin + code fallback ; aucune source unique.
2. Uniquement local : impossible a garantir sans DB prod ; local contient 263 projets et 58 migrations appliquees.
3. Uniquement prod : ACCESS BLOCKED DB.
4. La prod pourrait contenir des edits admin que des migrations de contenu ecraseraient.
5. Oui, local contient probablement donnees futures projets/referentiel, mais a confirmer face DB prod.
6. Migrations non appliquees prod : inconnu.
7. Homepage : modifier via admin home/`site_page.home`, migration seulement structurante/idempotente.
8. Practice : admin `practice`, pas migration wording sauf initialisation controlee.
9. Service : admin `services`, migration structure ou seed idempotent.
10. Projet : merge manuel, validation nominative, admin/import controle.
11. SEO : `site_page` pour title/meta/hero/body ; attention narratives hardcodees.
12. Migration : schema, donnees initiales, corrections ponctuelles idempotentes.
13. Admin : contenu editorial courant.
14. `PublicSiteConfig` : fallback technique, structure par defaut, jamais source wording courant.
15. MAPSI : produit/maillage, pas source technique constatee.
16. Backup : dump DB complet + dump tables editoriales + uploads hors Git.
17. Rollback : code previous release + restore cible DB/uploads.
18. Ordre deploy : backup, deploy code, migrations validees, cache, smoke tests.
19. Edit prod -> local : exporter ligne/champs par slug, merger explicitement, commit si necessaire.
20. Eviter drifts : source-of-truth par type + interdiction migrations wording non idempotentes.

## Verdict

OLING DATA/DEPLOYMENT READINESS: SAFE WITH FIXES

NEXT REQUIRED ACTIONS:

1. Identifier vrai serveur/path prod Symfony.
2. Obtenir acces console PHP prod.
3. Auditer migrations prod.
4. Auditer schema prod.
5. Auditer DB prod editoriale par slug/external_id.
6. Comparer projets local/prod.
7. Classer chaque drift : prod gagne, local gagne, merge.
8. Definir politique migrations contenu.
9. Faire backup prod complet et cible.
10. Demarrer seulement ensuite la phase SEO.
