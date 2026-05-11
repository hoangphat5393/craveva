# P0 — Governance Shadow Yield / UOM (rollup để sign-off)

Ngày cập nhật: 2026-05-09  
Nguồn chi tiết: `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`, `Modules/Production/Config/config.php`.

---

## 1) Mặc định kỹ thuật (baseline)

| Khóa                                              | Giá trị mặc định (config) | Ý nghĩa vận hành                                                                    |
| ------------------------------------------------- | ------------------------- | ----------------------------------------------------------------------------------- |
| `production.phase2.yield_uom_shadow_enabled`      | `false`                   | Không tính `planned_quantity_shadow` cho đối chứng; chỉ chạy planned chuẩn BOM × FG |
| `production.phase2.enforce_variance_approval`     | `true`                    | Vượt tolerance có thể yêu cầu duyệt trước post FG (kết hợp policy company)          |
| `production.phase2.enforce_quality_lock_sales_do` | `true`                    | Khóa giao DO khi production order chưa xong (theo cấu hình rollout)                 |

**Rollback nhanh (pilot):** đặt `yield_uom_shadow_enabled` = `false`; `php artisan config:clear`. Không cần migration.

---

## 2) Điều kiện bật shadow (cần PM + Tech Lead ký)

1. BOM / đơn vị đã map đủ (hoặc chấp nhận sai số có kiểm soát).
2. Có ít nhất một tenant pilot và cửa sổ UAT so sánh `planned` vs `planned_quantity_shadow`.
3. Ghi log P0: ai approve, ngày, phạm vi tenant.

---

## 3) Việc còn lại cho P0-03 (Done / Partial)

| Việc                                          | Owner          | Trạng thái |
| --------------------------------------------- | -------------- | ---------- |
| Sign-off bật shadow cho tenant pilot          | PM + Tech Lead | Pending    |
| Cập nhật execution log + screenshot/tenant id | Tech Lead      | Pending    |
