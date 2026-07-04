# Full easyAjax -> axios migration plan

**Created:** 2026-06-23  
**Scope:** migrate remaining `$.easyAjax(` in `resources/views/**` to `window.apiHttp`.

## Current Baseline

Latest verified scan after the installer environment fetch batch:

| Pattern | Result |
| ------- | ------ |
| `$.easyAjax(` | 0 matches |
| `$.ajax(` / `jQuery.ajax(` | 32 matches |
| `window.apiHttp` / `apiHttp.` | 1065 files |
| `Modules/**` using `$.easyAjax(` | 0 files |

Important: the installer environment view was converted with standalone browser `fetch` because installer pages do not load `window.apiHttp`. Direct `$.ajax` is tracked separately and remains the next migration backlog.

## Migration Rules

Use existing project patterns. Do not do a broad mechanical rewrite.

| Legacy pattern | Axios replacement |
| -------------- | ----------------- |
| form `$('#form').serialize()` | `window.apiHttp.postUrlEncoded(url, $('#form').serialize())` |
| object POST payload | `window.apiHttp.postUrlEncoded(url, { ... })` |
| multipart real file upload | `window.apiHttp.postForm(url, formElementOrFormData)` |
| GET with data | `window.apiHttp.get(url, { params: { ... } })` |
| DELETE via `_method=DELETE` | `window.apiHttp.delete(url, token)` |
| Laravel validation/display errors | `.catch(function(err) { $.handleApiFormError(err); })` |
| `blockUI: true` | `$.easyBlockUI(selector)` + `.finally(... $.easyUnblockUI(selector))` |
| `disableButton: true` | disable before request, re-enable in `.finally(...)` |

Before changing public/auth pages, verify the page loads:

- `public/js/main.js`
- `vendor/helper/helper.js`
- CSRF token or route-specific `_token`

## Wave Tracker

Status values:

- `Pending`: not started.
- `In progress`: active batch.
- `Converted`: code changed and static scan for that scope is clean.
- `Browser tested`: converted and tested with browser.
- `Blocked`: needs clarification or deeper fix.

| Wave | Area | Current count | Status | Notes |
| ---- | ---- | ------------- | ------ | ----- |
| 0 | Estimate/request/template + Production FG policy | 0 | Browser tested | Completed 2026-06-23. Validation tested for estimate request/template; Production FG policy save tested. |
| 1A | `resources/views/ticket-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean, PHP lint clean, ticket settings route/tab page load tested. Modal direct render tested; interactive modal submit was limited by browser click timeout. |
| 1B | `resources/views/lead-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean, PHP lint clean, lead settings route load tested. |
| 1C | `resources/views/invoice-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean and PHP lint clean. Browser smoke test is blocked by local web runtime missing `APP_KEY` after login; CLI config has `app.key`, so this appears to be web server/env cache drift rather than the invoice-settings change. |
| 1D | `resources/views/app-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean and PHP lint clean. Browser smoke test is still blocked by local web runtime missing `APP_KEY` after login. |
| 1E | `resources/views/attendance-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean and PHP lint clean. Browser smoke test is still blocked by local web runtime missing `APP_KEY` after login. |
| 1F | `resources/views/project-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean and PHP lint clean. Browser smoke test is still blocked by local web runtime missing `APP_KEY` after login. |
| 1G | `resources/views/profile-settings/**` | 0 files | Converted | Completed 2026-06-23. Static scan clean and PHP lint clean. Browser smoke test is still blocked by local web runtime missing `APP_KEY` after login. |
| 1H | Settings batch: contract, Craveva AI, message, module, social login | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. |
| 1I | Settings batch: company, log time, Google Calendar, theme, currency | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. |
| 1J | Small CRUD batch: company address, custom fields | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. |
| 1K | Settings batch: sign-up settings, task settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. |
| 1L | Settings batch: security settings, notification settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Browser smoke test blocked because `craveva-staging.test:443` refused connection. |
| 2A | `resources/views/contracts/**` | 0 files | Browser tested | Completed 2026-06-23. Static scan clean, PHP lint clean, contracts index/create page load tested, create validation tested with empty form. |
| 2B | `resources/views/lead-contact/**` | 0 files | Browser tested | Completed 2026-06-23. Static scan clean, PHP lint clean, lead contact index/create page load tested, create validation tested with empty form. |
| 2C | `resources/views/contract-template/**` | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Browser smoke test blocked because `craveva-staging.test:443` refused connection. |
| 2D | `resources/views/bank-account/**` | 5 files | Pending | Finance-impacting; avoid real destructive actions. |
| 2E | `resources/views/discussions/**` | 5 files | Pending | Modal/comment behavior. |
| 2F | `resources/views/appreciations/**` | 5 files | Pending | CRUD/modals. |
| 2G | `resources/views/recurring-invoices/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URL: `/account/recurring-invoices`. |
| 2H | `resources/views/recurring-expenses/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URL: `/account/recurring-expenses`. |
| 3A | `resources/views/auth/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URLs: login/register/password reset/invitation/2FA/email verification pages. |
| 3B | `resources/views/front/**` | 0 files | Converted | Completed 2026-06-25 for `front/tasks/**`. Static scan clean and PHP lint clean for changed files. Function URL patterns: public/front task list, calendar, task detail modal, task category/label/comment/note/subtask modals. |
| 3C | `resources/views/public-payment/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URL patterns: public invoice payment modals for Stripe/Paystack/Mollie/Flutterwave/Authorize. Real payment side effects not tested. |
| 3D | `resources/views/public-gdpr/**` | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: `/consent/l/{hash}`. |
| 3E | `resources/views/sign-up-settings/**` | 0 files | Converted | Completed 2026-06-24 as part of settings batch 1K. Static scan clean and PHP lint clean. |
| 4A | `resources/views/dashboard/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URL: `/account/dashboard`. |
| 4B | `resources/views/reports/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URLs: report pages under `/account/reports/*`. |
| 4C | `resources/views/task-settings/**` | 0 files | Converted | Completed 2026-06-24 as part of settings batch 1K. Static scan clean and PHP lint clean. |
| 4D | `resources/views/taskboard/**` + `taskboard.blade.php` | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/taskboards` and public taskboard route. |
| 4E | `resources/views/holiday/**` | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/holidays`. |
| 4F | `resources/views/leave-settings/**` | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Browser smoke test blocked because `craveva-staging.test:443` refused connection. |
| 4G | `resources/views/gdpr/**` + `gdpr-settings/**` | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/gdpr` and `/account/settings/gdpr`. |
| 5A | Small CRUD batch: tax settings, departments, designation | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/settings/taxes`, `/account/departments`, `/account/designations`. |
| 5B | Small CRUD batch: awards | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/appreciations/awards`. |
| 5C | Small CRUD batch: custom link settings, sticky notes | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/settings/custom-link-settings`, `/account/sticky-notes`. |
| 5D | Small modal/settings batch: employee shifts, unit type, timelog break, tax modal | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/settings/employee-shifts`, `/account/settings/unit-type`, `/account/timelogs/timelog-break`, `/account/settings/taxes`. |
| 5E | Small utility/HR batch: search, my calendar, department hierarchy, shift change | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/search`, `/account/my-calendar`, `/account/departments/department-hierarchy`, `/account/shifts/shifts-change`. |
| 5F | Settings batch: language settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/settings/language-settings`. |
| 5G | Small CRUD batch: appreciations, notices | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: `/account/appreciations`, `/account/notices`. |
| 5H | Permission settings batch: role permissions | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/settings/role-permissions`. |
| 5I | Small content batch: knowledge base | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/knowledgebase`. |
| 5J | Small collaboration batch: discussions | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/discussion`. |
| 5K | HR CRUD batch: holidays | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/holidays`. |
| 5L | HR schedule batch: shift rosters | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/shifts`. |
| 5M | Public form batch: lead and ticket forms | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL patterns: `/lead-form/{id}`, `/ticket-form/{id}`. |
| 5N | Public GDPR consent + commented task samples | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: `/consent/l/{hash}`. |
| 5O | Messaging batch: conversations and attachments | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/messages`. |
| 5P | Public contract signing | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: `/contract/{hash}`. |
| 5Q | Public proposal accept/decline | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: `/proposal/{hash}`. |
| 5R | Project Gantt view | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: project Gantt public/signed route. |
| 5S | Custom modules management | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/custom-modules`. |
| 5T | Storage settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/settings/storage-settings`. |
| 5U | Payment gateway settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/settings/payment-gateway-settings`. |
| 5V | Database backup settings | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/settings/database-backup-settings`. |
| 5W | Super-admin signup forms | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL pattern: signup public route. |
| 5X | Recurring events | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/recurring-event`. |
| 5Y | Bank accounts and transactions | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/bankaccounts`. |
| 5Z | Shared sections: topbar, sidebar, timer, shipping modal | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/dashboard` plus invoice shipping-address modal entry points. |
| 5AA | Shared components: auth language switcher and setting sidebars | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URLs: login page and settings sidebars. |
| 5AB | Public Gantt and taskboard shell views | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL patterns: public/signed Gantt and project taskboard routes. |
| 5AC | Proposal templates | 0 files | Converted | Completed 2026-06-24. Static scan clean and PHP lint clean for changed files. Function URL: `/account/proposal-template`. |
| 5AD | Global layout actions: notifications, timer pause/resume, OneSignal, message poll | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed file. Function URL: any authenticated app page such as `/account/dashboard`. |
| 5AE | Public invoice payment wrapper | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed file. Function URL pattern: public invoice payment route. |
| 5AF | Recurring tasks | 0 files | Converted | Completed 2026-06-25. Static scan clean and PHP lint clean for changed files. Function URL: `/account/recurring-task`. |
| 5AG | Installer environment view | 0 files | Converted | Completed 2026-06-27 after explicit approval. Uses standalone browser `fetch` because installer pages do not load `window.apiHttp`. Function URL: `/install/environment`. |
| 6 | Direct `$.ajax` / `jQuery.ajax` | 32 matches | Pending | Do only after `$.easyAjax` reaches zero. |

## Manual Test URLs

Use these URLs to manually verify converted `$.easyAjax` -> `window.apiHttp` scopes after the local web server is available.

| Scope | URL |
| ----- | --- |
| Tax settings | `/account/settings/taxes` |
| Departments | `/account/departments` |
| Designations | `/account/designations` |
| Awards | `/account/appreciations/awards` |
| Custom link settings | `/account/settings/custom-link-settings` |
| Sticky notes | `/account/sticky-notes` |
| Employee shifts | `/account/settings/employee-shifts` |
| Unit type | `/account/settings/unit-type` |
| Timelog break | `/account/timelogs/timelog-break` |
| Search | `/account/search` |
| My calendar | `/account/my-calendar` |
| Department hierarchy | `/account/departments/department-hierarchy` |
| Shift change requests | `/account/shifts/shifts-change` |
| Language settings | `/account/settings/language-settings` |
| Appreciations | `/account/appreciations` |
| Notices | `/account/notices` |
| Role permissions | `/account/settings/role-permissions` |
| Knowledge base | `/account/knowledgebase` |
| Discussions | `/account/discussion` |
| Holidays | `/account/holidays` |
| Shift rosters | `/account/shifts` |
| Lead public form | `/lead-form/{id}` |
| Ticket public form | `/ticket-form/{id}` |
| Public GDPR consent | `/consent/l/{hash}` |
| Messages | `/account/messages` |
| Public contract sign | `/contract/{hash}` |
| Public proposal action | `/proposal/{hash}` |
| Project Gantt | project Gantt public/signed route |
| Custom modules | `/account/custom-modules` |
| Storage settings | `/account/settings/storage-settings` |
| Payment gateway settings | `/account/settings/payment-gateway-settings` |
| Database backup settings | `/account/settings/database-backup-settings` |
| Super-admin signup | signup public route |
| Recurring events | `/account/recurring-event` |
| Bank accounts | `/account/bankaccounts` |
| Shared topbar/sidebar/timer | `/account/dashboard` |
| Shipping address modal | Invoice form pages that open the add shipping address modal |
| Auth language switcher | login page |
| Setting sidebars | any account settings page, plus super-admin settings pages |
| Public Gantt shell | public/signed Gantt route |
| Public taskboard shell | public taskboard route |
| Taskboard | `/account/taskboards` |
| Proposal templates | `/account/proposal-template` |
| Global layout actions | `/account/dashboard` |
| Auth pages | login/register/password reset/invitation/2FA/email verification pages |
| Front tasks | public/front task list, calendar, task detail, category/label/comment/note/subtask modals |
| Dashboard | `/account/dashboard` |
| Reports | report pages under `/account/reports/*` |
| GDPR user/settings pages | `/account/gdpr`, `/account/settings/gdpr` |
| Public invoice payment | public invoice payment route and gateway modals |
| Public payment modals | public invoice Stripe/Paystack/Mollie/Flutterwave/Authorize payment routes |
| Recurring invoices | `/account/recurring-invoices` |
| Recurring expenses | `/account/recurring-expenses` |
| Recurring tasks | `/account/recurring-task` |
| Installer environment | `/install/environment` |

## Verification Checklist Per Wave

1. Run pre-scan for scope:

```powershell
rg -n '\$\.easyAjax\s*\(' <scope> -g '*.blade.php' -g '*.js'
```

2. Convert only that scope.
3. Run PHP syntax checks on changed Blade files:

```powershell
php -l path\to\changed.blade.php
```

4. Run diff whitespace check:

```powershell
git diff --check -- <changed-paths>
```

5. Browser smoke test:

- login with test account when needed
- load index page
- open create/edit modal or page
- submit empty form to confirm validation behavior
- check console errors
- avoid destructive actions unless explicitly approved

6. Run post-scan:

```powershell
rg -n '\$\.easyAjax\s*\(' resources\views Modules -g '*.blade.php' -g '*.js'
```

7. Update:

- this file's wave status
- `docs/axios-migration/README.md`
- `docs/axios-migration/AJAX_AUDIT.md`

## Remaining Hotspots Snapshot

Use this snapshot only as the starting point. Refresh it after every wave.

| Area | Files |
| ---- | ----- |
| No `$.easyAjax` hotspots remain | 0 |

## Direct AJAX Backlog

Current direct `$.ajax` / `jQuery.ajax` groups:

| Area | Matches |
| ---- | ------- |
| `Modules/ServerManager/**` | 5 |
| `Modules/Biolinks/**` | 3 |
| `Modules/Onboarding/**` | 2 |
| `resources/views/super-admin/**` | 2 |
| `resources/views/timelogs/**` | 2 |
| `Modules/Biometric/**` | 1 |
| `resources/views/designations-hierarchy/**` | 1 |
| `resources/views/attendance-settings/**` | 1 |
| `resources/views/departments-hierarchy/**` | 1 |
| `resources/views/layouts/**` | 1 |
| `resources/views/invoices/**` | 1 |
| `resources/views/employees/**` | 1 |
