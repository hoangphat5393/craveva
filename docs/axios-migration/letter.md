# Letter module migration

Status: Completed

## Scope

- `Modules/Letter/Resources/views/**`

## Migrated in this wave

- Letter list actions: quick action changes and row delete flows.
- Letter create/edit modal submit flows.
- Template list actions: quick action changes and row delete flows.
- Template create/edit modal submit flows.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete`

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in module

- None in `Modules/Letter/Resources/views/**`.
