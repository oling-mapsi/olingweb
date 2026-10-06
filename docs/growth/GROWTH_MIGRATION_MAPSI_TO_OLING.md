# Growth migration MAPSI to Oling Web

## Architecture cible

Growth est un outil interne Oling Web, expose sous `/admin/growth` et protege par `ROLE_ADMIN`.

MAPSI SaaS n'est plus le cockpit Growth. Il reste une source de faits produit en lecture seule pendant la transition.

## Sources

### MAPSI SaaS

Role: `READ-ONLY PRODUCT FACT SOURCE`.

Endpoints lus par le client Oling:

- `/api/internal/growth/v1/health`
- `/api/internal/growth/v1/capabilities`
- `/api/internal/growth/v1/contact-snapshot`
- `/api/internal/growth/v1/usage-snapshot`
- `/api/internal/growth/v1/product-changes`

Variables:

- `MAPSI_PRODUCT_FACTS_BASE_URL=`
- `MAPSI_PRODUCT_FACTS_TOKEN=`

### Oling Web

Role: cockpit humain, orchestration Growth et publication Oling.

## Destinations

Les destinations sont portees par `GrowthDestination`:

- `OLING_PUBLIC`
- `MAPSI_PUBLIC`

`MAPSI_PUBLIC` designe le site commercial `mapsi.fr`, jamais un tenant MAPSI ni une base metier client.

## MAPSI_PUBLIC_PUBLISHING_MECHANISM

Le repo local servant `mapsi.fr` est `/Users/florestanrouet/myweb/mapsi_web`, remote `git@github.com:oling-mapsi/mapsi_web.git`.

Stack:

- Symfony 7.4
- Doctrine ORM
- Twig
- EasyAdmin present, admin custom present

Stockage contenu:

- actualites dans `NewsArticle`;
- champs draft/published separes;
- `growth_external_id` unique;
- preview via token;
- publication par copie draft vers published.

API existante:

- `POST /api/growth/news`
- `PATCH /api/growth/news/{externalId}`
- `GET /api/growth/news/{externalId}`
- `POST /api/growth/news/{externalId}/preview-url`
- `POST /api/growth/news/{externalId}/publish`
- `POST /api/growth/news/{externalId}/unpublish`

Auth:

- bearer token `MAPSI_GROWTH_NEWS_PUBLISHER_TOKEN` cote `mapsi_web`;
- cote Oling: `MAPSI_PUBLIC_NEWS_BASE_URL=`, `MAPSI_PUBLIC_NEWS_TOKEN=`.

Publication/deploiement:

- code de prod synchronise par `bin/deploy-prod.sh`;
- rsync vers `/var/www/mapsi.fr`;
- build local npm avant sync;
- migrations optionnelles via `--migrate`.

Conclusion: `MAPSI_PUBLIC` peut utiliser l'API existante, sans ecrire dans MAPSI SaaS.

## Modele Oling Growth

- `GrowthCampaign`: travail editorial, statut, createur, horodatage.
- `GrowthContent`: contenu genere/relu/approuve.
- `GrowthDestination`: enum explicite.
- `GrowthPublication`: destination, statut, identifiant externe, erreur.
- `GrowthAuditEvent`: trace minimale des actions humaines.

## Workflow

1. `GENERATE`
2. `DRAFT`
3. `REVIEW`
4. `APPROVE`
5. `PUBLISH`

La generation IA ne valide jamais un contenu. La publication exige un contenu approuve.

Transitions autorisees:

- `DRAFT` / `GENERATED` -> `REVIEWED`
- `REVIEWED` -> `APPROVED`
- `APPROVED` -> `PUBLISHED`
- `PUBLISHED` -> `ARCHIVED` pour la campagne uniquement

Transitions interdites:

- `DRAFT` -> `PUBLISHED`
- `GENERATED` -> `APPROVED`
- `PUBLISHED` -> hard delete
- `ARCHIVED` -> regenerate

## Prompts et sources

Les contextes editoriaux sont separes:

- Oling: transformation numerique, web, data, ERP et automatisation.
- MAPSI: contenu prudent pour mapsi.fr, sans donnee client ni metrique usage brute.

Les prompts vivent dans `GrowthEditorialContextProvider`; aucun prompt massif n'est place dans le controleur.

`MapsiProductFactsClient` reste strictement read-only. Les endpoints `health`, `capabilities`, `contact-snapshot`, `usage-snapshot` et `product-changes` sont valides par smoke/test, mais les snapshots sensibles ne sont pas transmis tels quels a OpenAI.

## Preview

- `OLING_PUBLIC`: draft via `GrowthPublishingService`, puis URL signee `/preview/ressources/{externalId}/{revisionId}`.
- `MAPSI_PUBLIC`: API preview `mapsi.fr` si configuree.
- fallback MAPSI sans credentials: preview interne admin Oling, non publique.

La preview ne publie jamais automatiquement.

## Regle de suppression

- Campagne jamais publiee: suppression dure possible avec `ROLE_ADMIN`, CSRF, confirmation et audit.
- Campagne avec ressource publiee: la campagne est archivee; la ressource publique distante reste intacte.
- Unpublish distant: action explicite separee, non couplee a la suppression Growth.

## Securite

- `/admin/growth` herite de l'access control `^/admin => ROLE_ADMIN`.
- actions sensibles en POST + CSRF.
- aucun secret en Git.
- audit sans token, JWT, API key ni secret.

## Publication

### OLING_PUBLIC

`OlingSitePublisher` reutilise le socle existant:

- `GrowthPublishingService`
- `SitePage`
- `SitePageRevision`
- draft puis publish explicite.

### MAPSI_PUBLIC

`MapsiPublicPublisher` appelle l'API `mapsi.fr` si les variables sont configurees.

Sans configuration, il marque la publication `DESIGN_ONLY` au lieu de publier.

## Generation IA

`OpenAiGrowthContentGenerator` utilise les parametres OpenAI deja presents:

- `OPENAI_API_KEY=`
- `CHAT_AI_OPENAI_BASE_URL`
- `CHAT_AI_OPENAI_MODEL`

Les erreurs timeout, 429, 5xx, reponse vide ou JSON invalide remontent comme echec de generation. Aucune publication automatique.

## Migration future des donnees

Les campagnes MAPSI existantes doivent etre migrees apres validation de parite:

1. export lecture seule depuis MAPSI;
2. import dans `GrowthCampaign` / `GrowthContent`;
3. rattachement destinations;
4. comparaison previews;
5. bascule operationnelle.

## Decommission MAPSI

Ne retirer Growth de MAPSI qu'apres:

- parite fonctionnelle acceptee;
- Oling Web deploye et teste;
- stashes MAPSI classes;
- endpoints MAPSI sources remplaces ou explicitement conserves;
- plan rollback valide.

## Rollback

- Code Oling: rollback Git standard.
- `OLING_PUBLIC`: restore previous via `SitePageRevision`.
- `MAPSI_PUBLIC`: utiliser l'API `unpublish` ou rollback `mapsi_web` selon incident.
- MAPSI SaaS: inchange pendant cette phase.

## Backlog de parite

### MVP_REQUIRED

- campaigns
- generation
- deletion
- approval
- publication
- audit
- preview Oling via service existant
- product facts read-only client

### PORT_NEXT

- weekly packs
- source packs
- assets multi-canaux
- retry
- schedule
- channel settings

### NEEDS_REDESIGN

- UI MAPSI master/studio
- roles MAPSI metier
- DataTables/composants MAPSI
- tenant context

### OBSOLETE

- routes `/master/growth`
- routes `/studio/growth`
- cockpit Growth heberge par MAPSI SaaS

## Matrice de parite

| FEATURE | MAPSI CURRENT | OLING TARGET | IMPLEMENTED | TESTED | REQUIRED BEFORE CUTOVER |
| --- | --- | --- | --- | --- | --- |
| campaigns | oui | `GrowthCampaign` | oui | oui | durcir UX |
| generation | oui | service OpenAI isole + contextes edito | oui | oui | non |
| deletion | oui commit `84b03fd` | POST CSRF + audit + archive si publie | oui | oui | non |
| weekly packs | oui | module futur | non | non | port next |
| source packs | oui | module futur | non | non | port next |
| assets | oui | `GrowthContent` puis assets dedies | partiel | partiel | modeliser multi-assets |
| approval | oui | review/approve | oui | oui | non |
| publication | oui | publishers destinations | oui | oui | smoke avec secrets |
| audit | oui | `GrowthAuditEvent` | oui | oui | listing admin |
| channel settings | oui | config destinations | non | non | port next |
| preview | oui | Oling existant + mapsi.fr API/fallback interne | oui | oui | non |
| retry | oui | futur | non | non | port next |
| schedule | oui | futur | non | non | port next |
| product facts | oui API MAPSI | client read-only Oling | oui | oui | smoke MAPSI |

## Matrice de parite finale Phase 2

| FEATURE | MAPSI | OLING | TESTED | CUTOVER BLOCKER |
| --- | --- | --- | --- | --- |
| campaigns | cockpit legacy | `/admin/growth` | oui | no |
| generation | legacy | OpenAI + contextes separes | oui | no |
| prompts/sources | legacy | `GrowthEditorialContextProvider` | oui | no |
| MAPSI facts | API interne | client read-only | oui | no |
| preview OLING_PUBLIC | n/a | signed preview | oui | no |
| preview MAPSI_PUBLIC | mapsi.fr API | API ou preview interne | oui | no |
| deletion | legacy | hard delete ou archive | oui | no |
| approval | legacy | review puis approve | oui | no |
| publication | legacy | publishers explicites | oui | no |
| audit | legacy | `GrowthAuditEvent` | oui | no |
| weekly packs | oui | PORT_NEXT | n/a | no |
| source packs | oui | PORT_NEXT | n/a | no |
| assets multi-canaux | partiel | PORT_NEXT | n/a | no |
| retry | oui | PORT_NEXT | n/a | no |
| schedule | oui | PORT_NEXT | n/a | no |
| channel settings | oui | PORT_NEXT | n/a | no |
