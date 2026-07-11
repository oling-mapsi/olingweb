# MGF-202 - Connecteur oling.fr

## Technologie

- Backend: Symfony 6.4 + PHP 8.1+
- Front: Twig + Webpack Encore + Bootstrap + JS maison
- Base de donnees: Doctrine ORM sur MySQL/MariaDB
- Stockage medias: fichiers locaux dans `public/uploads`

## CMS eventuel

Il n'y a pas de CMS standard detecte (pas de WordPress, Strapi, Drupal, Contentful, etc.).

Le site repose sur un back-office Symfony sur mesure:

- authentification Symfony form login;
- CRUD admin custom;
- contenu editorial stocke en base Doctrine;
- rendu public via controllers Twig.

## Mecanisme de creation d'article

Le mecanisme "article" actuel est en realite base sur `App\Entity\SitePage`.

Constats:

- les pages ressources utilisent des slugs `ressource-*`;
- la liste publique `/ressources` recupere toutes les `SitePage` dont le slug commence par `ressource-`;
- l'article public est servi sur `/ressources/{slug}`;
- le contenu principal de l'article est stocke dans `heroSideHtml`;
- la FAQ est stockee en JSON dans `bodyHtml`;
- le hero, title et meta description sont aussi stockes dans `site_page`.

Limite importante:

- la creation d'une nouvelle `SitePage` n'existe pas dans l'admin;
- `SitePageAdminController` ne permet que l'edition de pages pre-declarees dans `MANAGED_SITE_PAGES`;
- le `slug` est non editable dans le formulaire admin.

Conclusion:

- un nouvel article ne peut pas etre cree proprement depuis le back-office actuel;
- il faut aujourd'hui soit ajouter le slug en code, soit inserer la ligne en base, soit passer par migration.

## Brouillons

Pas de notion de brouillon detectee.

Etat actuel:

- aucun champ `status`, `isDraft`, `publishedAt` ou equivalent;
- aucune table de revision/version;
- un enregistrement dans l'admin ecrase directement la version servie.

## Authentification

- login/mot de passe Symfony natif;
- provider base sur `App\Entity\User`;
- acces `/admin/**` reserve a `ROLE_ADMIN`;
- pas d'API auth, pas d'OAuth, pas de token machine-to-machine detecte.

## Medias

- upload gere par `App\Service\UploadManager`;
- stockage sur disque local sous `public/uploads/...`;
- URL publiques de type `/uploads/...`;
- pas de DAM/CDN/media library externe detectee;
- suppression physique de l'ancien fichier lors de certains remplacements.

## SEO

- balise `title` et `meta description` en base pour `SitePage`;
- sitemap genere via `presta/sitemap-bundle`;
- pages ressources ajoutees automatiquement au sitemap;
- `X-Robots-Tag` et meta robots geres globalement;
- pas de workflow SEO separe detecte.

## Apercu

Pas de mecanisme d'apercu distinct detecte.

Etat actuel:

- la page visible publiquement est la meme que celle enregistree en admin;
- pas d'URL preview signee;
- pas d'environnement preview par contenu;
- pas de `noindex` dedie a un preview article.

## Publication

La publication est immediate.

Quand l'admin sauvegarde une `SitePage`:

- Doctrine `flush()` persiste;
- la page publique sert aussitot le nouveau contenu;
- aucune etape d'approbation n'existe.

## Retour arriere

Pas de retour arriere applicatif detecte.

En pratique aujourd'hui:

- pas d'historique en base;
- pas de version precedente restorable depuis l'admin;
- retour arriere possible seulement via sauvegarde DB, intervention manuelle, ou redeploiement/migration si le contenu vient du code.

## Recommandation

Recommandation principale: API editoriale sur mesure, pas workflow Git.

Pourquoi:

- le contenu live est deja en base;
- les medias sont geres par upload local;
- l'admin actuel est transactionnel, pas fichier-first;
- un workflow Git imposerait soit de rebasculer les contenus vers des fichiers sources, soit de synchroniser Git vers DB, ce qui complique inutilement la stack existante.

## Workflow cible propose pour Growth Factory

### 1. Creer un brouillon

Proposer une API privee du type:

- `POST /api/growth/drafts`

Payload minimal:

- type `resource_article`
- slug public souhaite
- title
- metaDescription
- heroBadge
- heroTitle
- heroIntro
- bodyHtml
- faqJson
- heroImage

Modele cible conseille:

- ajouter sur `site_page` un statut `draft|published|archived`;
- ajouter un champ `publishedRevisionId` ou equivalent;
- ajouter une table `site_page_revision`.

### 2. Mettre a jour ce brouillon

- `PATCH /api/growth/drafts/{id}`

Comportement:

- mise a jour de la revision brouillon;
- pas d'impact sur la version publiee.

### 3. Obtenir une URL d'apercu

- `GET /api/growth/drafts/{id}/preview-url`

Mecanisme recommande:

- URL signee temporaire, ex. `/preview/site-page/{id}?token=...`;
- rendu depuis la revision brouillon;
- headers `X-Robots-Tag: noindex, nofollow, noarchive`.

### 4. Publier uniquement apres approbation

- `POST /api/growth/drafts/{id}/submit`
- `POST /api/growth/drafts/{id}/approve`
- `POST /api/growth/drafts/{id}/publish`

Regle:

- seule la revision approuvee devient revision publiee;
- la publication ne doit plus etre liee a un simple `flush()` d'edition admin.

### 5. Depublier ou restaurer la version precedente

Endpoints proposes:

- `POST /api/growth/pages/{id}/unpublish`
- `POST /api/growth/pages/{id}/restore/{revisionId}`

Comportement:

- `unpublish`: retire la page du rendu public et du sitemap;
- `restore`: repointe la page vers une revision precedente publiee.

## Variante Git

Possible mais non recommandee dans l'etat actuel.

Elle n'aurait du sens que si OLING decide de rendre les contenus editoriaux file-based, par exemple:

- un fichier par article;
- build/import vers DB au deploy;
- preview via branche/environnement;
- publication via merge.

Sans cette refonte, Git ne couvre pas proprement:

- l'etat live en base;
- les medias uploades;
- l'approbation fine par article;
- le rollback applicatif instantane.

## Conclusion

Le site oling.fr est un back-office Symfony custom, avec contenu editorial stocke en base, sans brouillon, sans preview distinct, sans approval workflow, et sans versioning.

Pour Growth Factory, la bonne cible est une API editoriale privee branchee sur `SitePage` + revisions + preview signe + publication explicite + restauration de revision precedente.
