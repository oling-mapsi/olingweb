# OLING — Audit tracking visiteurs & parcours utilisateurs

Date : 2026-10-13
Périmètre : dépôt local `main`, contrôle HTML production léger sur `https://oling.fr/`.
Statut : audit et recommandations uniquement. Production non modifiée.

## 1. Synthèse de direction

OLING dispose d’un socle GA4 actif, identifié par `G-1EN73LS1B3`, chargé via le bundle JavaScript public.
Le Consent Mode v2 est présent et initialise GA4 avec `analytics_storage: denied` avant choix utilisateur.
Les pages `/admin` et `/login` sont exclues du layout public, du bandeau cookies et du widget chat.
Le formulaire contact envoie un événement GA4 fiable uniquement après succès serveur.
Les clics téléphone et CTA contact sont trackés après consentement, mais l’email n’est pas tracké.
Le chat IA pousse de nombreux événements dans `dataLayer`, sans relais GA4 confirmé dans le code.
Certains événements chat sont déclenchés avant confirmation serveur.
Les parcours visiteurs complets ne sont pas reconstituables aujourd’hui dans l’admin OLING.
L’exclusion interne par IP est inadaptée ; la bonne solution est un cookie first-party signé activé depuis l’admin.
Recommandation : conserver GA4 à court terme, ajouter un collecteur Symfony minimal + dashboard métier.

## 2. Inventaire technique du tracking actuel

### Confirmé dans le code

| Élément | Fichier | Preuve |
|---|---|---|
| GA4 configuré | `templates/base.html.twig:357` | `data-ga-id="G-1EN73LS1B3"` |
| Admin/login détectés | `templates/base.html.twig:42` | `isAdminPage` sur route `admin_`, `login`, `/admin`, `/login` |
| Scripts publics exclus admin | `templates/base.html.twig:453-583` | Chat, cookies et footer uniquement si `not isAdminPage` |
| Widget chat public | `templates/base.html.twig:453-454` | Include `_chat_widget_v2.html.twig` hors admin |
| Bandeau cookies | `templates/base.html.twig:456-502` | Choix essentiel/analytics |
| Consent Mode v2 | `assets/app.js:554-616` | `gtag('consent','default')`, puis update |
| Chargement gtag | `assets/app.js:574-599` | Injection `googletagmanager.com/gtag/js?id=${gaId}` |
| Attribution demande | `assets/app.js:375-552` | UTM/referrer/source/landing stockés en `sessionStorage` si consentement analytics |
| Contact submit GA4 | `assets/app.js:1458-1498` | `contact_submit` après `response.success` |
| Formulaire contact serveur | `src/Controller/ContactController.php:22-147` | CSRF, honeypot, rate limit, validation, email, réponse JSON |
| Chat dataLayer | `assets/js/chat-widget.js:315-328` | `window.dataLayer.push({ event: oling_chat_* })` |
| Chat lead confirmé serveur | `assets/js/chat-widget.js:952-973`, `src/Service/Chat/ChatConversationManager.php:114-175` | événement après réponse API |
| Dashboard chat actuel | `templates/admin/chat/index.html.twig:18-109` | métriques internes chat uniquement |

### Confirmé en production

Le HTML de `https://oling.fr/` contient le widget IA, les formulaires de contact chat, le questionnaire ERP, le footer public et les liens de contact. Contrôle effectué le 2026-10-13 via lecture HTML publique. La réponse HEAD production renvoie `200 OK`, serveur Apache, cookie `PHPSESSID` sécurisé `httponly` `samesite=lax`.

### Non vérifiable avec les accès disponibles

- Réception effective des événements dans GA4 DebugView / Realtime.
- Configuration GA4 : filtres de trafic interne, conversions, dimensions personnalisées, exclusions de référents.
- Requêtes réseau navigateur réelles vers `g/collect`, car aucun navigateur instrumenté n’a été exécuté dans cet audit.
- État exact de la branche GitHub distante et concordance commit prod/local.

## 3. Constats confirmés, risques et preuves

### GA4 et Consent Mode

GA4 est initialisé sur toutes les pages utilisant `base.html.twig`, sauf blocs publics masqués en admin. L’identifiant est codé dans le `<body>` : `G-1EN73LS1B3` (`templates/base.html.twig:357`). Le JavaScript crée `window.gtag`, déclare le consentement par défaut refusé, configure GA4 avec IP anonymisée et désactivation Google Signals (`assets/app.js:574-593`).

Risque : même avant acceptation, le script GA4 est chargé et la commande `config` est poussée avec Consent Mode refusé. C’est conforme à une logique Consent Mode, mais il faut valider côté CNIL/GA4 que les signaux cookieless sont bien acceptés dans la politique de consentement OLING.

### Tracking commercial actuel

Le formulaire contact est le point le plus fiable : l’événement `contact_submit` est émis uniquement dans le callback AJAX `success` et seulement si `response.success` est vrai (`assets/app.js:1476-1485`). Le serveur confirme après CSRF, honeypot, rate limit, validation, persistence et email (`src/Controller/ContactController.php:31-147`).

Risque : si l’email échoue après persistence, le serveur renvoie `success:false` (`src/Controller/ContactController.php:136-141`) ; la conversion GA4 n’est pas envoyée, alors que la demande est enregistrée. Décision métier à prendre : conversion = enregistré ou email envoyé.

### Chat IA

Le chat enregistre des conversations serveur avec source path, URL, referrer, IP hash et user-agent hash (`src/Service/Chat/ChatConversationManager.php:25-37`). Côté analytics, le widget pousse des événements `oling_chat_*` dans `dataLayer` (`assets/js/chat-widget.js:315-328`), mais aucun pont `dataLayer -> gtag('event')` ni GTM container n’est visible dans le code.

Risque majeur : les événements chat peuvent être visibles localement dans `dataLayer` mais absents de GA4. Les événements `form_submitted` et `lead_form_submitted` sont poussés avant confirmation serveur (`assets/js/chat-widget.js:1142-1149`) ; les événements fiables sont `lead_confirmed` / `lead_submission_confirmed`, poussés après réponse API (`assets/js/chat-widget.js:964-971`).

### Attribution des sources

Le code distingue UTM/referrer pour ChatGPT/OpenAI, Claude/Anthropic, Perplexity, Bing/Copilot, Google, referral, direct (`assets/app.js:405-420`). Les UTM sont conservés en session uniquement si `oling_cookie_consent = all` (`assets/app.js:441-467`).

Risque : LinkedIn n’a pas de classification dédiée ; avec `utm_source=linkedin`, la source devient `referral` (`assets/app.js:416`). Les parcours sans consentement analytics ne conservent pas l’attribution session en `sessionStorage`, mais le formulaire peut encore remplir le contexte courant.

### Parcours visiteurs

GA4 peut fournir pages d’entrée, pages vues, engagement, sorties et chemins agrégés, mais le dépôt ne contient pas encore le dashboard `OLING — Audience & Parcours`. L’admin existant couvre seulement les conversations IA et leads chat (`templates/admin/chat/index.html.twig:18-109`).

Risque : sans export GA4 Data API ou collecte serveur first-party, OLING ne peut pas exploiter simplement les parcours commerciaux A/B/C dans son administration.

## 4. Matrice des événements commerciaux

| Événement | Fichier source | Déclencheur | Consentement requis | Réception GA4 vérifiée | Risque doublon | Correction proposée |
|---|---|---|---|---|---|---|
| Page view GA4 | `assets/app.js:588-593` | chargement page | analytics | Non | Faible | Vérifier DebugView |
| Clic CTA contact | `assets/app.js:513-537` | clic lien `/contact` depuis pages mappées | analytics | Non | Faible | Ajouter mapping exhaustif + noms normalisés |
| Clic téléphone | `assets/app.js:539-549` | clic `tel:` | analytics | Non | Faible | OK, ajouter dashboard |
| Clic email | Non trouvé | clic `mailto:` | analytics | Non | N/A | Ajouter tracking sans valeur email |
| Soumission contact confirmée | `assets/app.js:1476-1485` | succès serveur `response.success` | analytics | Non | Faible | Marquer conversion GA4 |
| Ouverture chat IA | `assets/js/chat-widget.js:348-361`, `975-985` | ouverture panel | analytics en cible | Non, dataLayer seul | Moyen, deux noms | Unifier un seul événement |
| Première question chat | `assets/js/chat-widget.js:1037-1048` | première soumission message | analytics en cible | Non, dataLayer seul | Faible | Relayer vers GA4 après consentement |
| Besoin identifié | `assets/js/chat-widget.js:515-521` | qualification conversation | analytics en cible | Non | Moyen si rerender | Dédupliquer par conversation/token |
| Diagnostic commencé | `assets/js/chat-widget.js:52-57`, `1067-1093` | bouton action | analytics en cible | Non | Faible | Relayer GA4 |
| Diagnostic terminé | Non explicite | réponse assistant type diagnostic | analytics en cible | Non | N/A | Ajouter événement serveur/client sur type `diagnostic` |
| Note générée | `assets/js/chat-widget.js:52-57`, `1096-1098` | bouton action | analytics en cible | Non | Moyen | Émettre après succès serveur |
| Note téléchargée | `assets/js/chat-widget.js:52-57`, `1101-1105` | bouton téléchargement | analytics en cible | Non | Faible | Émettre après URL PDF reçue |
| Formulaire lead chat ouvert | `assets/js/chat-widget.js:764-772` | ouverture formulaire | analytics en cible | Non | Moyen | Dédupliquer |
| Lead chat soumis tentative | `assets/js/chat-widget.js:1142-1149` | clic bouton lead | analytics en cible | Non | Moyen | Ne pas compter comme conversion |
| Lead chat confirmé | `assets/js/chat-widget.js:964-971` | réponse API OK | analytics en cible | Non | Faible | Conversion fiable à relayer |
| Questionnaire ERP soumis succès | `assets/js/chat-widget.js:902-910`, `1157-1168` | API OK | analytics en cible | Non | Faible | Ajouter `erp_questionnaire_confirmed` après succès |
| Téléchargement ressources | Non trouvé | clic ressource/PDF | analytics | Non | N/A | Ajouter tracking générique sans URL sensible |

## 5. Stratégie d’exclusion interne

Solution recommandée : exclusion first-party avant tout tracking.

1. Ajouter en admin un bouton `Exclure cet appareil des statistiques`.
2. À l’activation, créer un cookie first-party `oling_internal_analytics_excluded=1.<signature>` avec `Secure`, `HttpOnly` si lu serveur, `SameSite=Lax`, durée 12 mois.
3. Signature HMAC serveur avec rotation possible.
4. Injecter dans le layout public un booléen non nominatif, par exemple `data-analytics-excluded="1"`, si le cookie est valide.
5. Dans `assets/app.js`, arrêter `initConsentMode`, `initDemandTracking` et tous les appels tracking si exclusion active.
6. Exclure systématiquement `/admin`, `/login`, `/logout`, `/preview`, et toute route interne avant chargement des scripts.
7. Prévoir un bouton `Réactiver cet appareil`.
8. Activer séparément sur chaque navigateur/appareil collaborateur.

Limites : navigation privée, nouveau navigateur, suppression cookies, nouvel appareil, historique GA4 déjà collecté non nettoyable parfaitement. Cette approche évite l’empreinte numérique intrusive et fonctionne hors IP.

## 6. Architecture recommandée

| Option | Forces | Limites | Avis |
|---|---|---|---|
| A. GA4 + Data API + dashboard Symfony | Réutilise le socle actuel, faible rupture, données source/canal riches, compatible OVH/Symfony | Parcours GA4 complexes, dépendance Google, consentement requis, DebugView/API à configurer | Bon court terme |
| B. Matomo auto-hébergé + dashboard OLING | Maîtrise des données, rapports parcours meilleurs, exclusion interne maîtrisable | Maintenance, durcissement, consentement CNIL selon configuration, intégration à construire | Bon moyen terme si priorité souveraineté/parcours |
| C. Plausible/Umami | Simple, léger, lisible | Parcours détaillés limités, attribution/conversions moins fines, consentement dépend du paramétrage | Insuffisant seul |

Recommandation : Option A renforcée immédiatement, avec un collecteur Symfony minimal pour conversions confirmées et exclusion interne. Étudier Matomo en P1 si OLING veut des parcours plus lisibles et une meilleure maîtrise des données.

## 7. Dashboard OLING — Audience & Parcours

### Bloc A — Synthèse

Indicateurs prioritaires : sessions humaines hors interne, sessions engagées, prospects confirmés.
Secondaires : évolution 7/30/90 jours, nouveaux visiteurs, taux conversion, pages/session.
Dénominateurs : sessions avec consentement analytics pour GA4 ; conversions serveur confirmées pour leads.

### Bloc B — Pages consultées

Table : Page, visites, entrées, temps engagé, sorties, conversions associées.
Regroupements : AMOA ERP, CRM, SIRH, GMAO, Gouvernance DSI, RGPD/DPO, Cyber, QSE, MAPSI. Normaliser `/fr`, `/en`, `/es`, slash final, query strings.

### Bloc C — Origine

Source, canal, campagne, UTM, assistant IA quand referrer/UTM fiable. Ne jamais inventer une provenance.

### Bloc D — Parcours

Top chemins agrégés : entrée → page 2 → page 3 → conversion/sortie. Transitions entre pages en table ou sankey simple.

### Bloc E — Conversions

Contact confirmé, lead IA confirmé, questionnaire ERP confirmé, clic téléphone, téléchargement utile.

### Bloc F — Qualité des mesures

Alertes : rupture GA4, événement chat non relayé, hausse direct/unknown, volume interne exclu, erreurs formulaires, chute conversions.

## 8. Plan d’action priorisé

### P0

- Valider dans GA4 DebugView les événements `page_view`, `contact_submit`, `phone_click`, `demand_cta_click`.
- Ajouter exclusion interne par cookie signé avant chargement tracking.
- Relayer les événements chat vers GA4 uniquement après consentement, ou les compter via serveur.
- Ne compter comme conversions que `contact_submit`, `lead_submission_confirmed`, `erp_questionnaire_confirmed`.
- Ajouter classification LinkedIn et campagnes UTM.

### P1

- Créer dashboard Symfony `OLING — Audience & Parcours`.
- Connecter GA4 Data API avec cache quotidien.
- Ajouter table serveur `analytics_conversion_event` pour conversions confirmées.
- Ajouter tracking `mailto:` et ressources téléchargées sans données personnelles.

### P2

- Comparer Matomo sur préproduction avec recette CNIL.
- Ajouter visualisation de transitions.
- Export CSV filtré par période et source.

## 9. Critères de recette mesurables

- Refus cookies : aucune conversion GA4 client, pas de cookies `_ga`.
- Acceptation cookies : `page_view`, CTA, téléphone et contact confirmé visibles DebugView.
- Admin connecté avec exclusion : zéro requête GA4 sur pages publiques et admin.
- `/admin` et `/login` : aucun widget chat, aucun bandeau cookies, aucun tracking public.
- Contact valide : un seul `contact_submit` après succès serveur.
- Chat lead valide : un seul `lead_submission_confirmed` après succès API.
- UTM LinkedIn : source `linkedin`, campagne conservée.
- Dashboard : période 7/30/90 jours, comparaison précédente, export CSV.

## 10. Points nécessitant des accès complémentaires

- Accès GA4 propriété OLING : DebugView, Admin, conversions, filtres internes, Data API.
- Accès production navigateur ou proxy réseau pour constater les requêtes `g/collect`.
- Commit SHA ou pipeline de déploiement pour comparer local/GitHub/prod.
- Décision RGPD sur usage Consent Mode cookieless et conservation serveur des conversions.

## 11. Décisions à soumettre à validation

- Conversion contact = message enregistré ou email effectivement envoyé ?
- Dashboard basé d’abord sur GA4 Data API ou collecte Symfony first-party hybride ?
- Conservation des conversions serveur : 180 jours, 13 mois ou autre durée ?
- Matomo à évaluer en P1 ou écarté pour 2026 ?
- Niveau de détail acceptable pour parcours agrégés sans suivi individuel nominatif.

## Conclusion

- **TRACKING ACTUEL :** GA4 présent et Consent Mode v2 codé ; fiabilité correcte pour contact confirmé, incertaine pour chat.
- **EXCLUSION INTERNE :** faisable ; solution recommandée par cookie first-party signé activé depuis l’administration.
- **PARCOURS VISITEURS :** analyse possible via GA4 agrégé, non exploitable aujourd’hui dans l’admin OLING.
- **TRACKING COMMERCIAL :** contact fiable ; téléphone/CTA présents ; email, ressources, ERP confirmé et relais chat GA4 manquants.
- **SOLUTION RECOMMANDÉE :** GA4 + Data API + dashboard Symfony + collecteur serveur minimal des conversions confirmées.
- **DASHBOARD OLING :** interface métier en 6 blocs : synthèse, pages, origine, parcours, conversions, qualité.
- **PRODUCTION MODIFIÉE : NON.**
