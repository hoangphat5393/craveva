# Module: Warehouse (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed — AJAX create flows use `window.apiHttp`

## Scope

`Modules/Warehouse/**` — routes, controllers, Blade assets as used by the module.

## Features

| Feature                                                      | easyAjax Found | Migrated to Axios                 | Status    |
| ------------------------------------------------------------ | -------------- | --------------------------------- | --------- |
| Warehouse create (`ajax/create.blade.php`)                   | Was            | Yes (`postUrlEncoded` + block UI) | Done      |
| Stock adjustment create (`stock/ajax/create.blade.php`)      | Was            | Yes                               | Done      |
| Stock transfer create (`transfer/ajax/create.blade.php`)     | Was            | Yes                               | Done      |
| Other views (index, full page forms, delete via form submit) | No             | N/A                               | Unchanged |

## API mapping

| Pattern             | Helper                                                |
| ------------------- | ----------------------------------------------------- |
| POST form (no file) | `apiHttp.postUrlEncoded(url, $('#form').serialize())` |
| Errors              | `catch` → `$.handleApiFormError(err)`                 |
| Block UI            | `$.easyBlockUI` / `$.easyUnblockUI` on form container |

Traditional HTML forms and `document.getElementById('delete-warehouse-' + id).submit()` for delete remain as before.

## Notes

- `window.apiHttp` from `public/js/main.js` (auth layouts).

## Changes log

- 2026-03-25 — Initial scan reported no `easyAjax`; scan missed inline scripts in ajax partials.
- 2026-03-27 — Migrated `$.easyAjax` → `apiHttp` in three create partials (`ajax/create`, `stock/ajax/create`, `transfer/ajax/create`).
