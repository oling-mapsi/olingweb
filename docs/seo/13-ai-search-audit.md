# Audit AI Search initial

Date: 2026-08-17

## Vérifié

- Dépôt courant:
  - `OAI-SearchBot = ALLOW`
  - `GPTBot = ALLOW`
- Production observée le 2026-08-17:
  - `User-agent: *`
  - `Disallow:`
  - `Disallow: /cgi-bin/`
  - `Sitemap: https://oling.fr/sitemap.xml`
- Sémantiquement, la production laisse donc crawler `OAI-SearchBot`, `Googlebot`, `Bingbot` et `GPTBot`.
- Les pages publiques comportent un `X-Robots-Tag: index, follow, max-image-preview:large` côté listener applicatif.
- Les pages publiques auditées contiennent des blocs structurés, listes et sections exploitables par les moteurs génératifs.

## Tableau crawlers

| Crawler | Production live | Dépôt | Statut audit | Impact SEO |
| --- | --- | --- | --- | --- |
| `OAI-SearchBot` | `ALLOW` par héritage `User-agent: *` | `ALLOW` explicite | `CONFIRMED` | pas de blocage utile observé |
| `Googlebot` | `ALLOW` par héritage `User-agent: *` | `ALLOW` implicite | `CONFIRMED` | pas de blocage utile observé |
| `Bingbot` | `ALLOW` par héritage `User-agent: *` | `ALLOW` implicite | `CONFIRMED` | pas de blocage utile observé |
| `GPTBot` | `ALLOW` par héritage `User-agent: *` | `ALLOW` explicite | `CONFIRMED` | gouvernance, pas prérequis ChatGPT Search |

## Décision robots

- `CONFIRMED`: la dérive `robots.txt` live vs dépôt est une dérive de déploiement.
- `CONFIRMED`: au 2026-08-17, elle relève de `P1`, pas `P0`, car aucun blocage de crawl utile n'est observé.
- `CONFIRMED`: `OAI-SearchBot` doit rester `ALLOW`.
- `CONFIRMED`: `GPTBot` reste une décision de gouvernance séparée, sans incidence obligatoire sur le référencement ChatGPT.

## Risques ouverts

- Pas de preuve au 2026-08-17 sur l'absence de blocage WAF/CDN/IP pour `OAI-SearchBot`.
- Pas de benchmark prompt/réponse encore produit.
- Plusieurs pages legacy et nouvelles couvrent des intentions proches, ce qui peut brouiller la citation par les moteurs génératifs.

## GPTBot policy decision

- Avant modification: `robots.txt` ne documentait pas explicitement de politique dédiée `GPTBot` dans le dépôt audité.
- Configuration actuelle: `GPTBot` est explicitement autorisé dans `public/robots.txt`.
- Production actuelle au 2026-08-17: `GPTBot` n'est pas mentionné explicitement mais reste `ALLOW` par héritage de `User-agent: *` avec `Disallow:` vide.
- Impact: utile pour l'entraînement/exploration selon la politique du robot, mais non requis pour le référencement dans ChatGPT Search.
- Décision audit du 2026-08-17: conserver `OAI-SearchBot` autorisé comme exigence SEO IA, et traiter `GPTBot` comme choix de gouvernance à valider par OLING, pas comme prérequis SEO.

## Exemple public vérifié

- `https://oling.fr/amoa-si` expose encore la faute `IS027001` avant correction de code.
- `https://oling.fr/mapsi/integration-progiciel` ne correspond pas sémantiquement à son slug.

Ces deux points dégradent la confiance et l'extractibilité.
