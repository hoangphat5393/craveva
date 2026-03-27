# Event calendar area migration

Status: Completed

## Scope

- `resources/views/event-calendar/**`

## Notes

- Migrated `$.easyAjax` to `window.apiHttp` with the same success paths (reload, modal HTML, Swal, `historyPush` where applicable).
- `postForm` was not required in this wave (no multipart-only flows in the migrated calls).

## Remaining easyAjax in area

- None in `resources/views/event-calendar/**`.
