# Onboarding module migration

Status: Completed

## Scope

- `Modules/Onboarding/Resources/views/**`

## Migrated in this wave

- Onboarding/offboarding settings: create, edit, list quick actions, and notification settings.
- Dashboard create modal with file upload flow.
- Employee onboarding/offboarding component actions.
- Start-onboarding component request actions.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` with file upload (`file: true`, `FormData`) -> `window.apiHttp.postForm`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete`

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in module

- None in `Modules/Onboarding/Resources/views/**`.
