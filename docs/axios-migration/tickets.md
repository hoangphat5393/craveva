# Tickets area migration

Status: Completed

## Scope

- `resources/views/tickets/**`

## Migrated in this wave

- Ticket index and detail page AJAX actions.
- Ticket form settings actions.
- Ticket create/edit modal and inline detail update flows.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` file upload / `FormData` -> `window.apiHttp.postForm`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete`

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in area

- None in `resources/views/tickets/**`.
