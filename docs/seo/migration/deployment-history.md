# Historique probable de déploiement migration

Date de référence: 2026-08-17

## Confirmé

- Branche inspectée localement: `feature/MGF-202-growth-publishing-contract`.
- Dernier commit local observé: `0c78cf8f` du 2026-07-25.
- Dernier commit `main` observé localement: `665b9660` du 2026-07-11.
- Aucun commit daté du 2026-08-09 ou du 2026-08-10 n'est présent dans l'historique Git local inspecté.

## Probable

- La refonte visible publiquement autour du 2026-08-10 n'est pas traçable par Git seul sur cette copie de travail.
- Les migrations non committées datées des 2026-08-08 et 2026-08-09 constituent le meilleur proxy local de la bascule de contenu:
  - `migrations/Version20260808120000.php`
  - `migrations/Version20260808123000.php`
  - `migrations/Version20260809130000.php`
  - `migrations/Version20260809143000.php`
  - `migrations/Version20260809150000.php`

## Lecture opérationnelle

- L'hypothèse la plus solide au 2026-08-17 est une mise en production ou préparation de mise en production portée par des changements de base et de contenu non encore figés dans un commit de branche exploitable pour un diff propre avant/après.
- Conséquence: le recovery SEO doit s'appuyer sur inventaires d'URLs, mesures HTTP et exports GSC, pas sur Git seul.
