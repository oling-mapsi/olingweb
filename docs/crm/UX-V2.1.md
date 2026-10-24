# CRM OLING — revue UX V2.1

24 octobre 2026 · Branche locale `feat/crm-ux-v2-1` · Aucun merge, push ou déploiement.

## Audit préalable

Base : `origin/main` à `d315a1c8` (PR24). L’ancienne branche `feat/crm-action-kpis`, à `8cbce815`, avait le même contenu applicatif. Les cinq modifications préexistantes dans `vendor` ont été conservées et exclues du commit. Aucun AGENTS.md supplémentaire dans ce checkout.

Les douze fichiers d’interface/service/documentation demandés ont été examinés, ainsi que les routes, le lancement local et les tests. Symfony 6.4, Twig, CSS et JavaScript natif ; aucune dépendance ajoutée. Le CSS et le JavaScript CRM sont chargés uniquement par son layout autonome.

Constats : neuf colonnes comprimées ; textes de cartes descendant à 8–11 px ; plusieurs couches de CSS contradictoires ; détails ouverts involontairement au survol ; vue Actions en Kanban par défaut ; relance et dépôt mal distingués ; raccourcis de statut et suppression concurrençant l’édition principale.

Écart du cahier des charges : les KPI livrés précédemment couvrent aussi les propositions clients. Le calcul et ses tests sont conservés, sans rétablir l’ancienne règle « marchés uniquement ». Le libellé précise le périmètre global et distingue dépôt/remise des relances. La documentation V5 obsolète a été corrigée.

## Changements réalisés

| Priorité | Résultat |
| --- | --- |
| P0 | Palette OLING unifiée, CSS limité à `.crm-workspace`, textes 14 px usuels, titres de carte 15 px, secondaires 12–13 px. |
| P0 | Cinq étapes actives ; affaires clôturées et neuf statuts accessibles via filtre, liste, détails et fiche. Largeur de colonne lisible, défilement interne et accès supérieur conservés. |
| P0 | Actions en liste par défaut, mode explicite conservé. Relance, dépôt/remise et action distincts. Tri chronologique sur la plus proche date utile, dates absentes en dernier. |
| P0 | Présentation des priorités : retard, aujourd’hui, sept prochains jours, sans relance planifiée. Les dépôts déjà effectués ne deviennent pas des relances fictives. |
| P1 | Cartes hiérarchisées ; action longue limitée à deux lignes, intégrale dans Détails et fiche ; absence d’action/date explicite. |
| P1 | Détails au clic/Entrée, fermeture explicite/Esc, restitution du focus. Pas de fermeture lors du défilement automatique au clavier. |
| P1 | Fiche : interlocuteurs et avancement, suivi, informations complémentaires repliées, historique intact, raccourcis secondaires, suppression séparée avec confirmation existante. |
| P1 | Filtres et modes sélectionnés plus visibles ; Connexions et alimentation reste replié. |

Les adaptations du contrôleur sont de présentation : filtrage des statuts affichés, mode par défaut, ordre et filtre de dates pour la liste, rendu HTML de la carte après sauvegarde. Les dates ne sont ni créées ni réécrites par ces filtres. Le tri/filtre utilise la première date pertinente (relance ou dépôt encore à faire), tandis que les deux dates sont affichées séparément. Les KPI utilisent toujours leur service existant.

## Fichiers modifiés et justification

| Fichier | Raison |
| --- | --- |
| `templates/admin/crm/layout.html.twig` | Portée CSS, lien d’évitement, navigation courante, version des assets. |
| `templates/admin/crm/index.html.twig` | Cinq colonnes, filtre de cycle de vie, liste quotidienne et dates séparées, régions navigables. |
| `templates/admin/crm/_card.html.twig` | Hiérarchie, dates explicites, informations manquantes, détails complets. |
| `templates/admin/crm/_date.html.twig` (nouveau) | Présentation commune des dates et marqueurs temporels, sans écriture. |
| `templates/admin/crm/_deadlines.html.twig` | Périmètre du KPI explicité ; calcul inchangé. |
| `templates/admin/crm/show.html.twig` | Regroupement des champs, raccourcis et suppression secondaires ; champs et formulaires conservés. |
| `templates/admin/crm/new.html.twig` | Titre principal et retour au portefeuille. |
| `public/crm/workspace.css` | Suppression des surcharges successives ; typographie, palette, focus et responsive. |
| `public/crm/workspace.js` | Détails explicites, focus après sauvegarde, compteurs et retrait d’une carte hors filtre, nouvelle carte rendue par le serveur. |
| `src/Controller/CrmAdminController.php` | Adaptations de présentation décrites ci-dessus ; mutations, sécurité et export conservés. |
| `tests/CrmAccessTest.php` | Attentes d’affichage adaptées (cinq colonnes et liste par défaut), consultation des statuts clôturés vérifiée. Assertions métier conservées. |
| `tests/CrmWorkspaceTest.php` (nouveau) | Tri des vraies dates, filtres, état vide, absence d’écriture à la navigation, conservation des champs après édition, rafraîchissement après mouvement. |
| `docs/crm/README.md`, `WORKSPACE-V5.md`, ce document et `ux-v2-1/` | Documentation cohérente et preuves de recette synthétique. |

## Recette locale

31 tests CRM, **415 assertions réussies**. Contrôles PHP, JavaScript, Twig (8 templates), conteneur Symfony et `git diff --check` réussis.

```sh
SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/phpunit --filter 'App\\Tests\\Crm'
APP_ENV=dev DATABASE_URL=sqlite:///%kernel.project_dir%/var/crm-local.db php bin/console lint:twig templates/admin/crm --no-debug
APP_ENV=dev DATABASE_URL=sqlite:///%kernel.project_dir%/var/crm-local.db php bin/console lint:container --no-debug
php -l src/Controller/CrmAdminController.php
node --check public/crm/workspace.js
git diff --check
```

Navigateur Chrome, base SQLite synthétique : Kanban, liste, segments marchés/clients, recherche sans résultat, filtre clôturé, mouvement à la souris enregistré après rechargement, clôture/réouverture, compteurs, détails, édition d’action et de deux dates conservée, liste Actions, KPI et clavier. Deux onglets ont confirmé le refus d’une version obsolète sans écrasement. Les protections CSRF/authentification, décisions, réimports et export restent vérifiées par les tests CRM existants.

| Largeur | Largeur de page | Kanban visible / contenu | Résultat |
| --- | --- | --- | --- |
| 1440 | 1440 | 1392 / 1392 | Cinq colonnes sans défilement horizontal. |
| 1280 | 1280 | 1232 / 1232 | Cinq colonnes sans défilement horizontal. |
| 768 | 768 | 720 / 1231 | Défilement interne et accès aux colonnes. |
| 375 | 375 | 343 / 1556 | Défilement interne, détails entièrement contenus dans l’écran. |

La fiche et la liste Actions ont également été vérifiées à 375 px : page de 375 px sans débordement, tableau défilant dans son conteneur.

Accessibilité contrôlée : contraste sRGB minimum sur les fonds utilisés de **5,26:1 pour les textes secondaires**, **5,54:1 pour le bleu**, **3,23:1 pour les bordures de contrôle**. Titres de cartes 15 px, secondaires 12–13 px. Focus bleu de 3 px, lien d’évitement, libellés textuels des retards, `aria-current`, boutons Détails/fermeture et compteurs annoncés. Parcours Entrée/Tab/Esc testé et focus restitué après changement de statut. Le clic mobile donne accès au changement de statut sans glisser-déposer ni survol obligatoire.

Limites : contrôles ciblés, pas de certification WCAG exhaustive ; lecteur d’écran et appareil tactile physique non testés. La recette mobile utilise un viewport Chrome et des interactions au clic. Pas de validation multi-navigateurs. Sur mobile, la liste conserve un défilement horizontal interne pour ne pas comprimer ses informations. Les filtres de date portent sur la prochaine date utile ; ce ne sont pas des tâches créées automatiquement.

## Captures sur données fictives

Les 15 fiches de démonstration restent présentes : 14 actives et une Sans suite. Une fiche fictive a été enrichie lors de la recette (action longue et deux dates), ce qui explique le compteur KPI différent entre les captures. Aucune donnée commerciale réelle n’a été lue ou utilisée.

- [Avant — 1440](ux-v2-1/before-1440.png)
- [Après — 1440](ux-v2-1/after-1440.png), [1280](ux-v2-1/after-1280.png), [768](ux-v2-1/after-768.png), [375](ux-v2-1/after-375.png)
- [Actions et relances](ux-v2-1/actions-1440.png), [fiche](ux-v2-1/detail-1440.png), [fiche mobile](ux-v2-1/detail-375.png), [détails mobiles](ux-v2-1/details-mobile.png)
- [Conflit de version](ux-v2-1/concurrency-error.png), [mesures de largeur](ux-v2-1/viewport-checks.json)

## Périmètre préservé et lancement

Aucun accès VPS ou secret de production. Aucune intervention sur les données réelles, les connecteurs, Outlook, les formulaires, les tâches automatiques, le schéma, les migrations, les valeurs de statut/décision, la qualification, la déduplication, l’historique, les règles métier des KPI, l’authentification, les contrôles CSRF ou l’export CSV. `CrmDeadlines.php` et ses tests métier sont inchangés. Les tests et manipulations navigateur ont utilisé seulement des données fictives locales ; les configurations n’ont pas été modifiées.

```sh
cd /Users/florestanrouet/myweb/olingw-crm
scripts/crm/local-start.sh
```

Ouvrir http://127.0.0.1:8092/admin/crm ; compte de démonstration et identifiants locaux déjà prévus par le dépôt. La base et les identifiants restent ignorés de Git. Le serveur local existant a été réutilisé.

## P2 proposés, non implémentés

- Recette avec lecteur d’écran et appareils tactiles physiques, puis Safari/Firefox.
- Mémorisation facultative du mode et des filtres par utilisateur, après validation du besoin.
- Éventuel compteur limité aux seuls marchés publics : décision métier séparée nécessaire ; aucun changement implicite des KPI.

Validation humaine requise avant toute intégration ou mise en production. Pour revoir : comparer `origin/main...feat/crm-ux-v2-1` ; aucune procédure de déploiement n’a été exécutée.
