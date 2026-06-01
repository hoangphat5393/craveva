# Production Operations Live (VI)

_Mục tiêu: tài liệu vận hành sống cho module Production, dùng để trả lời nghiệp vụ mà không cần rà code._

## 0) Loại sản phẩm (master trước BOM)

| Vai trò Production       | `products.type`                              | Nhãn form (gợi ý)                        |
| ------------------------ | -------------------------------------------- | ---------------------------------------- |
| Đầu ra BOM / TP lệnh SX  | `goods`                                      | Manufactured product                     |
| Component BOM (tiêu hao) | `raw_material`, `semi_finished`, `packaging` | Raw Material / Semi Finished / Packaging |
| Không dùng               | `service`                                    | Service                                  |

- Dropdown BOM **output** = scope `forBomOutput()` → chỉ `goods`.
- Dropdown BOM **component** = scope `forBomComponents()` → ba loại trên.
- SOP khách hàng: [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_VI.md`](../PROJECT%20BIOMIXING/PRODUCTION_MODULE_SOP_VI.md) mục 0–2 · chi tiết [`PRODUCTION_PRODUCT_TYPES_VI.md`](./PRODUCTION_PRODUCT_TYPES_VI.md).

## 1) Lifecycle trạng thái lệnh sản xuất

- `Draft` -> `Released` -> `In progress` -> `Completed`
- Có thể `Cancel` khi:
    - `Draft` (luôn cho phép)
    - `Released` nhưng **chưa post RM** và **chưa post FG**
- Không cho `Cancel` nếu:
    - `In progress`
    - `Completed`

## 2) Quy tắc tồn kho và reserve (đã triển khai)

### Khi Release

- Hệ thống chụp BOM snapshot theo `planned_quantity`.
- Hệ thống kiểm tra tồn khả dụng:
    - `available = on_hand - reserved` (đã trừ reserve Sales DO và lệnh SX khác)
- Nếu không đủ → chặn release (`insufficientRmToReserve`).
- Nếu đủ → tạo reservation RM qua `StockReservationService`, `reference_type = ProductionOrder`.
- Phân bổ lô RM theo **FEFO** (hết hạn sớm trước) — cùng tinh thần xuất kho; không cần user gán lô trước khi release.

**Quyết định PM (đã chốt, trước đây trong plan `19_*`):**

| Sự kiện                       | Reserve?                                                  |
| ----------------------------- | --------------------------------------------------------- |
| Draft (tạo/sửa kế hoạch)      | **Không**                                                 |
| **Release**                   | **Có** — cam kết sản xuất                                 |
| Gán lô RM trên batch (màn lô) | **Không** — chỉ chọn lô để post; reserve đã tạo ở Release |
| Post RM (Deduct)              | Trừ `quantity` thật; không tạo thêm reserve               |

### Khi Cancel (Released)

- Hệ thống `release` toàn bộ reservation active của order.

### Khi Post RM (Deduct raw materials)

- Hệ thống xuất kho RM (`quantity` giảm).
- **UOM:** trước allocation/outbound, `ProductionPostingService::postSingleConsumption` quy đổi planned qty sang **base unit** (`convertToBase`). Bug ticket: [`FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md`](../FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md) — **Fixed 2026-05-20**.
- Khi tất cả batch của order đã post RM -> `consume` reservation của order.
- Sau đó order chuyển `In progress` (nếu trước đó là `Released`).

### Khi Post FG

- Hệ thống nhập kho FG (`warehouse_product_batches` + trace).
- **Inventory ledger (P1c):** đồng thời ghi `purchase_stock_adjustments` / `purchase_inventory_adjustment` để SP hiện trên màn Inventory (Purchase). Backfill lịch sử: `php artisan production:backfill-fg-inventory-ledger`.
- Khi không còn output unposted -> order chuyển `Completed`.

## 3) Material shortage summary (đang vận hành)

- Mục đích: tổng hợp thiếu NVL theo `raw material + warehouse` trên **nhiều lệnh** (không mở từng lệnh để cộng tay).
- Công thức mỗi dòng NVL: `shortage = max(0, tổng_required − available)`; `available = on_hand − reserved` (base UOM).
- **Ý nghĩa status (PM):**
    - **Draft** — lập kế hoạch / mua sớm; **không** có reserve Production.
    - **Released / In progress** — nhu cầu đã cam kết; `available` phải phản ánh reserve Production (+ Sales DO nếu có).
- **Filter status (mặc định):** `active` = **Released + In progress** (nhu cầu đã cam kết, có reserve). Các lựa chọn khác: `draft`, `all` (Draft+Released+In progress), `released`, `in_progress`.
- `Completed` / `Cancelled`: không tính.

## 4) Ý nghĩa nghiệp vụ nhanh cho PM/QA

- `Draft`: lên kế hoạch, chưa reserve Production.
- `Released`: đã cam kết sản xuất, đã reserve RM.
- `In progress`: đã bắt đầu trừ RM / chạy batch.
- `Completed`: đã post FG xong.

## 5) Danh sách flow/test vận hành (canonical)

- Flow test run (VI): `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`
- Flow test run (EN): `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd`
- Test case UAT: `FUNC_IMPROVE/19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`
- Matrix test Biomixing: `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`

## 6) Không dùng các tài liệu kế hoạch cũ

- Các tài liệu plan triển khai đã hoàn tất cần coi là lịch sử.
- Khi cần thông tin vận hành hiện tại, ưu tiên đọc file này + flow/test case ở mục 5.

## 7) Batch — planned RM (ex–Step 1)

- **Không còn** bước checklist / nút _Create planned raw material lines from BOM snapshot_ trên UI mặc định.
- **Release** và **mở màn batch** (chưa có dòng RM): tự ghi `production_batch_consumptions` từ BOM snapshot trên lệnh.
- Checklist batch: **4 bước** (1 = gán lô RM → deduct → FG → post FG).

### Hành vi & code

| Sự kiện                    | Hệ thống                                                                           |
| -------------------------- | ---------------------------------------------------------------------------------- |
| Release (batch `PB-…` đầu) | `ProductionBatchPlannedLinesApplicator` → `applySnapshotToBatch()`                 |
| Mở batch chưa có RM        | Cùng auto-apply                                                                    |
| DB                         | `production_batch_consumptions`; `warehouse_product_batch_id` null đến bước gán lô |

**Code:** `ProductionBatchPlannedLinesPolicy`, `ProductionPlannedConsumptionFromSnapshotService`, `ProductionBatchWorkflowSteps`.

### Config (`production.ui`)

| Key                                       | Mặc định | Ý nghĩa                 |
| ----------------------------------------- | -------- | ----------------------- |
| `auto_apply_bom_snapshot_on_batch`        | `true`   | Tự insert planned lines |
| `show_batch_workflow_step_planned_lines`  | `false`  | Hiện step 1 checklist   |
| `show_apply_planned_from_snapshot_button` | `false`  | Nút magic trên batch    |

**Khôi phục Step 1 thủ công:** đặt 3 key trên = `false`/`true`/`true` → `config:clear` → route `POST .../apply-planned-from-bom-snapshot` vẫn tồn tại.

---

## 8) Phụ lục — audit snapshot (dev/QA)

_Luồng chuẩn: BOM master → lệnh draft (BOM bắt buộc) → Release (reserve + snapshot + batch) → batch 4 bước._

**Config SSOT:** `Modules/Production/Config/config.php` → `production.ui.*`

**File quan trọng:** `ProductionBomFirstPolicy`, `ProductionBatchWorkflowSteps`, `batches/show.blade.php`, `PRODUCTION_PRODUCT_TYPES_VI.md`, SOP `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_*`.

**Lưu ý khi đổi code:**

1. Preview form = BOM **master**; dòng batch = snapshot **trên lệnh** lúc release.
2. Planned qty chia đều theo số batch.
3. Material shortage scopes: `ProductionMaterialSummaryService::statusesForScope()`.
4. Test: `ProductionOrderBomFirstWorkflowTest`, `ProductionPostingServiceTest`.

_Gộp từ `PRODUCTION_BATCH_STEP1_RESTORE_VI.md` + `PRODUCTION_MODULE_AUDIT_VI.md` (pass 4, 2026-05-27)._
