# OLING.fr — Agent IA commercial V2.8.4

## Objet

Fiabiliser les demandes explicites de proposition / chiffrage afin de ne pas perdre un prospect chaud si le LLM est lent ou indisponible.

## Modèle et accès OpenAI

- Modèle configuré : `gpt-5.6-sol` (`chat_ai_openai_model_default`, surcharge possible par `CHAT_AI_OPENAI_MODEL`).
- Accès OpenAI hors sandbox : OK.
- Endpoint utilisé par le provider : `/v1/responses`.
- Blocage sandbox observé : DNS `api.openai.com` non résolu. Non retenu comme anomalie applicative.

## Corrections locales

- Ajout des intentions `quote_request` et `proposal_request` dans la qualification et le prompt français.
- Priorisation AMOA ERP quand le message contient explicitement ERP / AMOA / processus, pour éviter une mauvaise bascule CRM sur le mot `ventes`.
- Nouveau type de réponse `proposal_request` avec bouton `Recevoir une proposition OLING`.
- En cas d’indisponibilité LLM sur demande de proposition : message transparent, sans conseil inventé, avec bouton `Transmettre ma demande de proposition à OLING`.
- Préremplissage formulaire enrichi pour demande de proposition AMOA ERP industriel : objet, contexte PME / 40 utilisateurs, processus, prestations, attentes commerciales et texte original.
- Continuité locale en cas de backend indisponible : bouton `Copier ma demande`, sans accusé d’envoi.

## Recette automatisée

- `php -l` : OK sur les fichiers PHP modifiés.
- `node --check assets/js/chat-widget.js` : OK.
- `node --check tests/e2e/chat-conversion.spec.js` : OK.
- `./vendor/bin/phpunit tests/ChatResponderTest.php tests/ChatConversationManagerTest.php tests/ChatQualificationServiceTest.php tests/MistralChatProviderTest.php` : OK, 109 tests, 361 assertions.
- `npm run build` : OK, assets générés.
- `npm run test:e2e:chat` : OK, 40 tests Playwright passés.

## Recette live OpenAI

Commande : `php bin/console app:chat:live-commercial-audit`.

Résultat global :

- 21 scénarios joués.
- 16 scénarios commerciaux.
- 15 / 16 scénarios commerciaux avec bouton de contact.
- Taux CTA commercial : 93,8 %.
- Modèle effectif : `gpt-5.6-sol`.

Scénarios demandés :

| Scénario | Statut | Type | CTA | Latence |
|---|---:|---|---:|---:|
| DORA gestion d’actifs | OK | `contact_offer` | Oui | 13 338 ms |
| AMOA ERP | OK | `contact_offer` | Oui | 10 413 ms |
| RFE | OK | `contact_offer` | Oui | 14 376 ms |

Autres latences notables : SI Finance 19 074 ms, Sage X3 25 822 ms, information DORA 31 896 ms.

## Échec reproductible observé

Scénario `sirh` : réponse commerciale de qualité, mais `message_type=question` et action `start_diagnostic` seulement, sans bouton contact.

Conclusion : anomalie de conversion reproductible sur ce scénario précis, hors périmètre strict V2.8.4 demande de proposition ERP. Aucune refonte engagée.

## Décision

GO local pour V2.8.4 sur le parcours demande de proposition / fallback.

Pas de déploiement production.
