# Tests de régression SEO

Date: 2026-08-17

Fichier ajouté:

- `tests/SeoRegressionTest.php`

Couverture actuelle:

- canonical non-`www` verrouillée dans `templates/base.html.twig`
- présence explicite de `OAI-SearchBot` dans `public/robots.txt`
- présence explicite de `GPTBot` dans `public/robots.txt`
- disparition de la faute `IS027001`
- disparition de la route publique `/test`

Limites:

- ne valide pas encore les codes HTTP publics
- ne valide pas encore les sitemaps contre des réponses 200 réelles
- ne valide pas encore les money pages via navigateur
