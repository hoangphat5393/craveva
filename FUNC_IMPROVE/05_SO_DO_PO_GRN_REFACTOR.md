# Refactor SO → DO → Invoice & PO → GRN → Bill — tracker (rút gọn pass 8)

**Cập nhật:** 2026-06-16 (doc-to-code reconciliation — current schema đã cutover; Phase 5 chỉ còn cleanup grace-period có điều kiện access-log/sign-off; lịch sử đầy đủ: `git log -- FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR.md`)

**Schema / DROP legacy / ma trận bảng:** [`../FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md`](../FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md)  
**QA vận hành SO/PO/DO/GRN:** [`../FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md`](../FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md)  
**Staging ops (migrate/rehearsal):** [`../docs/STAGING_OPERATIONS.md`](../docs/STAGING_OPERATIONS.md) §5  
**UAT E2E mua-bán-kho:** [`../FUNC_LOGIC/SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`](../FUNC_LOGIC/SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md)

---

## 1. Quyết định kiến trúc (tóm tắt)

| Cũ (tên gây nhầm)              | Mới (ERP)    | Ghi chú vận hành                                      |
| ------------------------------ | ------------ | ----------------------------------------------------- |
| Sales Shipment                 | **Sales DO** | Xuất kho bán — bảng đích `sales_dos*` khi cutover bật |
| Delivery Order (Purchase menu) | **GRN**      | Nhập kho mua — bảng đích `grns*` khi cutover bật      |
| —                              | SO → Invoice | Kế toán; trigger kho qua DO, không qua Invoice        |
| —                              | PO → Bill    | Kế toán; trigger kho qua GRN                          |

**Chiến lược hiện tại:** current schema/runtime đã chạy bảng canonical. Compat code và lệnh migrate/rehearsal vẫn giữ để hỗ trợ DB dump cũ hoặc link cũ trong grace period; không coi đây là flow nghiệp vụ mới.

---

## 2. Trạng thái phase

| Phase | Mô tả ngắn                         | Trạng thái      |
| ----- | ---------------------------------- | --------------- |
| **1** | Naming UI, permission/route compat | **Done**        |
| **2** | Flow E2E + service alias + tests   | **Done**        |
| **3** | Migrate data rehearsal + rollback  | **Done**        |
| **4** | Staging cutover + runtime resolver | **Done**        |
| **5** | Drop legacy tables & dead code     | **Partial**     |

---

## 3. Cutover & config (vận hành)

| Key / flag                        | Giá trị khi cutover active |
| --------------------------------- | -------------------------- |
| `purchase.do_grn_cutover_enabled` | `true`                     |
| `purchase.flow_naming_mode`       | `compat_v2`                |

**Route naming:** UI/redirect dần sang `sales-do.*`, `grn.*`; backend vẫn bridge từ `sales-shipments.*`, `delivery-orders.*` khi cần.

**Runtime:** `SalesDoRuntime` / `GrnRuntime` hiện ưu tiên bảng canonical `sales_dos*` và `grns*`. Legacy aliases chỉ còn để an toàn khi mở DB dump cũ hoặc route/link cũ.

**Schema verified 2026-06-13:** DB hiện có `sales_dos`, `sales_do_items`, `grns`, `grn_items`; không còn bảng legacy `sales_shipments`, `delivery_orders`.

---

## 4. Lệnh Artisan (migrate / rehearsal)

Luôn **`--dry-run` trước**, backup DB trước `--execute`.

| Lệnh                                                   | Mục đích                                                 |
| ------------------------------------------------------ | -------------------------------------------------------- |
| `purchase:sales-do-migration-rehearsal`                | Rehearsal Sales DO                                       |
| `purchase:sales-do-reconcile-report --baseline=<json>` | Đối soát baseline vs hiện tại                            |
| `purchase:sales-do-migrate-data`                       | Migrate header/lines → `sales_dos` (`--execute --force`) |
| `purchase:sales-do-migrate-rollback --manifest=...`    | Rollback theo manifest                                   |
| `purchase:grn-migrate-data`                            | Migrate PO receiving → `grns`                            |
| `purchase:grn-migrate-rollback --manifest=...`         | Rollback GRN                                             |

Chi tiết thứ tự staging: `docs/STAGING_OPERATIONS.md` §5.1–5.9.

---

## 5. Phase 4 — current runtime (Done)

### Checklist

- [x] Backup DB staging, deploy code, migrate schema + data, bật cutover flag
- [x] Runtime Sales DO + GRN trên staging; smoke route/syntax pass
- [x] Schema canonical đang là source of truth (`sales_dos*`, `grns*`)

### Acceptance hiện tại

- [x] SO flow dùng Sales DO canonical
- [x] PO receiving dùng GRN canonical
- [x] Legacy DB tables đã absent trên current DB
- [x] Dev/staging smoke đã pass; UAT nghiệp vụ người dùng cuối tách thành sign-off vận hành nếu cần ký biên bản

**Tham chiếu test:** `SalesDoServiceLifecycleTest`, `GrnService` lifecycle tests, `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`.

---

## 6. Sales DO — Ship action hiện tại (Done core)

**Lý do triển khai:** trước đây người dùng bấm `Ship` trên màn Sales DO overview có thể gặp lỗi thiếu `Ship Qty`, phải đi vòng `Overview -> Edit -> Update -> Overview -> Ship`. Flow hiện tại tách thao tác xuất kho thành form Ship riêng để nhập số lượng/batch và submit một lần.

### Luồng nghiệp vụ đơn giản

1. SO đã tạo Sales DO.
2. Người dùng mở Sales DO detail / overview.
3. Bấm **Ship** -> mở right modal **Ship Delivery Order**.
4. Nhập `Ship Qty` cho từng dòng; dòng không xuất để `0`.
5. Nếu sản phẩm cần batch và `Ship Qty > 0`, bắt buộc chọn batch.
6. Submit **Ship**:
    - lưu ship qty / batch vào dòng DO,
    - validate remaining qty + batch availability,
    - post outbound stock,
    - chuyển DO sang `shipped` khi hợp lệ.

### Business rules giữ lại

| Rule | Ý nghĩa |
| ---- | ------- |
| Status là kết quả của action nghiệp vụ | Không dùng status dropdown để thay thế Ship. |
| `Edit` | Dùng sửa thông tin chứng từ trước khi xuất kho. |
| `Ship` | Dùng xác nhận xuất kho, kiểm dòng hàng, batch và tồn. |
| Nhiều sản phẩm | Form Ship hiển thị nhiều dòng DO. |
| Partial shipment | Cho phép dòng không xuất có `Ship Qty = 0`; tổng ship phải > 0. |
| Batch validation | Batch phải thuộc cùng company, warehouse, product và còn đủ available. |
| Không ship vượt | Không vượt remaining qty hoặc available batch qty. |

**Evidence 2026-06-16:** route `sales-do/{id}/ship-form` + legacy `sales-shipments/{id}/ship-form`; controller `SalesShipmentController::shipForm`; view `sales-shipment/ajax/ship.blade.php`; overview action dùng `openRightModal`; language key `purchase::app.shipDeliveryOrder`.

**Regression:** `SalesDoCreatePageTest` route/view/action label và `PurchaseSalesShipmentTranslationsTest` pass trong lượt triển khai.

---

## 7. Phase 5 — retirement (Partial)

### Trạng thái

- [x] Legacy DB tables đã drop trên current schema
- [x] Runtime không cần legacy tables cho flow chính
- Gated: Grace-period cleanup code còn lại: route aliases, migration/rehearsal commands, legacy compatibility branches — **không gỡ bừa** khi chưa có access log/sign-off

### Checklist cleanup còn lại

- Gated: Kiểm tra access log/route usage trước khi gỡ `sales-shipments.*` alias cũ
- Gated: Giữ migration/rehearsal commands nếu còn phải restore DB dump cũ
- Gated: Remove compat/dead code chỉ sau khi staging/prod không còn hit legacy route
- Gated: Cập nhật thêm `SALES_FULFILLMENT_SCHEMA_MATRIX` nếu chiến lược restore DB cũ thay đổi

### Audit 2026-06-16

- Code hiện vẫn có runtime/route bridge như `SalesDoRuntime`, `GrnRuntime`, `FlowPermission`, `SalesShipmentDataTable` và `DeliveryOrderDataTable` để chống lỗi khi còn link/browser state/DB dump cũ.
- Không thực hiện xóa alias hoặc command rehearsal trong lượt này vì đây là cleanup phá tương thích; cần access log xác nhận không còn hit `sales-shipments.*` / `delivery-orders.*` và cần quyết định restore DB dump cũ.
- Kết luận: Phase 5 giữ **Partial / gated cleanup**. Bước code tiếp theo chỉ nên làm sau khi có log 7-14 ngày không còn legacy route hoặc PM/ops chốt không cần restore dump cũ.

### Thứ tự cutover an toàn (nhắc)

1. Backup → deploy compat code → migrate schema
2. `migrate-data --dry-run` → report → execute
3. Reconcile → bật flag → UAT
4. Grace period giữ bảng cũ → **chỉ drop khi sign-off**

---

## 8. Definition of Done (khi đóng Phase 5)

- Unit: idempotent post outbound/inbound, reverse, anti-double-post
- Feature: lifecycle Sales DO + GRN; invoice/bill theo policy
- UAT: 2 happy path + partial + rollback scenario

---

## 9. Rủi ro trọng yếu

| Rủi ro               | Giảm thiểu                               |
| -------------------- | ---------------------------------------- |
| Double stock posting | Trigger canonical + test idempotent      |
| Mất mapping migrate  | dry-run + reconcile + backup             |
| Permission/UI miss   | Matrix Phase 1 + smoke theo role         |
| Rollback khó         | Rollback manifest bắt buộc trước cutover |

---

_Lịch sử tracker chi tiết (2026-03-30), permission matrix đầy đủ, issue log append-only: `git show` bản file trước pass 8._
