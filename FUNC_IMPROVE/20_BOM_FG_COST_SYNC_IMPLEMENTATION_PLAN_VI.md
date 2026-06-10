# Kế hoạch triển khai — BOM ↔ Giá vốn thành phẩm (FG)

**Cập nhật:** 2026-05-27  
**Trạng thái:** ✅ P1 code shipped (2026-05-27) — UAT + bật flag tenant còn lại  
**Owner:** Dev + PM + Finance  
**Liên quan:** Production BOM, Product `purchase_price`, Estimate Recipe BOM, B2B SO/Invoice

**Doc liên quan**

| File                                                                                               | Vai trò                                                |
| -------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)                                       | Trạng thái code hiện tại                               |
| [`PHASE1_QUOTATION_PM_HUMAN_VI.md`](./PHASE1_QUOTATION_PM_HUMAN_VI.md)                             | Recipe BOM trên báo giá                                |
| [`BIOMIXING_FLOW_CONCEPTS_VI.md`](./BIOMIXING_FLOW_CONCEPTS_VI.md)                                 | Khái niệm BOM / RM / FG                                |
| [`BIOMIXING_MULTITENANT_RISKS_VI.md`](./BIOMIXING_MULTITENANT_RISKS_VI.md)                         | B2B vs Production tenant                               |
| [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md) | Vận hành PO / posting                                  |

---

## 1. Tóm tắt cho PM (30 giây)

| Câu hỏi                                        | Trả lời hiện tại / sau P1                                                                              |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| BOM cost có **ghi đè** cost trên Product (FG)? | **Không** (hiện tại) → **Có** khi tick **Custom** + lưu BOM (P1)                                       |
| SO / Invoice B2B có dùng cost BOM?             | **Không** — chỉ **giá bán**                                                                            |
| Checkbox **Purchase Information**?             | **Bỏ hẳn** (UI + **xóa cột DB**) — thay bằng **Custom** trên FG + **Cost price luôn hiện** (theo type) |
| Gap chính                                      | FG cost tay **$10** vs BOM **$3.70** → P1 sync `purchase_price` khi lưu BOM                            |

**Hướng P1:** Bỏ `purchase_information` → **Custom** (`cost_from_bom`) trên **Manufactured product** → **disable** nhập cost → sync tổng NVL khi **lưu BOM**. B2B giá bán không đổi.

---

## 2. Hiện trạng code (baseline) → mục tiêu P1

### 2.1 Ba “cost” tách nhau

| Khái niệm                    | Lưu ở đâu                         | Dùng cho                                       |
| ---------------------------- | --------------------------------- | ---------------------------------------------- |
| **Product `purchase_price`** | `products.purchase_price`         | PO gợi ý cost, inventory, **đầu vào** tính BOM |
| **Production BOM cost**      | Không lưu trên BOM — tính runtime | UI BOM (`ProductionBomLineCostCalculator`)     |
| **Estimate Recipe BOM**      | `estimate_bom_lines`              | Margin OEM — tách khỏi Product FG              |

### 2.2 Hiện tại vs mục tiêu form Product

|                        | **Hiện tại**                                         | **Sau P1 (đã chốt)**                                                  |
| ---------------------- | ---------------------------------------------------- | --------------------------------------------------------------------- |
| `purchase_information` | Checkbox ẩn/hiện cost; `0` → `purchase_price = null` | **Xóa cột DB** + gỡ toàn bộ code tham chiếu                           |
| Cost price trên form   | Phụ thuộc checkbox                                   | **Luôn hiện** (trừ **Service**): NVL/FG bắt buộc có cost khi cần      |
| FG sản xuất            | Nhập cost tay                                        | Checkbox **Custom** → ô cost **disabled**; giá = tổng BOM sau lưu BOM |
| Cờ DB mới              | —                                                    | `cost_from_bom` (bool), chỉ meaningful cho `type = goods`             |

**Lưu ý:** Cột `purchase_information` **không** được dùng ở PO / Bill / GRN / SO / Invoice (chỉ form Product + validation). Xóa cột **an toàn** sau khi dọn code (~15 file PHP/Blade/JS).

### 2.3 File cần sửa khi drop `purchase_information`

| Khu vực              | Path                                                                                                                                                                                                 |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Migration drop cột   | `database/migrations/YYYY_MM_DD_drop_purchase_information_from_products_table.php`                                                                                                                   |
| Model                | `app/Models/Product.php`                                                                                                                                                                             |
| Form + JS            | `product-form-fields.blade.php`, `product-type-dependent-fields.blade.php`, `create.blade.php`, `edit.blade.php`, `product-form-client-validation.blade.php`, `purchase-product-unit-conversions.js` |
| Persist + validation | `PurchaseProductController.php`, `StorePurchaseProductRequest.php`, `UpdatePurchaseProductRequest.php`                                                                                               |
| Tests                | `PurchaseProductFormUxTest.php`, `ProductUnitPriceResolverCostOnlyTest.php` (bỏ assert PI)                                                                                                           |
| BOM sync (mới)       | `ProductionBomFgCostSyncService`, hook `ProductionBomController`                                                                                                                                     |

### 2.4 File / class BOM (giữ nguyên vai trò)

| Khu vực                | Path                                                              |
| ---------------------- | ----------------------------------------------------------------- |
| Tính cost dòng BOM     | `Modules/Production/Support/ProductionBomLineCostCalculator.php`  |
| Resolve purchase price | `Modules/Warehouse/Services/ProductUnitPriceResolver.php`         |
| Lưu BOM                | `Modules/Production/Http/Controllers/ProductionBomController.php` |

---

## 3. Quyết định PM / Dev (đã chốt trong plan)

| #   | Quyết định                | Lựa chọn đã chốt                                                                                   |
| --- | ------------------------- | -------------------------------------------------------------------------------------------------- |
| D1  | FG sản xuất — cost        | **BOM** (sync khi lưu BOM)                                                                         |
| D2  | UI Product                | **Bỏ** Purchase Information; **Custom** + `cost_from_bom`; cost **luôn hiện** (disable khi Custom) |
| D3  | Khi sync                  | **Khi lưu BOM** (P1)                                                                               |
| D4  | FG mua sẵn / không Custom | Nhập **cost tay** (ô enabled)                                                                      |
| D5  | Đổi cost NVL              | Re-save BOM hoặc job (P3)                                                                          |
| D6  | Tenant chỉ B2B            | Feature flag **tắt** — không Custom UX                                                             |
| D7  | Custom trước BOM          | **Cho lưu** FG; `purchase_price` null đến khi lưu BOM (§5.1)                                       |
| D8  | DB                        | **Drop** `products.purchase_information`; **không** default `purchase_price = 0` toàn bảng         |

---

## 4. Quy trình nghiệp vụ mục tiêu

### 4.1 Form Product theo loại (sau P1)

| Loại                            | Checkbox **Custom** | **Cost price** trên form                                             |
| ------------------------------- | ------------------- | -------------------------------------------------------------------- |
| Raw material / Semi / Packaging | Không               | Luôn hiện, **bắt buộc** nhập (> 0)                                   |
| **Manufactured (`goods`)**      | Có                  | Custom **OFF** → nhập tay; **ON** → **disabled**, hiện số (sync BOM) |
| Service                         | Không               | **Ẩn** (không cost)                                                  |

### 4.1.1 Product type pricing matrix sau P1

Nội dung này được giữ lại từ baseline cũ `21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md` trước khi retire file đó.

| Product type UI          | `products.type`   | Selling price | Cost price | UOM phụ | Ghi chú |
| ------------------------ | ----------------- | ------------- | ---------- | ------- | ------- |
| Manufactured product     | `goods`           | Có            | Có         | Không   | Thành phẩm / output BOM; có thể bật **Custom** để cost lấy từ BOM. |
| Raw Material             | `raw_material`    | Không         | Có         | Có      | Nguyên liệu; cost-only, dùng cho BOM / PO. |
| Semi Finished            | `semi_finished`   | Không         | Có         | Có      | Bán thành phẩm; cost-only như nguyên liệu. |
| Packaging                | `packaging`       | Không         | Có         | Không   | Bao bì dùng trong BOM. |
| Service                  | `service`         | Có            | Không      | Không   | Không tồn kho, không dùng Production BOM. |

Quyết định bảo tồn:

- `purchase_information` là legacy flag chỉ điều khiển show/hide cost trên form; không dùng cho PO / Bill / GRN / SO / Invoice.
- P1 đã thay legacy flag bằng rule theo `ProductType` + `cost_from_bom` cho Manufactured product.
- Không default `purchase_price = 0` toàn bảng; null vẫn có nghĩa là chưa có cost hợp lệ.

### 4.1.2 Product form visibility sau P1

Nội dung này được giữ lại từ `22_PRODUCT_FORM_UX_SIMPLIFICATION_PLAN_VI.md` trước khi retire file đó.

| Field / section | Raw Material | Semi Finished | Packaging | Manufactured (`goods`) | Service |
| --------------- | ------------ | ------------- | --------- | ---------------------- | ------- |
| Selling price | Ẩn | Ẩn | Ẩn | Hiện | Hiện |
| Cost price | Hiện, bắt buộc | Hiện, bắt buộc | Hiện, bắt buộc | Hiện; disabled khi Custom ON | Ẩn |
| Cost from BOM / Custom | Không | Không | Không | Có | Không |
| Wholesale / box / employee price | Ẩn | Ẩn | Ẩn | Giữ hoặc collapse cho B2B | Ẩn |
| UOM phụ | Hiện | Hiện | Ẩn | Ẩn | Ẩn |
| Inventory fields | Giữ nếu track inventory | Giữ nếu track inventory | Giữ nếu track inventory | Giữ | Ẩn |
| Images / advanced metadata | Có thể ẩn hoặc collapse | Có thể ẩn hoặc collapse | Có thể ẩn hoặc collapse | Có thể collapse | Ẩn mặc định |

Quyết định UX đã chốt:

- Raw Material / Semi Finished / Packaging là cost-only trên catalog.
- Service là sell-only, không tồn kho và không cost.
- Manufactured product giữ selling price cho B2B; cost có thể nhập tay hoặc khóa theo BOM khi Custom ON.
- Các field ít dùng nên collapse / ẩn theo type thay vì để form Product thành một form quá dài cho mọi loại.

### 4.2 Luồng end-to-end

```
NVL có purchase_price
  → Tạo FG + tick Custom (chưa BOM vẫn lưu được)
  → Tạo BOM → Lưu BOM → purchase_price FG = tổng BOM
  → SO / Invoice: giá bán only
```

### 4.3 Ví dụ — Nasi Lemak

| Bước                        | Số mục tiêu                   |
| --------------------------- | ----------------------------- |
| BOM UI                      | S$3.70                        |
| Product FG `purchase_price` | S$3.70 (sync; không S$10 tay) |
| SO / Invoice                | Giá bán không đổi             |

---

## 5. Phased implementation

### Phase 0 — Không dev

- [ ] SOP: NVL bắt buộc cost; FG Custom = cost từ BOM sau lưu BOM
- [ ] Pilot: NVL thiếu cost → BOM total null → không sync FG

---

### Phase 1 — MVP (~1–1.5 tuần)

**Mục tiêu:** Bỏ `purchase_information` + Custom + sync BOM → `purchase_price`. **Không** đổi B2B pricing.

| ID    | Task                        | Chi tiết                                                                                                                                  | Done |
| ----- | --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- | ---- |
| P1-00 | Drop `purchase_information` | Migration `dropColumn('purchase_information')`; gỡ fillable, requests, controller, Blade, JS, tests; grep sạch repo                       | ☑    |
| P1-01 | `cost_from_bom`             | Migration `boolean default false` trên `products`; chỉ UI/validation cho `goods`                                                          | ☑    |
| P1-02 | Config tenant               | `production.cost_sync.bom_drives_fg_purchase_price` (default false)                                                                       | ☑    |
| P1-03 | Service sync                | `ProductionBomFgCostSyncService` ← `summarizeSavedLines()` → `purchase_price` output FG                                                   | ☑    |
| P1-04 | Hook BOM                    | Sau `ProductionBomController` store/update (transaction)                                                                                  | ☑    |
| P1-05 | UI Product                  | Bỏ PI; **luôn** cột Cost (theo type); checkbox **Custom**; Custom ON → `#purchase_price` **disabled** + hint + link BOM                   | ☑    |
| P1-06 | Validation                  | RM/Semi/Packaging: `purchase_price` required, min > 0; Custom ON: cho lưu FG, `purchase_price` nullable đến BOM; sync skip nếu total null | ☑    |
| P1-07 | Data hygiene (optional)     | SP stockable cũ `purchase_price` null: để null hoặc Finance backfill — **không** mass-update `= 0`                                        | ☑    |
| P1-08 | Tests                       | Drop PI regression; BOM sync; Custom bootstrap; SO price unchanged                                                                        | ☑    |
| P1-09 | UAT                         | Mục 7                                                                                                                                     | ☐    |

**`purchase_price` — không làm:** default `0` toàn bảng (làm BOM/tồn sai). FG Custom chưa BOM: **null** + UI _Pending BOM_.

**Out of scope P1:** labor/overhead BOM; sync khi complete PO (P2); auto-recalc khi đổi NVL (P3)

### 5.1 Bootstrap FG trước BOM (D7)

| Bước                         | Hành vi                                                                                      |
| ---------------------------- | -------------------------------------------------------------------------------------------- |
| Lưu FG + Custom ON, chưa BOM | OK; `cost_from_bom = 1`; `purchase_price` **null**; cost field disabled, label _Pending BOM_ |
| Lưu BOM                      | Sync `purchase_price` = tổng (nếu config + total hợp lệ)                                     |

**Không:** validate “phải có BOM mới lưu Product”.

---

### Phase 2 — Actual cost on PO complete

(Như bản cũ — P2-01 … P2-05)

---

### Phase 3 — Backlog

- Job recalc FG khi NVL đổi cost
- Labor + overhead trên BOM
- Align Recipe BOM resolver với Production

---

## 6. Ảnh hưởng chứng từ

| Chứng từ                       | Sau P1                                                |
| ------------------------------ | ----------------------------------------------------- |
| PO / Bill / GRN / SO / Invoice | **Không đổi** (không từng đọc `purchase_information`) |
| Product form                   | **Đổi** — bỏ PI, Custom, cost luôn hiện               |
| Product FG cost                | **Đổi** khi lưu BOM (Custom ON)                       |
| Inventory valuation            | **Đổi** theo `purchase_price` FG sau sync             |

---

## 7. UAT checklist (Phase 1)

- [ ] Không còn checkbox / cột **Purchase Information** (DB + UI)
- [ ] NVL: Cost price luôn hiện, bắt buộc > 0
- [ ] FG mới + **Custom** trước BOM → lưu OK; cost disabled, _Pending_
- [ ] Lưu BOM → FG `purchase_price` ≈ tổng BOM
- [ ] FG **Custom OFF** → nhập cost tay
- [ ] SO giá bán không đổi
- [ ] Tenant flag off → không sync BOM (regression)

**Tests:** `ProductionBomFgCostSyncTest.php`, cập nhật `PurchaseProductFormUxTest.php`

---

## 8. Decision log

| Ngày       | Quyết định                                              | Người chốt | Ghi chú                           |
| ---------- | ------------------------------------------------------- | ---------- | --------------------------------- |
| 2026-05-27 | D2: **Custom** thay PI trên UI                          | Gary       |                                   |
| 2026-05-27 | **D8: Drop** `purchase_information` khỏi DB             | User + Dev | Gỡ ~15 file; PO/SO không dùng cột |
| 2026-05-27 | D7: Custom trước BOM — cho lưu FG                       | User       |                                   |
| 2026-05-27 | Cost luôn hiện; Custom ON → **disable** cost (không ẩn) | User       |                                   |
| 2026-05-27 | **Không** default `purchase_price = 0` toàn DB          | Dev        | Null + validate RM; tránh BOM sai |

---

## 9. Rủi ro & giảm thiểu

| Rủi ro                                           | Giảm thiểu                                                               |
| ------------------------------------------------ | ------------------------------------------------------------------------ |
| Migration drop cột trên tenant có custom SQL     | Chạy staging trước; backup                                               |
| FG cũ từng `purchase_information = 0`, cost null | Sau drop: type goods + Custom OFF → user nhập cost hoặc bật Custom + BOM |
| `purchase_price = 0` mass default                | **Không làm** (D8)                                                       |
| Custom ON, chưa BOM                              | Banner _Pending BOM_; sync khi lưu BOM                                   |
| NVL thiếu cost                                   | Block sync; cảnh báo BOM                                                 |

---

## 10. Tiến độ sprint

| Sprint | Mục tiêu                         | Trạng thái |
| ------ | -------------------------------- | ---------- |
|        | P0 SOP                           | ☐          |
|        | P1 MVP (drop PI + Custom + sync) | ☑ code     |
|        | P2 Actual cost                   | ☐          |

---

## 11. Stakeholder (EN)

> We will **remove** the legacy `purchase_information` flag, always show cost on product forms (by type), add a **Custom** option on manufactured products to **lock** cost to the BOM total on BOM save, and sync `purchase_price` — **B2B selling prices unchanged**.

---

_File living doc — cập nhật khi ship P1._
