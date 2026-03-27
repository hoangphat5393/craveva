# Biolinks module migration

Status: Completed

## Scope

- `Modules/Biolinks/Resources/views/**`

## Migrated in this wave

- Biolink blocks create/edit flows across block types (link, heading, paragraph, socials, embeds, media platforms, collectors).
- Biolink list and edit pages (quick actions, delete, settings, and slug update).
- Block table/action partials and related modal submissions.

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` with file upload (`FormData`) -> `window.apiHttp.postForm`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete` (or `_method` route pattern when required)

## Notes

- `postForm` is intentionally kept where image/avatar/favicon uploads are present.
- A duplicate-block action keeps `_method=GET` via `postUrlEncoded` to match existing backend route expectation.

## Remaining easyAjax in module

- None in `Modules/Biolinks/Resources/views/**`.
