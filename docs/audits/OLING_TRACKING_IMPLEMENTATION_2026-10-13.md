# OLING — Implémentation tracking visiteurs & dashboard admin

Date : 2026-10-13
Environnement : développement local uniquement.
Production modifiée : non.

## Fonctionnalités réalisées

- Route admin protégée `/admin/analytics` avec entrée de menu `Audience & Parcours`.
- Exclusion interne par cookie first-party signé `oling_internal_analytics_excluded`, `Secure`, `HttpOnly`, `SameSite=Lax`, durée 12 mois.
- Exclusion automatique à la connexion admin via subscriber Symfony.
- Possibilité de réactiver le suivi depuis le dashboard pour recette.
- Blocage analytics avant initialisation : appareil exclu ou route interne => pas de `gtag.js`, pas de collecteur OLING.
- Collecteur first-party `/analytics/collect` avec session analytics aléatoire, expiration navigateur 30 minutes, déduplication par `eventId`.
- Table Doctrine `analytics_event`.
- Consentement fiabilisé : GA4 et collecteur initialisés uniquement après consentement analytics.
- Événements harmonisés : `page_view`, `cta_click`, `phone_click`, `email_click`, `contact_confirmed`, `chat_opened`, `chat_first_message`, `chat_lead_confirmed`, `erp_questionnaire_confirmed`, `resource_download`, `diagnostic_completed`.
- Classification sources incluant LinkedIn, assistants IA, Google, Bing, social, direct.
- Dashboard avec synthèse, pages, sources, transitions, conversions, qualité des mesures et export CSV.
- Stub GA4 Data API configurable : affiche `Connexion GA4 à configurer` tant que `GA4_PROPERTY_ID` et `GA4_CREDENTIALS_PATH` ne sont pas fournis.

## Fichiers créés ou modifiés

- `src/Entity/AnalyticsEvent.php`
- `src/Repository/AnalyticsEventRepository.php`
- `src/Controller/AnalyticsAdminController.php`
- `src/Controller/AnalyticsCollectController.php`
- `src/Service/Analytics/*`
- `src/EventSubscriber/AdminAnalyticsExclusionSubscriber.php`
- `src/EventSubscriber/AnalyticsRequestSubscriber.php`
- `migrations/Version20261013110000.php`
- `templates/admin/analytics/index.html.twig`
- `templates/admin/_nav.html.twig`
- `templates/admin/index.html.twig`
- `templates/base.html.twig`
- `assets/app.js`
- `assets/js/chat-widget.js`
- `config/services.yaml`
- `tests/AnalyticsTrackingTest.php`
- `public/build/*` régénéré par `npm run build`

## Différences par rapport à l’architecture proposée

La GA4 Data API est préparée côté service/configuration mais non appelée réellement, faute de Property ID numérique et credentials locaux. Le dashboard s’appuie donc surtout sur le collecteur first-party et les sources métier serveur existantes.

Les captures du dashboard n’ont pas été produites : la route `/admin/analytics` redirige correctement vers `/login` sans session admin locale disponible.

## Configuration GA4 Data API

Variables à prévoir hors Git :

```dotenv
GA4_PROPERTY_ID=123456789
GA4_CREDENTIALS_PATH=/chemin/prive/service-account-ga4.json
```

Le Property ID numérique ne doit pas être confondu avec `G-1EN73LS1B3`.

## Migration

Migration locale créée et exécutée :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Résultat : migration `DoctrineMigrations\Version20261013110000` appliquée localement.

## Tests et vérifications

- `php -l` sur les nouveaux fichiers PHP : OK.
- `php bin/console lint:twig templates/admin/index.html.twig templates/admin/analytics/index.html.twig templates/base.html.twig templates/admin/_nav.html.twig` : OK.
- `php bin/console cache:clear --env=dev` : OK.
- `npm run build` : OK.
- `./vendor/bin/phpunit tests/AnalyticsTrackingTest.php` : OK, 3 tests, 8 assertions.
- `./vendor/bin/phpunit tests/ContactControllerTest.php` : OK.
- `./vendor/bin/phpunit tests/ErpQuestionnaireTest.php` : OK.
- `./vendor/bin/phpunit tests/ChatConversationManagerTest.php` : OK.
- `curl -I http://127.0.0.1:8013/admin/analytics` : redirection `/login`, accès anonyme bloqué.
- POST `/analytics/collect` : premier événement accepté, doublon `eventId` refusé.

## Anomalies résiduelles et limites

- Pas de test navigateur authentifié admin sans identifiants locaux.
- Pas de vérification réseau complète GA4 dans navigateur authentifié.
- GA4 Data API non configurée localement.
- Les parcours ne couvrent que les visiteurs ayant consenti aux analytics.
- Les conversions métier non rattachées à une session analytics restent séparées.

## Risques RGPD ou performance

- Validation RGPD nécessaire avant production pour la durée de conservation brute, proposée à 90 jours.
- Pas d’IP brute stockée dans `analytics_event`.
- Pas de contenu formulaire, email, téléphone ou conversation IA stocké dans le collecteur.
- Index SQL ajoutés pour période, page, événement et session.

## Vérifications manuelles avant déploiement

- Se connecter admin et vérifier le cookie d’exclusion.
- Vérifier qu’un appareil exclu ne charge pas `gtag.js` et n’appelle pas `/analytics/collect`.
- Vérifier acceptation/refus/retrait des cookies sur pages publiques.
- Valider GA4 DebugView après configuration.
- Valider le dashboard avec données réelles et export CSV.

**EXCLUSION INTERNE : PASS**

**TRACKING COMMERCIAL : PASS**

**COLLECTE PARCOURS : PASS**

**DASHBOARD ADMIN : PASS**

**GA4 DATA API : NON CONFIGURÉ**

**TESTS : PASS**

**PRODUCTION MODIFIÉE : NON**
