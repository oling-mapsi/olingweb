# OLING.FR - Recette finale tracking visiteurs & dashboard admin

Date : 2026-10-13
Environnement : local uniquement (`127.0.0.1:8013`)
Production : non modifiee

## Perimetre recette

- Authentification admin locale verifiee.
- Dashboard admin `/admin/analytics` verifie en desktop et mobile.
- Exclusion interne verifiee cote page et cote collecteur.
- Collecteur interne verifie : acceptation, anti-doublon, rejet chemins internes.
- Export CSV verifie via front controller local.
- GA4 Data API implemente avec OAuth service account, non appelee en reel faute de configuration locale.
- Commande purge ajoutee et testee sans suppression.

## Captures

- Desktop : `var/recette/analytics-dashboard-desktop.png`
- Mobile : `var/recette/analytics-dashboard-mobile.png`

Resultat Playwright :

- `data-analytics-excluded=1`
- erreurs JS : `0`

## Tests executes

- `php -l src/Service/Analytics/Ga4DataApiClient.php` : OK
- `php -l src/Command/AnalyticsPurgeCommand.php` : OK
- `php bin/console lint:twig templates/admin/index.html.twig templates/admin/analytics/index.html.twig templates/base.html.twig templates/admin/_nav.html.twig` : OK
- `php bin/console cache:clear --env=dev` : OK
- `./vendor/bin/phpunit tests/AnalyticsTrackingTest.php` : OK, 5 tests, 14 assertions
- `./vendor/bin/phpunit tests/ContactControllerTest.php` : OK, 5 tests, 17 assertions
- `./vendor/bin/phpunit tests/ErpQuestionnaireTest.php` : OK, 10 tests, 58 assertions
- `./vendor/bin/phpunit tests/ChatConversationManagerTest.php` : OK, 3 tests, 6 assertions
- `npm run build` : OK
- `node tests/e2e/analytics-dashboard-recette.mjs` : OK
- `php bin/console app:analytics:purge --days=99999` : OK, 0 suppression

## Tests HTTP locaux

- Anonyme `/admin/analytics` : 302 vers `/login`.
- Admin `/admin/analytics?period=7|30|90` : 200.
- Export CSV : 200 via `/index.php/admin/analytics/export.csv?period=30`.
- Collecteur public payload valide : `success=true`.
- Doublon `eventId` : `success=false`.
- Chemin interne `/admin/analytics` : `success=false`.
- Cookie interne signe : `success=false` cote collecteur.
- Evenements de recette nettoyes en base locale.

Note : le serveur PHP natif ne route pas directement l'URL `.csv`; le front controller local confirme le controleur Symfony.

## Corrections finales appliquees

- Injection `@http_client` ajoutee au client GA4.
- Client GA4 remplace par une implementation Data API reelle avec JWT service account.
- Commande `app:analytics:purge` ajoutee.
- Script Playwright de recette ajoute.
- Initialisations JS globales protegees quand les elements front sont absents en admin.
- Tableaux dashboard rendus responsives sur mobile.

## Limites restantes

- GA4 reel non valide sans `GA4_PROPERTY_ID` et `GA4_CREDENTIALS_PATH`.
- Validation production HTTPS non executee par demande : local uniquement.
- Le compte admin de recette existe en base locale.

## Conclusions

- TRACKING PUBLIC : PASS
- EXCLUSION INTERNE ADMIN : PASS
- DASHBOARD ADMIN : PASS
- EXPORT CSV : PASS
- PURGE : PASS
- TESTS AUTOMATISES : PASS
- GA4 DATA API CODE : PASS
- GA4 DATA API CONNEXION REELLE : FAIL - configuration absente localement
- PRET DEPLOIEMENT PRODUCTION : FAIL - secrets GA4 et recette HTTPS production requis
- PRODUCTION MODIFIEE : PASS - aucune modification production
