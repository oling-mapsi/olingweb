# Git migration diff

Date d'analyse: 2026-08-17

## Constat

- Symfony détecté en version `6.4.*` via `composer.json`.
- Aucun commit daté du 2026-08-09 ou du 2026-08-10 n'est présent sur la branche inspectée.
- La refonte visible est portée par un worktree très modifié avec fichiers suivis modifiés et nombreux fichiers non suivis datés autour des 2026-08-08 et 2026-08-09.

## Conséquence méthodologique

L'analyse A -> B -> C demandée par le brief n'est pas pleinement reconstructible depuis Git seul.

- État A (pré-refonte au 2026-08-09): NON VÉRIFIÉ depuis la branche locale.
- État B (mise en production du 2026-08-10): NON VÉRIFIÉ depuis la branche locale.
- État C (worktree actuel du 2026-08-17): vérifiable.

## Différences observables HEAD -> worktree

- Introduction de nouvelles familles d'URLs: `/expertises/*`, `/secteurs/*`, `/ressources/*`.
- Maintien en parallèle des familles historiques `/practice/*`, `/consulting/*`, `/expertises-audit/*`, `/business-apps/*`, `/mapsi/*`.
- Nouveau contenu éditorial structuré par `PublicSiteConfig` et `PublicSitePageResolver`.
- Sitemaps statiques régénérés au 2026-08-13 avec coexistence d'URLs historiques et nouvelles.

## Commandes de preuve

```bash
php bin/console debug:router
git status --short
git log --all --date=iso --pretty=format:'%h %ad %d %s' --since='2026-08-07' --until='2026-08-11 23:59:59'
git diff -- src/Controller/PracticeController.php src/EventListener/SitemapSubscriber.php templates/base.html.twig public/sitemap.practice.xml public/sitemap.services.xml
```
