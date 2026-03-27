# Performance module migration

Status: Completed

## Scope

- `Modules/Performance/Resources/views/**`

## Migrated in this wave

- Dashboard, objectives, key results, and check-ins AJAX flows.
- Meetings module flows: create/edit/show/list views, agenda/actions/discussion partials, and status updates.
- Performance settings pages and modal submits.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete`
- `postUrlEncoded` with method spoofing retained for specific routes requiring `_method=PUT/DELETE` plus extra payload

## Notes

- For recurring meeting delete flow, `_method=DELETE` with extra fields is preserved via `postUrlEncoded` to keep existing behavior.
- Existing block/unblock containers and refresh logic are preserved.

## Remaining easyAjax in module

- None in `Modules/Performance/Resources/views/**`.
