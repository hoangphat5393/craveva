# Hub — Đặc tả UI/UX cho phát triển tính năng mới (Backend / Full-stack)

**Tên file:** `DESIGN_BACKEND_UI_UX_VI.md` (trước đây: `HUB_BACKEND_UI_UX_DESIGN_SPEC_VI.md`, `HUB_FORM_UI_CONVENTIONS_VI.md`).

**Đối tượng:** dev/backend/full-stack khi **phát triển chức năng mới** có **danh sách (list)**, **thêm/sửa (create/edit)**, **chi tiết (show)** — cần **kế thừa** giao diện và trải nghiệm giống các màn Hub hiện có (Products, Orders, v.v.).

**Mục đích:**

1. **UI:** cùng component, class Bootstrap/theme, DataTable, `select-picker`, không tự chế màu hex lệch theme.
2. **UX:** giảm thao tác (ví dụ chỉnh trạng thái **trực tiếp trên list**), phản hồi rõ (hover, revert khi lỗi), trạng thái **đọc nhanh** bằng chấm màu; **gom thao tác dòng** vào menu Action nhất quán.

**Tham chiếu kỹ thuật layout/JS:** `FUNC_LOGIC/DESIGN_FRONTEND_UI.md` (mục §5 trỏ tới file này).

---

## 0. Quy trình gợi ý khi làm module mới

| Bước | Việc làm                                                                                                                                  |
| ---- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| 1    | Chọn **màn tham chiếu** gần nhất trong Hub (Orders = list + status inline; Products = filter + cột nhị phân; …).                          |
| 2    | Copy cấu trúc Blade: `@extends`, `@section('filter-section')`, action bar, `x-datatable.actions`, `@push('scripts')` + `datatable_js`.    |
| 3    | Cột **Action** (mục **4**); trạng thái: nếu có — pattern **mục 5** (nhị phân) hoặc **mục 6** (đa trạng thái + inline) và **bám map màu**. |
| 4    | Form create/edit: xử lý lỗi validation & redirect AJAX theo **mục 12** (tránh toast sai icon / thiếu field).                              |
| 5    | Sau khi code: chạy checklist **mục 10**.                                                                                                  |

---

## 1. Nút hành động chính (Primary)

- **Component:** `<x-forms.link-primary>` (hoặc `btn btn-primary` tương đương).
- **Class:** `btn btn-primary rounded f-14 p-2` — `resources/views/components/forms/link-primary.blade.php`.
- **Icon:** `icon` → `<i class="fa fa-{icon} mr-1"></i>` (ví dụ `plus` cho “Add …”).
- **Chiều cao:** ~36–40px từ `p-2` + `f-14`; không hard-code pixel trong Blade.
- **Màu:** theme `$primary` / `.btn-primary` — **không** gắn `#800000` cố định trong view.

## 2. Nút phụ (Secondary)

- **Component:** `<x-forms.link-secondary>` — Import, Export, Filters, Columns, v.v.

## 3. Thanh filter (danh sách kiểu Products / Orders)

- **Khối:** `x-filters.filter-box` + `div.select-box`, label `f-14 text-dark-grey`, `select.form-control.select-picker`.
- **Tìm kiếm:** `input-group` + `fa-search`, `@lang('app.startTyping')`.
- **Reset:** `x-forms.button-secondary` `#reset-filters`, `d-none` khi chưa lọc.
- **Tham chiếu:** `resources/views/products/index.blade.php`, `resources/views/orders/index.blade.php`.

---

## 4. Cột Action — menu dấu **ba chấm dọc** (`task_view` + Bootstrap dropdown)

### 4.1 Mục đích UX

- Các thao tác **theo một bản ghi** (View, Edit, Delete, Duplicate, …) gom vào **một nút** (⋮) để bảng gọn, cột hành động có chiều rộng ổn định.
- **Khác** cột **trạng thái** (§5–6): Action = **lệnh nghiệp vụ**; Status = **giá trị trạng thái** (select pill hoặc chỉ đọc).

### 4.2 Cấu trúc HTML / class chuẩn Hub

- **Wrapper:** `div.task_view`.
- **Nút mở menu:** thẻ `<a>` (hoặc `button` tương thích Bootstrap 4) với class  
  `task_view_more d-flex align-items-center justify-content-center dropdown-toggle`,  
  `type="link"`, `data-toggle="dropdown"`, `id="dropdownMenuLink-{id}"` **duy nhất theo dòng**.
- **Icon ba chấm dọc:** `<i class="icon-options-vertical icons"></i>` (Simple Line / theme icon — **không** tự đổi sang `fa-ellipsis-v` trừ khi toàn app thống nhất đổi icon set).
- **Menu:** `div.dropdown-menu.dropdown-menu-right` (menu căn phải, tránh tràn khỏi viewport).
- **Từng mục:** `a.dropdown-item` (hoặc `button.dropdown-item`), **icon trái** Font Awesome + **`mr-2`** + nhãn `@lang` / `trans()`.

Ví dụ khung (Orders — toàn bộ trong menu):

```html
<div class="task_view">
    <div class="dropdown">
        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-123" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-options-vertical icons"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-123" tabindex="0">
            <a class="dropdown-item" href="..."><i class="fa fa-eye mr-2"></i> View</a>
            <a class="dropdown-item" href="..."><i class="fa fa-edit mr-2"></i> Edit</a>
            <a class="dropdown-item" href="javascript:;">...</a>
        </div>
    </div>
</div>
```

- **Biến thể Products:** có thêm link **View** dạng text `taskView openRightModal` **bên ngoài** dropdown, còn Edit/Delete nằm trong menu — vẫn cùng class `task_view_more` + `dropdown-menu-right`. Tính năng mới chọn **một** kiểu (chỉ menu vs View nhanh + menu) và **giữ nhất quán** trong module.

### 4.3 Bộ icon gợi ý (đồng nhất DataTable hiện có)

| Hành động               | Icon          | Ghi chú                                                              |
| ----------------------- | ------------- | -------------------------------------------------------------------- |
| View                    | `fa fa-eye`   | `OrdersDataTable`, `ClientsDataTable`, Estimates…                    |
| Edit                    | `fa fa-edit`  | `openRightModal` nếu mở panel phải                                   |
| Delete                  | `fa fa-trash` | Thường kèm `delete-table-row` hoặc handler + **SweetAlert** xác nhận |
| Duplicate / tạo bản sao | `fa fa-copy`  | Invoices, Proposals, Estimates (`?invoice=`, `?estimate=` …)         |

- **Typography / màu:** nền menu trắng, chữ/icon **text-darkest-grey** / đen nhạt theo theme; shadow theo Bootstrap — **không** tự thêm style popup lệch theme.

### 4.4 Quyền & thứ tự mục

- Chỉ render từng `dropdown-item` khi user có quyền (mẫu: `ProductsDataTable` / `OrdersDataTable` kiểm `permission` trước khi nối chuỗi HTML).
- **Thứ tự đề xuất** khi đủ nhiều mục: **View → Edit → Duplicate (nếu có) → Delete** (Delete thường **cuối** vì rủi ro cao).

### 4.5 `rawColumns`

- Cột `action` chứa HTML → khai báo trong `rawColumns` của DataTable.

**Tham chiếu chính:** `app/DataTables/ProductsDataTable.php` (`addColumn('action')`), `app/DataTables/OrdersDataTable.php` (`addColumn('action')`).

---

## 5. Trạng thái nhị phân (Allowed / Not allowed — Products)

### 5.1 Chỉ đọc trong ô bảng

- Allowed: `<i class="fa fa-circle mr-1 text-dark-green f-10"></i>{{ __('app.allowed') }}`
- Not allowed: `<i class="fa fa-circle mr-1 text-red f-10"></i>{{ __('app.notAllowed') }}`
- **Code:** `app/DataTables/ProductsDataTable.php` — `editColumn('allow_purchase', ...)`.
- **UX:** chấm trước, chữ sau; `f-10`; `mr-1`.

### 5.2 Bulk action (toolbar)

- `x-datatable.actions` + `#quick-action-type` / `#change-status-action`; giá trị `1` / `0` — xem `resources/views/products/index.blade.php`.

### 5.3 Tính năng mới có “bật/tắt” hoặc đúng/sai

- **Ưu tiên:** cùng pattern chấm `text-dark-green` / `text-red` + `f-10`.
- **Badge:** chỉ khi toàn bộ màn đồng nhất badge (`badge-success` / `badge-danger`) — không trộn chấm + badge trên cùng một bảng.

### 5.4 Trạng thái dạng **badge** (list / detail chỉ đọc)

Dùng khi cột **Status** (hoặc Stock Health, trạng thái nghiệp vụ) **không** chỉnh inline — cần **đọc nhanh** bằng màu nền, giống **Purchase Inventory → Stock Health**.

#### 5.4.1 Markup chuẩn (Bootstrap 4 theme Hub)

```html
<span class="badge badge-success">Completed</span>
```

- **Class:** `badge badge-{variant}` — variant ∈ `success`, `warning`, `danger`, `info`, `secondary`, `primary` (theme sẵn có).
- **Không** hard-code màu hex; **không** trộn chấm tròn (§5.1) và badge trên **cùng một bảng**.
- **Nhãn:** luôn qua `__()` / `trans()` (vd. `production::app.statusLabels.{value}`).
- **DataTable:** cột badge trả HTML → khai báo `rawColumns`; escape label bằng `e()` trong PHP.

#### 5.4.2 Map màu gợi ý (semantic)

| Ý nghĩa                   | `badge-*`   | Ví dụ module                                                  |
| ------------------------- | ----------- | ------------------------------------------------------------- |
| Ổn / hoàn tất / active    | `success`   | Inventory **Normal**; Production **completed**                |
| Đang xử lý / cảnh báo nhẹ | `warning`   | Inventory **Low**, near expiry; Production **in_progress**    |
| Lỗi / hủy / critical      | `danger`    | Inventory **Critical**, **Expired**; Production **cancelled** |
| Đã phát hành / info       | `info`      | Production **released**                                       |
| Nháp / trung tính         | `secondary` | Production **draft**                                          |

**Tham chiếu Inventory (DataTable):** `Modules/Purchase/DataTables/PurchaseInventoryDataTable.php` — `editColumn('stock_health', ...)`.

**Tham chiếu Production orders (Blade list + detail):**

- Map + HTML: `Modules/Production/Support/ProductionOrderStatusBadge.php`
- Partial: `Modules/Production/Resources/views/partials/order-status-badge.blade.php`
- List: `Modules/Production/Resources/views/orders/index.blade.php`

#### 5.4.3 Cách copy sang module mới

1. Tạo class `{Module}{Entity}StatusBadge` với `VARIANT_BY_STATUS`, `label()`, `html()` (hoặc Blade component `@props(['variant','label'])`).
2. Dùng **hằng status** từ Entity (không magic string rải rác).
3. List Blade: `@include('…order-status-badge', ['status' => $row->status])` hoặc `{!! StatusBadge::html($row->status) !!}`.
4. Detail header: cùng partial — đồng nhất list ↔ show.
5. Test unit: mỗi status → đúng `badge-{variant}` + label đã dịch.

#### 5.4.4 Khi nào dùng badge vs §6 (select inline)

| Pattern              | Dùng khi                                                                |
| -------------------- | ----------------------------------------------------------------------- |
| **§5.4 Badge**       | Chỉ đọc; trạng thái đổi qua workflow / nút trên detail, không trên list |
| **§6 Select inline** | User được đổi trạng thái **trực tiếp trên list** (Orders)               |

Production orders → **badge (§5.4)**. Sales orders → **inline select (§6)**.

---

## 6. Trạng thái đa giá trị + chỉnh inline trên list (kế thừa Orders — **UI + UX**)

Đây là pattern **Orders → cột Status**: pill `select-picker`, mỗi option có **chấm màu + tên**, hover theo theme; đổi trạng thái **không bắt buộc** vào màn chi tiết.

### 6.1 UI (DataTable cell)

- **Select:** `class="form-control select-picker order-status"` + `data-order-id="{id}"`.
- **Mỗi `<option>`:** `data-content="..."` với chấm + label (trong PHP dùng nháy đơn bên trong attribute):

```html
data-content="<i class="fa fa-circle mr-2 text-warning"></i> Pending"
```

- **Map màu chuẩn Hub (Orders):**

| Trạng thái (value) | Class chấm                             | Ghi chú       |
| ------------------ | -------------------------------------- | ------------- |
| `pending`          | `text-warning`                         | vàng          |
| `on-hold`          | `text-info`                            | teal / info   |
| `failed`           | `text-dark`                            | xám đậm       |
| `processing`       | `text-primary`                         | primary theme |
| `completed`        | `text-success`                         | xanh          |
| `canceled`         | `text-red`                             | đỏ hủy        |
| `refunded`         | (circle, không text-\* trong code gốc) | hoàn tiền     |

- **Disabled:** khi `status` ∈ `refunded`, `canceled` — trên `<select>` (`OrdersDataTable`).
- **Không quyền sửa:** chỉ đọc + `match` màu + `f-10` (`OrdersDataTable::editColumn('status')`).

**File:** `app/DataTables/OrdersDataTable.php`.

### 6.2 UX (inline status)

1. `focus` lưu giá trị cũ (`data('prev')`); `change` gọi API — `resources/views/orders/index.blade.php` (`.order-status`).
2. **Revert** khi lỗi/hủy (`revertOrderStatusSelection`).
3. SweetAlert khi cần (`changeOrderStatus`).
4. Chỉ hiển thị option chuyển trạng thái **hợp lệ** (máy trạng thái).
5. Refresh bảng sau khi thành công.

### 6.3 Tính năng mới

- Cùng mechanic: `select-picker` + `data-content`; class select riêng (vd. `.my-entity-status`) nếu API khác Orders.
- Giữ họ màu semantic Bootstrap như bảng trên.

---

## 7. Textarea — mô tả dài (Description)

| Ngữ cảnh                   | Quy ước                                                                   | File                                                                                                                                       |
| -------------------------- | ------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `<x-forms.textarea>`       | **`rows="4"`** mặc định (plain textarea, không editor rich text)          | `resources/views/components/forms/textarea.blade.php`                                                                                      |
| Product / Purchase Product | `rows="4"` + `form-control`                                               | `resources/views/products/ajax/create.blade.php`, `edit.blade.php`; `Modules/Purchase/.../purchase-products/ajax/create.blade.php`         |
| Estimate / Quotation       | Plain `<textarea>` (line item summary, ghi chú, delivery): **`rows="4"`** | `resources/views/estimates/ajax/create.blade.php`, `edit.blade.php`; `resources/views/estimates/partials/quotation-extra-fields.blade.php` |

- **Rich text (Quill, …):** không dùng `rows` — chiều cao do CSS / editor; ví dụ khối `#description` trên invoice/estimate.

---

## 8. DataTables — phân trang & raw columns

- `@include('sections.datatable_css')` / `sections.datatable_js`.
- Cột HTML (`action`, `status`, …): `rawColumns` đầy đủ — `OrdersDataTable` / `ProductsDataTable`.

---

## 9. Liên kết chéo (tránh nhầm file cũ)

- `FUNC_LOGIC/DESIGN_FRONTEND_UI.md` §5 → trỏ tới **file này**.
- Form validation AJAX (inline + toast + `Reply::redirect`): **mục §12**; rollout Client: `FUNC_LOGIC/CLIENT_INLINE_VALIDATION_ROLLOUT.md`.
- Không dùng tên `FRONTEND_UI.md`, `HUB_BACKEND_UI_UX_DESIGN_SPEC_VI.md`, `HUB_FORM_UI_CONVENTIONS_VI.md` (đã đổi tên).

---

## 10. Checklist trước khi coi “xong” UI/UX màn mới

- [ ] Primary / secondary đúng component Hub.
- [ ] Filter + search + reset (nếu là list có lọc).
- [ ] **Cột Action (§4):** `task_view` + `task_view_more` + `icon-options-vertical`, menu `dropdown-menu-right`, icon `mr-2`, quyền + thứ tự mục.
- [ ] Trạng thái: pattern **§5** (chấm), **§5.4** (badge readonly), hoặc **§6** (inline select) — **một** pattern mỗi bảng; map màu nhất quán.
- [ ] Inline status: **focus prev**, **revert** khi lỗi, refresh bảng.
- [ ] Textarea mô tả: `rows` theo §7.
- [ ] DataTable: CSS/JS đủ; `rawColumns` đủ.
- [ ] Quyền: không được sửa → chỉ đọc (nhánh tương tự Orders).
- [ ] **Form create/edit (mục 12):** lỗi validation hiển thị rõ (field hoặc toast có nội dung từng rule); redirect sau `Reply::redirect` dùng đúng `action` + `url` (không chỉ `redirectUrl`).

---

## 11. Phụ lục — Purchase Inventory & BOM (triển khai tham chiếu)

### 11.1 Stock Health (Purchase Inventory)

- Cột **Stock Health** (`PurchaseInventoryDataTable`): chỉ **badge** theo tồn / HSD (Critical, Low, Normal, Expired, near expiry theo rule trong code) — **không** dùng icon chấm tròn trước badge cho trạng thái active/inactive sản phẩm (tránh trùng ngữ nghĩa với chấm trạng thái §5–6).

### 11.2 BOM — đơn vị trong nhãn select (không cột Unit type riêng)

- **Finished good:** một cột full width (`col-12`); nhãn mỗi `<option>` dùng **`$bomProductLabelWithUnit($p, $bomFgUnitByProductId)`** — tên + `(unit)` khi map có unit hợp lệ; không còn ô đọc unit bên cạnh.
- **Dòng nguyên liệu:** bảng **3 cột** (Component RM, Quantity per FG, Action); không cột Unit type; option component cũng dùng **`$bomProductLabelWithUnit`**.
- **JS:** `change` (capture trên `document`) + jQuery delegation `changed.bs.select` / `hidden.bs.select` cho `.bom-component-select` và `#output_product_id` — chỉ **`enforceComponentNotEqualFg`** / **`applyFgRestrictionAllRows`** (ẩn/disable option RM trùng FG, `refreshPicker`). Gọi `bindBomUnitPickerListeners()` sau **+ Add** / `window load` / `setTimeout` 150–500ms.
- **Dòng đầu không xóa:** hàng component **index 0** không render nút remove (Blade `@if ($i > 0)`); hàng thêm bằng **+ Add** có nút remove; `syncRemoveRowButtons()` giữ quy tắc sau reindex/xóa dòng.
- **Backend:** `ProductionBomController::addProductData()` vẫn gán `bomFgUnitByProductId` / `bomComponentUnitByProductId` (truy vấn **`unit_types`** theo tenant / `null`) — chỉ phục vụ nhãn option trong Blade, không còn JSON map + đồng bộ JS sang cột phụ.
- **Danh sách BOM (`boms/index`):** cột **Unit type** (đơn vị của **finished good**); `index()` lấy `outputProduct` đã eager-load, gom product theo trang rồi **`ProductionProductUnitLabelMap::forProducts()`** → view dùng `bomListFgUnitByProductId`.
- **Danh sách Production orders (`orders/index`):** cột **Unit type** (FG); `ProductionOrderController@index` eager `outputProduct`, map cùng class **`ProductionProductUnitLabelMap`** → `orderListFgUnitByProductId`.
- **Chi tiết Production order (`orders/show`):** dưới tên FG hiển thị **`orderFgUnitType`** (cùng map từ `outputProduct` đã load).

**File:** `Modules/Purchase/DataTables/PurchaseInventoryDataTable.php`, `Modules/Production/Support/ProductionProductUnitLabelMap.php`, `Modules/Production/Resources/views/boms/partials/form.blade.php`, `Modules/Production/Resources/views/boms/index.blade.php`, `Modules/Production/Resources/views/orders/index.blade.php`, `Modules/Production/Resources/views/orders/show.blade.php`, `Modules/Production/Http/Controllers/ProductionBomController.php`, `Modules/Production/Http/Controllers/ProductionOrderController.php`.

### 11.3 Production order create/edit — select lớn (FG + BOM)

Danh sách **Manufactured product** và **Bill of materials** có thể rất dài (~10k SKU). **Không** dùng `<select>` scroll thuần; bắt buộc **Bootstrap select-picker** có tìm kiếm.

| Field                                          | Component                                                     | Search             | Nhãn option                                                                                              |
| ---------------------------------------------- | ------------------------------------------------------------- | ------------------ | -------------------------------------------------------------------------------------------------------- |
| **Manufactured product** (`output_product_id`) | `<x-forms.select :search="true">` → `data-live-search="true"` | Gõ tên hoặc SKU    | `ProductionProductSelectLabel::forProduct($p)` → `Tên (SKU)`; `data-tokens` = SKU để live-search khớp mã |
| **Bill of materials** (`production_bom_id`)    | Cùng `:search="true"`                                         | Gõ tên FG / mã BOM | `ProductionBom::labelForSelect()` (đã có `FG — code`)                                                    |
| **Linked sales order**                         | Đã có `:search="true"`                                        | —                  | —                                                                                                        |

- **Controller:** `ProductionOrderController::addProductData()` load FG với `id`, `name`, `sku` (không đổi query scope `forBomOutput()`).
- **JS:** `production::orders.partials.bom-fg-sync-script` — sau khi đổi BOM vẫn gọi `selectpicker('refresh')` trên `#output_product_id` / `#production_bom_id`.
- **Tham chiếu pattern SKU:** `resources/views/orders/ajax/create.blade.php`, `invoices/ajax/edit.blade.php` (`data-content` + SKU trong ngoặc).

**File:** `Modules/Production/Support/ProductionProductSelectLabel.php`, `Modules/Production/Resources/views/orders/create.blade.php`, `Modules/Production/Resources/views/orders/edit.blade.php`, `tests/Unit/ProductionProductSelectLabelTest.php`.

---

## 12. Lỗi validation & phản hồi AJAX trên form create/edit

Mục này ghi **chuẩn hai lớp** (FE trước POST + server sau POST), cơ chế Hub hiện có, và so sánh Product / Estimate.

### 12.0 Tóm tắt hai lớp

| Lớp               | Khi nào chạy                                     | Toast                                                                                   | Lỗi theo field                               |
| ----------------- | ------------------------------------------------ | --------------------------------------------------------------------------------------- | -------------------------------------------- |
| **Frontend (FE)** | Trước khi gọi API                                | **Một dòng** góc trên phải (Swal toast) — ví dụ _Invalid data_ / _Dữ liệu không hợp lệ_ | Có — **custom jQuery**, class Bootstrap      |
| **Server**        | Sau `apiHttp` POST, Laravel 422 / `status: fail` | Chỉ khi lỗi **không map** được field (orphan)                                           | Có — `$.handleApiFormError` → `$.showErrors` |

**Không dùng** jQuery Validation Plugin, Parsley, VeeValidate trên form Hub create/edit chuẩn.

### 12.1 Nguồn sự thật (backend)

- **Rule:** `FormRequest` / `validate()` trong Controller.
- **Câu chữ lỗi:** Laravel `lang/.../validation.php` + `messages()` / `attributes()` trong request; module có thể thêm file lang riêng (ví dụ Purchase).

### 12.2 Hai nhánh hiển thị lỗi trên UI (frontend)

| Nhánh                              | Công nghệ                                                                                                                  | Cách dùng điển hình                                                                                                                                                                                                                                                                                                                                      | File / ghi chú                                                                                                             |
| ---------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **A — Legacy jQuery (theo field)** | **jQuery** + class **Bootstrap** (`.form-group`, `.has-error`, `.help-block`, đôi khi `.is-invalid` / `.invalid-feedback`) | `$.showErrors(errorsObject)` — tenant: bản ghi đè **`showErrorsLaravel.js`** (mục **§12.2.1**). **`$.easyAjax`**: `errorPosition: 'field'`.                                                                                                                                                                                                              | `vendor/helper/helper.js` + `resources/js/showErrorsLaravel.js`                                                            |
| **B — Axios `window.apiHttp`**     | **Axios** (`resources/js/http/apiClient.js`): 422 / `status: fail` → `Error` có `err.errors`, `err.message`.               | **Toast:** **SweetAlert2** (`Swal.fire` toast). Ghép nhiều field: **`window.apiHttp.formatValidationErrors(err)`** (chuỗi các message nối bằng `·`). **Redirect thành công:** với `Reply::redirect` Laravel trả `action: 'redirect'` + **`url`** (không phải `redirectUrl`); JS phải kiểm **cả hai** (`url` và `redirectUrl`) trước khi `location.href`. | `resources/js/http/apiClient.js`; ví dụ đã chuẩn hóa: `resources/views/estimates/ajax/create.blade.php`, `edit.blade.php`. |

**Không** dùng thư viện kiểu **jQuery Validation** / **Parsley** trong repo này cho các form Hub nói trên.

### 12.2.1 Ghi đè `$.showErrors` (tenant app)

- File **`resources/js/showErrorsLaravel.js`** được `require` từ **`resources/js/main.js`** — thứ tự load trang: `vendor/helper/helper.js` → **`js/main.js`** → nên **ghi đè** `$.showErrors` sau helper gốc.
- Hành vi bổ sung:
    - Chuỗi lỗi Laravel dạng **mảng** (`["…"]`) được **ghép** thành một dòng hiển thị.
    - Nếu không có `.form-group` bao control (ví dụ chỉ có **`x-forms.input-group`**), lần lượt thử host: **`.input-group`** → **cột grid** (`.col-lg-*` / `.col-md-*` / `.col-12`) → **`parent()`**.
    - **Không** tìm được control hoặc host → message đưa vào nhóm **orphan** → **một** `Swal` toast (các dòng nối bằng `·`). Trường hợp này dùng cho lỗi không khớp `name`/`id` trên DOM.
- Component **`client-selection-dropdown`**: bọc label + `input-group` trong **`<div class="form-group my-3">`** để đồng bộ với các field Hub khác.

### 12.2.2 Luồng server → inline field (sau POST)

```
apiHttp.post* → .catch(err)
    → $.handleApiFormError(err)     // global, vendor/helper/helper.js (bundle main.js)
        → $.extractApiValidationErrors(err)
        → $.showErrors(errorsObject) // ghi đè bởi tenant → showErrorsLaravel.js
```

- Tìm input: `[name="field"]` hoặc `#field`
- Host: `.form-group` → `.input-group` → cột grid → `parent()`
- Thêm: `.has-error` + `.help-block` / `.invalid-feedback` + `.is-invalid`
- **SweetAlert2:** chỉ toast **orphan** (không tìm được ô trên DOM)

### 12.2.3 Luồng FE → inline field (trước POST)

**Tự viết (custom)** trong Blade partial — **không** qua `$.showErrors` global.

- Ví dụ Purchase: `Modules/Purchase/Resources/views/purchase-products/partials/product-form-client-validation.blade.php`
- Hàm: `validatePurchaseProductForm(formSelector)` → `.is-invalid` + `.invalid-feedback.purchase-product-client-error`
- Toast: `Swal.fire({ toast: true, position: 'top-end', text: … })` — **một message chung**, không list từng field
- Partial include **bên trong** `<script>…</script>` cha — **không** bọc thêm `<script>` trong partial

### 12.3 Product (Add / Edit) — trạng thái thực tế trong code

- **Lưu form:** `submitPurchaseProductForm` → **FE** `validatePurchaseProductForm` (toast `top-end` _Invalid data_ + inline field) → nếu pass mới `apiHttp.postUrlEncoded`.
- **Server:** `.catch` → **`$.handleApiFormError(err)`** (inline qua `showErrorsLaravel.js`, giống Client/Estimate).
- **Partial:** `Modules/Purchase/Resources/views/purchase-products/partials/product-form-client-validation.blade.php` (include **trong** `<script>` cha, không bọc `<script>`).
- **Upload ảnh (Dropzone):** jQuery tự chèn `.help-block` / `.is-invalid` khi lỗi file — cùng họ visual Bootstrap.
- **Lang toast FE:** `purchase::app.productFormInvalidData` (LanguagePack + `PurchaseServiceProvider::registerTranslations`).

### 12.4 Estimate / Quotation (create / edit / list / show)

- **`apiHttp`** + **`.catch` → `$.handleApiFormError(err)`** — lỗi validation hiển thị **inline theo field** (giống Client); không có `errors` thì helper **fallback** toast như trước.
- **Redirect sau save:** nhận **`Reply::redirect`** → ưu tiên `response.action === 'redirect' && response.url`, fallback `redirectUrl` (create store từng trả `redirectUrl`).

### 12.5 So sánh nhanh: Product vs Estimate

| Tiêu chí             | Product (create/edit)                          | Estimate (create/edit/show)       |
| -------------------- | ---------------------------------------------- | --------------------------------- |
| **FE trước POST**    | `validatePurchaseProductForm` + toast một dòng | Chủ yếu server-first              |
| **Server inline**    | `$.handleApiFormError`                         | `$.handleApiFormError`            |
| **Redirect success** | Kiểm `url` + `redirectUrl` theo từng action    | Đã đọc `action` + `url` trên save |

**Khuyến nghị form dài:** FE validate (toast ngắn + đánh dấu field) **+** `handleApiFormError` sau POST **+** redirect `action` + `url`.

### 12.6 Pattern copy form mới

**Submit server:**

```javascript
window.apiHttp
    .postUrlEncoded(url, $form.serialize())
    .then(onSuccess)
    .catch(function (err) {
        $.handleApiFormError(err);
    });
```

**Thêm lớp FE (khuyến nghị modal / form dài):**

1. `validate{Feature}Form(formSelector)` — rule mirror FormRequest.
2. Trước POST: `if (!validate…(formSelector)) return;`
3. FE fail: `.is-invalid` + `.invalid-feedback` + **một** Swal toast `top-end` (lang key module); **không** gọi API.
4. Wrapper tùy chọn (Purchase): `submitPurchaseProductForm({ formSelector, url, onSuccess })`.

**Lang:** key toast trong `Modules/LanguagePack/Languages/modules/{Module}/{locale}/app.php` hoặc `Modules/{Module}/Resources/lang/`.

### 12.7 Màn tham chiếu

| Màn                          | FE validate                                | Server `.catch`                                                |
| ---------------------------- | ------------------------------------------ | -------------------------------------------------------------- |
| Purchase Product create/edit | `product-form-client-validation.blade.php` | `$.handleApiFormError`                                         |
| Client create/edit           | (tùy form)                                 | `$.handleApiFormError` — `CLIENT_INLINE_VALIDATION_ROLLOUT.md` |
| Estimate create/edit/show    | (server-first)                             | `$.handleApiFormError`                                         |

### 12.8 jQuery Validate?

|                       | Hub chuẩn                                   | jQuery Validate       |
| --------------------- | ------------------------------------------- | --------------------- |
| Dùng rộng trong repo? | **Không** (mặc định)                        | Không thấy            |
| Rule sync FormRequest | Manual trong JS partial                     | Có thể trùng lặp rule |
| Message               | `__()` / `@json(__('…'))` trong Blade       | Plugin messages       |
| Field UI              | Bootstrap + `showErrorsLaravel` / custom FE | Plugin + adapter      |

### 12.9 Checklist khi copy form mới

- [ ] Submit: `window.apiHttp` (không chỉ `$.easyAjax` trừ form legacy)
- [ ] `.catch` → `$.handleApiFormError(err)` (hoặc `formatValidationErrors` chỉ khi **chỉ** cần toast một chuỗi)
- [ ] (Tuỳ chọn) FE validate trước POST + toast `top-end` một dòng
- [ ] Partial JS **không** có thẻ `<script>` khi include trong script cha
- [ ] Message FE/BE qua `__()` / LanguagePack, không hard-code EN trong JS
- [ ] Success: `response.url` **và** `response.redirectUrl` nếu dùng `Reply::redirect`
- [ ] Không dùng `icon: 'error'` khi `status === 'success'`

---

_Cập nhật: 2026-05-23 — §5.4 badge trạng thái readonly (Inventory / Production orders); §11.3 Production order FG/BOM searchable select. Trước: 2026-05-21 — §12 validation hai lớp._
