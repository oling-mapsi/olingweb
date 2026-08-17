# Registre de risques migration SEO

Date de référence: 2026-08-17

| ID | Priority | Risque | Preuve | Impact | Statut |
| --- | --- | --- | --- | --- | --- |
| MIG-001 | P0 | Canonical dépendant du host demandé (`www` vs non-`www`) et host `www` encore servi publiquement | `templates/base.html.twig`, `templates/seo/resource-article.html.twig`, `templates/seo/_db-landing.html.twig`, matrice HTTP du 2026-08-17 | Risque de duplication d'indexation et de signaux divisés | Corrigé côté app, non vérifié côté infra |
| MIG-002 | P0 | Typo sitewide `IS027001` visible publiquement | `src/Service/PublicSiteConfig.php`, donnée dynamique `services.slug = si`, constat public sur `https://oling.fr/amoa-si` le 2026-08-17 | Dégradation crédibilité / matching sémantique ISO 27001 | Corrigé côté code, migration DB prête |
| MIG-003 | P0 | Route publique `/test` pointant vers un contrôleur absent | `config/routes.yaml`, `php bin/console router:match /test` | 500 public ou page parasite indexable | Corrigé |
| MIG-004 | P1 | Double exposition d'URLs historiques et nouvelles sans matrice canonique validée | `docs/seo/02-current-url-inventory.csv`, `docs/seo/03-legacy-url-inventory.csv` | Cannibalisation / dilution SEO | Ouvert |
| MIG-005 | P1 | URL legacy anormale `/mapsi/integration-progiciel` reliée à un contenu "Gouvernance et risques" | Constat public sur `https://oling.fr/mapsi/integration-progiciel` le 2026-08-17 | Mauvais alignement slug/contenu/intention | Ouvert |
| MIG-006 | P1 | Reconstitution A/B non possible depuis Git seul | `git log --since='2026-08-07' --until='2026-08-11 23:59:59'` ne renvoie aucun commit | Impossible d'attribuer proprement la migration du 2026-08-10 | Ouvert |
| MIG-007 | P1 | Absence de données GSC intégrées | Aucun export GSC présent dans le dépôt au 2026-08-17 | Baisse ou gain impossible à quantifier par requête | Ouvert |
