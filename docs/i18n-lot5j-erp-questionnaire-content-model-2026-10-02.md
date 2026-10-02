# I18N-5J ERP questionnaire content model - 2026-10-02

## Principle

The ERP questionnaire keeps a technical definition plus a localized content layer.

Technical values remain the source for validation, scoring, conditionals, persistence, PDF generation, email routing, and analytics. Labels and editorial copy are read from locale files.

## Files

- `data/i18n/erp_questionnaire.definition.json`
  - version
  - supported locales
  - required fields
  - option group codes
- `data/i18n/erp_questionnaire.fr.json`
  - validation messages
  - field labels
  - option labels
  - summary copy
  - PDF/result labels
  - email subjects and body copy

## Runtime

- `ErpQuestionnaireContentProvider` loads JSON content from the project directory.
- Current runtime locale is forced to `fr`.
- Current content version is `v1`.
- `ErpQuestionnaireSubmission` stores locale and questionnaire version for later migrations.

## Invariants

- Option values such as `finance`, `erp`, `short_term`, `scoping`, `30_60k` are not translated.
- Scoring thresholds and conditional rules are unchanged.
- Existing summary keys are unchanged.
- Existing PDF and email outputs still receive the same submission and summary data.

## Future EN/ES Enablement

To add another language later:

1. Add `data/i18n/erp_questionnaire.en.json` or `.es.json`.
2. Add the locale to `supported_locales`.
3. Route or select locale explicitly.
4. Keep technical option values unchanged.
