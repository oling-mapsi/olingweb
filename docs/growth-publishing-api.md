# Growth Publishing API

## Constat sur l'existant

- Technologie du site: PHP 8.1+, Symfony 6.4, Twig, Webpack Encore.
- Framework/CMS: back-office Symfony custom, aucun CMS standard detecte.
- Stockage des actualites: table Doctrine `site_page`, slugs `ressource-*`, pages publiques `/ressources/{slug}`.
- Structure des pages d'actualite: `<title>` via `site_page.title`, H1 via `hero_title`, extrait via `hero_intro`, contenu HTML via `hero_side_html`, FAQ optionnelle via `body_html`.
- Mecanisme de brouillon avant ce changement: aucun.
- Mecanisme de publication avant ce changement: edition directe en base, donc publication immediate.
- Gestion des images avant ce changement: champ texte `hero_image`; uploads locaux via `public/uploads` et `App\Service\UploadManager`.
- Champs SEO avant ce changement: `title`, `meta_description`, canonical Twig calculee, pas de canonical persistee.
- Deploiement: application Symfony deployee avec base SQL; build front via Encore. Le contenu `/ressources` n'est pas pilote par Git.
- Retour arriere avant ce changement: manuel seulement via DB ou redeploiement.

## Decision d'architecture

Strategie retenue: **A. API interne Growth**.

Raison:

- le contenu est deja en base, pas dans Git;
- il n'existe pas de CMS officiel a piloter;
- les actualites existent deja comme objet editorial `SitePage`;
- il faut un brouillon, une preview signee, une publication explicite et un rollback article par article.

## Modele ajoute

### Projection publique

`site_page` reste la source de rendu public. Pour les actualites Growth:

- `external_id` unique pour l'idempotence;
- `publication_status`: `draft`, `published`, `unpublished`;
- `canonical_url`;
- `categories`, `tags`;
- `publication_date`;
- `author_display_name`;
- `source_campaign_id`;
- `published_at`, `unpublished_at`.

Mapping de contrat vers le rendu existant:

- `title` Growth -> `site_page.hero_title`
- `excerpt` -> `site_page.hero_intro`
- `content_html` -> `site_page.hero_side_html`
- `meta_title` -> `site_page.title`
- `meta_description` -> `site_page.meta_description`
- `featured_image` -> `site_page.hero_image`

### Revisions

Nouvelle table `site_page_revision`:

- une revision a chaque sauvegarde de brouillon;
- une revision immuable a chaque publication;
- restauration de la version publiee precedente par clonage d'une ancienne revision vers une nouvelle revision publiee.

## Authentification et droits

- API privee uniquement sous `/api/growth/news`.
- Authentification de service par `Authorization: Bearer <GROWTH_API_TOKEN>`.
- Pas d'acces au back-office admin.
- Portee fonctionnelle limitee aux articles `/ressources`.
- Secrets hors Git:
  - `GROWTH_API_TOKEN`
  - `GROWTH_PREVIEW_SECRET`
  - `GROWTH_PREVIEW_TTL`

## Endpoints

### 1. Creer un brouillon

`POST /api/growth/news`

Comportement:

- idempotent sur `external_id`;
- si l'article existe deja, la route met a jour le brouillon au lieu de dupliquer;
- aucune publication directe.

### 2. Mettre a jour un brouillon

`PATCH /api/growth/news/{externalId}`

Contraintes:

- `external_id` du body doit matcher l'URL;
- le contenu est nettoye avant stockage.

### 3. Obtenir une URL d'aperçu protegee

`POST /api/growth/news/{externalId}/preview-url`

Comportement:

- genere une URL temporaire signee;
- la preview est servie sous `/preview/ressources/...`;
- `noindex,nofollow` sur la page preview.

### 4. Publier un brouillon approuve

`POST /api/growth/news/{externalId}/publish`

Comportement:

- clone la derniere revision de brouillon vers une revision publiee;
- projette cette revision dans `site_page`;
- rend l'article visible publiquement.

### 5. Lire le statut de publication

`GET /api/growth/news/{externalId}`

Retour:

- `publication_status`
- `has_pending_draft`
- `draft_revision_number`
- `published_revision_number`
- `published_at`
- `unpublished_at`

### 6. Mettre a jour une publication existante

Le meme `PATCH /api/growth/news/{externalId}` cree un nouveau brouillon meme si l'article est deja publie.

La publication publique ne change qu'au `POST /publish`.

### 7. Depublier ou restaurer la version precedente

- `POST /api/growth/news/{externalId}/unpublish`
- `POST /api/growth/news/{externalId}/restore-previous`

Comportement:

- `unpublish` retire la page de la liste et du detail publics sans supprimer l'historique;
- `restore-previous` republie la version publiee precedente si elle existe.

## Payload attendu

```json
{
  "external_id": "growth-article-001",
  "title": "Titre public de l'article",
  "slug": "titre-public-de-l-article",
  "excerpt": "<p>Resume court</p>",
  "content_html": "<p>Contenu HTML nettoye</p>",
  "meta_title": "Balise title SEO",
  "meta_description": "Meta description SEO",
  "canonical_url": "https://www.oling.fr/ressources/titre-public-de-l-article",
  "featured_image": "https://cdn.example.test/cover.jpg",
  "categories": ["AMOA", "ERP"],
  "tags": ["audit", "gouvernance"],
  "publication_date": "2026-07-11T09:00:00+00:00",
  "status": "draft",
  "author_display_name": "Growth Factory",
  "source_campaign_id": "campaign-202-growth"
}
```

## Validation et securite

- validation Symfony sur les champs minimaux;
- slug limite a `[a-z0-9-]`;
- sanitation HTML avec allowlist de balises/attributs;
- suppression des `script`, handlers `on*`, et URLs `javascript:`;
- canonical et image limites aux URLs `http(s)` ou chemins absolus `/...`;
- logs applicatifs sur creation, publication, depublishing et restauration.

## Images

- `featured_image` accepte une URL `http(s)` ou un chemin public `/uploads/...`;
- pas d'automatisation navigateur;
- pas d'upload binaire via cette V1.

## Deploiement et rollback

- deploiement normal Symfony + migration SQL `Version20260711110000`;
- rollback fonctionnel article par article via `restore-previous`;
- rollback infra standard conserve son role pour le code, mais plus pour revenir sur un contenu unique.

## Exemple rapide

```bash
curl -X POST https://www.oling.fr/api/growth/news \
  -H "Authorization: Bearer $GROWTH_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d @payload.json
```
