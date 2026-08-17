# Analyse GSC migration

Date de référence: 2026-08-17

## Statut

BASELINE REELLE DISPONIBLE, MAIS `NOT ENOUGH DATA` POUR LES DELTAS AVANT/APRES DEFINITIFS

## Nature des données

- Les fichiers de `docs/seo/gsc/import/` proviennent d'un export GSC "3 derniers mois".
- Ils constituent une baseline réelle utile pour corriger les hypothèses précédentes.
- Ils ne remplacent pas encore les exports dédiés `2026-07-13 -> 2026-08-09` et `2026-08-10 -> 2026-08-16` au niveau `page x query`.
- Les données Search Console API ou export peuvent être tronquées/échantillonnées selon les limites de GSC; elles ne doivent jamais être présentées comme un journal exhaustif de toutes les requêtes.

## Lecture temporelle initiale autour du 2026-08-10

Source: `Graphique.csv`

| Fenêtre | Jours | Clics | Impressions | CTR | Position moy. pondérée | Impressions / jour |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| `2026-07-13 -> 2026-08-09` | 28 | 29 | 1507 | 1.92% | 43.56 | 53.82 |
| `2026-08-03 -> 2026-08-09` | 7 | 6 | 450 | 1.33% | 52.39 | 64.29 |
| `2026-08-10 -> 2026-08-15` | 6 | 3 | 454 | 0.66% | 51.33 | 75.67 |

### Interprétation

- `CONFIRMED`: la baseline ne montre pas un effondrement immédiat d'impressions juste après le 2026-08-10.
- `CONFIRMED`: le CTR se dégrade sur la fenêtre post-refonte partielle visible (`1.33%` -> `0.66%`).
- `POSSIBLE`: la baisse de clics reflète surtout une volatilité de court terme et/ou une mauvaise adéquation snippet/intention.
- `NOT ENOUGH DATA`: impossible d'attribuer causalement ces mouvements à la refonte sans export `page x query` avant/après.

## Fragmentation d'hôte confirmée

Source: `Pages.csv`

- `CONFIRMED`: 13 URLs `www` ou `http` apparaissent encore dans GSC.
- `CONFIRMED`: ces variantes représentent déjà `202` impressions et `2` clics sur la baseline.
- `CONFIRMED`: pour la homepage, GSC voit séparément:
  - `https://oling.fr/` -> `345` impressions / `50` clics
  - `http://oling.fr/` -> `35` impressions / `1` clic
  - `https://www.oling.fr/` -> `9` impressions / `0` clic
- `CONFIRMED`: la fragmentation HTTP/host n'est donc pas seulement technique; elle est déjà visible dans Google.

## Pages focus demandées

| URL | Clics | Impressions | CTR | Position | Lecture |
| --- | ---: | ---: | ---: | ---: | --- |
| `https://oling.fr/amoa-si` | 0 | 271 | 0.00% | 39.96 | `CONFIRMED` visibilité réelle mais faible rendement |
| `https://oling.fr/consulting/assistance-a-maitrise-douvrage` | 0 | 183 | 0.00% | 21.51 | `CONFIRMED` legacy AMOA plus proche du Top 20 que `/amoa-si` |
| `https://oling.fr/practice/consulting` | 0 | 0 ou sous seuil export | n/a | n/a | `NOT ENOUGH DATA` |
| `https://oling.fr/business-apps/erp` | 0 | 66 | 0.00% | 35.27 | `CONFIRMED` vraie présence SEO |
| `https://www.oling.fr/erp-progiciel` | 0 | 1 | 0.00% | 5.00 | `CONFIRMED` quasi absence dans cette baseline |
| `https://oling.fr/gmao` | 1 | 9 | 11.11% | 19.22 | `CONFIRMED` faible mais mieux positionné |
| `https://www.oling.fr/gmao` | 0 | 26 | 0.00% | 25.69 | `CONFIRMED` fragmentation `www` active |
| `https://oling.fr/crm` | 0 | 9 | 0.00% | 18.56 | `CONFIRMED` faible présence |
| `https://www.oling.fr/crm` | 0 | 4 | 0.00% | 50.00 | `CONFIRMED` fragmentation `www` active |
| `https://oling.fr/si-finance` | 0 | 2 | 0.00% | 6.00 | `CONFIRMED` présence marginale |
| `https://oling.fr/mapsi/integration-progiciel` | 0 | 14 | 0.00% | 10.21 | `CONFIRMED` visibilité Google déjà existante |

## Clusters business issus des requêtes

Source: `Requêtes.csv`

| Requête | Impressions | Position | Lecture |
| --- | ---: | ---: | --- |
| `amoa erp` | 69 | 21.46 | `CONFIRMED` cluster business réel |
| `amoa si` | 44 | 25.55 | `CONFIRMED` cluster business réel |
| `cadrage stratégique amoa` | 41 | 13.54 | `CONFIRMED` quick win AMOA |
| `cabinet de conseil amoa` | 21 | 30.52 | `CONFIRMED` cluster utile mais bas |
| `cabinet amoa` | 17 | 26.24 | `CONFIRMED` cluster utile mais bas |
| `conseil gmao` | 19 | 19.05 | `CONFIRMED` quick win GMAO |
| `amoa si finance` | 3 | 29.67 | `CONFIRMED` signal faible |
| `expert rgpd` | 57 | 51.72 | `CONFIRMED` cluster RGPD visible mais loin |
| `logiciel conformité ai act` | 1 | 65.00 | `CONFIRMED` signal IA Act émergent |
| `audit iso 27001` | 2 | 48.00 | `CONFIRMED` signal ISO 27001 faible |
| `indicateurs qualité iso 9001` | 3 | 51.00 | `CONFIRMED` signal ISO 9001 faible |
| `certification qse` | 326 | 28.42 | `CONFIRMED` cluster le plus visible de la baseline |

## Typo `IS027001` / `027001`

- `CONFIRMED`: `Requêtes.csv` contient `027001` avec `7` impressions et position `6.00`.
- `CONFIRMED`: `Requêtes.csv` contient `is027001` avec `6` impressions et position `5.33`.
- Lecture: la faute avait déjà une empreinte de recherche/indexation. Le correctif était justifié et doit rester en place.

## Contenus test encore visibles dans GSC

- `CONFIRMED`: `https://oling.fr/ressources/pilot-oling-e2e-article` apparaît encore dans `Pages.csv` avec `3` impressions et position `1`.
- `CONFIRMED`: il ne s'agit donc pas d'un faux positif purement local; cette URL a bien touché l'index Google.
- Action requise: retrait public, retrait sitemap, traitement HTTP et vérification de désindexation.

## Top 20 pages par impressions

| URL | Clics | Impressions | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/expertises-audit/qse | 1 | 931 | 0.11% | 40.80 |
| https://oling.fr/ressources/indicateurs-qualite-si | 0 | 384 | 0.00% | 36.84 |
| https://oling.fr/ | 50 | 345 | 14.49% | 2.51 |
| https://oling.fr/practice/mapsi | 5 | 289 | 1.73% | 6.81 |
| https://oling.fr/amoa-si | 0 | 271 | 0.00% | 39.96 |
| https://oling.fr/business-apps/msbi | 0 | 268 | 0.00% | 71.79 |
| https://oling.fr/consulting/plan-strategique | 0 | 237 | 0.00% | 36.13 |
| https://oling.fr/a-propos/team | 14 | 228 | 6.14% | 3.57 |
| https://oling.fr/mapsi/automatisation-et-workflow | 0 | 215 | 0.00% | 41.88 |
| https://oling.fr/consulting/assistance-a-maitrise-douvrage | 0 | 183 | 0.00% | 21.51 |
| https://oling.fr/a-propos | 2 | 174 | 1.15% | 3.10 |
| https://oling.fr/mentions-legales | 3 | 148 | 2.03% | 3.38 |
| https://oling.fr/consulting/pilotage-des-plans | 1 | 133 | 0.75% | 20.95 |
| https://oling.fr/expertises-audit/rgpd | 7 | 131 | 5.34% | 42.35 |
| https://oling.fr/a-propos/metiers | 0 | 121 | 0.00% | 2.45 |
| https://oling.fr/consulting/reforme-facturation-electronique-amoa | 1 | 94 | 1.06% | 20.97 |
| https://oling.fr/business-apps/systeme-dinformation-geographique | 0 | 90 | 0.00% | 35.14 |
| https://www.oling.fr/mapsi-progiciel | 1 | 85 | 1.18% | 5.84 |
| https://oling.fr/a-propos/politiquergpd | 0 | 85 | 0.00% | 2.86 |
| https://oling.fr/contact | 0 | 81 | 0.00% | 5.30 |

## Top 20 pages business

| URL | Clics | Impressions | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/expertises-audit/qse | 1 | 931 | 0.11% | 40.80 |
| https://oling.fr/practice/mapsi | 5 | 289 | 1.73% | 6.81 |
| https://oling.fr/amoa-si | 0 | 271 | 0.00% | 39.96 |
| https://oling.fr/consulting/plan-strategique | 0 | 237 | 0.00% | 36.13 |
| https://oling.fr/mapsi/automatisation-et-workflow | 0 | 215 | 0.00% | 41.88 |
| https://oling.fr/consulting/assistance-a-maitrise-douvrage | 0 | 183 | 0.00% | 21.51 |
| https://oling.fr/consulting/pilotage-des-plans | 1 | 133 | 0.75% | 20.95 |
| https://oling.fr/expertises-audit/rgpd | 7 | 131 | 5.34% | 42.35 |
| https://oling.fr/consulting/reforme-facturation-electronique-amoa | 1 | 94 | 1.06% | 20.97 |
| https://www.oling.fr/mapsi-progiciel | 1 | 85 | 1.18% | 5.84 |
| https://oling.fr/a-propos/politiquergpd | 0 | 85 | 0.00% | 2.86 |
| https://oling.fr/mapsi/mapsi-risques | 0 | 67 | 0.00% | 66.97 |
| https://oling.fr/business-apps/erp | 0 | 66 | 0.00% | 35.27 |
| https://oling.fr/ressources/cadrage-projet-amoa-si | 0 | 61 | 0.00% | 9.57 |
| https://oling.fr/ressources/registre-traitements-rgpd | 0 | 39 | 0.00% | 24.08 |
| https://oling.fr/ressources/mapsi-comment-transformer-des-usages-disperses-en-pilotage-mesurable | 0 | 39 | 0.00% | 59.33 |
| https://oling.fr/consulting/management-des-processus | 0 | 38 | 0.00% | 24.68 |
| https://oling.fr/mapsi/mapsi-secu | 0 | 36 | 0.00% | 26.94 |
| https://oling.fr/expertises-audit/finance | 0 | 30 | 0.00% | 74.83 |
| https://oling.fr/consulting/tech-refresh | 0 | 29 | 0.00% | 8.34 |

## Top 30 requêtes business

| Requête | Clics | Impressions | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| certification qse | 0 | 326 | 0.00% | 28.42 |
| introduction à la certification qse | 0 | 215 | 0.00% | 37.18 |
| logiciel qse | 0 | 163 | 0.00% | 46.72 |
| formation lead auditor qse | 0 | 73 | 0.00% | 58.21 |
| amoa erp | 0 | 69 | 0.00% | 21.46 |
| amoa marketing noumea | 0 | 60 | 0.00% | 78.80 |
| expert rgpd | 0 | 57 | 0.00% | 51.72 |
| audit qse | 0 | 54 | 0.00% | 77.46 |
| amoa marketing nc | 0 | 53 | 0.00% | 43.26 |
| amoa si | 0 | 44 | 0.00% | 25.55 |
| amoa ged | 0 | 44 | 0.00% | 31.07 |
| cadrage stratégique amoa | 0 | 41 | 0.00% | 13.54 |
| cabinet de conseil amoa | 0 | 21 | 0.00% | 30.52 |
| conseil gmao | 0 | 19 | 0.00% | 19.05 |
| certification iso qse | 0 | 19 | 0.00% | 23.05 |
| cabinet amoa | 0 | 17 | 0.00% | 26.24 |
| experts en rgpd | 0 | 9 | 0.00% | 66.56 |
| amoa epm | 0 | 8 | 0.00% | 30.25 |
| implémentation logiciel qse | 0 | 7 | 0.00% | 31.57 |
| auditeur qse | 0 | 7 | 0.00% | 74.00 |
| conformité qse | 0 | 6 | 0.00% | 13.83 |
| outils qse | 0 | 6 | 0.00% | 61.83 |
| gmao sage | 0 | 5 | 0.00% | 60.80 |
| qse certification | 0 | 4 | 0.00% | 42.00 |
| norme qse | 0 | 4 | 0.00% | 59.75 |
| refonte erp finance | 0 | 4 | 0.00% | 67.00 |
| amoa agency | 0 | 3 | 0.00% | 19.00 |
| amoa si finance | 0 | 3 | 0.00% | 29.67 |
| services conformité rgpd | 0 | 3 | 0.00% | 33.33 |
| indicateurs qualité iso 9001 | 0 | 3 | 0.00% | 51.00 |

## Quick wins positions 4-10

| URL | Impressions | Clics | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/practice/mapsi | 289 | 5 | 1.73% | 6.81 |
| https://www.oling.fr/mapsi-progiciel | 85 | 1 | 1.18% | 5.84 |
| https://oling.fr/contact | 81 | 0 | 0.00% | 5.30 |
| https://oling.fr/ressources/cadrage-projet-amoa-si | 61 | 0 | 0.00% | 9.57 |
| https://oling.fr/expertises-audit/si | 37 | 0 | 0.00% | 9.73 |
| https://oling.fr/consulting/tech-refresh | 29 | 0 | 0.00% | 8.34 |
| https://oling.fr/mapsi/arborescence | 27 | 0 | 0.00% | 7.52 |
| https://oling.fr/ressources/aipd-rgpd-methode | 26 | 0 | 0.00% | 8.58 |
| https://oling.fr/projets | 24 | 0 | 0.00% | 9.17 |

## Quick wins positions 11-20

| URL | Impressions | Clics | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/consulting/gouvernance-des-donnees | 28 | 0 | 0.00% | 12.00 |

## Opportunités positions 21-40

| URL | Impressions | Clics | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/ressources/indicateurs-qualite-si | 384 | 0 | 0.00% | 36.84 |
| https://oling.fr/amoa-si | 271 | 0 | 0.00% | 39.96 |
| https://oling.fr/consulting/plan-strategique | 237 | 0 | 0.00% | 36.13 |
| https://oling.fr/consulting/assistance-a-maitrise-douvrage | 183 | 0 | 0.00% | 21.51 |
| https://oling.fr/business-apps/systeme-dinformation-geographique | 90 | 0 | 0.00% | 35.14 |
| https://oling.fr/business-apps/erp | 66 | 0 | 0.00% | 35.27 |
| https://oling.fr/ressources | 66 | 0 | 0.00% | 39.48 |
| https://oling.fr/expertises-audit/achats-marches | 62 | 0 | 0.00% | 25.98 |
| https://oling.fr/ressources/registre-traitements-rgpd | 39 | 0 | 0.00% | 24.08 |
| https://oling.fr/consulting/management-des-processus | 38 | 0 | 0.00% | 24.68 |
| https://oling.fr/mapsi/mapsi-secu | 36 | 0 | 0.00% | 26.94 |
| https://www.oling.fr/gmao | 26 | 0 | 0.00% | 25.69 |
| https://oling.fr/mapsi/mapsi-qualiopi | 23 | 1 | 4.35% | 29.91 |

## Pages à fort volume et CTR anormalement bas

| URL | Impressions | Clics | CTR | Position |
| --- | ---: | ---: | ---: | ---: |
| https://oling.fr/expertises-audit/qse | 931 | 1 | 0.11% | 40.80 |
| https://oling.fr/ressources/indicateurs-qualite-si | 384 | 0 | 0.00% | 36.84 |
| https://oling.fr/amoa-si | 271 | 0 | 0.00% | 39.96 |
| https://oling.fr/business-apps/msbi | 268 | 0 | 0.00% | 71.79 |
| https://oling.fr/consulting/plan-strategique | 237 | 0 | 0.00% | 36.13 |
| https://oling.fr/mapsi/automatisation-et-workflow | 215 | 0 | 0.00% | 41.88 |
| https://oling.fr/consulting/assistance-a-maitrise-douvrage | 183 | 0 | 0.00% | 21.51 |
| https://oling.fr/consulting/pilotage-des-plans | 133 | 1 | 0.75% | 20.95 |
| https://oling.fr/a-propos/metiers | 121 | 0 | 0.00% | 2.45 |

## Décisions corrigées par la baseline

- `CONFIRMED`: on ne peut plus considérer `/erp-progiciel` comme URL maître ERP par hypothèse; la baseline favorise nettement `/business-apps/erp`.
- `CONFIRMED`: on ne peut plus considérer `/practice/consulting` comme concurrent principal tant qu'il n'apparaît pas dans l'export; le duel observé est surtout `/amoa-si` vs `/consulting/assistance-a-maitrise-douvrage`.
- `CONFIRMED`: `/mapsi/integration-progiciel` a déjà une visibilité Google; aucune 301 ne doit être posée avant reconstitution d'intention et GSC `page x query`.

## Exports encore requis

- `docs/seo/gsc/gsc-page-query-before.csv` couvrant `2026-07-13 -> 2026-08-09`
- `docs/seo/gsc/gsc-page-query-after.csv` couvrant `2026-08-10 -> 2026-08-16`
- `docs/seo/gsc/gsc-page-query-week-before.csv` couvrant `2026-08-03 -> 2026-08-09`
- `docs/seo/gsc/gsc-page-query-week-after.csv` couvrant `2026-08-10 -> 2026-08-16`
- `docs/seo/gsc/gsc-url-delta.csv`
- `docs/seo/gsc/gsc-query-delta.csv`
- `docs/seo/gsc/query-url-switches.csv`
