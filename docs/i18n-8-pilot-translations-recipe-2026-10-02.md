# I18N-8 - recette du corpus pilote EN/ES

Date : 2026-10-02

## Corpus

| Page FR | EN slug | EN statut | ES slug | ES statut |
| --- | --- | --- | --- | --- |
| home | home | published | inicio | published |
| erp-progiciel | erp-software | published | asesoria-erp-software-gestion | published |
| amoa-si | business-side-it-project-advisory | reviewed | asesoria-proyectos-sistemas-informacion | reviewed |
| rgpd | gdpr-compliance | reviewed | cumplimiento-rgpd | reviewed |
| cyber-securite | cybersecurity | reviewed | ciberseguridad | reviewed |
| gmao | cmms | reviewed | gmao-gestion-mantenimiento | reviewed |
| crm | crm-advisory | reviewed | asesoria-crm | reviewed |
| facturation-electronique-amoa | e-invoicing-project-advisory | reviewed | asesoria-facturacion-electronica | reviewed |
| services | services | reviewed | servicios-y-ofertas | reviewed |
| contact | contact | reviewed | contacto | reviewed |

## Recette

- Generation OpenAI executee via `app:i18n:translate` pour 20 traductions EN/ES.
- Statuts appliques : 16 traductions `reviewed` et 4 traductions `published` apres revue pilote.
- Routes publiees controlees en 200 : `/en/home`, `/en/erp-software`, `/es/inicio`, `/es/asesoria-erp-software-gestion`.
- Route non publiee controlee en 404 : `/en/cmms`.
- Collisions de slug controlees en base : aucune collision detectee.
- Garde-fou ajoute : fallback de slug controle si la reponse IA fournit un slug vide.
- Rendu sain identifie : serveur Symfony local `https://127.0.0.1:8001` avec PHP-FPM.
- Limite precedente levee : le serveur PHP integre renvoyait un corps HTML vide, le serveur Symfony local rend les pages HTML completes.

## Reproductibilite I18N-8B

- Export EN : `data/i18n/reviewed/site_pages.en.json` avec 10 traductions.
- Export ES : `data/i18n/reviewed/site_pages.es.json` avec 10 traductions.
- Commande export : `php bin/console app:i18n:export-translations --locale=en --status=reviewed --status=published`.
- Commande import : `php bin/console app:i18n:import-translations --locale=en --dry-run`.
- Import dry-run EN/ES : `created=0 updated=0 unchanged=10 conflict=0`.
- Import reel idempotent EN/ES : `created=0 updated=0 unchanged=10 conflict=0`.
- Source identifier stable : source `SitePage` + slug FR source.
- `sourceContentHash` preserve sur les 20 lignes.
- Slugs preserves et collisions = 0.

## QA SEO multilingue I18N-8B

- FR temoin `/erp-progiciel` : 200, HTML non vide, `lang=fr`, canonical sans prefixe.
- FR home `/` : 200, HTML non vide, `lang=fr`, canonical `/`.
- EN published : `/en/home`, `/en/erp-software` en 200, HTML non vide, `lang=en`, canonical self.
- ES published : `/es/inicio`, `/es/asesoria-erp-software-gestion` en 200, HTML non vide, `lang=es`, canonical self.
- Hreflang reciproque publie : `fr`, `en`, `es`, `x-default`.
- `x-default` : canonical FR.
- OG minimum controle : `og:title`, `og:description`, `og:url`.
- Structured data global : description localisee et `availableLanguage` selon locale.
- Gating reviewed non publie : `/en/cmms` et `/es/gmao-gestion-mantenimiento` en 404.
- Sitemaps localises directs : `/sitemap.en.xml` contient uniquement `/en/home` et `/en/erp-software`; `/sitemap.es.xml` contient uniquement `/es/inicio` et `/es/asesoria-erp-software-gestion`.
- Sitemap root Presta : conserve les sections historiques `practice`, `services`, `default`; les sections localisees restent accessibles directement.
- Internal links : libelles EN/ES localises; les cibles non publiees restent en URLs FR existantes, sans URL localisee fabriquee.
- Language switcher UI : non expose dans le layout actuel; hreflang technique valide.
- Fuite FR visible : aucune fuite editoriale inattendue detectee dans le contenu principal et les libelles globaux; seules des URLs FR fallback restent visibles dans les hrefs quand la cible localisee n'est pas publiee.

## Validation

- `php -l src/Service/I18n/OpenAiContentTranslationProvider.php`
- `php -l src/Service/I18n/AiTranslationService.php`
- `php -l src/Service/I18n/SitePageTranslationSnapshotService.php`
- `php bin/console lint:container`
- `./vendor/bin/phpunit tests/AiTranslationWorkflowTest.php`
- `./vendor/bin/phpunit tests/I18nRoutingFoundationTest.php`
- `./vendor/bin/phpunit tests/SitePageTranslationSnapshotServiceTest.php`
- `curl -I` sur les 4 routes publiees et 1 route non publiee
