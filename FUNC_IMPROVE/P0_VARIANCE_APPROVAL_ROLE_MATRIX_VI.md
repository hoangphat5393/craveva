# P0 — Duyệt lệch FG (approve variance) — ma trận quyền (đối chiếu code)

Ngày cập nhật: 2026-05-09  
Phạm vi: action `POST /account/production/outputs/{output}/approve-variance` → `ProductionBatchController@approveOutputVariance`.

---

## Kết luận nhanh

- UI chỉ kiểm tra **`edit_production_orders`** (cùng nhóm quyền với các thao tác khác trên lệnh/lô sản xuất), không có permission riêng `approve_fg_variance`.
- Người dùng **không** có `edit_production_orders` (hoặc không thuộc `all|added|owned|both`) → **403** khi gọi route (giống post FG, sửa output, v.v.).

---

## Ma trận (nghiệp vụ → kỹ thuật)

| Hành động nghiệp vụ                           | Route / method                        | Điều kiện kỹ thuật                                                                                                                   | Ghi chú                          |
| --------------------------------------------- | ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------- |
| Duyệt lệch số lượng FG trước khi post nhận FG | `production.outputs.approve-variance` | Module `production` bật; `edit_production_orders` ∈ {all, added, owned, both}; output thuộc công ty; `posted_at` của output còn null | Gán `approved_by`, `approved_at` |
| Post nhận FG sau khi policy bắt duyệt         | `production.outputs.post-fg-receipt`  | Cùng `edit_production_orders`; thêm rule `ProductionFgQuantityPolicyService` khi `enforce_variance_approval`                         | Xem config `production.phase2`   |

---

## Việc BA/PM cần chốt (ngoài code)

- Vai trò nào trong tổ chức được gán **`edit_production_orders`** để thực hiện duyệt lệch (thường là QA/Production lead — map vào role có sẵn trong hệ thống).
- Trường hợp **tách** quyền “chỉ duyệt lệch, không sửa toàn bộ lệnh”: hiện **chưa** có gate riêng — cần backlog permission mới nếu doanh nghiệp bắt buộc tách biệt.

---

## Bằng chứng code (tham chiếu)

- `Modules/Production/Http/Controllers/ProductionBatchController.php` — `approveOutputVariance`, `assertEditProductionOrders`.
