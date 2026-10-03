# I18N-9 Wave 3R - Human Review + Progressive Publication

Date: 2026-10-03

Base prod head: `ec9239f1`

## Scope

- Reviewed Wave 3 sectors, projects and team profiles for EN/ES.
- No bulk AI generation.
- Progressive publication only.

## Decisions

- Sectors: 3 EN + 3 ES reviewed and published. Sector indexes EN/ES also published to expose localized index routes.
- Projects: 10 EN + 10 ES reviewed. First safe batch published: 5 EN + 5 ES.
- Projects kept reviewed only: lightweight/legacy rows with sparse public metadata or lower confidence for immediate publication.
- Team: 7 EN + 7 ES profiles reviewed and published. Team page EN/ES published.

## Corrections

- Replaced EN project wording using "case study/case" with "reference".
- Corrected one ES project slug/title that remained partially French.
- Neutralized one EN team bio phrase to stay closer to the public FR source.
- Synchronized Wave 1 page snapshots for localized `projects` and `team` publication status.

## Validation

- Confidentiality review: PASS.
- Factual parity: PASS.
- Fabricated claims: 0 identified in the published subset.
- FR leakage: no unexpected French in published Wave 3 editorial fields; proper names/legal names retained.
- Tests:
  - `./vendor/bin/phpunit`: 153 tests, 491 assertions.
  - `php bin/console lint:container`: OK.
  - `php bin/console lint:twig templates/`: OK.
  - `php bin/console lint:yaml config/ translations/ data/i18n/ --parse-tags`: OK.

## Expected Final Counts

- Sector EN: published 4, reviewed 0, to_review 0.
- Sector ES: published 4, reviewed 0, to_review 0.
- Project EN: published 5, reviewed 5, to_review 0.
- Project ES: published 5, reviewed 5, to_review 0.
- Team EN: published 7, reviewed 0, to_review 0.
- Team ES: published 7, reviewed 0, to_review 0.

## Next

I18N-9 Wave 4 - Resources / Articles / Long-tail SEO.
