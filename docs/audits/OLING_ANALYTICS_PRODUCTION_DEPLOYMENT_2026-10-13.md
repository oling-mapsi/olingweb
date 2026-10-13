# OLING Analytics - Déploiement production

Date : 2026-10-13

## Déploiement

- Production : `oling.fr`, chemin applicatif `/var/www/oling.fr`.
- Branche : `main`.
- Commit de départ : `6fdea6f3977b51e2411dc8e3457e20e0ac02bf77`.
- Commit production validé : ce commit de finalisation, voir git log -1.
- Déploiement : pull Git direct, sans release symlink.
- Production non modifiée hors périmètre analytics.

## Sauvegardes

Sauvegarde pré-déploiement vérifiée :

`/var/backups/oling/analytics/analytics-20261013-131508`

Contenu contrôlé :

- Dump base complet `oling_db_full.sql.gz` : `gzip -t` OK, SHA256 OK.
- Archive `public/` pré-déploiement : SHA256 OK.
- Archive `.env.local` pré-déploiement : SHA256 OK.
- État Git, commit précédent et migrations documentés.

## GA4

- Property ID : `374507565`.
- Credentials : compte de service installé hors dépôt, non exposé.
- Chemin production : `/var/www/.config/oling/ga4-service-account.json`.
- Permissions : répertoires privés, fichier lisible uniquement par `www-data`.
- Variables production : `GA4_PROPERTY_ID` et `GA4_CREDENTIALS_PATH` configurées hors Git.

Smoke-test réel GA4 exécuté en production :

- 7 jours : 16 sessions, 25 pages vues.
- 30 jours : 101 sessions, 479 pages vues.

Les données GA4 restent séparées des sessions collectées par OLING.

## Migrations

Migrations analytics exécutées :

- `DoctrineMigrations\Version20261013110000`
- `DoctrineMigrations\Version20261013143000`

État production après migration :

- Migrations exécutées : 77.
- Migrations disponibles : 77.
- Nouvelles migrations restantes : 0.

## Contrôles

- Lint PHP ciblé : OK.
- Lint Twig analytics/contact : OK.
- Tests PHPUnit analytics, contact, ERP questionnaire, chat manager : OK.
- Build assets : OK.
- Pages publiques critiques : OK.
- Login admin : OK.
- Dashboard `/admin/analytics?period=7` : OK.
- Dashboard `/admin/analytics?period=30` : OK.
- Dashboard `/admin/analytics?period=90` : OK.
- Export CSV authentifié : OK.
- Consentement RGPD : OK.
- Refus consentement : aucun appel GA4/collecteur.
- Acceptation consentement : collecteur actif.
- Retrait consentement : collecteur et GA4 coupés.
- Événement JavaScript de conversion forgé : aucune conversion confirmée créée.
- Navigation admin mobile : scroll horizontal visible.

## Rollback

Procédure de retour arrière :

1. Revenir au commit `6fdea6f3977b51e2411dc8e3457e20e0ac02bf77`.
2. Restaurer `public/` depuis `/var/backups/oling/analytics/analytics-20261013-131508/public_build_before.tar.gz` si nécessaire.
3. Restaurer `.env.local` depuis l'archive de backup si nécessaire.
4. Vider et réchauffer le cache Symfony.
5. En cas d'incident base, restaurer `oling_db_full.sql.gz`.
6. Relancer les contrôles publics et admin.

Les deux tables analytics ajoutées sont additives. Un rollback code peut fonctionner sans rollback base immédiat.

## Anomalies résiduelles

- Architecture production actuelle : checkout Git direct, pas de déploiement atomique par symlink.
- Fichiers non suivis préexistants en production : `public/sitemap.en.xml`, `public/sitemap.es.xml`.
- Dépréciations Symfony/Doctrine/Presta observées pendant cache/migrations, hors périmètre analytics.

## Synthèse

```text
DÉPLOIEMENT PRODUCTION : PASS
SITE PUBLIC : PASS
ADMINISTRATION : PASS
GA4 DATA API : PASS
COLLECTEUR : PASS
EXCLUSION INTERNE : PASS
CONSENTEMENT RGPD : PASS
ATTRIBUTION CONVERSIONS : PASS
EXPORT CSV : PASS
MIGRATIONS : PASS
ROLLBACK DISPONIBLE : OUI

COMMIT PRODUCTION : ce commit de finalisation, voir git log -1
URL DASHBOARD : https://oling.fr/admin/analytics
ANOMALIES RÉSIDUELLES :
- Checkout Git direct sans release symlink.
- Deux sitemaps publics non suivis préexistants.
- Dépréciations techniques préexistantes hors analytics.
```
