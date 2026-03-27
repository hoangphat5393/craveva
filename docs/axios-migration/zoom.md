# Zoom module migration

Status: Completed

## Scope

- `Modules/Zoom/Resources/views/**`

## Migrated in this wave

- Meeting flows: create, edit, edit occurrence, show tabs, notes create/edit.
- Meeting list/calendar actions: status updates, join/end, and delete patterns.
- Notification settings: zoom, slack, and email setting save actions.
- Category create modal action.
- Index-level tab or async content loads using GET.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete` or `postUrlEncoded` with `_method=DELETE` when extra payload is required

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in module

- None in `Modules/Zoom/Resources/views/**`.
