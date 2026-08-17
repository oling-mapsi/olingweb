# Audit technique initial

Date: 2026-08-17

## Vérifié

- `composer.json`: Symfony `6.4.*`.
- `public/robots.txt`: présent, désormais enrichi pour `OAI-SearchBot` et `GPTBot`.
- `templates/base.html.twig`: canonical, hreflang et `og:url` désormais forcés sur `https://oling.fr`.
- `config/routes.yaml`: route `/test` supprimée.
- `public/sitemap.xml`: index statique listant `sitemap.practice.xml`, `sitemap.services.xml`, `sitemap.default.xml`.

## À contrôler ensuite

- Réponses HTTP réelles `200/301/404` pour chaque URL de `docs/seo/03-legacy-url-inventory.csv`.
- Redirections serveur `http -> https`, `www -> non-www` et nombre de hops.
- Canonical réelle rendue en production sur les URLs legacy et sur les nouvelles `/expertises/*`.
- Absence de `noindex` accidentel sur les money pages.

## Points sensibles

- Les sitemaps statiques reflètent un état mixte de l'architecture. Ils ne permettent pas encore d'inférer une stratégie canonique fiable.
- La route dynamique `/{practice}/{slug}` maintient des URLs historiques non visibles dans l'inventaire statique courant dérivé du routeur.
