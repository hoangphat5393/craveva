# P2 — Đa đơn vị + UOM price trên Sản phẩm (UX KiotViet, dữ liệu ERP) + Kho

**Cập nhật:** 21/05/2026 (triển khai A–C xong; UAT còn lại)  
**Đối tượng đọc:** PM, BA, Dev  
**Liên quan:** [`PHASE2_PM_PLAN_VI.md`](./PHASE2_PM_PLAN_VI.md) (P2-1 / Sprint D), [`11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`](./11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md), [`BIOMIXING_MULTITENANT_RISKS_VI.md`](./BIOMIXING_MULTITENANT_RISKS_VI.md)

---

## 1. Kết luận một câu (cho PM)

**Triển khai được.** Kho đã có quy đổi **số lượng** (`product_unit_conversions` + `WarehouseUnitConversionService`). **Thiếu** trên UX: **UOM price** (giá theo từng đơn vị) + màn Sản phẩm như KiotViet. Epic **P2-UOM** (~**3 sprint**, A→B→C), **không** gộp Phase 2 MVP đã chốt.

**Thiết kế chốt:** **Luồng & bố cục giống KiotViet**; **đơn vị chọn từ danh mục `unit_types`** (không gõ tên tự do trên dòng) — hợp lý hơn cho ERP đa module, kho, multi-tenant.

---

## 2. «Thiếu UOM price» — PM đang nói gì?

| Thuật ngữ     | Ý nghĩa                                                                         |
| ------------- | ------------------------------------------------------------------------------- |
| **UOM**       | Unit of Measure — kg, gói, chai, Cái…                                           |
| **UOM price** | **Giá bán (và có thể giá mua) theo từng đơn vị** — vd 100.000đ/kg, 330.000đ/gói |

### Hệ thống hiện tại

| Có                                                                | Chưa có                                            |
| ----------------------------------------------------------------- | -------------------------------------------------- |
| `products.price` + **một** `unit_id` (đơn vị gốc)                 | Bảng giá **theo từng UOM** trên master SP          |
| `product_unit_conversions` chỉ `factor_to_base` (quy SL kho)      | Cột `selling_price` / `for_sale` trên conversion   |
| Dòng SO/Estimate: `unit_price` + `unit_id` (nhập **tay** khi bán) | Đổi đơn vị trên đơn → **tự kéo** giá từ master UOM |
| `wholesale_price`, `price_per_box`… (cột rời, không gắn UOM)      | Thống nhất với quy đổi gói ↔ kg                    |

**Tóm lại:** PM gọi **«thiếu UOM price»** = thiếu **giá × đơn vị** trên sản phẩm (và luồng đơn đọc từ đó), không phải thiếu danh mục Unit Type.

---

## 3. KiotViet vs Craveva — thiết kế nào hợp lý hơn?

| Tiêu chí                  | KiotViet (gõ tên đơn vị tự do) | **Craveva (đã chốt)** — UX KiotViet + `unit_types` |
| ------------------------- | ------------------------------ | -------------------------------------------------- |
| Tốc độ nhập lần đầu       | Nhanh (gõ «Cái», «gói»)        | Hơi chậm hơn (chọn / «Thêm đơn vị»)                |
| Nhất quán / báo cáo       | Dễ trùng tên                   | Một danh mục theo công ty                          |
| Kho, GRN, `convertToBase` | Cần sync tên → `unit_id`       | **Sẵn** — code đã dùng `unit_id`                   |
| SO / UOM price            | Cần layer riêng                | `ProductUnitPriceResolver` + map                   |
| Multi-tenant Biomixing    | Rủi ro nhầm kg/gói             | Chuẩn hóa được                                     |
| Giống demo KiotViet       | 100%                           | ~85% (khác: **dropdown** thay **text**)            |

**Kết luận dự án Craveva:** **Thiết kế cột phải hợp lý hơn** cho ERP + Warehouse + Production. **Giữ y hệt KiotViet** chỉ nên dùng nếu PM chấp nhận «tạo Unit Type khi Save» từ text — phức tạp, dễ rác dữ liệu.

### PM copy-paste

> Giao diện và thứ tự thao tác **giống KiotViet** (chỉ thêm đơn vị phụ sau khi có giá; mỗi dòng có hệ số + giá + «bán được»). Đơn vị **chọn từ danh mục** (vẫn **tự thêm** tên mới qua nút «Thêm đơn vị» như form hiện tại) để **kho và đơn hàng không lệch** — đây là ERP, không chỉ POS.

---

## 4. Quyết định UX màn Sản phẩm (tham chiếu ảnh KiotViet)

### 4.1 Đơn vị gốc (dòng đầu — không nằm trong `product_unit_conversions`)

| Thành phần   | Craveva                                               | Ghi chú                                           |
| ------------ | ----------------------------------------------------- | ------------------------------------------------- |
| Tên đơn vị   | **Dropdown** `unit_types` (field `unit_type` hiện có) | Không ô text «Basic unit name» tự do như KiotViet |
| Giá bán      | `selling_price` → `products.price`                    | Bắt buộc trước khi thêm đơn vị phụ                |
| Cho phép bán | ☑ trên đơn vị gốc (optional) hoặc mặc định bán được   | Có thể map `for_sale` logic cho base              |

User **vẫn tự thêm** loại đơn vị mới: nút **«Thêm đơn vị»** cạnh dropdown (đã có trên `product-form-fields.blade.php`) → tạo bản ghi `unit_types`, rồi chọn.

### 4.2 Điều kiện bật «+ Thêm đơn vị» (ảnh lỗi KiotViet)

**Giống KiotViet** — chỉ khi **đủ cả hai**:

1. Đã chọn **đơn vị gốc** (`unit_id`).
2. **Giá bán** > 0 (`selling_price`).

Nếu chưa đủ: nút **disabled** + tooltip / message (tương đương toast _«Basic unit has not been entered»_ / chưa có giá).

### 4.3 Dòng đơn vị phụ (sau khi bấm «+ Thêm đơn vị» — ảnh dòng thứ 2)

| Cột UI       | Dữ liệu                                                    | Hành vi                                                        |
| ------------ | ---------------------------------------------------------- | -------------------------------------------------------------- |
| Đơn vị       | `unit_id` (select, ≠ đơn vị gốc, không trùng dòng khác)    |                                                                |
| Quy đổi      | `factor_to_base` — hiển thị `= [input] × [tên đơn vị gốc]` | 1 gói = 12 Cái → factor = 12                                   |
| Giá bán      | `selling_price` nullable                                   | Mặc định `products.price × factor`; **cho sửa tay** (override) |
| Cho phép bán | `for_sale`                                                 | Lọc dropdown SO (giai đoạn B)                                  |
| Xóa          | —                                                          | Xóa dòng trước khi Save                                        |

**Không** lưu đơn vị gốc vào bảng `product_unit_conversions`.

### 4.4 JS (giống KiotViet)

- Đổi `factor` hoặc `products.price` → cập nhật giá dòng phụ (trừ dòng đã override).
- Badge «Giá tùy chỉnh» nếu `selling_price` ≠ `price × factor`.

### 4.5 Phạm vi **KHÔNG** làm — «quy đổi / unit tự do» như KiotViet (text)

| KiotViet (ảnh)                                  | Epic P2-UOM **không** có bước này                                  |
| ----------------------------------------------- | ------------------------------------------------------------------ |
| Ô **Basic unit name** gõ chữ («Cái», «áqwed»)   | Đơn vị gốc = **dropdown** `unit_types`                             |
| Ô **Unit name** dòng phụ gõ chữ tự do           | Đơn vị phụ = **dropdown** (+ nút «Thêm đơn vị» → tạo `unit_types`) |
| Quy đổi chỉ dựa trên **tên hiển thị** trên form | Quy đổi kho = **`unit_id` + `factor_to_base`** trong DB            |

**Lý do:** `WarehouseUnitConversionService` chỉ nhận `unit_id` (FK), không đọc chuỗi tên. Gõ tự do mà không lưu map → tồn kho **không** quy đổi đúng (hoặc lỗi nếu bật strict).

**Tùy chọn tương lai (ngoài epic, chỉ khi PM ký riêng):** khi Save SP, auto **tạo** `unit_types` từ text rồi map — ước thêm ~0,5 sprint + rủi ro trùng tên; **không** nằm trong A/B/C mặc định.

**Có trong epic (giống KiotViet về UX, khác dữ liệu):**

- Chỉ bật «+ Thêm đơn vị» sau **đơn vị gốc + giá bán**.
- Dòng phụ: **hệ số** `= N × [tên đơn vị gốc]` + **giá** + **for sale** (tên gốc lấy từ `unit_types`, không gõ).

---

## 5. KiotViet vs hiện trạng vs sau epic

| Tính năng           | Hiện tại                | Sau P2-UOM                                 |
| ------------------- | ----------------------- | ------------------------------------------ |
| Đơn vị gốc + giá    | 1 `unit_id` + 1 `price` | Giữ + block UI rõ                          |
| Đơn vị phụ + hệ số  | DB có, không UI         | UI + lưu map                               |
| **UOM price**       | Không                   | `selling_price` trên conversion + resolver |
| «For sale» từng UOM | Không                   | `for_sale`                                 |
| Tồn kho quy SL      | Có (Warehouse)          | Dùng map từ màn SP                         |
| BOM / lệnh SX       | Nhập tay cùng UOM       | Giai đoạn C                                |

---

## 6. Nền kỹ thuật đã có (tái sử dụng)

| Thành phần        | Đường dẫn                                                                                   |
| ----------------- | ------------------------------------------------------------------------------------------- |
| Bảng quy đổi      | `product_unit_conversions` — `Modules/Warehouse/Database/Migrations/2026_04_02_220000_...`  |
| Quy SL            | `Modules/Warehouse/Services/WarehouseUnitConversionService.php`                             |
| Tồn               | `StockMovementService`, `StockReservationService`, `WarehouseAvailabilityService`           |
| Strict conversion | `warehouse/company-flow-settings` → `strict_unit_conversion`                                |
| Production shadow | `production.phase2.yield_uom_shadow_enabled` = **false** — sau UAT A+B                      |
| Form SP hiện tại  | `Modules/Purchase/Resources/views/purchase-products/partials/product-form-fields.blade.php` |

**Mới (giai đoạn A):** `ProductUnitPriceResolver` (cùng module Warehouse hoặc Purchase) — `resolveSellingPrice(companyId, productId, unitId)`.

---

## 7. Bản đồ triển khai — bổ sung chỗ nào

```text
[Sản phẩm - Purchase]     ★ A: UI + gate «+ Thêm đơn vị» + UOM price
        ↓
product_unit_conversions (+ selling_price, for_sale, sort_order)
        ↓
ProductUnitPriceResolver
        ↓
[SO / Estimate dòng]      ★ B: unit dropdown + auto unit_price
        ↓
[Warehouse movement]        ★ đã có convertToBase
        ↓
[BOM / Lệnh SX]           ★ C
```

| Giai đoạn        | File / module chính                                                                               |
| ---------------- | ------------------------------------------------------------------------------------------------- |
| **A — DB**       | `Modules/Warehouse/Database/Migrations/` (alter `product_unit_conversions`)                       |
| **A — Model**    | `Modules/Warehouse/Entities/ProductUnitConversion.php`                                            |
| **A — Service**  | `WarehouseUnitConversionService` (giữ) + `ProductUnitPriceResolver` (mới)                         |
| **A — API**      | `Modules/Purchase/Http/Controllers/PurchaseProductController.php` + Form Request sync conversions |
| **A — UI**       | `product-form-fields.blade.php` + partial `product-unit-conversions.blade.php` + JS               |
| **A — Test**     | `tests/Feature/WarehouseUnitConversionFlowTest.php` + test lưu product + resolver                 |
| **B — SO**       | `app/Http/Controllers/OrderController.php`, `orders/ajax/add_item.blade.php`, `edit.blade.php`    |
| **B — Estimate** | `EstimateController` + view add item (nếu PM cần)                                                 |
| **B — Kho**      | Chỉ vận hành + banner flow settings; PO/GRN khi có scope                                          |
| **C**            | `bom-lines.blade.php`, `ProductionOrderMaterialRequirementsSummary`, config shadow                |

**Không bổ sung tại:** chỉ sửa danh mục Unit Types (không đủ UOM price); không thêm `price_per_box` mới; không bật shadow trước map SP.

---

## 8. Phạm vi 3 giai đoạn

### Giai đoạn A — Sản phẩm + UOM price (bắt buộc)

| #   | Việc                                                                           |
| --- | ------------------------------------------------------------------------------ |
| A1  | Section **«Quản lý theo đơn vị»** (collapse, dưới Classification)              |
| A2  | Gate **«+ Thêm đơn vị»** = `unit_id` + `selling_price > 0`                     |
| A3  | Dòng phụ: select unit, factor, giá, for_sale, xóa                              |
| A4  | Migration: `selling_price`, `for_sale`, `sort_order` (optional `for_purchase`) |
| A5  | `PurchaseProductController` sync conversions (transaction)                     |
| A6  | `ProductUnitPriceResolver`                                                     |
| A7  | Validation + test + VI labels (`Modules/LanguagePack` Purchase/Warehouse)      |

**Không trong A:** BOM/estimate tự quy đổi; bật shadow Production.

### Giai đoạn B — Đơn hàng + kho

| #   | Việc                                                                      |
| --- | ------------------------------------------------------------------------- |
| B1  | SO: dropdown unit = gốc + `for_sale`; đổi unit → `unit_price` từ resolver |
| B2  | PO/GRN: unit có map (khi có scope Purchase)                               |
| B3  | Banner strict conversion nếu SP thiếu map                                 |
| B4  | Seed/import map pilot Oldtown                                             |

### Giai đoạn C — Biomixing

| #   | Việc                                                    |
| --- | ------------------------------------------------------- |
| C1  | BOM báo giá: hiển thị quy đổi SL (read-only)            |
| C2  | Tổng NL lệnh SX: optional `convertToBase` (flag tenant) |
| C3  | Bật `yield_uom_shadow_enabled` sau sign-off             |

---

## 9. Thiết kế dữ liệu

### 9.1 Đơn vị gốc

`products.unit_id` + `products.price`.

### 9.2 Đơn vị phụ — `product_unit_conversions`

| Cột              | Ý nghĩa                                    |
| ---------------- | ------------------------------------------ |
| `factor_to_base` | 1 đơn vị phụ = X đơn vị gốc                |
| `selling_price`  | nullable; null → `products.price × factor` |
| `for_sale`       | Hiện trên SO/Estimate                      |
| `for_purchase`   | optional — PO                              |
| `sort_order`     | Thứ tự UI                                  |

```text
quantity_base   = quantity_entered × factor_to_base
price_derived   = products.price × factor_to_base   (nếu selling_price null)
```

---

## 10. Warehouse & multi-tenant

| Quy tắc                                                   | Lý do                    |
| --------------------------------------------------------- | ------------------------ |
| Tồn luôn theo đơn vị gốc                                  | Schema hiện tại          |
| Chứng từ `unit_id` ≠ gốc → phải có map                    | Tránh 3000 gói = 3000 kg |
| Tenant B2B: không bật strict, có thể không dùng block UOM | Không đổi hành vi        |
| Biomixing: A+B trước UAT; strict sau seed                 | An toàn tồn              |

Cờ gợi ý: `multi_unit_enabled` per company (ẩn block UOM nếu tắt).

---

## 11. Ước lượng

| Giai đoạn | Sprint |
| --------- | ------ |
| A         | 1–1,5  |
| B         | 1      |
| C         | 1      |
| **Tổng**  | ~3     |

---

## 12. Rủi ro & workaround

| Rủi ro                         | Giảm thiểu                                   |
| ------------------------------ | -------------------------------------------- |
| Sai hệ số                      | Preview «1 [phụ] = X [gốc]»; validate factor |
| Strict + chưa map              | Banner + checklist                           |
| PM kỳ vọng gõ tên như KiotViet | Giải thích §3 — chọn danh mục                |
| `price_per_box` trùng          | Doc: ưu tiên map UOM mới                     |
| A chưa quy đổi BOM             | Nói rõ trong demo                            |

**Workaround trước code:** chuẩn hóa `unit_id` gốc; BOM/SX cùng UOM với kho; SO `unit_price` tay.

---

## 13. Definition of Done

### A

- [x] Gate «+ Thêm đơn vị» đúng điều kiện (ảnh KiotViet).
- [x] ≥1 đơn vị phụ: factor + UOM price (auto/override) + for_sale.
- [x] Lưu/đọc lại; test resolver + inbound conversion.

### B

- [x] SO: đổi unit → đúng UOM price.
- [x] Báo giá, Hóa đơn, PO: cùng pattern (2026-05-20).
- [ ] **UAT** Oldtown: 1 case gói + kg không lệch tồn.

### C

- [x] BOM hiển thị quy đổi; NL SX tổng theo base.
- [ ] Shadow yield UOM nếu PM ký (`yield_uom_shadow_enabled`).

---

## 14. Tài liệu & code

| File                                 | Mục đích        |
| ------------------------------------ | --------------- |
| `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` | Epic này        |
| `PHASE2_PM_PLAN_VI.md`               | Sprint D / P2-1 |
| `WarehouseUnitConversionService.php` | Quy SL          |
| `product-form-fields.blade.php`      | UI gốc          |
| `PurchaseProductController.php`      | Lưu SP          |
| `BIOMIXING_GAP_STATUS_VI.md`         | Gap tổng        |

---

## 15. Phụ lục — Luồng user (text wireframe)

```text
1. Mở Tạo/Sửa sản phẩm
2. Chọn [Đơn vị gốc ▼]  — hoặc [Thêm đơn vị] tạo mới trong danh mục
3. Nhập [Giá bán] > 0
4. → Nút [+ Thêm đơn vị] bật
5. Bấm [+ Thêm đơn vị]
   → Dòng: [Đơn vị ▼] | = [__] × [Tên đơn vị gốc] | [Giá] | ☑ Bán | 🗑
6. Lưu → map + giá vào DB; kho/SO dùng sau (B)
```

---

---

## 16. Trạng thái triển khai (code — 2026-05-21)

| Phase   | Trạng thái | Ghi chú                                                                                                               |
| ------- | ---------- | --------------------------------------------------------------------------------------------------------------------- |
| **A**   | ✅ Done    | `PurchaseProductController` + blades `product-unit-conversions*`; migration `selling_price`, `for_sale`, `sort_order` |
| **B**   | ✅ Done    | `DocumentLineUnitPricing`, route `orders/product-unit-price`, partials SO/Estimate/Invoice/PO                         |
| **C**   | ✅ Done    | `ProductUnitQuantityHintService` (estimate BOM); `ProductionOrderMaterialRequirementsSummary`                         |
| **UAT** | ⏳ Pending | Checklist §11 — một lượt Product → SO → Estimate → PO → Production                                                    |

**Audit tài liệu:** [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)

---

_Khi PM duyệt UAT: chốt Oldtown case gói/kg · Shadow UOM chỉ bật sau governance P0._
