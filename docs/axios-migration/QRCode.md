# Module: QRCode

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Features

| Feature                                     | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------- | -------------- | ----------------- | ------ |
| List + delete row (DataTable)               | Yes            | Yes               | Done   |
| Create modal: load fields by type (GET)     | Yes            | Yes               | Done   |
| Create modal: preview QR (POST multipart)   | Yes            | Yes               | Done   |
| Create modal: save (POST multipart)         | Yes            | Yes               | Done   |
| Edit modal: load fields by type (GET)       | Yes            | Yes               | Done   |
| Edit modal: preview / save (POST multipart) | Yes            | Yes               | Done   |

**Note:** `$.ajaxModal` for lightbox show is unchanged (not `easyAjax`).

## UI → API → Controller → DB

| UI                          | Method                | Route name       | Controller                 | DB                            |
| --------------------------- | --------------------- | ---------------- | -------------------------- | ----------------------------- |
| Delete row                  | POST+`_method=DELETE` | `qrcode.destroy` | `QRCodeController@destroy` | `qr_code_data`                |
| Change type → reload fields | GET                   | `qrcode.fields`  | `QRCodeController@fields`  | N/A (view only)               |
| Generate preview            | POST (multipart)      | `qrcode.preview` | `QRCodeController@preview` | N/A                           |
| Save create / update        | POST (multipart)      | `qrcode.store`   | `QRCodeController@store`   | `qr_code_data` (+ logo files) |

All routes are under middleware `auth`, prefix `account` (see `Modules/QRCode/Routes/web.php`).

## API mapping (representative)

- `GET /account/qrcode/fields/{type}` — `Reply::dataOnly` with HTML fragment `view`
- `POST /account/qrcode/preview` — `QrPreview` request; returns `qr` data URI
- `POST /account/qrcode` — `QrPreview` / store; returns `qr`, `id` (Reply success + data)
- `POST /account/qrcode/{id}` with `_method=DELETE` — delete; Reply success message

## Notes

- **Validation**: Server uses `QrPreview` Form Request; errors may be Laravel `Reply::formErrors` or 422 JSON. Callers currently show a **toast** on failure; inline field errors like `easyAjax` + `handleFail` can be added later via `err.errors` + a small helper.
- **Files**: `postForm` uses `FormData` from the form DOM node (equivalent to `easyAjax` `file: true` + container form).
- **Edge case**: Edit save uses the same `qrcode.store` route as create, with hidden `qrId` — unchanged server contract.

## Changes log

- **2026-03-25** — `resources/js/http/apiClient.js` (new), `resources/js/main.js` (require client), `Modules/QRCode/Resources/views/qrcode/index.blade.php`, `qrcode/ajax/create.blade.php`, `qrcode/ajax/edit.blade.php`: replace all `$.easyAjax` with `window.apiHttp`; keep block UI helpers; add Swal toast on errors.
