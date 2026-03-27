# Area: Recruit module (waves 1-3)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope done so far

`Modules/Recruit/Resources/views/recruit-setting/index.blade.php`  
`Modules/Recruit/Resources/views/report/index.blade.php`  
`Modules/Recruit/Resources/views/jobs/index.blade.php`  
`Modules/Recruit/Resources/views/job-applications/**`  
`Modules/Recruit/Resources/views/interview-schedule/**`  
`Modules/Recruit/Resources/views/front/**`  
`Modules/Recruit/Resources/views/jobs/**`  
`Modules/Recruit/Resources/views/recruit-setting/ajax/**`  
`Modules/Recruit/Resources/views/recruit-setting/**/*.blade.php`  
`Modules/Recruit/Resources/views/skills/**`  
`Modules/Recruit/Resources/views/candidate-database/**`

## Features migrated so far

| Feature                                                                      | easyAjax Found | Migrated to Axios | Status |
| ---------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| Recruit settings: tab ajax switch                                            | Yes            | Yes               | Done   |
| Recruit report dashboard: chart/stat refresh                                 | Yes            | Yes               | Done   |
| Jobs index: quick action, delete, change status                              | Yes            | Yes               | Done   |
| Job application board: load board, load more, delete/collapse column         | Yes            | Yes               | Done   |
| Interview schedule (index/show/table/ajax/modals/evaluation/reschedule)      | Yes            | Yes               | Done   |
| Front recruit pages (job opening/apply/alert)                                | Yes            | Yes               | Done   |
| Job applications full subtree (table/board/create-edit/show + ajax/partials) | Yes            | Yes               | Done   |
| Jobs full subtree (show/profile/candidate/interview/offer-letter + modals)   | Yes            | Yes               | Done   |
| Recruit setting subtree (ajax + create/edit modals)                          | Yes            | Yes               | Done   |
| Skills subtree                                                               | Yes            | Yes               | Done   |
| Candidate database subtree                                                   | Yes            | Yes               | Done   |

## Remaining scope

- None in `Modules/Recruit/Resources/views/**` (static scan: no `$.easyAjax(` matches).

## Changes log

- 2026-03-27 — Migrated Recruit wave 1 (settings/report/jobs index/job-app board) from `$.easyAjax` to `window.apiHttp` with existing UX behavior retained.
- 2026-03-27 — Wave 2 continued: completed `interview-schedule/**`, `front/**`, and partial `job-applications/**` (source/remark/skill/import/note edit).
- 2026-03-27 — Wave 2 completed for `job-applications/**` (including table/board and all related ajax/modal flows).
- 2026-03-27 — Wave 3 completed: migrated remaining Recruit views (`jobs/**`, `recruit-setting/**`, `skills/**`, `candidate-database/**`), bringing Recruit module views to zero `$.easyAjax`.
