# Controle qualite SEO - 8 mars 2026

## Verification meta/page (controle code statique)

| URL | Template | Title | Meta description | H1 unique | Canonical | Liens internes | CTA vers /contact |
|---|---|---|---|---|---|---|---|
| `/amoa-si` | `templates/amoa-si.html.twig` | OK | OK | OK | OK | OK | OK |
| `/rgpd` | `templates/seo/rgpd.html.twig` | OK | OK | OK | OK | OK | OK |
| `/cyber-securite` | `templates/seo/cyber-securite.html.twig` | OK | OK | OK | OK | OK | OK |
| `/conseil-qualite` | `templates/seo/conseil-qualite.html.twig` | OK | OK | OK | OK | OK | OK |
| `/public-pme-eti` | `templates/seo/public-pme-eti.html.twig` | OK | OK | OK | OK | OK | OK |
| `/ressources` | `templates/seo/resources-index.html.twig` | OK | OK | OK | OK | OK | OK |
| `/ressources/{slug}` | `templates/seo/resource-article.html.twig` | OK | OK | OK | OK | OK | OK |

## Verification articles Markdown

| Article | Mots | 1200-1800 | Lien vers /contact | Nb liens internes |
|---|---:|---|---|---:|
| `aipd-rgpd-methode.md` | 1372 | OK | OK | 4 |
| `cadrage-projet-amoa-si.md` | 1412 | OK | OK | 5 |
| `choisir-cabinet-conseil-amoa-pme-eti.md` | 1362 | OK | OK | 4 |
| `feuille-route-cyber-pme-eti.md` | 1318 | OK | OK | 4 |
| `indicateurs-qualite-si.md` | 1263 | OK | OK | 4 |
| `nis2-dora-par-ou-commencer.md` | 1224 | OK | OK | 4 |
| `registre-traitements-rgpd.md` | 1600 | OK | OK | 4 |
| `transformation-si-secteur-public.md` | 1366 | OK | OK | 5 |

## Navigation reelle (mobile + desktop)

Verification navigateur a faire en environnement reel (non automatisable integralement depuis cette session terminal).

Checklist execution:
- Desktop (>=1280px): ouvrir chaque URL, verifier menu, hero, rendu article, CTA contact, footer.
- Mobile (390px): verifier menu burger, scroll, lisibilite texte, boutons CTA cliquables, absence debordement horizontal.
- Verifier la canonical sur 3 pages articles via code source.
- Tester un envoi formulaire sur `/contact` (et verifier remontee GA4).

## Controle live production (8 mars 2026)

Verification effectuee sur les URLs cibles en production (`https://oling.fr`).

| URL | HTTP | H1 detectes | Canonical detectee |
|---|---:|---:|---|
| `/amoa-si` | 200 | 1 | `https://oling.fr/amoa-si` |
| `/rgpd` | 200 | 1 | `https://oling.fr/rgpd` |
| `/cyber-securite` | 200 | 1 | `https://oling.fr/cyber-securite` |
| `/conseil-qualite` | 200 | 1 | `https://oling.fr/conseil-qualite` |
| `/public-pme-eti` | 200 | 1 | `https://oling.fr/public-pme-eti` |
| `/ressources` | 200 | 1 | `https://oling.fr/ressources` |
| `/ressources/registre-traitements-rgpd` | 200 | 1 | `https://oling.fr/ressources/registre-traitements-rgpd` |
