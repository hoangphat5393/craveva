# Warehouse Scope B — Nhật ký / trạng thái triển khai (theo dõi)

**Cập nhật:** 2026-03-28  
**Phạm vi:** Outbound bán hàng qua `StockMovementService`, reversal, tắt legacy `PurchaseStockAdjustment` trên payment khi bật flag, validation invoice theo kho.

---

## 1) Trạng thái tổng quan

| Mục                       | Trạng thái                                                                        | Ghi chú                                              |
| ------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------------------- |
| Code backend Scope B (v1) | **Đã merge trong repo staging**                                                   | Bật bằng env `WAREHOUSE_SALES_OUTBOUND_ENABLED=true` |
| UAT staging / Go-No-Go    | **Chưa** — cần QA ký                                                              | Xem `WAREHOUSE_UAT_GO_NO_GO_SHEET.md`                |
| Test tự động              | **Unit** (config, DI, PaymentObserver, `shouldPostOutbound`, guard `app.seeding`) | Feature test full DB (happy path) có thể bổ sung sau |

---

## 2) Thành phần đã gắn (file chính)

| Thành phần                                | Vị trí                                                                                                                                                                                |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Service outbound + reversal + idempotency | `Modules/Warehouse/Services/InvoiceWarehouseStockService.php`                                                                                                                         |
| Bảng posting                              | `invoice_warehouse_stock_postings` — migration `2026_03_28_120000_create_invoice_warehouse_stock_postings_table.php`                                                                  |
| Model                                     | `Modules/Warehouse/Entities/InvoiceWarehouseStockPosting.php`                                                                                                                         |
| Observer invoice                          | `app/Observers/InvoiceObserver.php` — `sync` sau created/updated; `reverse` đầu deleting (chỉ bỏ qua khi `isSeedingData()`, không dùng `runningInConsole` — tránh chặn PHPUnit/queue) |
| Controller validation + transaction       | `app/Http/Controllers/InvoiceController.php` — `do_it_later == direct` + wrap `save()` khi bật                                                                                        |
| Legacy payment stock                      | `Modules/Purchase/Observers/PaymentObserver.php` — `return` sớm nếu `warehouse.sales_outbound_enabled`                                                                                |
| Config                                    | `Modules/Warehouse/Config/config.php` — `sales_outbound_enabled`                                                                                                                      |
| Cảnh báo inbound kép                      | `Modules/Warehouse/Providers/WarehouseServiceProvider.php` — log khi PO+DO inbound cùng bật                                                                                           |
| Đăng ký service                           | `WarehouseServiceProvider` — singleton `InvoiceWarehouseStockService`                                                                                                                 |
| Env mẫu                                   | `.env.example` — dòng comment `WAREHOUSE_*`                                                                                                                                           |
| Lang                                      | `Modules/Warehouse/Resources/lang/*/app.php` — `err_no_warehouse_for_invoice`                                                                                                         |
| Test                                      | `tests/Unit/InvoiceWarehouseStockScopeBTest.php`                                                                                                                                      |

---

## 3) Quyết định nghiệp vụ đã “đóng” trong code (v1 — có thể đổi sau khi PM xác nhận)

| Chủ đề                        | Giá trị đang implement                                                                                                                                                                                                                                                           |
| ----------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **OUTBOUND_TRIGGER**          | Đồng bộ tồn khi invoice **không còn draft** và **không phải credit note**: chạy sau `created` / cuối `updated` (observer), tức là sau khi lưu trạng thái & dòng hàng. Không chờ “paid-only” trừ khi PM đổi.                                                                      |
| **WAREHOUSE_RESOLUTION_RULE** | `clientdetails.default_warehouse_id` (hợp lệ trong company) → warehouse `is_default` → warehouse `active` đầu tiên. **Chưa** có `warehouse_id` từng dòng invoice.                                                                                                                |
| **REVERSAL_MATRIX (v1)**      | **Reverse** toàn bộ posting khi **xóa invoice** (deleting). **Sync lại** (reverse rồi post lại) khi **create/update** — bao phủ đổi số lượng/trạng thái draft. Credit note (`credit_note != 0`) không post outbound. **`db:seed`:** không sync/reverse (tránh ghi tồn khi seed). |
| **Validation tồn**            | Khi outbound bật + `direct`: kiểm tra `WarehouseProductStock` theo kho đã resolve; trừ phần “committed” unpaid (invoice khác); update invoice loại trừ chính invoice hiện tại.                                                                                                   |

---

## 4) Việc còn lại / theo dõi

- [ ] Chạy migration trên staging; bật `WAREHOUSE_SALES_OUTBOUND_ENABLED` có kiểm soát.
- [ ] UAT theo `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` (Scope B).
- [ ] PM xác nhận chính thức trigger (draft vs sent vs paid) nếu khác v1.
- [ ] (Tùy chọn) Feature test với DB đầy đủ: outbound, đủ tồn, idempotency, reversal.
- [ ] Cập nhật `WAREHOUSE_UAT_GO_NO_GO_SHEET.md` sau khi QA có bằng chứng.

---

## 5) Tham chiếu nhanh

- Prompt gốc: `WAREHOUSE_SCOPE_B_CURSOR_IMPLEMENTATION_PROMPT.md`
- Alignment PM: `WAREHOUSE_PM_ENG_ALIGNMENT_BRIEF.md`
