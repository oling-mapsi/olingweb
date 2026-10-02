# I18N-5K AI consultant localization foundation - 2026-10-02

## Architecture Before

- Chat UI copy was in Twig and JavaScript.
- OpenAI system/user prompts were embedded in PHP.
- Chat lead validation, summaries and email labels were embedded in PHP/Twig.
- Chat conversation stored locale, but runtime could inherit Symfony `default_locale`.

## Architecture After

- Runtime locale is forced to `fr` for public chat.
- Chat conversation stores `locale=fr` and `promptVersion=v1`.
- OpenAI prompts are versioned in `data/i18n/ai_consultant.fr.json`.
- Heuristic fallback reply blocks are loaded from `data/i18n/ai_consultant.fr.json`.
- Shared chat copy is loaded through `AiConsultantContentProvider`.
- Widget UI uses Symfony translations and injects JS copy through a DOM JSON object.
- Chat lead email source is localized through the same provider.

## Prompt Model

- Model: versioned JSON.
- Locale: `fr`.
- Version: `v1`.
- Source: `data/i18n/ai_consultant.fr.json`.
- Decision: JSON is preferable here because prompts are code-reviewed and coupled to response schema/taxonomy. No BO prompt editor was added.

## Locale Propagation

- `ChatConversationManager::createConversation()` ignores the temporary Symfony default locale and persists `fr`.
- OpenAI user prompt renders using the conversation locale.
- Lead summary/email uses the conversation locale.

## Tests

- `AiConsultantLocalizationTest`: prompt source, version, locale and persisted prompt version.
- `HeuristicAiProviderTest`: deterministic heuristic copy is resolved through the localized content provider.
- Existing chat responder, qualification, commercial sector regression, heuristic provider and ERP questionnaire tests remain passing.

## Status

PASS. AI consultant prompts, shared copy, email copy, UI microcopy and deterministic heuristic business replies now have a localized source of truth.
