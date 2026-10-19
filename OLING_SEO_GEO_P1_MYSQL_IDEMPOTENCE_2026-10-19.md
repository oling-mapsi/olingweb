# OLING SEO/GEO P1 — idempotence MySQL

19 octobre 2026. **Correctif JSON validé sur MySQL 8.4.10 : 17 / 0 / 0.** Aucune connexion à la production, aucun déploiement pendant ce lot. La release inactive antérieure n’a pas été modifiée.

**Réserve avant nouvelle revue de déploiement :** `/consulting` perd les paramètres UTM dans le candidat initial. Cette route reste strictement inchangée, conformément à l’interdiction de modifier les redirections. L’idempotence est corrigée ; la conformité SEO complète du futur déploiement reste bloquée sur ce point hors diff.

## A. Root cause

Le candidat initial `c75be97fa8966fa91257b4b65ee47f39fe3c518c` utilise une comparaison stricte de tableaux PHP pour décider de réécrire les payloads JSON. MySQL réordonne les clés associatives. Les sous-objets `landingNarrative.citablePassages.1` de la ligne 11 et `citablePassages.0` de la ligne 6 sont donc réécrits malgré leur équivalence sémantique.

La régression est reproduite sur la **vraie instance MySQL locale** avec l’ancien code extrait de Git : **premier passage 17 UPDATE ; second 2 UPDATE ; IDs [6, 11]**. Les deux valeurs relues en base sont sémantiquement identiques aux paramètres des UPDATE redondants.

## B. Correctif

Dans `Version20261019220000`, `normalizeJsonForComparison()` trie récursivement les clés des tableaux associatifs et conserve l’ordre des tableaux séquentiels. Les scalaires restent inchangés.

`valuesAreEquivalent(fieldType, current, expected)` centralise la comparaison : structures JSON décodées normalisées avec `===` ; champs SQL ordinaires strictement comparés avec `===`. La migration l’utilise pour ses gardes JSON, la détection du changement de payload et la sélection des champs à mettre à jour. La restauration ciblée de recette réutilise **ce même comparateur**.

Aucun changement de texte, owner, title/H1, CTA, redirection, sitemap, template, traduction ou `llms.txt`. Le plan éditorial validé reste inchangé. Aucun tri des listes, aucune comparaison permissive, aucune égalité de chaînes JSON brutes pour décider d’un changement JSON.

## C. Tests unitaires

| Cas obligatoire | Résultat |
|---|---|
| Objet simple : clés permutées | EQUAL |
| Objet imbriqué / citablePassages : clés permutées | EQUAL |
| Liste ordonnée inversée | DIFFERENT |
| Entier 1 / chaîne "1" | DIFFERENT |
| Objet vide / clé value=null | DIFFERENT |
| Ligne production 11, passage 1 réordonné | NO UPDATE |
| Ligne production 6, passage 0 réordonné | NO UPDATE |

Tests complémentaires : booléen/entier, entier/flottant, null/chaîne vide, absence/null imbriqués, ordre de liste imbriquée ; les champs texte contenant des chaînes JSON restent stricts. Tests de migration : champs non concernés et traductions conservés, idempotence et rejet d’une vraie dérive.

Suite ciblée complète : **33 tests, 13 902 assertions ; 32 réussis, 1 échec MAPSI préexistant**. Les tests du comparateur et de la migration donnent respectivement **11 tests / 15 assertions** et **4 tests / 13 assertions**, tous réussis.

L’échec inchangé est `CorePagesSourceTest::testMapsiPracticeLinksToInternalMapsiServicePages` : attente de `/mapsi/automatisation-et-workflow` dans une liste source où elle n’est pas présente. Son test et `data/i18n/practice_narratives.fr.json` sont identiques au candidat initial. Aucun correctif MAPSI.

## D. Tests MySQL 8.4

- Serveur exécuté : **MySQL Community Server 8.4.10**, ARM64/macOS, [archive officielle](https://cdn.mysql.com/archives/mysql-8.4/mysql-8.4.10-macos15-arm64.tar.gz).
- Instance jetable : `/private/tmp/oling-p1-mysql84-20261019`, socket privé, `--skip-networking --mysqlx=OFF`. Aucun service système installé, aucun port TCP ouvert.
- Base de recette de la dernière exécution : `oling_p1_recipe_543e1b7621a1`, supprimée par le test ; instance arrêtée après recette.
- Données : export public de baseline déjà conservé localement, SHA-256 `a80c8febff4d4892a7080a810c20d5148adb5b86f1d2e9842eac1521942579eb`. Aucun nouvel export production.
- Schéma minimal des cinq tables lues par la migration : les deux tables parentes ID/slug et les trois tables de traductions. **192 + 108 + 12 = 312 lignes de traduction**, toutes locales importées. Colonnes `structured_data` et `public_narrative` en vrai type MySQL **JSON**, IDs en INT et champs ordinaires en LONGTEXT. Ce n’est pas une recréation complète du schéma applicatif.

Canonicalisation réellement vérifiée par INSERT puis SELECT :

```json
{"zz":1,"a":{"long":true,"b":2}}
```

Valeur relue :

```json
{"a": {"b": 2, "long": true}, "zz": 1}
```

Les chaînes et les ordres de clés diffèrent ; le comparateur sémantique retourne **TRUE**. Le test MySQL complet passe avec **13 752 assertions**, incluant la reproduction de l’ancien défaut et la restauration de toutes les valeurs.

Preuve : [proof.json](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/mysql84/proof.json). Journal : [phpunit.log](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/mysql84/phpunit.log).

Pour reproduire : initialiser une instance jetable MySQL 8.4 sur socket privé sous `/private/tmp/oling-p1-mysql84-*`, puis exécuter le test opt-in :

```sh
OLING_P1_MYSQL_SOCKET=/private/tmp/oling-p1-mysql84-20261019/mysql.sock \
OLING_P1_BASELINE_FILE=/private/tmp/oling-seo-p1-source-20261019/public-content.json \
OLING_P1_ORIGINAL_MIGRATION="$PWD/var/seo-p1/mysql84/original/migrations/BeforeJsonFix.php" \
OLING_P1_MYSQL_PROOF_FILE="$PWD/var/seo-p1/mysql84/proof.json" \
/opt/homebrew/opt/php@8.4/bin/php vendor/bin/phpunit tests/SeoP1MySqlIdempotenceTest.php
```

L’ancienne migration de comparaison est extraite du commit initial, renommée `BeforeJsonFix`, avec son plan inchangé dans le même agencement relatif, sous `var/seo-p1/mysql84/original/`. Elle est uniquement une fixture ignorée. Le test versionné crée et supprime sa propre base `oling_p1_recipe_*`, refuse tout socket hors du répertoire privé local et exige MySQL 8.4. Sans variables opt-in il est SKIPPED ; dans cette recette il a réellement été exécuté.

## E. Migration premier passage

**17 UPDATE exécutés**, 14 lignes `site_page_translation`, 3 lignes `service_translation`, uniquement FR. SQL du candidat corrigé appliqué en transaction sur la base MySQL de recette. Une ligne affectée est vérifiée pour chacune des 17 instructions.

La sauvegarde ciblée contient table, ID, locale, champs, valeurs avant et cibles. Elle provient des SELECT de recette avant la première écriture ; aucune sauvegarde de production n’est écrasée.

## F. Migration deuxième passage

**0 UPDATE.** La migration effectue de nouveaux SELECT sur MySQL après le premier passage. Les valeurs ne proviennent pas d’un cache mémoire des cibles. Les cas 6 et 11 sont donc couverts après canonicalisation effective.

## G. Migration troisième passage

**0 UPDATE.** Troisième lecture indépendante, aucune nouvelle écriture. Résultat stable.

## H. Test véritable drift

Deux altérations volontaires dans la base jetable : `hero_title` de la ligne 6 et le texte du passage JSON de la ligne 11. Dans les deux cas : **Editorial drift / UNEXPECTED CMS DRIFT**, migration refusée, aucune instruction en attente exécutée et snapshot intégral inchangé après le refus.

La normalisation ignore uniquement l’ordre des clés associatives. Elle ne neutralise ni un texte différent, ni un type scalaire différent, ni une liste réordonnée, ni la différence structurelle entre clé absente et clé null.

## I. Backup / restore

Cycle complet : baseline → sauvegarde ciblée des 17 lignes → migration → contrôles 0/0 → restauration ciblée. Les champs sont verrouillés et comparés avant la restauration ; une vraie modification JSON survenue après migration provoque le refus de restauration, vérifié sans écriture partielle.

Après retrait de la dérive de test, **17 lignes restaurées**. Toutes les colonnes des **312 lignes de traduction** correspondent à la baseline : égalité sémantique pour les JSON, stricte pour les autres champs. Les traductions EN/ES et les autres lignes restent préservées. Le `down()` irréversible n’est pas utilisé. Aucun outil de rollback production n’a été exécuté ou modifié pendant ce lot.

## J. Non-régression SEO

Les sources éditoriales et le plan sont strictement identiques à `c75be97f`. Le HTML local est rendu sur la base de recette applicative SQLite existante ; la preuve d’idempotence repose séparément sur MySQL 8.4.10 réel.

| Contrôle local | Résultat |
|---|---|
| AMOA SI owner ; deux alias 301 en un saut | PASS |
| /practice/consulting conserve les UTM | PASS |
| /consulting conserve les UTM | **FAIL préexistant au correctif, hors diff** |
| ERP owner / support cadrage / hub / support AMOA métier | PASS, identiques au candidat validé |
| Questionnaire ERP présent | PASS, aucune soumission |
| QSE et ISO 9001 distincts, menu mobile à deux entrées | PASS |
| llms.txt rendu et cartographie unique | PASS, contenu inchangé |
| Sitemap FR | PASS : 116 URL, alias consulting absent, owners/supports présents |
| Crawl des 116 URL / anciens liens consulting | PASS : 200 partout, aucun lien obsolète |
| GMAO, Finance, RGPD, Cyber : title/H1/canonical/texte/schema | PASS : identiques à la baseline locale |
| JSON | PASS : 46 fichiers |
| Twig | PASS : 187 fichiers |
| YAML | PASS : 3 fichiers |
| PHP / routes / diff check | PASS |
| HTML rendu | PASS : 12 routes ciblées, metadata/CTA/breadcrumb/schema |
| Desktop / mobile | PASS : 7 pages × 1440×1000 et 390×844 ; aucune image cassée ni débordement à ces tailles |

**Réserve UTM :** le contrôle supplémentaire révèle que `/consulting?utm_source=...` aboutit à `/amoa-si` sans paramètres, car le contrôleur validé redirige avec un tableau de paramètres vide. Ce code est inchangé. Le périmètre interdit sa correction ici ; il faut le traiter séparément avant de présenter un candidat entièrement conforme au précédent cahier de déploiement. L’owner AMOA SI et les deux cibles 301 restent corrects. Le statut AMOA SI final est conservateur : FAIL pour cette conformité complète, sans régression causée par le correctif JSON.

Les contrôles et les captures sont dans [mysql84](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/mysql84). Exemple desktop et mobile :

![QSE desktop locale](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/mysql84/visual/expertises-audit_qse-desktop.jpg)

![ERP cadrage mobile local](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/mysql84/visual/erp-progiciel-mobile.jpg)

## K. Diff final

Branche : `codex/seo-geo-p1-prod-20261019`. Parent : `c75be97fa8966fa91257b4b65ee47f39fe3c518c`. Nouveau commit : celui contenant ce rapport ; SHA exact fourni dans le message de livraison.

Le commit contient uniquement :

- `migrations/Version20261019220000.php` : comparaison JSON centralisée.
- `tests/SeoP1CorrectionMigrationTest.php` : deux cas exacts supplémentaires.
- `tests/SeoP1JsonComparisonTest.php` : invariants du comparateur.
- `tests/SeoP1MySqlIdempotenceTest.php` : recette MySQL opt-in, dérive et restauration.
- `OLING_SEO_GEO_P1_MYSQL_IDEMPOTENCE_2026-10-19.md` : ce rapport.

Le précédent rapport de déploiement reste un fichier local distinct non inclus. Ni les données de recette, ni l’ancienne migration fixture, ni le binaire MySQL, ni les outils de `/private/tmp` ne sont versionnés. Historique conservé, aucun amend/rebase. Aucun push ou déploiement effectué dans ce lot.

**Idempotence corrigée et démontrée. Nouvelle revue production complète : non prête tant que la réserve UTM de `/consulting` n’est pas résolue dans un périmètre autorisé.** L’échec MAPSI préexistant reste documenté et hors lot.
