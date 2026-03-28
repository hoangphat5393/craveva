# Warehouse — Tóm tắt nội bộ (PM / QA / Dev)

**Cập nhật:** 2026-03-28  
**Luồng nghiệp vụ (đọc trước):** [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)  
**Checklist UAT đầy đủ:** [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)  
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

- [ ] **Trước nâng cấp kho:** audit baseline Product / Client / PO / DO / Purchase Inventory / Order / Invoice / Payment — [`WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md`](WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md) (**local** trước push; staging sau `git pull`).
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

_Tài liệu gộp thay thế: `WAREHOUSE_SCOPE_B__`, `WAREHOUSE*UAT_GO_NO_GO_SHEET`, `WAREHOUSE_MIAOLIN_IMPLEMENTATION_PLAN`, `WAREHOUSE_PM_ENG_ALIGNMENT_BRIEF`, `WAREHOUSE_UAT_PRE_IMPLEMENTATION_ANALYSIS`, `WAREHOUSE_PM_GAP_VERIFICATION\*_`, `DINGXIN*\*` (nội dung nghiệp vụ Dingxin nằm trong FLOW).*
