# Audit canonical et redirects

Date: 2026-08-17

## Décision technique appliquée

- Host canonique applicatif fixé à `https://oling.fr`.
- Subscriber Symfony ajouté pour rediriger `https://www.oling.fr/*` vers `https://oling.fr/*` après déploiement.

## Correctifs appliqués

- `templates/base.html.twig`
- `templates/seo/resource-article.html.twig`
- `templates/seo/_db-landing.html.twig`
- `templates/services.html.twig`
- `templates/practices.html.twig`

## Ce qui est maintenant garanti côté rendu Twig

- Canonical absolue en `https://oling.fr`.
- Hreflang `fr-FR` et `x-default` alignés sur ce host.
- `og:url` aligné sur ce host.

## Non vérifié au 2026-08-17

- Déploiement effectif de la redirection applicative `https://www.oling.fr/*` vers `https://oling.fr/*`.
- Redirection serveur effective de `http://oling.fr/*` vers `https://oling.fr/*`.
- Redirection serveur directe de `http://www.oling.fr/*` vers `https://oling.fr/*` en un seul hop.

## Preuve publique disponible

Les contenus `https://oling.fr/amoa-si` et `https://www.oling.fr/amoa-si` étaient tous deux accessibles publiquement lors de l'audit du 2026-08-17, ce qui justifie le verrouillage du canonique côté application même avant validation du reverse proxy.

## Matrice HTTP observée le 2026-08-17

- `http://oling.fr/*` -> `301` -> `https://oling.fr/*`
- `http://www.oling.fr/*` -> `301` -> `https://www.oling.fr/*`
- `https://www.oling.fr/*` -> `200`
- `https://oling.fr/*` -> `200`

Source détaillée: `docs/seo/http-host-matrix.csv`.
