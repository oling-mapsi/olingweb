# Reconciliation production OLING.fr - 2026-08-30

## A. PROD INFRA CONFIRMED

| Item | Valeur |
| --- | --- |
| Domaine | `oling.fr`, `www.oling.fr` |
| DNS A | `54.37.230.240` |
| `www` | CNAME vers `oling.fr` |
| SSH confirme | `ovh-vps` |
| Hostname | `srv-web-oling` |
| User SSH | `ubuntu` |
| Vhost | Apache |
| DocumentRoot | `/var/www/oling.fr/public` |
| Statut | CONFIRMED OLING PROD |

Autres candidats :

| Host | Source | Resultat | Confidence |
| --- | --- | --- | --- |
| `ovh-vps` / `54.37.230.240` | DNS + `~/.ssh/config` + vhost Apache | vrai serveur prod | HIGH |
| `ovh-vps-mapsi-gpmlm` / `135.125.201.221` | ancien audit | dumps historiques, pas app Symfony OLING | LOW |
| `oling-web` / `90.15.25.87:223` | `~/.ssh/config` | timeout | LOW |
| `oling.fr` / `146.88.233.153:5022` | `~/.ssh/config` | timeout, DNS non concordant | LOW |

## B. PROD APP PATH

- Application : `/var/www/oling.fr`
- Public root : `/var/www/oling.fr/public`
- Indices de confirmation : `composer.json`, `public/index.php`, vhost `oling.fr.conf`, routes/templates OLING, remote Git `oling-mapsi/olingweb`.
- Pas de mecanisme artifact/release separe constate : c'est un checkout Git direct.

## C. PROD COMMIT

- Branch prod : `main`
- Commit prod : `885a81293fb3fb0f24f0668f16eb717521ff004a`
- Remote : `git@github.com:oling-mapsi/olingweb.git`
- Working tree prod : propre.

## D. LOCAL VS PROD COMMIT

| Item | Local | Prod | Statut |
| --- | --- | --- | --- |
| Branch | `main` | `main` | SAME |
| Commit | `885a81293fb3fb0f24f0668f16eb717521ff004a` | `885a81293fb3fb0f24f0668f16eb717521ff004a` | SAME |
| Ahead / behind | 0 / 0 vs origin | 0 / 0 constate par commit identique | SAME |

## E. PROD PHP / SYMFONY

| Item | Prod |
| --- | --- |
| PHP CLI | 8.4.11 |
| Symfony | 6.4.19 |
| APP_ENV | `prod` |
| APP_DEBUG | false |
| Cache dir | `/var/www/oling.fr/var/cache/prod` |

Note : local est en PHP 8.2.28. A surveiller, mais aucun drift fonctionnel detecte dans cet audit.

## F. PROD DATABASE

| Item | Valeur |
| --- | --- |
| Engine | MySQL via Doctrine DBAL PDO MySQL |
| Host | `127.0.0.1` |
| Port | `3306` |
| DB name | `oling_db` |
| DB user | `oling_user` |
| Source config | `/var/www/oling.fr/.env.local` |

Mot de passe non affiche.

## G. MIGRATIONS LOCAL VS PROD

| Item | Local | Prod | Statut |
| --- | --- | --- | --- |
| Available | 58 | 58 | SAME |
| Executed | 58 | 58 | SAME |
| Current | `Version20260825103000` | `Version20260825103000` | SAME |
| New | 0 | 0 | SAME |

Migrations sensibles verifiees comme appliquees en prod :

| Migration | Type | Statut | Risque futur |
| --- | --- | --- | --- |
| `20260824113000` | contenu home/practices | applied local/prod | ne pas rejouer manuellement |
| `20260824143000` | structure projets SEO | applied local/prod | OK |
| `20260824170000` | contenu editorial | applied local/prod | ne pas utiliser comme modele de wording recurrent |
| `20260824173000` | nettoyage contenu IA | applied local/prod | risque si edits admin futurs |
| `20260824174000` | force align site_page | applied local/prod | pattern a eviter |
| `20260824225729` | services/meta SEO | applied local/prod | pattern wording a eviter |
| `20260825103000` | table index chat | applied local/prod | faible |

## H. SCHEMA LOCAL VS PROD

`doctrine:schema:validate` :

- Local : mapping OK, DB non synchronisee.
- Prod : mapping OK, DB non synchronisee.

Le dump SQL read-only est equivalent local/prod. Drift uniquement sur indexes :

```sql
ALTER TABLE projet RENAME INDEX uniq_50159caafd79aeb TO UNIQ_50159CA99F75D7B0;
ALTER TABLE projet_team RENAME INDEX idx_5d82cbbec18272 TO IDX_B3324930C18272;
ALTER TABLE projet_team RENAME INDEX idx_5d82cbbe296cd8ae TO IDX_B3324930296CD8AE;
ALTER TABLE app_user RENAME INDEX uniq_app_user_email TO UNIQ_88BDF3E9E7927C74;
ALTER TABLE legal_page RENAME INDEX uniq_19e4d2cb989d9b62 TO UNIQ_39715897989D9B62;
ALTER TABLE site_page RENAME INDEX uniq_8e131ef2989d9b62 TO UNIQ_2F900BD9989D9B62;
ALTER TABLE site_page RENAME INDEX uniq_site_page_external_id TO UNIQ_2F900BD99F75D7B0;
DROP INDEX uniq_site_page_revision_number ON site_page_revision;
DROP INDEX idx_site_page_revision_lookup ON site_page_revision;
```

Classification : STRUCTURAL_ONLY, non bloquant SEO, a traiter dans une migration technique separee si necessaire.

## I. SITE_PAGE DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 70 | 70 | SAME |
| Checksum contenu | `17bd0d0da02c8bc1b697393c13d24edb` | `17bd0d0da02c8bc1b697393c13d24edb` | IDENTICAL |

Champs signes : `slug`, `title`, `meta_description`, `publication_status`, `hero_badge`, `hero_title`, `hero_intro`, `hero_image`, `hero_side_html`, `body_html`.

Action : aucun merge requis.

## J. HOMEPAGE DRIFT

| Element | Local | Prod | Statut |
| --- | --- | --- | --- |
| `site_page.home` | inclus checksum `site_page` | inclus checksum `site_page` | IDENTICAL |
| `home_section` | count 4, checksum `1ab14eabe6aab2332882979c40383b67` | idem | IDENTICAL |
| featured practices | inclus checksum `practice` | idem | IDENTICAL |

Action : aucun merge requis. Toute future evolution home doit passer par admin DB ou migration strictement idempotente.

## K. PRACTICE DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 4 | 4 | SAME |
| Checksum | `d474a6c7a3661c997c6ecb3215d5168a` | `d474a6c7a3661c997c6ecb3215d5168a` | IDENTICAL |

Slugs couverts : `consulting`, `business-apps`, `expertises-audit`, `mapsi`.

Action : aucun merge requis.

## L. SERVICES DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 34 | 34 | SAME |
| Checksum | `a79202f33ab5eb12a46137f1b0ce2b04` | `a79202f33ab5eb12a46137f1b0ce2b04` | IDENTICAL |

Action : aucun `ONLY_LOCAL`, aucun `ONLY_PROD`, aucun service different detecte par signature.

## M. PROJECTS DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 263 | 263 | SAME |
| `external_id` non null | 193 | 193 | SAME |
| `software_tags` presents | 64 | 64 | SAME |
| featured | 12 | 12 | SAME |
| historical | 0 | 0 | SAME |
| Checksum | `d0b2a4c93fb46429c8a6c1342f3cde87` | `d0b2a4c93fb46429c8a6c1342f3cde87` | IDENTICAL |

Relations :

| Relation | Local | Prod | Statut |
| --- | --- | --- | --- |
| `projet_services` | 112 | 112 | SAME |
| `projet_team` | 0 | 0 | SAME |
| projets avec metier | 70 | 70 | SAME |

Action : aucun merge requis, mais ne pas publier automatiquement les projets `A valider avant publication nominative`.

## N. TEAM DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 7 | 7 | SAME |
| Checksum | `4d3c12ac2fb563d1e236556fc66e3abb` | `4d3c12ac2fb563d1e236556fc66e3abb` | IDENTICAL |
| `projet_team` | 0 | 0 | SAME |
| `services_team` | 48 | 48 | SAME |

Action : aucun merge requis. Ne pas remplir `projet_team` automatiquement.

## O. METIER DRIFT

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| Count | 11 | 11 | SAME |
| Checksum | `1b97b0e8856a6169f9e99e68ae1b3b5c` | `1b97b0e8856a6169f9e99e68ae1b3b5c` | IDENTICAL |

Action : aucun merge requis.

## P. RESOURCES DRIFT

Les ressources sont stockees dans `site_page` avec slug `ressource-*` et contenu long dans `hero_side_html`.

| Metric | Local | Prod | Statut |
| --- | --- | --- | --- |
| `ressource-*` + hub `ressources` | 18 lignes | 18 lignes | SAME |
| Inclus checksum `site_page` | oui | oui | IDENTICAL |

Action : aucun contenu ressource publie uniquement en prod detecte. PROD_WINS ne s'applique pas aujourd'hui, mais doit rester la regle si une publication admin/prod apparait.

## Q. HARDCODED CONTENT

`SeoLandingController.php` contient encore des narratives hardcodees pour CRM, GMAO, SI Finance :

- DB `site_page` gagne pour `title`, `meta_description`, `hero_badge`, `hero_title`, `hero_intro`, `body_html`.
- Controller gagne pour blocs narratifs : `promise`, titres de sections, `focus`, `missionPhases`, `deliverables`, `projectContexts`, `clientTypes`, `supportLinks`, `schemaServiceType`.
- Prod utilise exactement le meme code que local.
- Passage futur en DB : il faudra migrer ces tableaux vers `site_page.body_html` ou une table structuree, sinon ces contenus restent non administrables.

`PublicSiteConfig.php` :

- STRUCTURAL : schemas de blocs, CTA defaults, fallback pages expertises/secteurs.
- TECHNICAL DEFAULT : routes CTA, images fallback, tags par defaut, maillage.
- EDITORIAL FALLBACK : titres, intros, listes, textes commerciaux. A reduire progressivement.

Aucune refonte effectuee.

## R. SOURCE OF TRUTH FINAL

| Donnee | Source finale |
| --- | --- |
| Code | Git |
| Schema | Doctrine migrations |
| Contenu editorial courant | DB via admin |
| Homepage | DB via admin |
| Practices | DB via admin |
| Services | DB via admin |
| Projets | DB via admin/import controle |
| Team | DB via admin |
| Metiers | DB via admin |
| Ressources | DB/admin/publication workflow |
| Progiciels canoniques | `config/oling/software_taxonomy.php` |
| Rattachements projets/progiciels | DB |
| `PublicSiteConfig` | fallback minimal |
| SEO landing narratives | a terme DB, plus controller hardcode |

## S. PROD_WINS

Aucun ecart PROD_WINS detecte dans les tables auditees.

## T. LOCAL_WINS

Aucun ecart LOCAL_WINS detecte dans les tables auditees.

## U. MERGE_REQUIRED

Aucun merge de contenu requis aujourd'hui.

Seul point a traiter separement : drift schema technique sur indexes, classification STRUCTURAL_ONLY.

## V. BACKUP PROCEDURE

Ne pas executer sans validation.

Emplacement conseille hors Git :

- `/var/backups/oling/mysql/`
- `/var/backups/oling/mysql-clear/` seulement si politique interne confirmee
- espace dispo : environ 40G sur `/`
- taille app `/var/www/oling.fr` : environ 791M

Commandes redactees :

```bash
TS=$(date +%Y%m%d-%H%M%S)
mkdir -p /var/backups/oling/mysql/$TS
mysqldump -u oling_user -p --single-transaction --routines --triggers oling_db > /var/backups/oling/mysql/$TS/oling_db_full.sql
sha256sum /var/backups/oling/mysql/$TS/oling_db_full.sql > /var/backups/oling/mysql/$TS/oling_db_full.sql.sha256
```

Backup editorial cible :

```bash
TS=$(date +%Y%m%d-%H%M%S)
mysqldump -u oling_user -p --single-transaction oling_db site_page practice services projet metier team home_section legal_page projet_services services_team projet_team > /var/backups/oling/mysql/$TS/oling_editorial.sql
sha256sum /var/backups/oling/mysql/$TS/oling_editorial.sql > /var/backups/oling/mysql/$TS/oling_editorial.sql.sha256
```

Backup uploads :

```bash
TS=$(date +%Y%m%d-%H%M%S)
tar -C /var/www/oling.fr -czf /var/backups/oling/uploads-$TS.tar.gz public/uploads
sha256sum /var/backups/oling/uploads-$TS.tar.gz > /var/backups/oling/uploads-$TS.tar.gz.sha256
```

## W. REFRESH LOCAL PROCEDURE

1. Backup DB locale.
2. Export prod cible uniquement des tables necessaires.
3. Exclure/anonymiser donnees personnelles hors besoin.
4. Importer dans une base locale temporaire.
5. Comparer par `slug`, `external_id`, designation stable.
6. Appliquer localement seulement les lignes/champs PROD_WINS.
7. Rejouer migrations locales si besoin.
8. Tester rendu local.

Aujourd'hui : refresh local non necessaire, DB locale et prod sont identiques sur le perimetre audite.

## X. FUTURE DEPLOY PROCEDURE

1. Verifier Git local propre.
2. Snapshot read-only prod : commit, migrations, schema, checksums DB.
3. Classer les drifts eventuels.
4. Integrer au local les PROD_WINS avant modification.
5. Modifier localement.
6. Editorial courant : admin/import controle.
7. Structure : migration Doctrine.
8. Tests local.
9. Backup prod complet + editorial + uploads si besoin.
10. Deploy code.
11. Migrations structurelles seulement.
12. Cache clear controle.
13. Smoke test technique.
14. Smoke test SEO : title/meta/H1/canonical/sitemap.

## Y. ROLLBACK PROCEDURE

Objectif < 15 min :

1. Code : revenir au commit precedent deploye.
2. Contenu DB : restaurer dump editorial cible.
3. Schema : utiliser `down` seulement si teste ; sinon restore DB cible/complet.
4. Uploads : restaurer archive `public/uploads`.
5. Cache : clear/warmup seulement apres decision rollback.
6. Smoke test : `/`, `/amoa-si`, `/business-apps/erp`, `/crm`, `/gmao`, `/si-finance`, `/projets`, `/rgpd`, `/cyber-securite`, `/conseil-qualite`.

## Z. BLOCKERS BEFORE SEO

Aucun bloqueur data local/prod sur le perimetre audite.

Fixes avant grosse phase SEO :

1. Decider si le drift index doit etre traite par migration technique.
2. Encadrer par regle ecrite l'interdiction des migrations de wording recurrentes.
3. Prevoir migration/refonte future pour sortir les narratives SEO hardcodees du controller.
4. Executer backup valide juste avant toute modification.

PRODUCTION DATA VISIBILITY:
COMPLETE

LOCAL/PROD RECONCILIATION:
READY

SEO PHASE 1:
SAFE TO START
