# I18N AI consultant inventory - 2026-10-02

| Source | Texte | Type | Runtime use | Target |
|---|---|---|---|---|
| `templates/includes/_chat_widget_v2.html.twig` | launcher, close, forms, consent, ERP-in-chat labels | UI_MICROCOPY | Public chat widget | Symfony translations `chat.*` |
| `assets/js/chat-widget.js` | status, errors, welcome, ERP result labels | UI_MICROCOPY / RESULT_COPY | Browser runtime | DOM JSON from Twig translations |
| `OpenAiResponsesProvider` | developer prompt | SYSTEM_INSTRUCTION | OpenAI Responses input | `data/i18n/ai_consultant.fr.json` |
| `OpenAiResponsesProvider` | user prompt template | PROMPT_TEMPLATE | OpenAI Responses input | `data/i18n/ai_consultant.fr.json` |
| `ChatResponder` | welcome, emergency, contact, confidentiality, safety fallback | RESULT_COPY / FALLBACK | Reply assembly | `data/i18n/ai_consultant.fr.json` |
| `ChatConversationManager` | lead validation and confirmation | VALIDATION / RESULT_COPY | Lead submission | `data/i18n/ai_consultant.fr.json` |
| `ChatSummaryService` | lead summary and ERP AMOA summary | BUSINESS_CONTENT / EMAIL_COPY | Email preparation | `data/i18n/ai_consultant.fr.json` |
| `ChatLeadMailer` + templates | email subject/body labels | EMAIL_COPY | Internal lead email | `data/i18n/ai_consultant.fr.json` |
| `ChatApiController` | empty/error/success JSON messages | VALIDATION / RESULT_COPY | API responses | `data/i18n/ai_consultant.fr.json` + ERP content |
| `ChatQualificationService` | keyword maps and technical taxonomy | TECHNICAL | Qualification logic | Keep in PHP |
| `HeuristicAiProvider` | deterministic fallback reply blocks | BUSINESS_CONTENT | Heuristic provider | `data/i18n/ai_consultant.fr.json` |

## KPI

- AI CONSULTANT BUSINESS CONTENT HARDCODED BEFORE: high, spread across PHP/Twig/JS.
- AI CONSULTANT UI MICROCOPY HARDCODED BEFORE: high, widget template and JS.
- AI PROMPTS HARDCODED IN PHP BEFORE: 2 large prompt blocks.
- AI PROMPTS HARDCODED IN PHP AFTER: 0 for OpenAI provider.
- HEURISTIC BUSINESS CONTENT HARDCODED IN PHP AFTER: 0.
- UI MICROCOPY SOURCE AFTER: Symfony translations rendered into Twig and DOM JSON.
- PROMPT SOURCE AFTER: `data/i18n/ai_consultant.fr.json`.
- HEURISTIC COPY SOURCE AFTER: `data/i18n/ai_consultant.fr.json`.
- KNOWN RESIDUAL: none identified for AI consultant business copy in this lot.
