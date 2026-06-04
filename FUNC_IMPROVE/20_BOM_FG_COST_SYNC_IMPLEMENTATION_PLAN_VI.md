# Kế hoạch triển khai — BOM ↔ Giá vốn thành phẩm (FG)

**Cập nhật:** 2026-05-27  
**Trạng thái:** 📋 Planning — chờ PM chốt scope  
**Owner:** Dev + PM + Finance  
**Liên quan:** Production BOM, Product `purchase_price`, Estimate Recipe BOM, B2B SO/Invoice

**Doc liên quan**

| File                                                                                               | Vai trò                                                  |
| -------------------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)                                       | Trạng thái code hiện tại                                 |
| [`PHASE1_QUOTATION_PM_HUMAN_VI.md`](./PHASE1_QUOTATION_PM_HUMAN_VI.md)                             | Recipe BOM trên báo giá                                  |
| [`BIOMIXING_FLOW_CONCEPTS_VI.md`](./BIOMIXING_FLOW_CONCEPTS_VI.md)                                 | Khái niệm BOM / RM / FG                                  |
| [`BIOMIXING_MULTITENANT_RISKS_VI.md`](./BIOMIXING_MULTITENANT_RISKS_VI.md)                         | B2B vs Production tenant                                 |
| [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md) | Vận hành PO / posting                                    |
| [`21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md`](./21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md)     | Hiện trạng checkbox Purchase Information & giá theo type |

---

## 1. Tóm tắt cho PM (30 giây)

| Câu hỏi                                        | Trả lời hiện tại                                                                                                  |
| ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| BOM cost có **ghi đè** cost trên Product (FG)? | **Không**                                                                                                         |
| SO / Invoice B2B có dùng cost BOM?             | **Không** — chỉ **giá bán**                                                                                       |
| Cost BOM dùng để làm gì?                       | Tính **hiển thị** trên màn BOM; copy sang **Recipe BOM báo giá**; đầu vào tính từ **cost NVL** (`purchase_price`) |
| Gap chính                                      | FG có thể **$10** (nhập tay) trong khi BOM **$3.70** → tồn kho / báo cáo nội bộ lệch                              |

**Hướng đề xuất:** Phân loại SP trên Product + **BOM là nguồn cost FG** (sync khi lưu BOM) + B2B không đổi.

---

## 2. Hiện trạng code (baseline)

### 2.1 Ba “cost” tách nhau

| Khái niệm                    | Lưu ở đâu                         | Dùng cho                                                                |
| ---------------------------- | --------------------------------- | ----------------------------------------------------------------------- |
| **Product `purchase_price`** | `products.purchase_price`         | PO mua NVL, inventory valuation, **đầu vào** tính BOM                   |
| **Production BOM cost**      | Không lưu trên BOM — tính runtime | UI BOM (`ProductionBomLineCostCalculator` → `ProductUnitPriceResolver`) |
| **Estimate Recipe BOM**      | `estimate_bom_lines`              | Margin OEM / VP approval — **tách** khỏi Product FG                     |

### 2.3 ⚠️ Không nhầm: **Purchase Information** (có sẵn) vs **Cost from BOM** (đề xuất P1)

Trên form Product (`purchase-products/partials/product-form-fields.blade.php`) đã có checkbox **Purchase Information** — **không phải** tính năng BOM price PM mới đề xuất.

|                                    | **Purchase Information** (hiện tại)                   | **Cost from BOM** (kế hoạch P1 — chưa code)            |
| ---------------------------------- | ----------------------------------------------------- | ------------------------------------------------------ |
| **Field DB**                       | `products.purchase_information` (0/1)                 | Đề xuất: `cost_from_bom` hoặc config tenant            |
| **Khi bật**                        | Hiện **Cost price** — nhập giá vốn **tay**            | Ẩn nhập tay; hiện cost **read-only từ BOM**            |
| **Khi tắt**                        | **Ẩn** Cost price; lưu `purchase_price = null`        | _(chưa có — không áp dụng)_                            |
| **Mục đích gốc (Purchase module)** | SP **có** theo dõi mua hàng / giá vốn (NVL, hàng mua) | SP **FG sản xuất** — cost lấy từ BOM, sync khi lưu BOM |
| **Liên quan BOM?**                 | **Không** — chỉ show/hide ô nhập                      | **Có** — gắn Production BOM                            |

**Công dụng thực tế của Purchase Information hôm nay**

- **Tick:** sản phẩm cần **Cost price** (bắt buộc khi lưu) — dùng cho PO mua, tồn kho, và **đầu vào** tính tổng BOM (cost NVL).
- **Bỏ tick:** sản phẩm **không** khai báo giá vốn trên catalog (ví dụ chỉ bán, không mua / không cần cost) → Cost price biến mất trên form.

**JS:** `purchase-products/ajax/edit.blade.php` — `#purchase_information` change → add/remove class `d-none` trên `.purchase_information`.

**Kết luận triển khai:** **Không** tái sử dụng checkbox Purchase Information cho BOM FG — logic ngược (tắt = ẩn cost, bật = nhập tay). P1 cần **checkbox/flag riêng** cho FG manufactured + service sync BOM → `purchase_price`.

---

### 2.4 File / class chính

| Khu vực                | Path                                                                                        |
| ---------------------- | ------------------------------------------------------------------------------------------- |
| Tính cost dòng BOM     | `Modules/Production/Support/ProductionBomLineCostCalculator.php`                            |
| Resolve purchase price | `Modules/Warehouse/Services/ProductUnitPriceResolver.php`                                   |
| Lưu BOM                | `Modules/Production/Http/Controllers/ProductionBomController.php`                           |
| Copy BOM → báo giá     | `app/Services/Estimates/EstimateProductionBomCopier.php`                                    |
| Margin báo giá         | `app/Services/Estimates/EstimateRecipeMarginSummary.php`                                    |
| Convert Estimate → SO  | `app/Http/Controllers/EstimateController.php` (`convertToSalesOrder`)                       |
| Form cost product      | `Modules/Purchase/Resources/views/purchase-products/partials/product-form-fields.blade.php` |

### 2.5 Sau Production Order complete

- Xuất/nhập kho: `Modules/Production/Services/ProductionPostingService.php`
- **Không** cập nhật `purchase_price` FG sau complete (baseline)

---

## 3. Quyết định PM cần chốt

Ghi kết quả vào bảng **Decision log** (mục 8).

| #   | Quyết định                                  | Lựa chọn A (đề xuất)                                                  | Lựa chọn B                    |
| --- | ------------------------------------------- | --------------------------------------------------------------------- | ----------------------------- |
| D1  | FG sản xuất — cost lấy từ đâu?              | **BOM (standard)**                                                    | Nhập tay trên Product         |
| D2  | UI Product — checkbox PM đề xuất            | **“Cost from BOM”** (field mới — **không** dùng Purchase Information) | Chỉ ẩn cost, không sync       |
| D3  | **Khi nào** sync BOM → FG `purchase_price`? | **Khi lưu BOM** (P1)                                                  | Thêm **khi complete PO** (P2) |
| D4  | SP không có BOM / mua sẵn                   | Luôn nhập cost tay                                                    | —                             |
| D5  | Đổi cost NVL sau PO mua                     | Cập nhật `purchase_price` NVL → **re-save BOM** hoặc job recalc FG    | Manual                        |
| D6  | Tenant chỉ B2B (không Production)           | Feature flag **tắt** — không đổi UX                                   | —                             |

---

## 4. Quy trình nghiệp vụ mục tiêu

### 4.1 Phân loại sản phẩm

| Loại                 | Cost                             | Form Product                                       |
| -------------------- | -------------------------------- | -------------------------------------------------- |
| NVL / packaging      | Nhập tay (bắt buộc nếu SX / OEM) | Cost price **hiện**                                |
| FG mua sẵn           | Nhập tay                         | Cost price **hiện**                                |
| FG sản xuất (có BOM) | **Từ BOM**                       | **Ẩn** nhập tay; hiện _Standard cost from BOM: $X_ |

### 4.2 Luồng end-to-end (chuẩn)

```
NVL purchase_price đủ
  → Tạo BOM FG
  → Lưu BOM → sync FG purchase_price = tổng BOM
  → Quotation: Load BOM (recipe) + giá bán
  → Duyệt margin (recipe vs giá bán)
  → SO / Invoice: giá bán only
  → Production Order → complete → tồn kho FG theo purchase_price đã sync
```

### 4.3 Ví dụ số — Nasi Lemak

| Bước         | Document         | Số (mục tiêu sau triển khai)        |
| ------------ | ---------------- | ----------------------------------- |
| NVL + BOM    | BOM UI           | **S$3.70** / phần                   |
| Product FG   | `purchase_price` | **S$3.70** (sync, không S$10 tay)   |
| Estimate     | Bán 1.000 × S$12 | Doanh thu S$12.000; recipe ~S$3.700 |
| SO / Invoice | Giá bán          | S$12.000 — **không đổi**            |
| Tồn FG 1.000 | Inventory        | ~S$3.700 (không S$10.000)           |

---

## 5. Phased implementation

### Phase 0 — Không dev (ngay)

- [ ] PM + Finance ký **SOP tạm:** NVL bắt buộc cost; FG có BOM không tin cost tay trên product
- [ ] Training sales: margin OEM = Recipe BOM; SO = giá bán
- [ ] Pilot tenant: kiểm tra NVL thiếu `purchase_price` → BOM = 0

**Effort:** 0 dev

---

### Phase 1 — MVP (đề xuất sprint 1, ~1 tuần)

**Mục tiêu:** Checkbox + sync standard cost khi lưu BOM. **Không** đổi B2B pricing.

| ID    | Task                     | Chi tiết kỹ thuật                                                                                   | Done |
| ----- | ------------------------ | --------------------------------------------------------------------------------------------------- | ---- |
| P1-01 | Migration / flag product | Cột `cost_from_bom` (bool) hoặc config + chỉ áp FG `forBomOutput()`                                 | ☐    |
| P1-02 | Config tenant            | `production.cost_sync.bom_drives_fg_purchase_price` (default false)                                 | ☐    |
| P1-03 | Service sync             | `ProductionBomFgCostSyncService`: `summarizeSavedLines()` → update `products.purchase_price` output | ☐    |
| P1-04 | Hook save BOM            | Gọi sync sau `ProductionBomController` store/update (transaction)                                   | ☐    |
| P1-05 | UI Product               | Checkbox **Cost from BOM**; ẩn `#purchase_price` khi bật; read-only label + link BOM                | ☐    |
| P1-06 | Validation               | Không cho bật `cost_from_bom` nếu chưa có BOM active; cảnh báo NVL thiếu cost                       | ☐    |
| P1-07 | Tests                    | Feature: save BOM → FG price; flag off → không đổi; B2B SO price unchanged                          | ☐    |
| P1-08 | UAT checklist            | Mục 7 file này                                                                                      | ☐    |

**Effort ước lượng:** 5–8 ngày dev (1 người quen codebase) + QA

**Out of scope P1:** complete PO actual cost, labor/overhead, auto-sync khi đổi cost NVL

---

### Phase 2 — Actual cost on production complete (~2–3 tuần)

**Mục tiêu:** Sau complete PO, FG cost = cost NVL thực xuất (nếu Finance cần).

| ID    | Task                                                                   | Done |
| ----- | ---------------------------------------------------------------------- | ---- |
| P2-01 | Phân tích posting / consumption — lưu unit cost tại thời điểm xuất     | ☐    |
| P2-02 | Rollup actual → FG `purchase_price` (hoặc bảng `product_cost_history`) | ☐    |
| P2-03 | Policy: standard (save BOM) vs actual (complete) — config              | ☐    |
| P2-04 | Audit log khi cost FG đổi                                              | ☐    |
| P2-05 | Finance UAT + regression inventory                                     | ☐    |

**Blocker:** Hiện consumption **chưa** lưu unit cost riêng — cần design trước khi estimate chính xác.

---

### Phase 3 — Backlog (optional)

- [ ] Job recalc FG khi `purchase_price` NVL đổi (GRN / adjust stock)
- [ ] Đồng bộ logic cost Recipe BOM = Production BOM resolver (UOM conversion thống nhất)
- [ ] Báo cáo COGS / margin SO vs BOM
- [ ] Labor + overhead trên BOM

---

## 6. Ảnh hưởng theo chứng từ (không đổi vs đổi)

| Chứng từ                  | Sau P1                                    | Ghi chú                                      |
| ------------------------- | ----------------------------------------- | -------------------------------------------- |
| **PO mua NVL**            | Không đổi                                 | Vẫn gợi ý cost NVL                           |
| **Estimate / Recipe BOM** | Không bắt buộc đổi                        | Vẫn copy NVL cost; có thể align resolver sau |
| **SO / Invoice B2B**      | **Không đổi**                             | Giá bán + Pricing tier                       |
| **Production BOM UI**     | Không đổi                                 | Đã tính cost                                 |
| **Product FG cost**       | **Đổi** khi lưu BOM (nếu flag + checkbox) | Sync `purchase_price`                        |
| **Inventory valuation**   | **Đổi** theo FG cost mới                  | Finance cần biết                             |

---

## 7. UAT checklist (Phase 1)

**Setup:** FG Nasi Lemak; NVL có cost; BOM tổng ≈ S$3.70; FG cost tay cũ S$10.

- [ ] Bật flag tenant + tick **Cost from BOM** trên FG → ô cost ẩn, hiện BOM cost read-only
- [ ] Lưu BOM → Product FG `purchase_price` = tổng BOM (~3.70)
- [ ] Tạo SO B2B → giá bán **không** phụ thuộc cost FG
- [ ] Convert Estimate → SO → giá bán giữ nguyên; recipe không copy sang SO
- [ ] Complete PO → tồn FG × cost ≈ khớp BOM (không còn 10.000 nếu 1000 × 3.70)
- [ ] SP mua sẵn (không tick) → vẫn nhập cost tay bình thường
- [ ] Flag tenant **off** → hành vi như hiện tại (regression)

**Tests gợi ý:** `tests/Feature/ProductionBomFgCostSyncTest.php` (tạo khi implement)

---

## 8. Decision log (PM điền)

| Ngày | Quyết định                       | Người chốt | Ghi chú |
| ---- | -------------------------------- | ---------- | ------- |
|      | D1–D6                            |            |         |
|      | Phạm vi sprint (P1 only / P1+P2) |            |         |
|      | Tên checkbox UI (EN/VI)          |            |         |

---

## 9. Rủi ro & giảm thiểu

| Rủi ro                                            | Giảm thiểu                                             |
| ------------------------------------------------- | ------------------------------------------------------ |
| NVL thiếu cost → BOM = 0 → sync FG = 0            | Validate trước save BOM; block sync nếu tổng null      |
| Sửa BOM → cost FG nhảy → valuation đổi            | Feature flag; thông báo Finance; optional audit log P2 |
| User hiểu nhầm checkbox “Custom”                  | Đổi label → **Cost from BOM** / **Manufactured**       |
| Tenant B2B thuần                                  | Flag off — zero UX change                              |
| Estimate recipe vs production resolver khác (UOM) | Document; Phase 3 align                                |

---

## 10. Theo dõi tiến độ (cập nhật hàng sprint)

| Sprint | Mục tiêu       | Trạng thái | PR / branch |
| ------ | -------------- | ---------- | ----------- |
|        | P0 SOP         | ☐          |             |
|        | P1 MVP         | ☐          |             |
|        | P2 Actual cost | ☐          |             |

---

## 11. Một câu trả lời stakeholder (EN)

> BOM cost does **not** currently override finished-goods product cost. Planned fix: optional **Cost from BOM** on manufactured products, sync standard cost when BOM is saved — **B2B selling prices unchanged**.

---

_File living doc — cập nhật checkbox ☐ → ☑ khi hoàn thành task; đổi trạng thái header khi PM chốt / ship._
