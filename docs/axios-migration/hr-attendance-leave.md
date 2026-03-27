# HR / attendance / leave — easyAjax → `window.apiHttp`

**Status:** Completed (2026-03-27).

## Scope

| Area              | Path                                   | Notes                                                                                                                                                           |
| ----------------- | -------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Employees         | `resources/views/employees/**`         | Profile tabs, ajax partials (tasks, projects, tickets, documents, timelogs, leaves, permissions, immigration, modals), index CRUD, category/subcategory modals. |
| Attendances       | `resources/views/attendances/**`       | (Prior wave)                                                                                                                                                    |
| Timelogs          | `resources/views/timelogs/**`          | Index, by-employee, ajax create/edit/timer/active timer, reject modal.                                                                                          |
| Leaves            | `resources/views/leaves/**`            | Calendar, approve/reject, ajax flows; `index.blade.php` quick action migrated in this wave.                                                                     |
| Weekly timesheets | `resources/views/weekly-timesheets/**` | (Prior wave)                                                                                                                                                    |

## Verification

```bash
rg '\$\.easyAjax\(' resources/views/employees resources/views/attendances resources/views/timelogs resources/views/leaves resources/views/weekly-timesheets --glob '*.blade.php'
```

Expected: no matches.

## Patterns used

- `apiHttp.get` + `historyPush` for employee/timelog tab loads (same pattern as `project-templates/show`).
- `apiHttp.postUrlEncoded` for filters, quick actions, timers, approve/revert, assign role, serialized forms without files.
- `apiHttp.postForm` for employee create/update, passport/visa, import, document uploads, timelog create/edit.
- `apiHttp.delete` for standard destroys; leave destroy with extra fields uses `postUrlEncoded` with `_method: DELETE`.
- Conditional `$.easyBlockUI` when `loading === true` (employee attendance `showTable`).
- Document expiry create/update: `catch` preserves `422` + `$.showErrors(err.errors)`.
