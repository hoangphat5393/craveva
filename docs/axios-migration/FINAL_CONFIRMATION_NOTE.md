# Final confirmation note - easyAjax to axios

Date: 2026-03-27

## Current decision

- Keep migrating all Blade `$.easyAjax` calls to `window.apiHttp`.
- Keep `public/vendor/helper/helper.js` untouched during migration waves to avoid big-bang risk.
- Preserve UX/UI behavior per page (block UI, toasts, redirects, datatable refresh, modal lifecycle).

## Is full migration better for the system?

Short answer: **Yes, if done with strict compatibility checks**.

Main benefits:

- One HTTP client (`window.apiHttp`) gives consistent CSRF, error normalization, and request behavior.
- Easier debugging and maintenance than mixed jQuery helper patterns.
- Cleaner long-term frontend direction for new features.
- Lower coupling to legacy helper internals.

Main risks (manageable):

- Behavior drift if a page relied on subtle `easyAjax` side effects.
- Missed edge cases in pages with custom callbacks.

Risk control used:

- Migrate in waves.
- Keep old UX callbacks and blockUI targets unchanged.
- Run build + quick smoke checks each wave.

## Upcoming migration groups (next waves)

The remaining migration scope is mainly in `resources/views/**` (core app).  
Planned rollout groups (prioritized):

1. **Finance core** — **done** (see [finance-core.md](./finance-core.md))
    - `resources/views/invoices/**`
    - `resources/views/estimates/**`, `resources/views/proposals/**`
    - `resources/views/credit-notes/**`, `resources/views/expenses/**`

2. **HR / attendance / leave** — **done** (see [hr-attendance-leave.md](./hr-attendance-leave.md))
    - `resources/views/employees/**`
    - `resources/views/attendances/**`, `resources/views/timelogs/**`
    - `resources/views/leaves/**`, `resources/views/weekly-timesheets/**`

3. **Payroll** (`Modules/Payroll/Resources/views/**`) — **done** (see [payroll.md](./payroll.md)); `rg '$.easyAjax('` on the module returns 0 matches.

4. **Recruit** (`Modules/Recruit/Resources/views/**`) — **done** (see [recruit.md](./recruit.md)).

5. **System settings**
    - `resources/views/invoice-settings/**`, `resources/views/notification-settings/**`
    - `resources/views/security-settings/**`, `resources/views/app-settings/**`
    - `resources/views/leave-settings/**`, `resources/views/project-settings/**`

6. **Customer/public/auth flows**
    - `resources/views/auth/**`
    - `resources/views/public-payment/**`, `resources/views/public-gdpr/**`
    - `resources/views/front/**`

7. **Residual shared views**
    - `resources/views/layouts/**`, `resources/views/sections/**`, and remaining cross-feature partials.

Execution policy for each wave:

- Migrate 1-2 functional groups per wave (or smaller subgroups if high risk).
- Keep HTML/CSS and UI interaction unchanged; replace transport layer only.
- Run `pnpm run production` and smoke test key create/edit/delete flows before closing each wave.

## For new nwidart modules later, will easyAjax come back?

- It will **not** come back **if team convention is enforced**.
- By default, many old snippets may still copy `easyAjax` unless we set a guard.

Required rule for new modules:

- In `Modules/*/Resources/views/**`, use only `window.apiHttp` (`get`, `postUrlEncoded`, `postForm`, `delete`).
- Do not add new `$.easyAjax` calls.

Recommended guardrails:

- Add PR checklist item: "No new `$.easyAjax` in changed files".
- Add CI/pre-commit grep check to fail if `$.easyAjax(` appears in new diffs.
- Keep this migration tracker updated per module.

## Final recommendation

- Continue migration until `resources/views/**` is clean.
- After that, optionally deprecate/remove `easyAjax` helper in a dedicated hardening phase.
- After migration is complete, consider a **follow-up hardening phase** to shorten repeated `apiHttp` + block UI + Swal patterns (toast helper, `withBlockUI` wrapper, moving JS out of Blade). See **After full migration: shorter, DRY code** in [README.md](./README.md).
