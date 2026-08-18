# PRODUCTION_GIT_DRIFT

Priorité: `P1`

Date de constat: `2026-08-18`

## Constat

- production `oling.fr` corrigée fonctionnellement par patch ciblé
- SHA prod observé: `665b96604449975b79dd297a3b2ceec474ae8c12`
- SHA local cible de travail Phase 1F/2A: `d0e6f9c57a0c08ac82ec3581512321afaa9e45a6`
- le working tree prod n’est pas propre

## Risque

- divergence entre état Git déclaré et état réellement servi
- déploiements futurs plus difficiles à raisonner
- risque de perte de correctifs si une réconciliation brutale est tentée

## Interdictions

- pas de `git reset --hard`
- pas de `git clean -fd`
- pas d’écrasement automatique des modifications prod non inventoriées

## Stratégie future

1. inventorier les modifications prod hors Git utiles vs bruit
2. reconstruire un arbre propre en préproduction ou worktree dédié
3. rejouer les correctifs validés via commits traçables
4. aligner ensuite la prod sur un SHA propre

## Statut

`OPEN`
