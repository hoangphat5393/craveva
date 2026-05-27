# Batch Step 1 — Tự sinh planned RM & khôi phục nút thủ công (VI)

**Trạng thái (2026-05):** Step 1 checklist _"Create planned raw material lines from BOM snapshot"_ **đã tắt trên UI**. Hệ thống **tự** ghi `production_batch_consumptions` từ snapshot lệnh (cùng logic nút magic cũ).

**Liên quan:** `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` §7 · BOM-first: `Modules/Production/Config/config.php` → `bom_first_workflow_*`

---

## Hành vi hiện tại

| Sự kiện                                                                       | Việc hệ thống làm                                                                        |
| ----------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **Release** lệnh (tạo batch `PB-…` đầu tiên)                                  | `ProductionBatchPlannedLinesApplicator` → `applySnapshotToBatch()`                       |
| **Mở** `production/batches/{id}` (batch chưa có dòng RM, lệnh đã có snapshot) | Cùng auto-apply (lô cũ / lô tạo trước khi bật config)                                    |
| Checklist batch                                                               | **4 bước**, đánh số **1–4** trên UI (bắt đầu _Gán lô kho_); không còn step planned lines |
| Nút _Create planned…_                                                         | **Ẩn** (route POST vẫn tồn tại)                                                          |

**DB:** Mỗi dòng = 1 row `production_batch_consumptions` (`warehouse_product_batch_id` = null cho đến bước gán lô). **Không** trừ tồn.

**Code:**

- Policy: `Modules/Production/Support/ProductionBatchPlannedLinesPolicy.php`
- Auto: `Modules/Production/Services/ProductionBatchPlannedLinesApplicator.php`
- Logic snapshot → batch: `ProductionPlannedConsumptionFromSnapshotService::applySnapshotToBatch()`
- Workflow: `ProductionBatchWorkflowSteps` (lọc step `planned_lines` khi config tắt)

---

## Config (`production.ui`)

| Key                                       | Mặc định (Biomixing) | Ý nghĩa                                      |
| ----------------------------------------- | -------------------- | -------------------------------------------- |
| `auto_apply_bom_snapshot_on_batch`        | `true`               | Tự insert planned lines (release + mở batch) |
| `show_batch_workflow_step_planned_lines`  | `false`              | Hiện step 1 trên checklist                   |
| `show_apply_planned_from_snapshot_button` | `false`              | Hiện nút magic trên màn batch                |

Sau đổi config: `php artisan config:clear` (nếu dùng config cache).

---

## Khôi phục Step 1 thủ công (pilot / ngoại lệ)

1. Trong `Modules/Production/Config/config.php`:
    - `auto_apply_bom_snapshot_on_batch` => **`false`**
    - `show_batch_workflow_step_planned_lines` => **`true`**
    - `show_apply_planned_from_snapshot_button` => **`true`**
2. Clear config cache.
3. Vận hành: Release → mở batch → user bấm **Create planned raw material lines from bill of materials snapshot** → gán lô → Deduct → …

**Route (không xóa):** `POST production/batches/{batch}/apply-planned-from-bom-snapshot`  
**Controller:** `ProductionBatchController::applyPlannedFromBomSnapshot`

**Lô đã auto trước đó:** Nút không hiện nếu batch đã có consumption (`snapshotApplyRequiresEmptyConsumptions`). Cần xóa dòng planned trên batch (chỉ khi chưa post RM) hoặc tạo batch mới.

---

## Cập nhật tài liệu khi đổi lại

- `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md` — bước "Generate planned raw materials"
- `FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` — §3.2 batch
- Flow MMD test nếu có bước bấm nút magic

---

## Changelog

| Ngày    | Thay đổi                                                  |
| ------- | --------------------------------------------------------- |
| 2026-05 | Bỏ Step 1 UI; auto-apply planned RM; ghi file restore này |
