# P1 Sitemap Generation

Date de référence: 2026-08-18

## Statut

- `CONFIRMED`: le sitemap live statique actuellement publié est valide.
- `CONFIRMED`: `presta:sitemap:dump --env=prod` échoue en production.
- `P1`: le bug n'empêche pas l'indexation actuelle, mais empêche une régénération propre et répétable.

## Erreur exacte

Commande:

```bash
php bin/console presta:sitemap:dump --env=prod
```

Erreur:

```text
Invalid argument for route "expertises_show": The route "expertises_show" cannot have the sitemap option because it requires parameters other than ""
Some mandatory parameters are missing ("slug") to generate a URL for route "expertises_show".
```

## Route concernée

- `CONFIRMED`: `expertises_show`
- fichier repéré: [PublicSiteController.php](/Users/florestanrouet/myweb/olingw/src/Controller/PublicSiteController.php:32)

## Paramètre manquant

- `CONFIRMED`: `slug`

## Cause probable

- `LIKELY`: une route paramétrée est marquée ou traitée comme sitemapable sans fournir la valeur obligatoire `slug`.
- `LIKELY`: le listener Presta tente de générer l'URL directement depuis la définition de route, ce qui casse le dump global.

## Correction propre possible

1. retirer l'option sitemap de la route paramétrée `expertises_show` si elle y est portée directement ou indirectement ;
2. laisser le [SitemapSubscriber.php](/Users/florestanrouet/myweb/olingw/src/EventListener/SitemapSubscriber.php:132) publier explicitement les URLs concrètes `/expertises/{slug}` ;
3. relancer `php bin/console presta:sitemap:dump --env=prod` ;
4. vérifier que `sitemap.default.xml` contient toujours les URLs attendues en HTTPS non-www.

## Tests nécessaires

- `php bin/console presta:sitemap:dump`
- vérification que la commande ne lève plus d'exception
- vérification de la présence des cinq money pages dans le sitemap généré
- vérification de l'absence d'URLs paramétrées brutes ou invalides
- smoke check HTTP des sitemaps publiés

## Note production

- `CONFIRMED`: pendant la phase SI Finance, le contournement retenu a été la publication du sitemap statique valide, sans modifier les URLs SEO.
