# Finance core — easyAjax → `window.apiHttp`

**Status:** Completed (2026-03-27).

## Scope

| Area         | Path                              | Notes                                                                   |
| ------------ | --------------------------------- | ----------------------------------------------------------------------- |
| Invoices     | `resources/views/invoices/**`     | No `$.easyAjax` remaining (already migrated earlier).                   |
| Estimates    | `resources/views/estimates/**`    | Index + ajax create/edit/show migrated.                                 |
| Proposals    | `resources/views/proposals/**`    | Index + ajax create/edit/show migrated.                                 |
| Credit notes | `resources/views/credit-notes/**` | Index, edit, convert, ajax partials, file upload.                       |
| Expenses     | `resources/views/expenses/**`     | Index, ajax create/edit/show/import, category create, `edit.blade.php`. |

## Verification

```bash
rg '\$\.easyAjax\(' resources/views/invoices resources/views/estimates resources/views/proposals resources/views/credit-notes resources/views/expenses --glob '*.blade.php'
```

Expected: no matches.

## Patterns used

- `apiHttp.get(url, { params })` for GET with query data.
- `apiHttp.postUrlEncoded` for form `serialize()` and flat POST bodies.
- `apiHttp.postForm` for multipart (files): expense create/update/import, estimate/proposal saves with uploads, credit note file upload, estimate accept signature.
- `apiHttp.post` for JSON bodies (e.g. `discount.calculate`, `creditnotes.apply_invoice_credit`).
- `apiHttp.delete(url, token)` for Laravel DELETE from script context.
- `$.easyBlockUI` / `$.easyUnblockUI` + `.catch` → Swal toast for errors, aligned with `resources/views/invoices/**` migrations.
