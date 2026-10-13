# OLING Analytics - Finalisation GA4 et attribution conversions

Date : 2026-10-13

## Statut

- Production : non modifiée, aucun déploiement effectué.
- Dashboard `/admin/analytics` : ergonomie conservée, ajout métier limité aux conversions attribuables.
- Property GA4 : `374507565`.
- GA4 Data API : code prêt, smoke-test ajouté, mais accès réel bloqué par credentials absents ou non lisibles.

## GA4

Commande de contrôle :

```bash
php bin/console app:analytics:ga4:smoke-test
```

Résultat local :

```text
GA4 non configuré : définir GA4_PROPERTY_ID et GA4_CREDENTIALS_PATH vers un JSON de compte de service lisible.
```

Action humaine requise :

1. Activer Google Analytics Data API côté Google Cloud.
2. Créer ou choisir un compte de service.
3. Ajouter son email dans la propriété GA4 `374507565` avec un rôle lecture seule.
4. Déposer le JSON hors dépôt Git.
5. Configurer `GA4_PROPERTY_ID=374507565` et `GA4_CREDENTIALS_PATH=/chemin/hors-git/service-account.json`.
6. Relancer le smoke-test, puis vider le cache applicatif.

Aucun secret n'a été ajouté au dépôt, aux logs applicatifs ou au navigateur.

## Attribution conversions

Mécanisme implémenté :

- Session analytics collectée uniquement après consentement.
- Identifiant de session transmis par champs cachés/payloads.
- Conversion confirmée uniquement côté serveur après persistance métier réussie.
- Table dédiée `analytics_conversion_attribution`.
- Types rattachés : `contact`, `chat_lead`, `erp_questionnaire`.
- Données stockées : type, référence métier technique, session, page d'entrée, source, pages visitées, page de conversion, date.
- Aucune donnée personnelle supplémentaire dans les événements analytics.
- Prospects non attribuables conservés dans les compteurs métier, séparés des conversions attribuables.

Le client ne peut pas confirmer une conversion via un simple événement JavaScript : l'attribution est créée seulement après validation serveur.

## Dashboard

Ajouts :

- Conversions attribuables par page d'entrée et source.
- Dernières conversions avec page d'entrée, source, page de contact et parcours visité.
- Taux de conversion calculé uniquement avec : conversions attribuables / sessions consenties.
- Export CSV enrichi avec les lignes `attribution`.
- Navigation admin mobile rendue plus clairement scrollable, sans refonte UX.

## Contrôles effectués

- `php -l` sur les nouveaux services, entités, repositories et commandes : OK.
- `php bin/console lint:twig templates/admin/analytics/index.html.twig templates/contact.html.twig` : OK.
- `./vendor/bin/phpunit tests/AnalyticsTrackingTest.php` : OK, 7 tests, 26 assertions.
- `./vendor/bin/phpunit tests/ContactControllerTest.php` : OK, 5 tests, 17 assertions.
- `./vendor/bin/phpunit tests/ErpQuestionnaireTest.php` : OK, 10 tests, 58 assertions.
- `./vendor/bin/phpunit tests/ChatConversationManagerTest.php` : OK, 3 tests, 6 assertions.
- `npm run build` : OK.
- Recette Playwright `/admin/analytics?period=30` mobile/tablette/desktop : OK, aucune erreur JS.
- Vérification périodes 7 et 30 jours : sections présentes, blocage GA4 affiché, exclusion appareil visible.
- Export CSV authentifié via front controller local : OK.

Note Doctrine : `doctrine:schema:validate` confirme les mappings, mais signale un écart local préexistant sur des index historiques et `chat_message.fallback_used`, hors périmètre de cette livraison.

## Déploiement atomique proposé

1. Sauvegarder base + artefacts courants.
2. Déployer le code et les assets en release datée.
3. Configurer les variables GA4 hors Git.
4. Exécuter les migrations Doctrine.
5. Vider le cache.
6. Lancer `php bin/console app:analytics:ga4:smoke-test`.
7. Vérifier `/admin/analytics?period=7`, `/admin/analytics?period=30`, export CSV et exclusion appareil.
8. Basculer le symlink/current uniquement si tous les contrôles sont OK.

## Retour arrière

1. Rebasculer vers la release précédente.
2. Vider le cache.
3. Conserver la table `analytics_conversion_attribution` si possible : elle est additive et ne bloque pas l'ancienne version.
4. Si rollback base obligatoire : restaurer la sauvegarde pré-déploiement.
5. Supprimer/désactiver temporairement `GA4_CREDENTIALS_PATH` en cas d'incident d'accès Google.
