# Baseline préprod 2026-08-24

## Matrice sources

| Ancien texte | Source | Table / fichier | Slug | Page publique | Priorité | Action proposée |
| --- | --- | --- | --- | --- | --- | --- |
| `Transformation des organisations maîtrisée de bout en bout.` | Base | `home_section` | `hero` | Homepage | P1 | Remplacer par un titre de section factuel aligné avec l’AMOA SI. |
| `Chaque jour, nous nous emparons du changement...` | Base | `home_section` | `projects` | Homepage / projets | P1 | Remplacer par un bloc “Réalisations et missions OLING”. |
| `Transformation digitale, automatisation et IA utile` | Base | `services` | `transformation-digitale-automatisation-et-ia-utile` | Mega-menu / pages services | P0 | Conserver le slug, remplacer uniquement les libellés visibles. |
| `Accompagnement personnalisé pour une transformation... réussie et performante.` | Base | `practice` | `consulting` | `/amoa-si` | P0 | Réécrire en description AMOA SI factuelle. |
| `...propulsant votre entreprise vers l'avenir.` | Base | `practice` | `business-apps` | `/practice/business-apps` | P1 | Réécrire en description métier sobre. |
| `...croissance durable.` | Base | `practice` | `expertises-audit` | `/practice/expertises-audit` | P1 | Réécrire en description conformité / risques factuelle. |
| `Des projets concrets qui montrent comment OLING remet les transformations sous contrôle` | Code fallback | `src/Service/PublicSiteConfig.php` | `projets` | `/projets` | P1 | Remplacer par un titre de réalisations plus neutre. |
| `Maillage` / `actif existant` | Code template | `templates/practice-home.html.twig` / `templates/services.html.twig` | n/a | `/amoa-si`, pages services | P1 | Remplacer par des intitulés éditoriaux non SEO. |

## Snapshots ciblés avant modification

### `site_page.home`

- `title`: `OLING | AMOA SI, ERP, CRM, GMAO & conformité`
- `meta_description`: `Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France et DROM.`
- `hero_badge`: `Cabinet de conseil AMOA SI depuis 2012`
- `hero_title`: `Cadrer et piloter|vos projets SI|et conformité`
- `hero_intro`: version AMOA SI déjà alignée
- `body_html`: JSON structuré déjà aligné, sauf tags homepage sans `MAPSI`

### `site_page.projets`

- `title`: `Projets`
- `hero_title`: vide
- `hero_intro`: vide
- `body_html`: vide

### `home_section`

- `practices.title`: `Quatre expertises complémentaires pour cadrer, déployer et piloter vos projets`
- `hero.title`: `Transformation des organisations maîtrisée de bout en bout.`
- `projects.title`: `Chaque jour, nous nous emparons du changement et générons des résultats concrets.`

### `practice`

- `consulting.description`: `Accompagnement personnalisé pour une transformation des outils et des processus réussie et performante.`
- `expertises-audit.description`: `Analyse rigoureuse, amélioration continue, conformité réglementaire, renforcement des processus, soutenant votre croissance durable.`
- `business-apps.description`: `Assistance à l'intégration de solutions applicatives innovantes, déploiement efficace, soutien aux opérations, adaptabilité, propulsant votre entreprise vers l'avenir.`

### `services`

- `transformation-digitale-automatisation-et-ia-utile.designation`: `Transformation digitale, automatisation et IA utile`
- `transformation-digitale-automatisation-et-ia-utile.introduction_short`: `Accélérer la transformation digitale avec des automatisations tenables, des agents utiles et une trajectoire réaliste pour TPE, PME et PMI.`
- `amoa-ia-pilotage-de-projets-ia-et-agents-metier.description`: contient encore `faire tenir des déploiements utiles`

## Priorités de rendu confirmées

- Homepage : `site_page.home` prime sur le fallback `PublicSiteConfig`, puis le JSON `body_html` complète les blocs structurés.
- `/projets` : faute de contenu `site_page` exploitable, la page repose actuellement sur le fallback `PublicSiteConfig`.
- `/amoa-si` : route alias vers la practice `consulting`; le rendu vient de `practice`, `service` et `PublicSiteConfig`, pas d’une `site_page` dédiée.
