# Hub — Đặc tả UI/UX cho phát triển tính năng mới (Backend / Full-stack)

**Tên file:** `DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md` (trước đây: `HUB_BACKEND_UI_UX_DESIGN_SPEC_VI.md`, `HUB_FORM_UI_CONVENTIONS_VI.md`).

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
| 4    | Sau khi code: chạy checklist **mục 10**.                                                                                                  |

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

| Ngữ cảnh                   | Quy ước                     | File                                                                                                                               |
| -------------------------- | --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `<x-forms.textarea>`       | `rows="3"` mặc định         | `resources/views/components/forms/textarea.blade.php`                                                                              |
| Product / Purchase Product | `rows="4"` + `form-control` | `resources/views/products/ajax/create.blade.php`, `edit.blade.php`; `Modules/Purchase/.../purchase-products/ajax/create.blade.php` |

---

## 8. DataTables — phân trang & raw columns

- `@include('sections.datatable_css')` / `sections.datatable_js`.
- Cột HTML (`action`, `status`, …): `rawColumns` đầy đủ — `OrdersDataTable` / `ProductsDataTable`.

---

## 9. Liên kết chéo (tránh nhầm file cũ)

- `FUNC_LOGIC/DESIGN_FRONTEND_UI.md` §5 → trỏ tới **file này**.
- Không dùng tên `FRONTEND_UI.md`, `HUB_BACKEND_UI_UX_DESIGN_SPEC_VI.md`, `HUB_FORM_UI_CONVENTIONS_VI.md` (đã đổi tên).

---

## 10. Checklist trước khi coi “xong” UI/UX màn mới

- [ ] Primary / secondary đúng component Hub.
- [ ] Filter + search + reset (nếu là list có lọc).
- [ ] **Cột Action (§4):** `task_view` + `task_view_more` + `icon-options-vertical`, menu `dropdown-menu-right`, icon `mr-2`, quyền + thứ tự mục.
- [ ] Trạng thái: pattern **§5** hoặc **§6** và map màu nhất quán.
- [ ] Inline status: **focus prev**, **revert** khi lỗi, refresh bảng.
- [ ] Textarea mô tả: `rows` theo §7.
- [ ] DataTable: CSS/JS đủ; `rawColumns` đủ.
- [ ] Quyền: không được sửa → chỉ đọc (nhánh tương tự Orders).

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

---

_Cập nhật: 2026-05-10 — §4 Action; §11 phụ lục Inventory/BOM (Stock Health không dot; BOM: unit trong label option, không cột Unit type)._
