# OLING WEB - Lot final Analytics

Date : 2026-10-13
Environnement : local uniquement
Production : non modifiee

## Corrections realisees

- KPI separes : sessions/pages collecteur, prospects base metier, taux non affiche si non rattachable.
- Taux de conversion neutralise tant qu'aucun lien serveur fiable session -> lead n'existe.
- Famille `/` corrigee en `Accueil`.
- Dashboard compacte : KPI reduits, sections ordonnees, sources explicites, reglages de suivi deplaces en bas.
- Mobile corrige a 375 px, 768 px et 1440 px : pas d'erreur JS, navigation admin en defilement local, tableaux scrollables localement.
- GA4 local : `GA4_PROPERTY_ID=374507565` configure, aucun secret ajoute.
- Purge securisee : option `--event-id-prefix` ajoutee pour tester sans toucher aux donnees hors fixture.

## Definitions KPI

- Visites mesurees : sessions distinctes consenties issues du collecteur first-party.
- Pages consultees : evenements `page_view` consentis issus du collecteur first-party.
- Prospects confirmes : total metier issu des contacts, leads chat IA et questionnaires ERP.
- Taux de conversion : non calculable tant qu'un prospect metier n'est pas relie cote serveur a une session collectee.
- Sources/parcours : donnees collecteur uniquement.
- Conversions commerciales : base metier pour contacts/chat/ERP, collecteur pour clics telephone/email/telechargements.

## Captures

- `var/recette/analytics-dashboard-mobile-375.png`
- `var/recette/analytics-dashboard-tablet-768.png`
- `var/recette/analytics-dashboard-desktop-1440.png`

Resultat Playwright : `data-analytics-excluded=1`, erreurs JS `0`.

## Tests

- `php -l src/Command/AnalyticsPurgeCommand.php` : OK
- `php bin/console lint:twig templates/admin/analytics/index.html.twig templates/admin/_nav.html.twig` : OK
- `npm run build` : OK
- `./vendor/bin/phpunit tests/AnalyticsTrackingTest.php` : OK, 6 tests, 19 assertions
- `./vendor/bin/phpunit tests/ContactControllerTest.php` : OK, 5 tests, 17 assertions
- `./vendor/bin/phpunit tests/ErpQuestionnaireTest.php` : OK, 10 tests, 58 assertions
- `./vendor/bin/phpunit tests/ChatConversationManagerTest.php` : OK, 3 tests, 6 assertions
- Export anonyme : 302 vers login.
- Faux POST public `contact_confirmed` : ne cree pas de prospect metier.
- Purge fixture : 1 ancien evenement supprime, evenement recent conserve puis nettoye.

## GA4

Statut : connecteur code pret, configuration credentials requise.

Procedure credentials :

1. Activer Google Analytics Data API dans le projet Google Cloud.
2. Creer un compte de service dedie lecture analytics.
3. Telecharger la cle JSON hors Git, sur le serveur cible.
4. Donner au mail du compte de service le role Lecteur sur la propriete GA4 `374507565`.
5. Configurer `GA4_CREDENTIALS_PATH` vers le fichier JSON prive.
6. Verifier que `GA4_PROPERTY_ID=374507565`.
7. Vider le cache Symfony et ouvrir `/admin/analytics`.

## Mise en production preparee

Sans execution :

1. Sauvegarde DB.
2. Deployer le code.
3. Installer assets buildes.
4. Configurer secrets/env GA4 hors Git.
5. Executer migration Doctrine.
6. Vider cache prod.
7. Recette HTTPS : consentement, exclusion admin, dashboard, export CSV normal, absence GA4 sur appareil exclu.

Retour arriere :

1. Revenir au tag/commit precedent.
2. Restaurer assets precedents.
3. Vider cache prod.
4. Si necessaire, conserver la table analytics sans l'exposer ; aucune donnee metier critique n'en depend.

## Anomalies residuelles

- GA4 reel non teste sans credentials.
- Taux de conversion volontairement non calcule faute de lien serveur fiable session -> prospect.
- Le menu admin mobile reste en defilement horizontal local pour preserver les autres ecrans sans refonte.

## Statuts

- KPI ET DENOMINATEURS : PASS
- UX DESKTOP : PASS
- UX MOBILE : PASS
- EXCLUSION INTERNE : PASS
- PARCOURS ET CONVERSIONS : PASS
- GA4 CONNECTEUR : CONFIGURATION REQUISE
- TESTS ET SECURITE : PASS
- PRET POUR PRODUCTION : NON
- PRODUCTION MODIFIEE : NON
