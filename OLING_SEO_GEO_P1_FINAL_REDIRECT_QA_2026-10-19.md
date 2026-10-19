# OLING SEO/GEO P1 — dernière recette redirections

19 octobre 2026. Base : `94eaec03ed7f663cc44855009114715505985abb` ; branche `codex/seo-geo-p1-prod-20261019`.

**PASS — réserve UTM levée. Aucune production touchée, aucun déploiement.**

Le seul changement fonctionnel concerne `/consulting` : la route fixe `amoa_si` est générée par Symfony, puis la query string normalisée par `Request::getQueryString()` est ajoutée séparément. Les paramètres ne sont jamais transmis comme arguments de routage. Ils ne peuvent modifier ni host, ni scheme, ni destination, ni fragment. Aucun hostname codé en dur. L’ordre des clés peut être normalisé par Symfony ; les valeurs et l’ordre des listes sont conservés.

Les autres aliases et leurs comportements restent inchangés. `/practice/consulting` n’est pas modifié.

| Contrôle | Résultat |
|---|---|
| /consulting sans paramètres | 301 directe vers /amoa-si |
| /consulting avec UTM, ref, gclid, tableaux et valeurs encodées | Paramètres conservés |
| /practice/consulting avec les mêmes cas | 301 directe, paramètres conservés |
| Sauts HTTP suivis jusqu’à la destination | Un seul ; destination 200 |
| _route, _host, _scheme, _fragment, destination, url | Restent de simples paramètres de query ; cible fixe |
| Déploiement sous /preview | /preview/amoa-si, sans domaine imposé |
| Tests redirections | 16 tests / 103 assertions : PASS |
| Vérifications HTTP locales | 17 cas : PASS |
| Recette MySQL Community Server 8.4.10 | 17 / 0 / 0 |
| Migration / comparateur / plan SEO | Octets strictement identiques à la base |
| Pages HTML comparées à la base | 11 pages identiques |
| GMAO / Finance / RGPD / Cyber | Métadonnées, texte, liens et schemas inchangés |
| llms.txt | Inchangé ; réponse locale identique au fichier |
| Sitemap FR / crawl | 116 URL, alias consulting absent ; toutes répondent 200 |
| JSON / Twig / YAML | 46 / 187 / 3 fichiers : PASS |
| PHP / routes / diff check / rendered HTML | PASS |

Les 11 pages comparées : `/amoa-si`, `/business-apps/erp`, `/erp-progiciel`, `/expertises/amoa-erp-applications-metiers`, `/consulting/assistance-a-maitrise-douvrage`, `/expertises-audit/qse`, `/conseil-qualite`, `/gmao`, `/si-finance`, `/expertises-audit/rgpd`, `/cyber-securite`.

Suite complète ciblée : **49 tests, 14 005 assertions ; 48 réussis, 1 échec MAPSI préexistant hors périmètre**. L’échec reste `CorePagesSourceTest::testMapsiPracticeLinksToInternalMapsiServicePages` (attente de `/mapsi/automatisation-et-workflow`). Le test et sa source de données sont strictement identiques à la base. Cette exception est celle autorisée dans la demande ; elle n’est pas corrigée ici.

MySQL est exécuté uniquement sur l’instance locale jetable, sans TCP ni production. La base créée par le test est supprimée et l’instance arrêtée après recette.

Preuves : [redirections et non-régression HTTP](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/redirect-qa-20261019/http-proof.json), [MySQL](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/redirect-qa-20261019/mysql-proof.json), [tests](/Users/florestanrouet/.codex/worktrees/oling-seo-geo-p1-20261019/olingw/var/seo-p1/redirect-qa-20261019/phpunit.log). Le crawl et le HTML sont aussi contrôlés par le script local existant `var/seo-p1/check-rendered.py`.

Le nouveau commit contient uniquement le contrôleur, `tests/ConsultingRedirectTest.php` et ce rapport. Le rapport historique du déploiement interrompu est exclu. Aucun amend/rebase, aucun push ni fichier temporaire versionné. Le SHA exact du commit contenant ce rapport est fourni dans le message de livraison.

**READY FOR PROD DEPLOYMENT: YES**, selon les critères de ce lot. Prochaine étape : nouvelle autorisation explicite de déploiement, puis préflight production et contrôle de dérive avant toute écriture.
