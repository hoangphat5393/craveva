# Warehouse — Tóm tắt nội bộ (PM / QA / Dev)

**Cập nhật:** 2026-03-28  
**Quy trình tổng (PO / DO / SO / Invoice / Kho):** [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)  
**Luồng chỉ module kho:** [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)  
**Checklist UAT:** [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)  
**Mục lục:** [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)

File này **gộp** nội dung từ: nhật ký Scope B, prompt Cursor, Go/No-Go sheet, kế hoạch Miaolin, alignment PM (EN), phân tích pre-UAT, gap verification — để **chỉ còn một chỗ** tra trạng thái và quyết định.

---

## 1) Trạng thái tổng quan

| Hạng mục                                                                                        | Trạng thái                                                                      |
| ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| Warehouse core (master, adjustment, transfer, movement, inbound PO/DO, purchase inventory sync) | Code có; **cần UAT** theo checklist                                             |
| **Scope B** — outbound bán qua `StockMovementService`                                           | **Code v1 trong repo**; bật `WAREHOUSE_SALES_OUTBOUND_ENABLED=true` + migration |
| Go/No-Go Miaolin “inventory-aware sales”                                                        | **No-Go** cho đến khi QA có **bằng chứng UAT staging** (movement + tồn)         |
| Test tự động                                                                                    | Unit tests Scope B; feature test DB đầy đủ = tùy chọn                           |

---

## 2) Scope A vs B (ý nghĩa)

| Scope | Ý nghĩa “xong”                                                                                                                                                          |
| ----- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **A** | Vận hành kho: CRUD kho, điều chỉnh tồn, chuyển kho, ledger, nhập mua (một inbound chuẩn), sync inventory, quyền.                                                        |
| **B** | A + **bán hàng trừ tồn đúng kho** + reversal/idempotency + **không** mutate legacy `PurchaseStockAdjustment` trên payment khi flag bật + kiểm tra tồn invoice theo kho. |

---

## 3) Ước lượng effort (tham chiếu — không phải hợp đồng)

| Phạm vi                             | Dev (ngày-person, gợi ý) | QA/UAT (gợi ý) | Wall-clock điển hình                 |
| ----------------------------------- | ------------------------ | -------------- | ------------------------------------ |
| A — chỉ kho vận hành                | ~3–10                    | ~2–6           | ~1–2 tuần                            |
| B — đủ Miaolin checklist + outbound | ~10–20                   | ~4–10          | ~3–5 tuần sau khi chốt quyết định PM |
| A rồi B nối tiếp                    | ~13–30 tổng              | ~6–16 tổng     | ~4–7 tuần + buffer                   |

**Cursor AI:** hỗ trợ viết doc/code lặp; **không** thay QA hay quyết định nghiệp vụ; tiết kiệm thực tế code/doc thường ~10–25%.

---

## 4) Điều kiện Go/No-Go (tóm tắt)

| Điều kiện                                                      | Owner     | Trạng thái (2026-03)                         |
| -------------------------------------------------------------- | --------- | -------------------------------------------- |
| Chỉ **một** inbound canonical (PO **hoặc** DO) trên prod       | PM + Tech | [ ] cấu hình + smoke                         |
| Sales outbound qua `StockMovementService` đúng trigger đã chốt | Dev       | [x] **code v1** — [ ] **bằng chứng staging** |
| Reversal (xóa/sửa invoice…)                                    | Dev       | [x] **code v1** — [ ] **UAT**                |
| `PaymentObserver` không sửa legacy stock khi flag ON           | Dev       | [x] code + unit test                         |
| Checklist pass ≥ ~95%, không blocker                           | QA        | [ ]                                          |

**No-Go hiện tại:** thiếu **kết quả UAT checklist** + evidence (screenshot/movement) sau khi deploy Scope B lên staging.

---

## 5) Xác minh gap PM (đối chiếu code — cập nhật sau Scope B)

| Gap (UAT)                       | Kết quả verify                                                  | Ghi chú (2026-03)                                                            |
| ------------------------------- | --------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| **A. Thiếu sales outbound**     | Trước: đúng (thiếu). **Nay:** đã có **v1** khi bật env + module | Vẫn cần UAT                                                                  |
| **B. Nhập đôi PO+DO**           | Đúng (rủi ro config)                                            | Chỉ bật một inbound; có log cảnh báo nếu cả hai true                         |
| **C. Payment legacy không kho** | Đúng                                                            | Khi `WAREHOUSE_SALES_OUTBOUND_ENABLED`: observer payment **bỏ** chỉnh legacy |
| **D. UI batch/expiry**          | Đúng (medium)                                                   | Service hỗ trợ; form có thể chưa đủ                                          |
| **E. Ledger deep-link**         | Low                                                             | Backlog                                                                      |

---

## 6) Câu hỏi PM nên chốt (Section 3 alignment — rút gọn)

1. **Trigger outbound:** Giữ v1 (lưu invoice không draft) hay đổi sang paid / sau giao hàng?
2. **Chọn kho:** Giữ fallback client default + kho mặc định công ty hay thêm **warehouse_id từng dòng** invoice?
3. **Reversal:** Return/refund/void sau chốt — quy trình và có cần thêm luồng không?
4. **Scope sign-off:** Chỉ A hay đủ B cho Miaolin?

**Ask một dòng tới PM:** _Xác nhận rollout chỉ Scope A hay full Miaolin (B), và duyệt trigger + chọn kho để lịch & estimate ổn định._

---

## 7) Thành phần code Scope B (tham chiếu nhanh)

| Thành phần                       | Vị trí                                                                                                    |
| -------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Service                          | `Modules/Warehouse/Services/InvoiceWarehouseStockService.php`                                             |
| Migration / model posting        | `2026_03_28_120000_create_invoice_warehouse_stock_postings_table.php`, `InvoiceWarehouseStockPosting.php` |
| Observer                         | `app/Observers/InvoiceObserver.php`                                                                       |
| Invoice validation + transaction | `app/Http/Controllers/InvoiceController.php`                                                              |
| Tắt legacy payment stock         | `Modules/Purchase/Observers/PaymentObserver.php`                                                          |
| Config                           | `warehouse.sales_outbound_enabled`, `WAREHOUSE_SALES_OUTBOUND_ENABLED`                                    |
| Test                             | `tests/Unit/InvoiceWarehouseStockScopeBTest.php`                                                          |

**Guard seeding:** sync/reverse **không** chạy khi `config('app.seeding')` (db:seed); **không** dùng `runningInConsole` để tránh chặn queue/PHPUnit.

**Quyết định kỹ thuật v1 (có thể đổi sau PM):**

- Outbound: invoice **không draft**, **không credit note**.
- Kho: client default → company default → kho active đầu tiên.
- Reverse: xóa invoice; update = reverse + post lại.

---

## 8) Việc còn lại (checklist nội bộ)

- [ ] **Trước nâng cấp kho:** audit baseline — **§10** dưới đây (**local** trước push; staging sau `git pull`).
- [ ] Staging: migrate + bật flag có kiểm soát.
- [ ] Chạy [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md) (đặc biệt mục sales outbound).
- [ ] Cập nhật bảng Go/No-Go trong file này khi QA có evidence.
- [ ] (Tùy chọn) Feature test DB đầy đủ.

---

## 9) Phụ lục — Non-negotiables khi implement (giữ từ prompt gốc)

1. Mọi thay đổi tồn bán hàng qua `StockMovementService` (không sửa tay batch/tồn từ controller).
2. `DB::transaction` cho đơn vị nghiệp vụ đã chọn (sync invoice bọc trong transaction; controller bọc `save()` khi bật).
3. Idempotency (bảng posting).
4. Feature flag rollout.
5. Validation tồn theo kho khi outbound bật.
6. Company scope + exception nghiệp vụ warehouse.
7. Một inbound canonical + cảnh báo nếu hai cờ inbound cùng bật.

---

## 10) Audit trước khi triển khai / nâng cấp Warehouse

**Mục đích:** Đảm bảo Product, Client, PO, DO, Inventory, Order, Invoice, Payment **ổn** trước khi bật rộng kho / `WAREHOUSE_SALES_OUTBOUND_ENABLED`. **Phạm vi:** chỉ Craveva (không DigiWin).

**Quy trình:** code chạy ổn **local** (migrate, PHPUnit, smoke) → **push** → staging **pull** → smoke bổ sung.

### 10.1 Tài liệu tham chiếu khi audit

| File                                                                                                                       | Dùng khi                              |
| -------------------------------------------------------------------------------------------------------------------------- | ------------------------------------- |
| [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)                                 | Thứ tự nghiệp vụ PO/DO/SO/Invoice/kho |
| [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md)                                                                         | Observer, bảng DB, Scope B            |
| [`FLOW_ADD_PRODUCT.md`](FLOW_ADD_PRODUCT.md) / [`FLOW_ADD_INVENTORY.md`](FLOW_ADD_INVENTORY.md)                            | Product / Purchase Inventory          |
| [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md)                                                       | Rủi ro legacy (đọc kèm note Scope B)  |
| [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)                                                                   | URL, permission                       |
| [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)                                                 | UAT sau audit                         |
| [`SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md`](SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md) | Client + `default_warehouse_id`       |

### 10.2 Checklist baseline (A–J)

**Quy ước:** `[ ]` chưa kiểm — `[x]` pass — **FAIL** ghi mô tả. **N/A** nếu tenant không dùng luồng.

**A. Môi trường:** branch/commit; module Warehouse trạng thái; ghi `.env` inbound (chỉ **một** PO **hoặc** DO chuẩn); `WAREHOUSE_ALLOW_NEGATIVE_STOCK`.

**B. Product:** tạo/import SKU; hàng hóa vs service; custom field không vỡ form.

**C. Client:** lưu OK; `default_warehouse_id` đúng company; import designated warehouse nếu có.

**D. PO:** có `warehouse_id` + `product_id`; `delivered` → inbound khi cờ PO bật; không nhập đôi cùng lô.

**E. DO inbound:** N/A hoặc `received` + cờ DO; không bật đôi inbound với PO cho cùng lần nhận.

**F. Purchase Inventory:** phiếu + import tồn — delta movement đúng.

**G. Order (SO):** tạo/chuyển trạng thái OK; nhớ 1 SO → tối đa 1 Invoice gắn `order_id`.

**H. Invoice & Payment:** trước bật Scope B — lưu/xóa/draft không lỗi; sau bật flag — outbound, sửa, xóa, `PaymentObserver` không legacy lệch.

**I. Warehouse smoke:** 2 kho, default; điều chỉnh ±; chuyển kho; movement filter; xóa kho có tồn bị chặn.

**J. Hồi quy:** multi-company; quyền; log không spike lỗi.

### 10.3 Thứ tự khuyến nghị

1. Local: `migrate`, PHPUnit warehouse/invoice, checklist **A→J**.
2. Push → staging pull → smoke.
3. Bật Scope B → lặp **H** (outbound) + **I**.
4. [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md) sign-off.
5. Prompt dev (nếu còn gap): **§11** dưới đây.

### 10.4 Sign-off audit

| Vai trò    | Tên | Ngày | Ghi chú |
| ---------- | --- | ---- | ------- |
| QA / Owner |     |      |         |
| Tech       |     |      |         |

---

## 11) Cursor prompt UAT + gap + PM “đa kho cơ bản”

**Trước prompt:** hoàn thành **§10** (ưu tiên local).

**Nguồn đọc:** `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`, `WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`, `WAREHOUSE_MASTER_GUIDE.md`, file này §1–9.

### 11.1 Prompt dán vào Cursor Agent

```
You are working in the Craveva Laravel repo (Modules/Warehouse + Purchase + Invoice observers).

GOAL
Close gaps so the Warehouse module passes internal UAT in FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md sections A–I, WITHOUT DigiWin/external ERP integration.

SCOPE
- In scope: warehouse master CRUD, bulk, Excel import, stock adjustment, transfer, movements ledger, PO-delivered OR DO-received inbound (single canonical), Purchase Inventory sync, permissions, validation, UX smoke, Scope B verify/fix only.
- Out of scope: DigiWin files, morning import from external ERP, Dingxin two-step reserve/outbound parity, new public APIs unless checklist requires.

PROCESS
1) Read WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md. Per subsection A–I: PASS / GAP / CONFIG-ONLY.
2) Fix GAP minimally: StockMovementService, DB::transaction, WarehouseBusinessException, existing permissions.
3) Critical: InvoiceWarehouseStockService + flag — idempotency, reversal, tests/Unit/InvoiceWarehouseStockScopeBTest.php.
4) High: one inbound path only.
5) Medium: batch/expiry on adjustment UI if checklist requires.
6) Low: movement deep links — backlog if not quick.
7) Run PHPUnit warehouse-related tests.

DELIVERABLES
- Code + tests; summary table section → PASS/GAP.
```

### 11.2 Gap so với UAT (tóm tắt)

| Mức        | Nội dung                                                                                            |
| ---------- | --------------------------------------------------------------------------------------------------- |
| Bằng chứng | Verify A–I local/staging; Scope B movement + reversal.                                              |
| Medium     | UI lô/HSD điều chỉnh/chuyển kho có thể hạn chế.                                                     |
| Low        | Deep link movement; stub API. Không làm sort_order / kéo thả kho.                                   |
| Cấu hình   | Một inbound; `WAREHOUSE_ALLOW_NEGATIVE_STOCK`; bật `WAREHOUSE_SALES_OUTBOUND_ENABLED` khi test bán. |

### 11.3 PM nói multi warehouse “cơ bản” — UAT thực tế gồm gì?

So với WMS lớn thì “cơ bản” đúng; so với “chỉ nhiều kho” thì UAT đã thêm: import/bulk kho, điều chỉnh ±, chuyển kho, ledger, PO/DO + sync inventory, permission, **bán trừ tồn (Scope B)**.

**Một dòng tới PM:** _Đa kho trong UAT gồm vận hành + mua nhập + sổ cái + phân quyền + nhánh invoice outbound khi bật flag._

---

_Tài liệu đã gộp vào file này: audit trước upgrade và prompt Cursor (tên file cũ đã xóa). PM questionnaire tiếng Anh: phụ lục cuối trong `WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md`._
