# Audit SEO migration OLING.fr

Date d'audit: 2026-08-17

## 1. Migration health

AMBER. La baseline GSC réelle des 3 derniers mois corrige plusieurs hypothèses: le legacy conserve une vraie visibilité, la fragmentation `www/http` est déjà visible dans Google, et les décisions AMOA/ERP ne peuvent toujours pas être figées sans export `page x query` avant/après.

## 2. GSC findings

- Statut: `BASELINE DISPONIBLE`, mais `NOT ENOUGH DATA` pour les deltas avant/après définitifs.
- Export réel "3 derniers mois" intégré depuis `docs/seo/gsc/import/`.
- Semaine `2026-08-03 -> 2026-08-09`: `450` impressions, `6` clics, `1.33%` CTR, position `52.39`.
- Fenêtre visible `2026-08-10 -> 2026-08-15`: `454` impressions, `3` clics, `0.66%` CTR, position `51.33`.
- La baseline ne montre pas de chute immédiate d'impressions, mais un CTR plus faible après le 2026-08-10.
- Les données Search Console API sont potentiellement tronquées/échantillonnées; même avec API active, elles ne doivent pas être interprétées comme un journal exhaustif.

## 3. Top 20 losses

- `NOT ENOUGH DATA` pour un vrai classement des pertes avant/après par URL tant que `page x query` n'est pas fourni.
- La baseline montre néanmoins que `/amoa-si`, `/consulting/assistance-a-maitrise-douvrage`, `/business-apps/erp` et `/mapsi/integration-progiciel` ont une valeur SEO réelle.

## 4. Query → URL switches

- `NOT ENOUGH DATA` tant que `docs/seo/gsc/query-url-switches.csv` n'est pas alimenté.

## 5. Legacy URL recovery

- 39 URLs legacy déjà inventoriées, dont la variante historique `https://oling.fr/microsoft365/integration-progiciel`.
- Les URLs suivantes ont désormais une valeur `CONFIRMED` dans la baseline GSC et ne doivent pas être redirigées sur hypothèse:
  - `https://oling.fr/consulting/assistance-a-maitrise-douvrage`
  - `https://oling.fr/business-apps/erp`
  - `https://oling.fr/mapsi/integration-progiciel`
- Aucun mapping old → new ne doit être forcé sans équivalence sémantique ni GSC.

## 6. Canonical host

- Matrice HTTP du 2026-08-17: `http://oling.fr/*` converge déjà vers `https://oling.fr/*`.
- `http://www.oling.fr/*` converge encore d'abord vers `https://www.oling.fr/*`.
- `https://www.oling.fr/*` répond encore `200` en public.
- Correctif applicatif ajouté; revalidation prod requise après déploiement.
- La phase 1C ne peut pas être close tant que `https://www.oling.fr/*` retourne encore `200`.

## 7. P0 corrigés

- Canonical/OG/hreflang verrouillés sur `https://oling.fr`.
- `robots.txt` enrichi avec `OAI-SearchBot` et `GPTBot`.
- Route publique `/test` supprimée.
- Subscriber de redirection `www` ajouté.
- Migration DB de correction `IS027001 -> ISO 27001` ajoutée.

## 8. P0/P1 restant

- P0: vérifier en production que `https://www.oling.fr/*` ne sert plus `200` après déploiement.
- P0: traiter `https://oling.fr/ressources/pilot-oling-e2e-article`, déjà visible dans GSC.
- P1: arbitrer `/mapsi/integration-progiciel`.
- P1: dérive de déploiement `robots.txt` entre dépôt et production; crawl utile observé, donc pas de P0 à ce stade.
- P1: documenter ou réviser la politique `GPTBot`.
- P1: produire la mesure GSC avant/après réelle en `page x query`.

## 9. Cannibalisation

- AMOA: `POSSIBLE` entre `/amoa-si` et `/consulting/assistance-a-maitrise-douvrage`; `/practice/consulting` reste `NOT ENOUGH DATA` dans cette baseline.
- ERP: `POSSIBLE`, mais la baseline favorise clairement `/business-apps/erp` face à `/erp-progiciel`.
- Pas de décision de fusion/redirection avant GSC.

## 10. Confidence level

- `CONFIRMED`: matrice HTTP `www`, présence des URLs legacy dans les sitemaps, incohérence `/mapsi/integration-progiciel`, baseline GSC réelle, requêtes `027001` / `is027001`, présence GSC de `pilot-oling-e2e-article`.
- `CONFIRMED`: `pilot-oling-e2e-article` relève d'une chronologie `before_and_after` au sens audit migration, pas d'un contenu purement post-refonte.
- `LIKELY`: refonte portée par les migrations des 2026-08-08 et 2026-08-09 plus que par un commit Git proprement identifiable.
- `NOT ENOUGH DATA`: pertes de clics/impressions par URL avant/après, switches query → URL, cannibalisation mesurée au niveau requête.
