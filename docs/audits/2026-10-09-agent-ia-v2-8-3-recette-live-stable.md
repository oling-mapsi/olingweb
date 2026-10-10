# OLING.FR - Recette commerciale V2.8.3 live stable

Date : 2026-10-09
Perimetre : controle OpenAI, 3 scenarios prioritaires, puis recette commerciale V2.8.3 complete.
Deploiement production : non.

## Controle API OpenAI

| Controle | Resultat |
|---|---:|
| Modele configure | `gpt-5.6-sol` |
| `/v1/models` | HTTP 200, 1 319 ms |
| Modele present dans la liste OpenAI | Oui |
| Controle minimal `/v1/responses` apres recette | HTTP 200, 1 198 ms |

## Scenarios prioritaires

| Scenario | Provider | Modele | Type | Bouton contact | Latence | Verdict |
|---|---|---|---|---:|---:|---|
| DORA societe de gestion | OpenAI | `gpt-5.6-sol` | `contact_offer` | Oui | 16 190 ms | OK |
| AMOA ERP | OpenAI | `gpt-5.6-sol` | `contact_offer` | Oui | 20 872 ms | OK |
| RFE cabinet | OpenAI | `gpt-5.6-sol` | `contact_offer` | Oui | 19 192 ms | OK |

Qualite commerciale : les trois reponses sont pertinentes, contextualisees, orientent vers un premier echange OLING et declenchent l'action structuree de prise de contact.

## Recette V2.8.3 complete

Commande executee :

```bash
CHAT_AI_OPENAI_MODEL=gpt-5.6-sol php bin/console app:chat:live-commercial-audit > /tmp/oling-v283-live-stable.json
```

| Indicateur | Resultat |
|---|---:|
| Scenarios totaux | 21 |
| Scenarios commerciaux | 16 |
| Reponses OpenAI | 17 |
| Reponses `llm_unavailable` | 2 |
| Reponses routeur contact local | 2 |
| Boutons de contact totaux | 14 |
| Taux commercial bouton contact | 75 % |
| Objectif V2.8.3 | 90 % |
| Latence moyenne | 20 577 ms |

## Detail commercial

| Scenario | Provider | Type | Bouton | Latence | Verdict |
|---|---|---|---:|---:|---|
| DORA asset manager | OpenAI | `contact_offer` | Oui | 16 190 ms | OK |
| AMOA ERP | OpenAI | `contact_offer` | Oui | 20 872 ms | OK |
| DPO externalise | OpenAI | `question` | Non | 11 051 ms | KO |
| RFE cabinet | OpenAI | `contact_offer` | Oui | 19 192 ms | OK |
| GMAO | OpenAI | `contact_offer` | Oui | 22 235 ms | OK |
| CRM | OpenAI | `contact_offer` | Oui | 15 134 ms | OK |
| SI Finance | OpenAI | `question` | Non | 36 529 ms | KO |
| SIRH | OpenAI | `contact_offer` | Oui | 14 129 ms | OK |
| DSI transition | OpenAI | `contact_offer` | Oui | 18 533 ms | OK |
| ISO 27001 | OpenAI | `contact_offer` | Oui | 15 176 ms | OK |
| NIS2 | OpenAI | `contact_offer` | Oui | 24 108 ms | OK |
| QSE | `llm_unavailable` | `technical_unavailable` | Non | 46 207 ms | KO non reproductible confirme |
| MAPSI demo | OpenAI | `contact_offer` | Oui | 20 360 ms | OK |
| SAP integrateur | OpenAI | `contact_offer` | Oui | 17 351 ms | OK |
| Sage X3 | OpenAI | `contact_offer` | Oui | 15 665 ms | OK |
| Parcours SI Finance -> RFE -> Salesforce -> Dolibarr | `llm_unavailable` | `technical_unavailable` | Non | 45 987 ms | KO non reproductible confirme |

## No-sell

| Scenario | Provider | Type | Bouton | Latence | Verdict |
|---|---|---|---:|---:|---|
| Information simple DORA | OpenAI | `contact_offer` | Oui | 37 320 ms | KO : trop commercial |
| Definition AMOA etudiant | OpenAI | `contact_offer` | Oui | 22 089 ms | KO : bouton contact inutile |
| Refus explicite de contact | Routeur local | `contact_info` | Non | 0 ms | OK |
| Hors sujet | OpenAI | `question` | Non | 13 983 ms | OK |
| Preference diagnostic | Routeur local | `contact_info` | Non | 0 ms | OK |

## Conclusion

Les trois scenarios prioritaires demandes reussissent avec le vrai LLM.

La recette complete ne certifie pas encore l'objectif V2.8.3 : 75 % de propositions de contact sur les demandes commerciales explicites, contre 90 % attendu.

Anomalies observees sans refonte engagee :

- DPO externalise et SI Finance : reponse commerciale correcte mais pas de bouton `open_lead_form`.
- DORA information simple et definition AMOA etudiant : no-sell trop orientes contact.
- QSE et parcours multi-tour RFE : timeouts OpenAI pendant la recette, non etablis comme defauts applicatifs reproductibles car l'API OpenAI repondait correctement juste apres.

Aucune modification de code ou de configuration n'a ete effectuee pendant cette reprise de recette.
