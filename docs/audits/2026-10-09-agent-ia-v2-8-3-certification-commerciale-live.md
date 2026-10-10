# OLING.FR - Agent IA commercial V2.8.3

## Verdict

**NO-GO production.**

La recette live demandee n'est pas certifiable au 2026-10-09 : les appels minimaux OpenAI fonctionnent, mais les conversations completes de l'agent OLING n'ont pas ete servies de facon stable par le LLM. Les resultats obtenus proviennent majoritairement du fallback `llm_unavailable`, donc ils ne peuvent pas valider le comportement commercial genere.

## Changements appliques

- Ajout de la commande de recette live `app:chat:live-commercial-audit`.
- Correction du modele OpenAI par defaut : `gpt-5.6` remplace par `gpt-5.6-sol`, modele effectivement disponible sur le compte teste.
- Conservation du routage multi-provider existant : OpenAI prioritaire, Mistral configure si cle disponible, fallback heuristique/technique si indisponibilite.
- Correction des copies de fallback technique pour ne plus exposer `/contact?chat_fallback=1` en texte brut.

## Diagnostic connectivite

| Controle | Resultat |
|---|---:|
| Execution sans autorisation reseau externe | Echec DNS vers `api.openai.com` |
| `/v1/models` OpenAI avec cle locale autorisee | OK, 135 modeles retournes |
| Test minimal `/v1/responses` `gpt-5-mini` | OK |
| Test minimal `/v1/responses` `gpt-5.6-sol` | OK |
| Cle Mistral locale visible | Non |
| Conversation agent complete via Symfony | Instable : timeouts, erreurs DNS/TLS/connectivite |

Les secrets n'ont pas ete exposes dans ce rapport.

## Recette live executee

Commande :

```bash
CHAT_AI_OPENAI_MODEL=gpt-5-mini php bin/console app:chat:live-commercial-audit > /tmp/oling-chat-live-audit-v283.json
```

Perimetre : 21 scenarios, dont 16 demandes commerciales explicites et 5 scenarios no-sell.

| Indicateur | Resultat |
|---|---:|
| Scenarios totaux | 21 |
| Demandes commerciales | 16 |
| Reponses LLM indisponibles | 19 |
| Reponses routeur contact local | 2 |
| Demandes commerciales avec bouton contact | 0 / 16 |
| Taux de proposition contact commerciale | 0 % |
| Objectif V2.8.3 | 90 % |

## Scenarios couverts

Demandes commerciales testees : DORA societe de gestion, ERP/AMOA, DPO externalise, RFE, GMAO, CRM, SI Finance, SIRH, DSI de transition, ISO 27001, NIS2, QSE, MAPSI, SAP, Sage X3, parcours multi-tour SI Finance -> RFE -> Salesforce -> Dolibarr.

No-sell testes : information simple DORA, definition AMOA pour etudiant, refus explicite de contact, hors sujet, preference pour diagnostic avant contact.

## Defauts bloquants constates

1. **Recette live non probante** : les conversations completes echouent majoritairement avant generation LLM.
2. **Objectif commercial non atteint** : 0 % des 16 demandes commerciales explicites declenchent l'action structuree `open_lead_form`.
3. **Fallback technique non conforme V2.3 pendant la recette** : en mode indisponibilite, la reponse expose encore `/contact?chat_fallback=1` en texte brut. Correctif applique ensuite dans les copies serveur et navigateur.
4. **Parcours SI Finance -> RFE -> Salesforce -> Dolibarr non valide** : le dernier tour finit en `technical_unavailable`, sans bouton ni pre-remplissage verifiable.
5. **Email transport non certifie** : aucun envoi de test commercial n'a ete effectue, la recette conversationnelle etant deja bloquee.

## Validation locale

- `php -l src/Command/ChatLiveCommercialAuditCommand.php` : OK.
- `php bin/console list app:chat` : la commande `app:chat:live-commercial-audit` est disponible apres vidage du cache.
- `node --check assets/js/chat-widget.js` : OK.
- Validation JSON `data/i18n/ai_consultant.*.json` : OK.
- `./vendor/bin/phpunit tests/ChatResponderTest.php tests/MistralChatProviderTest.php` : OK, 102 tests, 334 assertions.
- `npm run test:e2e:chat` : OK, 12 tests Playwright, incluant desktop/mobile. Validation UI mockee, pas certification LLM live.

## Decision

La V2.8.3 est **non certifiee** pour production.

Pre-requis avant nouvelle certification :

- stabiliser les appels LLM complets depuis Symfony ;
- corriger le fallback technique pour ne jamais afficher `/contact?chat_fallback=1` en brut ;
- rejouer les 21 scenarios avec vrai LLM ;
- verifier le bouton `Transmettre mon projet a OLING`, le pre-remplissage serveur et l'absence d'envoi automatique ;
- executer le test email uniquement avec transport de test.
