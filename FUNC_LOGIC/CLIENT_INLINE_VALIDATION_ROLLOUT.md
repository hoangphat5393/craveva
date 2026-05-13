# Client Inline Validation Rollout

## Muc tieu

Dong bo co che hien thi validation error theo field (inline) cho cac form thuoc module Client khi dung `window.apiHttp`.

## Nguyen nhan goc

- Form cu dung `$.easyAjax` se auto render `errors` vao field.
- Nhieu form moi da chuyen sang `window.apiHttp` + `.catch(...)` nhung chi `Swal(err.message)`.
- Khi backend tra validation theo dang `status: fail` + `errors`, UI chi hien toast `"The given data was invalid."` va khong danh dau field.

## Rule xu ly duoc ap dung

Da tao helper global trong `public/vendor/helper/helper.js`:

- `$.extractApiValidationErrors(err)`
- `$.handleApiFormError(err, options)`
- **Tenant:** `$.showErrors` được ghi đè bởi `resources/js/showErrorsLaravel.js` (qua `main.js`) — flatten mảng Laravel, host `.input-group` / cột grid, **Swal** cho lỗi không map được field (xem `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md` §12.2.1).

Trong `.catch(function(err) { ... })` chi can:

```js
$.handleApiFormError(err);
```

Helper se:

1. Tu dong lay validation errors tu nhieu schema:
    - `err.errors`
    - `err.payload.errors`
    - `err.response.data.errors`
2. Normalize message va goi `$.showErrors(...)` de hien inline.
3. Neu khong phai validation thi fallback toast `Swal`.

## Files da cap nhat (Phase 1 - Client Core)

- `resources/views/clients/ajax/create.blade.php`
- `resources/views/clients/ajax/edit.blade.php`
- `resources/views/clients/ajax/quick_create.blade.php`
- `resources/views/clients/contacts/create.blade.php`
- `resources/views/clients/contacts/edit.blade.php`
- `resources/views/clients/notes/create.blade.php`
- `resources/views/clients/notes/edit.blade.php`
- `resources/views/clients/notes/verify-password.blade.php`
- `resources/views/clients/files/edit.blade.php`
- `resources/views/clients/ajax/import.blade.php`
- `resources/views/clients/create_category.blade.php`
- `resources/views/clients/create-subcategory.blade.php`
- `resources/views/clients/ajax/profile.blade.php`

## Backend validation bo sung da ap dung

- `app/Http/Requests/Admin/Client/StoreClientRequest.php`
- `app/Http/Requests/Admin/Client/UpdateClientRequest.php`

Cap nhat:

- `name` bat buoc va message ro rang.
- `email` bat buoc khi `login` = `enable` (ho tro ca gia tri `yes`), message ro rang:
    - `Email is required when client login is enabled.`

## Cach test nhanh

1. Vao `Clients > Add Client`.
2. De trong `Client Name`, bam Save -> phai hien loi ngay duoi field.
3. Bat `Client Can Login = Yes`, de trong `Email`, bam Save -> phai hien loi ngay duoi field `Email`.
4. Kiem tra form Edit Client va Contact create/edit tuong tu.

## Ghi chu rollout staging/hub

1. Deploy code.
2. `php artisan optimize:clear`
3. Hard refresh trinh duyet (Ctrl+F5).
4. Re-test theo checklist tren.

## Files da cap nhat (Phase 2 - Client List/Action)

- `resources/views/clients/ajax/payments.blade.php`
- `resources/views/clients/ajax/orders.blade.php`
- `resources/views/clients/ajax/credit_notes.blade.php`
- `resources/views/clients/ajax/estimates.blade.php`
- `resources/views/clients/ajax/tickets.blade.php`
- `resources/views/clients/ajax/invoices.blade.php`
- `resources/views/clients/ajax/notes.blade.php`
- `resources/views/clients/ajax/projects.blade.php`
- `resources/views/clients/ajax/contacts.blade.php`
- `resources/views/clients/ajax/documents.blade.php`
- `resources/views/clients/index.blade.php`
- `resources/views/clients/show.blade.php`
- `resources/views/clients/notes/index.blade.php`
- `resources/views/clients/contacts/show.blade.php`
- `resources/views/clients/gdpr/consent-form.blade.php`

## Files da cap nhat (Phase — Estimates / Quotations)

- `resources/views/estimates/ajax/create.blade.php` — save + change client / product category / add line item: `$.handleApiFormError(err)`.
- `resources/views/estimates/ajax/edit.blade.php` — tuong tuc.
- `resources/views/estimates/ajax/show.blade.php` — change status, decline, accept signature, send, delete, v.v.
- `resources/views/estimates/index.blade.php` — quick action, send, delete, change status, convert.
- `resources/views/estimates/ajax/import.blade.php` — da co tu truoc.

**Test (hop dong JSON validation cho UI):** `tests/Feature/EstimateStoreValidationJsonTest.php` — `postJson` thieu `client_id` → `422` + `errors.client_id`.

## Ket qua sau Phase 2

- Cac `.catch(function(err){...})` trong module Client da duoc chuan hoa ve `$.handleApiFormError(err);`.
- Giam lap code `Swal.fire(...)` tai tung view, de maintain va review de hon.
- Rieng cac block co custom UI fallback (vi du right-modal 403/404/500) van giu hanh vi cu, nhung da check helper truoc khi fallback toast.

## Prompt goi y cho cac module khac

```text
Muc tieu:
- Chuan hoa hien thi validation inline cho module <MODULE_NAME> khi dung window.apiHttp.

Yeu cau:
1) Dung helper global $.handleApiFormError(err) trong tat ca catch cua form submit.
2) Khong lap lai block Swal + normalize errors trong tung view.
3) Neu can, bo sung request rules/messages de loi ro rang theo field.
4) Cap nhat file theo doi tai FUNC_LOGIC/<MODULE_NAME>_INLINE_VALIDATION_ROLLOUT.md.

Tieu chi hoan thanh:
- Khi submit sai du lieu, loi hien ngay duoi field (is-invalid + invalid-feedback).
- Khong chi con toast "The given data was invalid.".
- Da clear cache va co checklist test nhanh.
```
