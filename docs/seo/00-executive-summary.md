# Audit SEO migration OLING.fr

Date d'audit: 2026-08-17

## 1. Migration health

AMBER. La migration du 2026-08-10 n'a pas encore de preuve GSC exploitable, mais l'audit du 2026-08-17 confirme un doublon d'hôte `www`, une exposition legacy persistante et au moins une URL incohérente à risque.

## 2. GSC findings

- Statut: `NOT ENOUGH DATA`
- Aucun export GSC ni accès API exploitable trouvé au 2026-08-17.
- Placeholders créés sous `docs/seo/gsc/` pour les comparatifs avant/après attendus.
- Les données Search Console API sont potentiellement tronquées/échantillonnées; même avec API active, elles ne doivent pas être interprétées comme un journal exhaustif.

## 3. Top 20 losses

- `NOT ENOUGH DATA` tant que `docs/seo/gsc/gsc-url-delta.csv` n'est pas alimenté.

## 4. Query → URL switches

- `NOT ENOUGH DATA` tant que `docs/seo/gsc/query-url-switches.csv` n'est pas alimenté.

## 5. Legacy URL recovery

- 38 URLs legacy déjà inventoriées dans [03-legacy-url-inventory.csv](/Users/florestanrouet/myweb/olingw/docs/seo/03-legacy-url-inventory.csv).
- 38 restent à investiguer dans [04-url-migration-map.csv](/Users/florestanrouet/myweb/olingw/docs/seo/04-url-migration-map.csv).
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
- P1: arbitrer `/mapsi/integration-progiciel`.
- P1: documenter ou réviser la politique `GPTBot`.
- P1: produire la mesure GSC avant/après réelle.

## 9. Cannibalisation

- AMOA: `POSSIBLE` entre `/amoa-si`, `/practice/consulting` et `/consulting/assistance-a-maitrise-douvrage`.
- ERP: `POSSIBLE` entre `/erp-progiciel` et `/business-apps/erp`.
- Pas de décision de fusion/redirection avant GSC.

## 10. Confidence level

- `CONFIRMED`: matrice HTTP `www`, présence des URLs legacy dans les sitemaps, incohérence `/mapsi/integration-progiciel`, absence d'export GSC local.
- `LIKELY`: refonte portée par les migrations des 2026-08-08 et 2026-08-09 plus que par un commit Git proprement identifiable.
- `NOT ENOUGH DATA`: pertes de clics/impressions, switches query → URL, cannibalisation mesurée.
