# Readiness Gate Phase 2

Date de référence: 2026-08-17

## Verdict

NOT READY

## P0

- `BLOCKED` `www -> non-www` n'est pas corrigé en production. `https://www.oling.fr/*` répond encore `200`.
- `PARTIAL` le correctif existe localement dans `CanonicalHostRedirectSubscriber.php` et désormais aussi dans `public/.htaccess`, mais il n'est pas validé en live.
- `BLOCKED` la page test `https://oling.fr/ressources/pilot-oling-e2e-article` a touché GSC; le correctif local existe mais la suppression live n'est pas prouvée.
- `PARTIAL` canonical applicatif fixé sur `https://oling.fr`.
- `PARTIAL` `robots.txt` local explicite `OAI-SearchBot`, mais la production observée le `2026-08-17` ne reflète pas encore ce fichier.

## Migration

- `PARTIAL` architecture AMOA gelée mais non close:
  - `/amoa-si` candidate money page
  - `/consulting/assistance-a-maitrise-douvrage` actif legacy à préserver
  - `/practice/consulting` hub possible
- `PARTIAL` architecture ERP gelée mais non close:
  - `/business-apps/erp` favori GSC actuel
  - `/erp-progiciel` trop faible pour devenir master sur hypothèse
  - aucune 301 définitive encore démontrée
- `PARTIAL` `integration-progiciel` sécurisé par décision `KEEP/INVESTIGATE`, pas encore résolu sémantiquement.
- `PARTIAL` le mapping legacy critique est mieux documenté mais non finalisé.

## GSC

- `CONFIRMED` baseline 3 mois intégrée.
- `CONFIRMED` périodes réelles des exports disponibles identifiées.
- `CONFIRMED` les comparaisons inégales ont été normalisées en `impressions/day` et `clicks/day` dans l'analyse.
- `BLOCKED` la comparaison exacte `2026-08-03 -> 2026-08-09` vs `2026-08-10 -> 2026-08-16` en `page x query` est manquante.
- `BLOCKED` aucune cartographie `query -> old URL / new URL` n'est encore démontrée.

## Blockers restants

1. Déploiement et validation HTTP publique réelle de `www -> https://oling.fr`.
2. Vérification live de la disparition de `pilot-oling-e2e-article`.
3. Exports GSC exacts `page x query` pour les fenêtres pré/post refonte.
4. Décision finale AMOA avec preuve requête -> URL.
5. Décision finale ERP avec preuve requête -> URL.

## Condition pour passer READY

- production non-`www` validée en HTTP live;
- aucun contenu test critique encore indexable;
- `robots.txt` production aligné sur la politique voulue;
- décisions AMOA/ERP soit démontrées, soit explicitement gelées sans danger;
- query mapping critique suffisamment documenté pour éviter une 301 destructive.
