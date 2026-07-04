# Root Workspace Cleanup

**Date:** 2026-07-04  
**Status:** Completed

## Removed

- `.phpstorm.meta.php`: 25 MB generated IDE metadata; Composer no longer generates it automatically.
- `.playwright-mcp/`: generated browser logs and page snapshots.
- `test-results/`: generated test output.
- `.agents/`: empty directory.
- `debug-ae998e.log`: obsolete debug trace.
- `estimate-demo-filled.png`: unreferenced demo screenshot.
- `GUI_ADMIN_ADD_STAGING_IP.txt`: retired staging/Cloud SQL instruction.
- `guide.txt`: unreferenced temporary theme note.
- `rule_deffault.txt`: unused duplicate ignore rules.
- `run-schedule.bat`: stale hard-coded `F:\web\new.craveva.com` scheduler path.
- `test.htaccess`: unused cPanel test configuration.
- `theme-custom.staging.css`: unreferenced staging stylesheet.
- `phpunit.xml.bak`: ignored local backup.
- `ai_widget/`: unused standalone AI widget samples, diagnostics, and Cursor rules.
- `backup/`: local 463.85 MB database recovery dump, removed at the user's request.

## Reorganized

- `MD_HEADER_TEMPLATE.md` -> `docs/documentation/MD_HEADER_TEMPLATE.md`
- `MD_WORKFLOW_QUICKSTART.md` -> `docs/documentation/MD_WORKFLOW_QUICKSTART.md`
- `RAG_agent.md` -> `docs/archive/RAG_AGENT_PLATFORM_HELP_PLAN.md`
- `ERP Product Usage & Policy Clarification.md` ->
  `docs/archive/ERP_PRODUCT_USAGE_POLICY_QUESTIONNAIRE.md`

## Prevented regeneration

- Added `.phpstorm.meta.php`, `.playwright-mcp/`, `test-results/`, and `.agents/`
  to `.gitignore`.
- Kept `/backup/` in `.gitignore` so future local database dumps remain untracked.
- Removed automatic `artisan ide-helper:meta` execution from Composer's
  `post-update-cmd`. The IDE helper package remains installed for manual use.

No Laravel runtime source, database data, dependency directory, or deployment
secret was changed by this cleanup.
