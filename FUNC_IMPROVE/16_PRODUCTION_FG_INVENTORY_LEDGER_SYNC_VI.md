# Production FG → Purchase Inventory ledger — lỗ hổng & vá (P1c)

**Ngày ghi:** 2026-05-23  
**Trạng thái:** **Done** (code + backfill command)  
**Liên quan:** [`13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`](13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md) (hai sổ tồn), [`01_PROD_BOM_FG_POLICY_VI.md`](01_PROD_BOM_FG_POLICY_VI.md), [`06_INVENTORY_BUSINESS_IMPROVE.md`](06_INVENTORY_BUSINESS_IMPROVE.md).

---

## 1. Phân loại vấn đề
223223                                        |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| Post FG có ghi kho đúng không?              | **Có** — `StockMovementService::recordInbound` → `warehouse_product_batches`, Trace, Stock batches. |
| Inventory list / Products có tự hiện không? | **Trước P1c: Không** — thiếu dòng `purchase_inventory_adjustment` + `purchase_stock_adjustments`.   |
| Bug code?                                   | **Không** — hành vi cũ nhất quán với implementation.                                                |
| Lỗ hổng (gap)?                              | **Có** — thiếu cầu nối Production → sổ Inventory (Purchase).                                        |
| Sai nghiệp vụ user?                         | **Không** — kỳ vọng “SX xong thấy tồn trên Inventory” là hợp lý; sản phẩm chưa đáp ứng end-to-end.  |

---

## 2. Hai kho trên lệnh sản xuất (nghiệp vụ)

| Trường                | DB                | Ý nghĩa trong code                                            |
| --------------------- | ----------------- | ------------------------------------------------------------- |
| Kho nguyên liệu       | `rm_warehouse_id` | Kho **trừ** NL (Available / post consumption).                |
| Kho sản phẩm sản xuất | `fg_warehouse_id` | Kho **nhập TP** mặc định (form output; có thể đổi từng dòng). |

**Không** mô hình “xưởng vật lý” — chỉ kho logic WMS. Cùng một kho cho cả hai = đơn kho.

---

## 3. Batch: Production vs Inventory

|                                                       | Production (Add finished product)                                     | Add Inventory                                               |
| ----------------------------------------------------- | --------------------------------------------------------------------- | ----------------------------------------------------------- |
| Batch bắt buộc?                                       | **Có** (`batch_number` required) — mã **lô SX** (lot), không phải SKU | **Không** bắt buộc trên form                                |
| Lưu kho (`warehouse_product_batches`)                 | Có — đúng `batch_number` (vd. GAGA)                                   | Sync warehouse thường `batch_number = null` → lô “không mã” |
| Lưu phiếu (`purchase_stock_adjustments.batch_number`) | Metadata trên dòng ledger (P1c)                                       | Tùy chọn                                                    |

**Lý do không thấy trên Inventory trước P1c:** không phải vì batch “tào lao”, mà vì **chưa có dòng ledger** cho SP + kho — dù batch kho đã có.

**Gợi ý mã lô SX:** `PB-{batch_code}`, ca/ngày, hoặc quy tắc nội bộ; một dòng output = một lô trace.

---

## 4. Giải pháp P1c (đã triển khai)

### 4.1 Hành vi mới

Sau `postFinishedGoodsReceipt` (trong cùng transaction, sau `recordInbound`):

1. `ProductionFgInventoryLedgerSync::ensureLedgerLineAfterFgReceipt()`
2. Tìm hoặc tạo `purchase_stock_adjustments` cho `product_id` + `warehouse_id` (gắn `warehouse_id` nếu dòng legacy null).
3. Cập nhật `net_quantity` = tồn warehouse hiện tại (`warehouse_product_stock` hoặc tổng batch).
4. **Không** gọi thêm inbound — tránh double tồn.

### 4.2 Code

| Thành phần | Path                                                                                              |
| ---------- | ------------------------------------------------------------------------------------------------- |
| Service    | `Modules/Purchase/Services/ProductionFgInventoryLedgerSync.php`                                   |
| Hook       | `Modules/Production/Services/ProductionPostingService::postFinishedGoodsReceipt`                  |
| Backfill   | `php artisan production:backfill-fg-inventory-ledger`                                             |
| Test       | `tests/Unit/ProductionFgInventoryLedgerSyncTest.php`, assert trong `ProductionPostingServiceTest` |

### 4.3 Backfill dữ liệu cũ (Bánh kem, v.v.)

```bash
php artisan production:backfill-fg-inventory-ledger --dry-run
php artisan production:backfill-fg-inventory-ledger
php artisan production:backfill-fg-inventory-ledger --company=ID
```

Chạy sau deploy trên staging/production cho FG đã post trước P1c.

---

## 5. Checklist verify

1. Post FG lô mới → **Stock batches** có lô + qty.
2. **Inventory** tìm tên SP / SKU → có dòng, cột Available/Ending khớp tổng kho.
3. **Products** — cột tồn (`stock_on_hand`) phản ánh `net_quantity` đã refresh.
4. Backfill `--dry-run` → `would_create` / `would_refresh` hợp lý → chạy thật.

---

## 6. Backlog còn lại (không thuộc P1c)

| Hạng mục                                                          | Ghi chú                                     |
| ----------------------------------------------------------------- | ------------------------------------------- |
| Inventory list SSOT = `warehouse_product_stock` (không cần phiếu) | `06_INVENTORY_BUSINESS_IMPROVE.md` P0 query |
| Add Inventory: đẩy `batch_number` form vào inbound warehouse      | Tránh lô null vs lô có mã tách biệt         |
| P2 `inventory_mode` theo company                                  | `13_OPENING_STOCK` §4                       |

---

## 7. Lịch sử

| Ngày       | Ghi chú                                                           |
| ---------- | ----------------------------------------------------------------- |
| 2026-05-23 | Phân tích case Bánh kem / GAGA; doc + P1c code + backfill command |
