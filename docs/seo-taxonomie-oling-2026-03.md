# Plan SEO + Taxonomie complet - oling.fr

_Date de reference : 7 mars 2026_

## 1) Objectif business SEO

Positionner oling.fr sur les intentions transactionnelles et expertise autour de :
- RGPD
- AMOA SI
- conseil
- qualite
- cyber securite
- public
- PME
- ETI

## 2) Taxonomie cible (piliers + clusters)

## Niveau 1 - Pages piliers
- `/amoa-si` : pilier AMOA SI (intention principale)
- `/rgpd` : pilier conseil RGPD
- `/cyber-securite` : pilier conseil cyber securite
- `/conseil-qualite` : pilier conseil qualite
- `/public-pme-eti` : pilier cibles clients (public/PME/ETI)
- `/projets` : preuve sociale et cas d'usage
- `/contact` : conversion

## Niveau 2 - Clusters de soutien (a produire)
- Guides RGPD : registre, AIPD, sous-traitance, droits des personnes
- Guides AMOA SI : cadrage, RFP, comitologie, conduite du changement
- Guides cyber : gouvernance SSI, NIS2, DORA, plan de traitement
- Guides qualite : pilotage process, indicateurs, amelioration continue
- Pages sectorielles : secteur public, PME, ETI (cas concret + offre)

## Niveau 3 - Entites de preuve
- `projets` par secteur et enjeu (RGPD, cyber, qualite, AMOA)
- temoignages/benefices quantifies
- pages equipe/expertise

## 3) Mapping mots-cles -> URL prioritaire

- `AMOA SI` -> `/amoa-si`
- `conseil AMOA SI` -> `/amoa-si`
- `RGPD` -> `/rgpd`
- `conseil RGPD` -> `/rgpd`
- `cyber securite` -> `/cyber-securite`
- `conseil cyber securite` -> `/cyber-securite`
- `conseil qualite` -> `/conseil-qualite`
- `secteur public` -> `/public-pme-eti`
- `PME` -> `/public-pme-eti`
- `ETI` -> `/public-pme-eti`
- `cabinet de conseil` -> `/amoa-si` (+ maillage vers les 4 autres pages)

## 4) Regles editoriales SEO (obligatoires)

- 1 intention principale par page (pas de cannibalisation)
- H1 unique contenant le mot-cle cible
- mot-cle cible dans : URL, title, meta description, H1, intro, au moins un H2
- 2 a 5 liens internes contextuels vers pages soeurs
- CTA visible au dessus de la ligne de flottaison vers `/contact`
- preuves de credibilite : certifications, references, secteurs servis, methodologie

## 5) Maillage interne cible

- Footer : liens vers les 5 pages piliers
- Page `/amoa-si` -> liens vers `/rgpd`, `/cyber-securite`, `/conseil-qualite`, `/public-pme-eti`
- Pages piliers -> liens croises entre elles + `/projets` + `/contact`
- Pages secteur (`/metiers` et `/public-pme-eti`) -> liens vers pages expertises
- Pages legales RGPD/securite -> lien vers pages conseil correspondantes

## 6) Technique SEO (done + a faire)

## Deja applique dans le code
- creation de 4 pages SEO dediees :
  - `/rgpd`
  - `/cyber-securite`
  - `/conseil-qualite`
  - `/public-pme-eti`
- page `/amoa-si` dediee activee (landing AMOA SI)
- redirections 301 des aliases AMOA (`/consulting`, `/practice/consulting`) vers `/amoa-si`
- maillage footer ajoute vers les pages piliers
- meta robots durcis pour admin/login (`noindex,nofollow`)
- `X-Robots-Tag` renforce : noindex sur admin/login/pages en erreur
- sitemap : URLs practice canoniques (`/practice/{slug}`) et exclusion alias AMOA

## A faire tout de suite (ops)
- regenerer les sitemaps en production (`presta:sitemap:dump`) puis verifier les dates
- soumettre `sitemap.xml` dans Google Search Console
- verifier qu'il n'existe pas de sitemap statique obsolete servi par le serveur
- lancer un crawl (Screaming Frog/Sitebulb) pour verifier :
  - 200/301/404
  - canonical unique
  - title/H1/meta manquants
  - maillage interne vers pages piliers

## 7) Plan de contenu 90 jours

## Sprint 1 (S1-S2)
- publier 5 pages piliers (fait cote code)
- enrichir chaque page pilier avec 1 section FAQ (3 a 5 questions)
- ajouter 3 cas clients relies a RGPD / cyber / AMOA

## Sprint 2 (S3-S6)
- publier 8 articles cluster (2 par pilier)
- ajouter schema FAQ/Article quand pertinent
- ajouter tableaux comparatifs (ex: PME vs ETI sur gouvernance SI)

## Sprint 3 (S7-S12)
- publier 6 pages longue traine sectorielles
- renforcer internal linking depuis projets et metiers
- optimiser CTR (titles + metas selon impressions GSC)

## 8) KPI de pilotage

- positions top 3 / top 10 sur 8 mots cibles
- clics SEO sur les 5 pages piliers
- conversions SEO vers formulaire `/contact`
- nombre de pages valides indexees
- part de trafic marque vs non-marque

## 9) Ce que vous devez faire (priorite business)

1. Valider les 5 pages piliers et leur ton commercial.
2. Mettre 1 offre claire par page (livrables, delais, resultat attendu).
3. Publier au moins 2 preuves concretes par pilier (cas client ou chiffres).
4. Ouvrir/parametrer Google Search Console et GA4 si pas deja finalise.
5. Soumettre sitemap et demander l'indexation des 5 pages piliers.
6. Produire 2 contenus/mois minimum pendant 3 mois (cluster).
7. Suivre chaque semaine impressions, CTR, positions, conversions.

## 10) Gouvernance recommande

- 1 owner SEO (pilotage)
- 1 owner contenu (production)
- 1 owner technique (correctifs)
- rituel hebdo 30 min : KPI + blocages + priorites

