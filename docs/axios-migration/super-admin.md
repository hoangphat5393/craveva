# Super-admin area migration

Status: Completed

## Scope

- `resources/views/super-admin/**`

## Notes

- Migrated `$.easyAjax` to `window.apiHttp` while preserving block/unblock targets, redirects, Swal, and JSON `Reply` handling.
- `window.apiHttp.postForm` is used only where the legacy call used `file: true` / multipart (logos, theme assets, company images, profile avatar, offline payment slip, SEO image, custom fields with upload, etc.).
- Deletes use `window.apiHttp.delete` unless the route required extra body fields; then `postUrlEncoded` with `_method` is kept to match Laravel expectations.

## Remaining easyAjax in area

- None in `resources/views/super-admin/**`.
