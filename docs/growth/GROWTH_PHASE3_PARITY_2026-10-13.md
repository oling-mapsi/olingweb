# Growth Phase 3 parity

OLING Admin is the cockpit. Growth Factory remains the campaign engine and source of truth.

No OLING Growth campaign tables are introduced in Phase 3.

| Feature | Status |
| --- | --- |
| Campaign list/detail | `/studio-simple/campaigns` via server-side client |
| Campaign types | `MAPSI_USERS`, `MAPSI_MARKETING`, `OLING` preserved |
| Channels | `mapsi_site`, `oling_site`, `linkedin_manual`, `email_manual` preserved |
| Generation/regeneration | Delegated to Growth Factory |
| Channel editing | Delegated to Growth Factory asset update with `expected_version` |
| Publication | Explicit POST only, no auto-publish after generation |
| Weekly Packs / Source Packs / retry / schedule / channel settings | Preserved in Growth Factory, advanced admin screens still pending |
| Production writes | Not executed in tests |
