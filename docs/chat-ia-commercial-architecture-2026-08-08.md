# Module de chat IA commercial premium OLING

## 1. Recommandation de stack

### Option A. Symfony natif + widget custom + API Mistral + RAG SQL léger

| Brique | Choix |
| --- | --- |
| Widget chat | Twig + JS custom dans `templates/base.html.twig` + `assets/app.js` |
| Backend conversation | Contrôleurs/API Symfony + services applicatifs |
| Stockage | Doctrine sur la base existante |
| Moteur IA | API Mistral (`mistral-small-latest` ou équivalent) |
| RAG léger | index SQL maison + snippets publics normalisés |
| Email | Symfony Mailer existant |

Avantages : simple, rapide, UE, bon compromis coût/qualité, peu de dépendances.
Limites : dépendance à un fournisseur IA externe.

### Option B. Symfony natif + widget custom + OpenAI Responses API + RAG SQL léger

| Brique | Choix |
| --- | --- |
| Widget chat | Twig + JS custom |
| Backend conversation | Symfony |
| Stockage | Doctrine |
| Moteur IA | OpenAI Responses API |
| RAG léger | index SQL maison |
| Email | Symfony Mailer |

Avantages : meilleure robustesse conversationnelle, meilleur résumé.
Limites : hébergement non UE, coût un peu moins prévisible, moins aligné avec la préférence UE.

### Option C. Symfony natif + widget custom + Ollama auto-hébergé + RAG SQL léger

| Brique | Choix |
| --- | --- |
| Widget chat | Twig + JS custom |
| Backend conversation | Symfony |
| Stockage | Doctrine |
| Moteur IA | Ollama + modèle open source |
| RAG léger | index SQL maison |
| Email | Symfony Mailer |

Avantages : souveraineté maximale, open source.
Limites : qualité variable, ops plus lourde, latence, tuning, plus risqué pour un parcours commercial premium.

### Stack finale recommandée

Option A.

Justification :
- elle respecte la stack Symfony existante ;
- elle évite un produit tiers de live chat inutile au MVP ;
- elle garde le contrôle produit, RGPD et UX ;
- elle minimise le coût et la dette ;
- elle permet un fallback simple vers le formulaire existant ;
- elle prépare une abstraction `AiProviderInterface` pour changer plus tard de moteur.

## 2. Architecture cible

### Front

Le widget est injecté globalement dans `templates/base.html.twig`.

Rôle du widget :
- ouvrir/fermer la conversation ;
- afficher les messages ;
- persister un `conversationToken` en `localStorage` ;
- transmettre la page source, le referrer et les réponses visiteur ;
- déclencher la collecte des coordonnées ;
- proposer le fallback vers `/contact`.

### Back Symfony

Rôle du backend Symfony :
- créer/reprendre une session ;
- enregistrer les messages ;
- charger le contexte public ;
- appeler le moteur IA ;
- calculer la qualification ;
- générer un résumé exploitable ;
- valider les champs obligatoires ;
- envoyer l’email à Florestan ;
- appliquer la rétention.

### Moteur IA

Rôle :
- produire la prochaine réponse ;
- reformuler proprement ;
- aider à la qualification ;
- générer un résumé strictement borné au contenu de conversation et au contexte public injecté.

Le moteur ne lit jamais directement la base.

### Base de données

Rôle :
- stocker conversations, messages, qualification, consentement, email status ;
- stocker l’index documentaire public léger ;
- permettre la reprise de session.

### Email

Rôle :
- notifier Florestan à la soumission qualifiée ;
- éventuellement envoyer un accusé visiteur plus tard, hors MVP.

### Flux bout en bout

1. clic sur “Parler à un consultant” ;
2. ouverture du widget ;
3. `POST /api/chat/conversations` si aucune session ;
4. message d’accueil IA ;
5. visiteur répond ;
6. `POST /api/chat/messages` ;
7. Symfony récupère les snippets publics pertinents ;
8. Symfony appelle le moteur IA avec prompt système + contexte + historique court ;
9. Symfony stocke réponse + métadonnées ;
10. lorsque le besoin est suffisamment qualifié, l’IA demande les coordonnées ;
11. visiteur soumet coordonnées + consentement ;
12. `POST /api/chat/conversations/{token}/lead` ;
13. Symfony valide, génère résumé final, envoie l’email, marque la conversation `submitted` ;
14. le visiteur voit la confirmation ;
15. la session reste réouvrable en lecture/reprise.

### Cycle d’une conversation

`new` → `active` → `qualified` → `lead_pending` → `submitted`

Statuts annexes :
- `abandoned`
- `expired`
- `escalation_ready`
- `error`

## 3. Modèle de données

### Entités minimales

#### `chat_conversation`

- `id`
- `public_token` : UUID exposé au front
- `status` : `new|active|qualified|lead_pending|submitted|abandoned|expired|error`
- `started_at`
- `last_message_at`
- `submitted_at`
- `expires_at`
- `consent_at`
- `consent_text_version`
- `source_path`
- `source_url`
- `referrer`
- `locale`
- `ip_hash`
- `user_agent_hash`
- `resume_token` : optionnel si différent du token public
- `email_sent_at`
- `email_status`
- `retention_purge_at`

#### `chat_message`

- `id`
- `conversation_id`
- `role` : `assistant|visitor|system`
- `content`
- `message_type` : `welcome|question|answer|summary|fallback|consent`
- `sequence_number`
- `source_page_ids` : JSON
- `source_document_ids` : JSON
- `model_name`
- `prompt_version`
- `created_at`

#### `chat_lead`

- `id`
- `conversation_id`
- `full_name`
- `email`
- `phone`
- `company`
- `job_title` : optionnel
- `need_description`
- `rgpd_consent` : bool
- `rgpd_consent_at`
- `preferred_contact_channel` : optionnel
- `created_at`

#### `chat_qualification`

- `id`
- `conversation_id`
- `primary_need`
- `secondary_need`
- `urgency_level`
- `organization_type`
- `organization_size`
- `maturity_level`
- `estimated_value_band`
- `commercial_intent`
- `confidence_score`
- `summary_short`
- `summary_long`
- `next_step_recommended`
- `updated_at`

#### `chat_public_document`

- `id`
- `document_key`
- `source_type` : `site_page|service|practice|project|team|resource`
- `source_entity_id`
- `title`
- `url`
- `section_title`
- `content_text`
- `normalized_text`
- `keywords` : JSON
- `is_active`
- `updated_at`
- `checksum`

### Réutilisation de l’existant

`src/Entity/Messagerie.php` ne couvre pas le besoin. Le conserver pour le formulaire classique, sans le détourner pour le chat.

### Rétention

- conversations non soumises : purge à 30 jours ;
- conversations soumises : 180 jours max en base chat ;
- lead utile conservé dans le canal métier si nécessaire, sinon purge ;
- messages bruts purgés plus tôt que le résumé si possible.

### Reprise de session

- `public_token` stocké en `localStorage` ;
- endpoint `GET /api/chat/conversations/{token}` ;
- si conversation expirée : nouvelle conversation préremplie avec dernier résumé visible côté back uniquement si conforme.

## 4. UX conversationnelle

### Message d’accueil exact recommandé

“Bonjour, je peux vous aider à qualifier votre besoin en transformation SI, ERP, conformité, cybersécurité, RGPD ou IA. Décrivez en quelques lignes votre contexte, votre enjeu ou votre blocage actuel.”

### Séquence de qualification

1. contexte libre ;
2. nature du besoin ;
3. impact métier ;
4. urgence ;
5. état d’avancement ;
6. taille/type d’organisation ;
7. attente vis-à-vis d’OLING ;
8. coordonnées.

### Ordre des questions

Après la réponse libre initiale :
1. “S’agit-il plutôt d’un cadrage, d’un projet en cours à sécuriser, d’une mise en conformité ou d’un besoin d’aide à la décision ?”
2. “Quel est l’enjeu principal aujourd’hui : délai, risque, conformité, fiabilité opérationnelle, coûts ou pilotage ?”
3. “À quel horizon devez-vous décider ou agir ?”
4. “Où en êtes-vous : réflexion initiale, consultation, projet lancé, situation bloquée ?”
5. “Quel type de structure êtes-vous et avec quel périmètre approximatif ?”

### Logique de relance

- si réponse vague : demander un exemple concret ou une échéance ;
- si réponse trop large : proposer 3 catégories maximum ;
- si réponse hors périmètre : le dire explicitement et rediriger vers le formulaire.

### Gestion des réponses vagues

Réponse type :
“Pour vous orienter utilement, il me manque un point concret : le sujet porte-t-il surtout sur un outil, une organisation, une contrainte réglementaire ou un risque opérationnel ?”

### Moment où demander les coordonnées

Quand 4 blocs sont connus :
- besoin principal ;
- urgence ;
- maturité ;
- attente commerciale.

Ne pas demander les coordonnées dès le premier message.

### Message final avant envoi

“Merci. J’ai les éléments nécessaires pour transmettre une synthèse exploitable à un consultant OLING. Je vous demande maintenant vos coordonnées de contact.”

### Confirmation d’envoi

“Merci, votre demande a bien été transmise. Un consultant OLING reviendra vers vous avec le bon niveau d’échange en fonction de votre contexte.”

### Fallback formulaire classique

Toujours afficher un lien secondaire :
- “Préférez-vous le formulaire classique ?”
- URL : `/contact`

## 5. Taxonomie commerciale

### Classification principale

- `transformation_si`
- `amoa_erp`
- `organisation_gouvernance`
- `conformite`
- `rgpd`
- `cybersecurite`
- `ia_data_automatisation`
- `autre`

### Urgence

- `immediate` : < 2 semaines
- `short_term` : 2 à 8 semaines
- `planned` : 2 à 6 mois
- `exploratory` : > 6 mois

### Taille / type d’organisation

Type :
- `pme`
- `pmi`
- `eti`
- `public`
- `association`
- `autre`

Taille :
- `1_49`
- `50_249`
- `250_999`
- `1000_plus`
- `unknown`

### Maturité du besoin

- `flou`
- `cadre`
- `consultation`
- `en_cours`
- `bloque`

### Valeur potentielle

- `low`
- `medium`
- `high`

Règle simple MVP :
- `high` si urgence + projet structurant + décideur identifié ;
- `medium` si besoin qualifié sans budget/échéance claire ;
- `low` sinon.

### Intention commerciale

- `diagnostic`
- `cadrage`
- `assistance_projet`
- `audit`
- `mise_en_conformite`
- `expertise_ponctuelle`
- `orientation`

## 6. Stratégie de réponse IA

### Anti-hallucination

Le prompt système doit imposer :
- répondre uniquement à partir du contexte fourni ;
- ne jamais inventer client, offre, prix, délai, engagement ;
- dire explicitement quand l’information n’est pas disponible ;
- privilégier la reformulation prudente.

### Restriction de périmètre

L’assistant répond uniquement sur :
- expertises ;
- services ;
- secteurs ;
- cas clients ;
- cabinet ;
- équipe ;
- ressources ;
- contact.

Hors périmètre :
- support technique client ;
- informations privées ;
- engagement contractuel ;
- chiffrage ;
- conseil juridique définitif.

### Exploitation du contenu public du site

Sources prioritaires :
- `SitePage`
- `Practice`
- `Services`
- `Projet`
- `Team`
- ressources SEO publiques

Le backend prépare 3 à 8 snippets maximum par réponse.

### Exploitation des données publiques en base

Seulement les colonnes déjà rendues publiquement.

Interdiction :
- email interne ;
- notes admin ;
- brouillons ;
- métadonnées non publiques.

### Génération du résumé fiable

Le résumé final doit être généré à partir :
- des messages visiteur ;
- de la qualification structurée ;
- des coordonnées validées.

Pas besoin d’injecter tout l’historique brut.

### Prévention des promesses inventées

Le prompt interdit :
- “nous garantissons” ;
- “nous avons déjà fait exactement cela” sans source ;
- toute estimation de délai ou budget sans formule prudente.

## 7. Stratégie RAG légère compatible Symfony

### Extraction des contenus publics utiles

Créer un service `PublicContentIndexer`.

Il :
- lit les entités publiques ;
- nettoie le HTML ;
- segmente par page/section ;
- normalise les accents ;
- stocke des snippets en table `chat_public_document`.

### Pages / tables à indexer

- `site_page`
- `practice`
- `services`
- `projet`
- `team`
- `content_item` si publié

### Format de document interne

Un snippet = :
- `title`
- `url`
- `section_title`
- `content_text`
- `keywords`
- `source_type`

### Rafraîchissement de l’index

Deux mécanismes :
- commande CLI `app:chat:index-public-content`
- refresh ciblé après sauvegarde admin sur les entités publiques

### Gestion des changements de contenu admin

Dans les controllers/admin forms existants, déclencher un service de re-indexation ciblée après `flush`.

### Solution simple avant moteur vectoriel

MVP :
- recherche SQL fulltext ou LIKE pondéré ;
- score par titre + mots-clés + contenu ;
- top 5 snippets ;
- pas de pgvector au départ.

Évolution v2 :
- pgvector ou Meilisearch si le corpus augmente.

## 8. Conception email

### Objet recommandé

`[OLING][Lead IA] {besoin principal} - {société} - {niveau d'urgence}`

Exemple :
`[OLING][Lead IA] AMOA ERP - Dupont Industrie - short_term`

### Structure exacte

1. en-tête lead
2. coordonnées
3. synthèse courte
4. qualification automatique
5. extrait de conversation utile
6. contexte source
7. consentement
8. lien admin

### Champs à inclure

- date/heure
- nom
- email
- téléphone
- société
- page d’origine
- besoin principal
- besoin secondaire
- urgence
- maturité
- type/taille d’organisation
- intention commerciale
- valeur potentielle
- résumé court
- résumé long
- message initial visiteur
- consentement RGPD + horodatage

### CTA utile

- lien admin de consultation de la conversation ;
- ou au minimum identifiant de conversation.

### Format recommandé

Email texte + HTML Twig.

Template :
- `templates/emails/chat_lead_notification.html.twig`
- `templates/emails/chat_lead_notification.txt.twig`

## 9. RGPD / conformité

### Données collectées

- contenu conversationnel utile ;
- coordonnées ;
- consentement ;
- métadonnées minimales de session.

### Consentement à afficher

“J’accepte que les informations transmises dans cette conversation soient utilisées par OLING pour traiter ma demande de contact et me recontacter dans ce cadre.”

Consentement obligatoire avant soumission.

### Politique de rétention recommandée

- non soumis : 30 jours ;
- soumis : 180 jours max dans le module chat ;
- purge automatique quotidienne.

### Minimisation

Ne pas demander :
- budget détaillé ;
- données sensibles ;
- identifiants système ;
- données de santé ;
- secrets métiers.

### Sécurité

- hash IP et user agent, pas stockage brut si non nécessaire ;
- validation stricte ;
- CSRF ou jeton de session signé ;
- rate limiting ;
- logs d’erreur sans contenu sensible ;
- accès admin restreint.

### Hébergement

Base et app sur hébergement existant.
Fournisseur IA UE préféré.

### Mentions à afficher dans le widget

- usage de l’IA ;
- périmètre public de réponse ;
- lien politique RGPD ;
- lien formulaire classique.

### À tracer

- création session ;
- soumission lead ;
- envoi email ;
- purge ;
- erreurs provider.

### À ne pas conserver

- brouillons inutiles au-delà de la rétention ;
- IP brute si évitable ;
- réponses IA sans valeur métier après purge ;
- données sensibles partagées par erreur, à masquer/supprimer.

## 10. Découpage d’implémentation Symfony

### Routes / API à créer

- `POST /api/chat/conversations`
- `GET /api/chat/conversations/{token}`
- `POST /api/chat/conversations/{token}/messages`
- `POST /api/chat/conversations/{token}/lead`
- `POST /api/chat/conversations/{token}/close`

Admin :
- `GET /admin/chat/conversations`
- `GET /admin/chat/conversations/{id}`

### Contrôleurs

- `src/Controller/Api/ChatConversationController.php`
- `src/Controller/Api/ChatMessageController.php`
- `src/Controller/Api/ChatLeadController.php`
- `src/Controller/Admin/ChatConversationAdminController.php`

### Services

- `ChatConversationManager`
- `ChatReplyService`
- `ChatQualificationService`
- `ChatSummaryService`
- `PublicContentIndexer`
- `PublicContextResolver`
- `AiProviderInterface`
- `MistralAiProvider`
- `ChatLeadMailer`
- `ChatRetentionService`

### Entités Doctrine

- `ChatConversation`
- `ChatMessage`
- `ChatLead`
- `ChatQualification`
- `ChatPublicDocument`

### Commandes

- `app:chat:index-public-content`
- `app:chat:purge-expired`

### Intégration frontend

Recommandation MVP :
- nouveau partial `templates/includes/_chat_widget.html.twig`
- inclusion globale dans `templates/base.html.twig`
- hook sur tous les CTA “Parler à un consultant” via attribut `data-chat-open`

### JS widget

Créer :
- `assets/js/chat-widget.js`

Responsabilités :
- état UI ;
- stockage local token ;
- appels API ;
- rendu messages ;
- collecte coordonnées ;
- fallback contact.

### Persistence session

- token public en `localStorage`
- reprise par API au chargement
- reset manuel possible

### Envoi email

Réutiliser Symfony Mailer.
Ne pas réutiliser tel quel `src/Controller/ContactController.php`.
Créer un mailer dédié.

### Hook avec le bouton “Parler à un consultant”

Aujourd’hui les CTA pointent vers `/contact` dans :
- `templates/base.html.twig`
- `templates/index.html.twig`
- plusieurs templates de pages

MVP :
- conserver `href="/contact"` ;
- ajouter `data-chat-open="true"` ;
- intercepter en JS ;
- si JS absent, le fallback `/contact` reste intact.

## 11. MVP recommandé

### À développer en premier

1. widget global minimal ;
2. création/reprise de conversation ;
3. moteur de réponse borné au contenu public ;
4. collecte des coordonnées ;
5. email de notification ;
6. purge/rétention ;
7. fallback `/contact`.

### Peut attendre

- back-office de lecture complet ;
- handoff humain live ;
- analytics avancées ;
- vector search ;
- accusé email visiteur ;
- multilingue ;
- pièces jointes.

### Hors scope initial

- live chat opérateur ;
- synchronisation CRM ;
- scoring prédictif complexe ;
- authentification visiteur ;
- voice/chat temps réel.

### Ordre de réalisation optimal

1. modèle de données
2. API Symfony
3. index public léger
4. provider IA
5. widget front
6. email
7. purge + admin minimal

## 12. Risques / arbitrages

### Risques techniques

- dérive de prompt ;
- réponses trop longues ;
- index public incomplet ;
- latence provider.

Arbitrage :
- historique court ;
- snippets limités ;
- timeout + message de fallback.

### Risques UX

- effet chatbot gadget ;
- collecte trop tôt ;
- questions trop nombreuses.

Arbitrage :
- ton sobre ;
- 4 à 6 tours avant collecte ;
- réponses courtes, orientées décision.

### Risques RGPD

- surcollecte ;
- rétention trop longue ;
- conservation de données sensibles.

Arbitrage :
- minimisation stricte ;
- purge planifiée ;
- message d’avertissement explicite.

### Risques éditoriaux

- promesses implicites ;
- confusion entre expertise et engagement commercial.

Arbitrage :
- prompt restrictif ;
- formulation prudente ;
- pas de budget ni délai auto.

### Risques qualité IA

- hallucination ;
- mauvaise qualification ;
- résumé incomplet.

Arbitrage :
- taxonomie fermée ;
- résumé structuré ;
- email incluant aussi le verbatim initial.

## 13. Livrables techniques attendus ensuite

### Fichiers / composants à créer

- `src/Entity/ChatConversation.php`
- `src/Entity/ChatMessage.php`
- `src/Entity/ChatLead.php`
- `src/Entity/ChatQualification.php`
- `src/Entity/ChatPublicDocument.php`
- `src/Controller/Api/ChatConversationController.php`
- `src/Controller/Api/ChatMessageController.php`
- `src/Controller/Api/ChatLeadController.php`
- `src/Service/ChatConversationManager.php`
- `src/Service/ChatReplyService.php`
- `src/Service/ChatQualificationService.php`
- `src/Service/ChatSummaryService.php`
- `src/Service/PublicContentIndexer.php`
- `src/Service/PublicContextResolver.php`
- `src/Service/Ai/AiProviderInterface.php`
- `src/Service/Ai/MistralAiProvider.php`
- `src/Service/ChatLeadMailer.php`
- `src/Command/IndexChatPublicContentCommand.php`
- `src/Command/PurgeChatConversationsCommand.php`
- `templates/includes/_chat_widget.html.twig`
- `templates/emails/chat_lead_notification.html.twig`
- `templates/emails/chat_lead_notification.txt.twig`
- `assets/js/chat-widget.js`
- `assets/styles/chat-widget.css`

### Migrations

- création des 5 tables chat ;
- index sur `public_token`, `status`, `updated_at` ;
- index fulltext ou colonnes de recherche sur `chat_public_document`.

### Endpoints

- création session ;
- lecture session ;
- envoi message ;
- soumission lead ;
- fermeture conversation.

### Tests minimums

- test API création session ;
- test reprise session ;
- test soumission lead avec consentement ;
- test refus sans consentement ;
- test résumé/qualification ;
- test envoi email ;
- test purge rétention ;
- test fallback CTA sans JS.

## Stack finale recommandée

Symfony 6.4 + Doctrine + Twig + JS custom global + Symfony Mailer + provider IA Mistral via interface d’abstraction + index documentaire public SQL léger.

## Architecture MVP recommandée

Widget global sobre injecté dans `base.html.twig`, API Symfony dédiée, stockage Doctrine séparé du formulaire existant, RAG léger sur contenus publics indexés, soumission lead avec email récapitulatif, reprise de session par token local, purge automatique.

## Ordre de développement conseillé

1. tables Doctrine + migrations
2. indexeur de contenu public
3. API conversations/messages/lead
4. provider IA + prompts
5. widget front + hook CTA
6. email récapitulatif
7. purge + admin minimal
