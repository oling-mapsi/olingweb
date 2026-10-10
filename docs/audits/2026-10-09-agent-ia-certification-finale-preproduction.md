# OLING.FR — Agent IA commercial — Certification finale préproduction

Date : 2026-10-09
Périmètre : local uniquement, aucun déploiement.

## 1. État Git figé

- HEAD candidat de base : `f20b6096`.
- État livrable réel : `f20b6096` + modifications locales V2 à V2.8.4 non commitées.
- Sauvegarde locale avant contrôle : `/private/tmp/oling-certification-2026-10-09`.
- Sauvegardes créées : `git-status-short.txt`, `worktree.diff`, `git-log.txt`, `git-reflog.txt`.
- Aucun `reset`, aucun changement de modèle, aucun changement de timeout.

Le dépôt contient de nombreux fichiers modifiés/non suivis : code chat, providers OpenAI/Mistral, dashboard, PDF, assets compilés, tests et rapports d’audit. Un commit de gel est nécessaire avant production.

## 2. Conversations OpenAI réelles

10 scénarios backend réels exécutés avec `gpt-5.6-sol`.

| Scénario | Résultat |
|---|---|
| ERP industriel 1 | OK OpenAI, CTA, méthodologie, jours, livrables, références, budget |
| ERP industriel 2 | OK OpenAI, CTA |
| DORA | OK OpenAI, CTA |
| DPO externalisé | OK OpenAI, CTA |
| AMOA SI Finance | OK OpenAI, CTA |
| RFE Salesforce / Dolibarr | OK OpenAI, CTA |
| GMAO | OK OpenAI, CTA |
| CRM | Échec OpenAI reproductible : HTTP 429 |
| ISO 27001 | Échec OpenAI reproductible : HTTP 429 |
| Question informative | Routage local contact/info, pas d’OpenAI requis |

Probe direct CRM/ISO : `OpenAI API returned HTTP 429`.

## 3. Latences

- Moyenne : `7 942 ms`.
- Médiane : `9 701 ms`.
- P95 indicatif : `13 196 ms`.
- Maximum : `13 196 ms`.
- Échecs : `2/10`, cause démontrée : quota/rate-limit OpenAI HTTP 429.

Réserve : les performances sont acceptables sur réponses réussies, mais la disponibilité fournisseur n’est pas stabilisée au moment de la recette.

## 4. Qualité commerciale

Les réponses OpenAI réussies sont exploitables, structurées et orientées conversion. Le scénario ERP prioritaire couvre la méthode, l’estimation en jours, les livrables, les références et le budget/modalités.

Le rendu Markdown est validé par test visuel automatisé : titres, paragraphes, listes, gras, liens, accents, apostrophes et protection HTML/script.

## 5. Transmission des leads

Tests Playwright mockés validés :

- CTA visible.
- Formulaire prérempli.
- Pas d’envoi automatique.
- Consentement non coché par défaut.
- Téléphone facultatif.
- Parcours desktop et mobile.

Non validé en réel : réception email isolée dans un transport de test. À faire avant GO production.

## 6. Notes PDF

Tests PHPUnit `ScopingNotePdfServiceTest` : OK.

À compléter avant GO sécurité/RGPD : accès non autorisé, autre conversation, expiration/purge et absence d’accès public direct en environnement préproduction complet.

## 7. Habilitations dashboard

Non rejoué complètement pendant cette passe. À valider avant GO production :

- anonyme refusé ;
- utilisateur non admin refusé ;
- admin habilité accepté ;
- contrôle côté serveur.

## 8. Cookies

Session vierge testée : la bannière cookies apparaît et intercepte le launcher tant qu’aucun choix n’est fait. Les boutons `Accepter tout` et `Refuser tout (minimum)` sont présents. Après choix explicite, le widget s’ouvre.

Aucun masquage artificiel utilisé.

## 9. Sécurité

Validé automatiquement :

- échappement HTML/script dans le rendu Markdown ;
- absence d’exécution de script injecté ;
- refus fonctionnel d’injection dans le test Playwright Markdown.

À compléter avant GO sécurité/RGPD :

- audit complet des protocoles de liens dangereux ;
- CSRF sur endpoints sensibles ;
- absence de secrets dans logs ;
- politique cookies/session en préproduction.

## 10. Checklist RGPD/DPO

Validation DPO requise sur :

- base légale conversations/leads/analytics ;
- durées de conservation conversations, qualifications, notes PDF ;
- destinataires des leads ;
- mentions d’information ;
- sous-traitants LLM et transferts éventuels ;
- droits des personnes ;
- traceurs et consentement ;
- accès administratifs ;
- purge et journalisation.

Cette recette technique ne vaut pas validation juridique.

## 11. Tests automatisés

OK :

- `npm run build`.
- `php -l src/Service/Chat/ChatConversationManager.php`.
- `php bin/console lint:twig templates/includes/_chat_widget_v2.html.twig templates/chat/scoping_note_pdf.html.twig templates/admin/chat/index.html.twig`.
- PHPUnit ciblés : OpenAI provider, Mistral provider, ChatResponder, ChatConversationManager, ScopingNotePdfService, ChatQualificationService.
- Playwright `chat-ui` desktop : OK.
- Playwright `chat-ui` mobile : OK.
- Playwright `chat-conversion` desktop : OK.
- Playwright `chat-contact` desktop : OK.

Correction effectuée pendant recette : robustesse du libellé CTA fallback proposition dans `ChatConversationManager`.

## 12. Défauts résiduels

- Bloquant production : HTTP 429 OpenAI reproductible sur CRM et ISO 27001 après plusieurs appels.
- Validation email réelle isolée non effectuée.
- Contrôles dashboard/PDF sécurité complets non rejoués en environnement préproduction complet.
- Dépôt non gelé en commit unique.

## 13. Configuration production à vérifier

- `CHAT_AI_PROVIDER=auto`.
- `OPENAI_API_KEY` présente, non affichée.
- Modèle effectif : `gpt-5.6-sol`.
- Mistral secondaire configurable.
- OpenAI timeout provider : `35 s`.
- Budget global chat : `55 s`.
- `MAILER_DSN` production ou transport de recette selon environnement.
- Répertoires privés PDF non publics.
- Tâche purge conversations/PDF planifiée.
- Cache Symfony prod régénéré.
- Assets compilés présents.

## 14. Procédure de déploiement préparée

1. Créer un commit de gel depuis l’état local validé.
2. Vérifier variables `.env` production sans afficher les secrets.
3. Installer dépendances Composer/NPM si nécessaire.
4. Compiler les assets.
5. Vider/réchauffer le cache Symfony prod.
6. Appliquer migrations éventuelles après contrôle.
7. Redémarrer services PHP/web.
8. Smoke tests post-livraison : accueil, widget, ERP, CTA, formulaire, dashboard admin, PDF.

Déploiement interdit sans instruction explicite `GO PROD`.

## 15. Procédure rollback

1. Identifier le commit pré-déploiement.
2. Restaurer code et assets précédents.
3. Restaurer configuration compatible.
4. Ne pas supprimer les leads reçus après déploiement.
5. Vérifier compatibilité migrations avant retour arrière.
6. Redémarrer services.
7. Rejouer smoke tests chat, contact, dashboard.

## 16. Décision GO / NO-GO

- GO technique : partiel. Le logiciel et les tests critiques automatisés passent, mais la recette OpenAI réelle présente 2 échecs HTTP 429.
- GO sécurité/RGPD : NO-GO. Validation DPO et contrôles complets préproduction non finalisés.
- GO production : NO-GO. Attendre levée du 429 OpenAI, validation email réelle isolée, gel Git et instruction explicite `GO PROD`.
