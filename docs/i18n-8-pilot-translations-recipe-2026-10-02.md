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
- Statuts appliques : 20 traductions `reviewed`, dont seulement 4 `published` apres revue pilote.
- Routes publiees controlees en 200 : `/en/home`, `/en/erp-software`, `/es/inicio`, `/es/asesoria-erp-software-gestion`.
- Route non publiee controlee en 404 : `/en/cmms`.
- Collisions de slug controlees en base : aucune collision detectee.
- Garde-fou ajoute : fallback de slug controle si la reponse IA fournit un slug vide.
- Limite de recette locale : le serveur PHP integre renvoie un corps HTML vide sur toutes les routes testees, y compris FR ; le controle fin canonical/hreflang/sitemap doit etre rejoue dans un environnement de rendu sain.

## Validation

- `php -l src/Service/I18n/OpenAiContentTranslationProvider.php`
- `php -l src/Service/I18n/AiTranslationService.php`
- `php bin/console lint:container`
- `./vendor/bin/phpunit tests/AiTranslationWorkflowTest.php`
- `curl -I` sur les 4 routes publiees et 1 route non publiee
