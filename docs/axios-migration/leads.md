# Leads area migration

Status: Completed

## Scope

- `resources/views/leads/**`

## Migrated in this wave

- Lead list, board, filters, and show-page AJAX actions.
- Lead create/edit/import flows.
- Lead notes, follow-up, files, and GDPR consent flows.
- Lead form settings updates and status/stage related actions.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` file upload / `FormData` -> `window.apiHttp.postForm`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete` (or `_method` patterns where endpoint expects method spoofing)

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in area

- None in `resources/views/leads/**`.
