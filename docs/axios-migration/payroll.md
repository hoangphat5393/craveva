# Area: Payroll module (waves 1-3)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope done so far

`Modules/Payroll/Resources/views/**` — all subtrees fully migrated:

- `index.blade.php`, `payroll/index.blade.php`
- `payroll-setting/index.blade.php` + all modals + `ajax/**`
- `payroll-report/index.blade.php` + `ajax/employee-tds.blade.php`
- `payroll/ajax/status-modal.blade.php`, `show-modal.blade.php`, `edit-modal.blade.php`
- `overtime-setting/**`
- `overtime-request/**`
- `employee-salary/**`
- `payroll-expenses/**`

## Features migrated

| Feature                                                                                     | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| Payroll main list: generate payslip, regenerate, update status, delete, get-cycle-data      | Yes            | Yes               | Done   |
| Payroll settings: tab ajax + delete payment method/salary component/salary group/salary TDS | Yes            | Yes               | Done   |
| Payroll settings: all create/edit modals (component, payment method, group, TDS, status)    | Yes            | Yes               | Done   |
| Payroll settings: manage-employee-modal, employee-hourly-rate, currency, salary-setting     | Yes            | Yes               | Done   |
| Payroll report: tab ajax switch + employee TDS fetch                                        | Yes            | Yes               | Done   |
| Payroll show/edit/status modals: delete payroll, edit payroll, get expense title            | Yes            | Yes               | Done   |
| Overtime settings: index + modals + policy employee partial                                 | Yes            | Yes               | Done   |
| Overtime request: index + create/edit/show                                                  | Yes            | Yes               | Done   |
| Employee salary: index + create/edit/increment/show + salary-update-component               | Yes            | Yes               | Done   |
| Payroll expenses: index + show + overview partial                                           | Yes            | Yes               | Done   |

## Remaining scope

None — `rg '$.easyAjax(' Modules/Payroll/Resources/views` returns 0 matches.

## Changes log

- 2026-03-27 — Migrated Payroll wave 1 (`index`, `payroll/index`, `payroll-setting/index`, `payroll-report/index`) from `$.easyAjax` to `window.apiHttp`, preserving existing UI flows.
- 2026-03-27 — Wave 2 continued on list/index screens: `overtime-setting/index`, `overtime-request/index`, `employee-salary/index`, `payroll-expenses/index`.
- 2026-03-27 — Wave 2 completed for overtime/employee-salary/payroll-expenses subtrees (including ajax/modal/show partials).
- 2026-03-27 — Wave 3 (final): migrated remaining 17 files in `payroll-setting/**` (modals + ajax partials), `payroll/ajax/**` (status/show/edit modals), and `payroll-report/ajax/employee-tds`. Module fully completed.
