# I18N ERP questionnaire inventory - 2026-10-02

## Scope

- Public route: `/erp-progiciel/questionnaire`
- Result route: `/erp-progiciel/questionnaire/{token}/pdf`
- Core services:
  - `ErpQuestionnairePayloadMapper`
  - `ErpQuestionnaireSummaryService`
  - `ErpQuestionnairePdfGenerator`
  - `ErpQuestionnaireMailer`
- Views:
  - `templates/erp_questionnaire/form.html.twig`
  - `templates/erp_questionnaire/result.html.twig`
  - `templates/erp_questionnaire/pdf.html.twig`
  - `templates/emails/erp_questionnaire_*`

## Findings

- Technical answer codes are already stable and must remain non-localized.
- Editorial and UI content was distributed across PHP services and Twig templates.
- The questionnaire has no EN/ES public route and must keep FR as the only published locale.
- The Symfony default locale remains unchanged.
- Existing scoring, conditional blocks, PDF generation, and email flow are preserved.

## Localized Source

- Technical definition: `data/i18n/erp_questionnaire.definition.json`
- French content: `data/i18n/erp_questionnaire.fr.json`
- Runtime provider: `ErpQuestionnaireContentProvider`

## Persistence

- New submission metadata:
  - `locale`, default `fr`
  - `questionnaireVersion`, default `v1`
- Migration: `Version20261002200000`

## Exclusions

- No EN/ES routes.
- No EN/ES content.
- No prompt or chat-widget behavior change.
- No deploy/build artifact change.
