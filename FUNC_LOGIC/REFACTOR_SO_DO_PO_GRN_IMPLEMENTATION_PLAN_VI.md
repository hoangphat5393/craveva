# Kế hoạch triển khai chi tiết — Refactor `SO -> DO -> Invoice` và `PO -> GRN -> Bill`

**Ngày tạo:** 2026-03-30  
**Phạm vi:** Refactor kỹ thuật thật sự, nhưng chỉ xóa `sales_shipments` sau khi flow mới đã hoàn thiện + test pass + UAT pass + rehearsal staging pass.  
**Nguyên tắc vàng:** Không làm big-bang phá luồng hiện tại.

---

## 0) Mục tiêu và ràng buộc bắt buộc

## Mục tiêu

- Bán: chuẩn hóa thành `SO -> DO (stock out) -> Invoice`.
- Mua: chuẩn hóa thành `PO -> GRN (stock in) -> Bill`.
- Giảm nhầm lẫn tên gọi và artifact lộn xộn.

## Ràng buộc bắt buộc

- Không xóa `sales_shipments` ở đầu dự án refactor.
- Không cho phép double stock posting inbound/outbound.
- Chỉ cutover khi có test + UAT sign-off.
- Phải có rollback plan trước mỗi phase có thay đổi DB.

---

## 1) Kiến trúc target (sau refactor)

- **Sales DO** là chứng từ outbound chính cho SO.
- **GRN** là chứng từ inbound chính cho PO.
- Kho được điều phối bằng 1 canonical trigger cho mỗi chiều:
    - outbound: trigger tại Sales DO `shipped` (hoặc trạng thái chốt tương đương),
    - inbound: trigger tại GRN `received`.
- Invoice/Bill giữ vai trò kế toán, không thay trigger kho nếu không được thiết kế rõ.

---

## 2) Kế hoạch theo phase

## Phase 1 — Foundation & Compatibility (không phá luồng cũ)

### Công việc

- Chốt domain model mới:
    - Sales DO entity/service/route/permission.
    - GRN entity/service/route/permission.
- Giữ `sales_shipments` hoạt động song song (compat mode).
- Chuẩn hóa naming UI/SOP:
    - Sales side dùng thuật ngữ DO bán.
    - Purchase receiving dùng thuật ngữ GRN.
- Bổ sung feature flag/cấu hình cutover.

### Deliverables

- Tài liệu mapping artifact cũ -> mới.
- Danh sách API/route cũ và mới.
- Permission matrix mới.

### Permission matrix (Phase 1 mapping)

| Nghiệp vụ hiển thị mới    | Permission kỹ thuật hiện tại                                | Ghi chú                                                      |
| ------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------ |
| Sales DO - view           | `view_sales_shipment`                                       | Giữ permission cũ trong Phase 1 để tránh migration role lớn. |
| Sales DO - create         | `create_sales_shipment`                                     | Alias theo UI, không đổi check permission backend ở Phase 1. |
| Sales DO - update         | `update_sales_shipment`                                     |                                                              |
| Sales DO - ship/deliver   | `ship_sales_shipment`                                       |                                                              |
| Sales DO - cancel/reverse | `cancel_sales_shipment`                                     |                                                              |
| GRN - view/manage         | `view_purchase_order` (menu) + `delivery-orders.*` hiện hữu | Phase 1 chưa tách permission GRN riêng.                      |

### Route/API compatibility map (Phase 1)

| Nghiệp vụ hiển thị mới         | Route kỹ thuật giữ nguyên      | Ghi chú                                              |
| ------------------------------ | ------------------------------ | ---------------------------------------------------- |
| Sales DO list/create/edit/show | `sales-shipments.*`            | Phase 1 chỉ đổi wording UI, chưa đổi route name.     |
| GRN list/create/edit/show      | `delivery-orders.*`            | Đây là inbound receiving path hiện hữu của Purchase. |
| GRN đổi trạng thái nhanh       | `delivery-orders.changeStatus` | Giữ API hiện tại để tránh regression DataTable.      |

### Acceptance

- Không regression ở flow hiện tại.
- User đã nhìn thấy tên nghiệp vụ nhất quán trên UI (dù backend cũ chưa bị xóa).

---

## Phase 2 — Xây flow mới end-to-end

### Công việc Sales

- Tạo Sales DO từ SO.
- Sales DO lifecycle: draft/confirm/ship/deliver/reverse/cancel.
- Sync outbound kho idempotent + reverse.
- Invoice tạo sau DO (hoặc theo policy đã chốt), không double outbound.

### Công việc Purchase

- Chuẩn hóa GRN lifecycle cho PO.
- Inbound kho theo GRN `received` với batch/expiry/rule (FIFO/FEFO).
- Bill giữ vai trò AP, không tự post stock nếu không có policy mới.

### Deliverables

- Controller/Service/View/Route/Permission cho flow mới.
- Unit + feature tests cho 2 flow.

### Acceptance

- Test pass cho:
    - `SO -> DO -> stock out -> invoice`.
    - `PO -> GRN -> stock in -> bill`.
- Không double-post kho trong các edge case.

---

## Phase 3 — Data migration rehearsal (local -> staging)

### Công việc

- Viết migration/command chuyển dữ liệu:
    - map `sales_shipments` + items sang DO bán mới.
    - map trạng thái và timestamp quan trọng.
- Chạy dry-run nhiều lần trên local dữ liệu clone staging.
- Sinh báo cáo reconciliation trước/sau migration:
    - tổng qty ship,
    - số movement outbound,
    - số chứng từ theo trạng thái.

### Deliverables

- Script migration có chế độ `--dry-run`.
- Script verify/reconciliation.
- Runbook chuyển dữ liệu staging.

### Acceptance

- Reconciliation đạt ngưỡng sai lệch = 0 (hoặc đúng policy đã chốt).
- Có rollback script kiểm chứng chạy được.

---

## Phase 4 — Staging cutover có kiểm soát

### Công việc

- Backup DB staging.
- Deploy code flow mới.
- Chạy migration dữ liệu (không xóa bảng cũ ngay).
- Bật flag cutover.
- Chạy smoke test + UAT checklist.

### Deliverables

- Biên bản deploy staging.
- Kết quả UAT checklist.

### Acceptance

- Luồng SO và PO chạy đúng.
- Không lỗi critical trong log.
- Stakeholder xác nhận pass.

---

## Phase 5 — Retirement `sales_shipments` (chỉ sau khi pass)

### Điều kiện bắt buộc trước khi xóa

- Phase 1-4 đều complete.
- UAT pass + sign-off.
- Không có bug blocker mở.
- Reconciliation staging pass.

### Công việc

- Gỡ route/controller/view/permission cũ `sales-shipments.*`.
- Xóa bảng cũ sau thời gian grace period.
- Xóa code thừa do compat.
- Cập nhật tài liệu cuối cùng.

### Deliverables

- Migration drop an toàn.
- Danh sách artifact đã remove.
- Postmortem ngắn + lessons learned.

---

## 3) Kiểm thử bắt buộc (Definition of Done)

- Unit test:
    - idempotent posting outbound/inbound,
    - reverse chính xác,
    - guard chống double.
- Feature test:
    - lifecycle Sales DO,
    - lifecycle GRN,
    - tạo invoice/bill theo policy.
- UAT tay:
    - 2 happy paths,
    - partial flows,
    - rollback scenario.

---

## 4) Kế hoạch chuyển dữ liệu lên staging (gợi ý thứ tự)

1. Backup DB + code snapshot.
2. Deploy code có migration ở chế độ compat.
3. Chạy migrate schema mới.
4. Chạy migrate data `--dry-run`, lưu report.
5. Chạy migrate data thực tế.
6. Chạy reconcile script.
7. Bật cutover flag.
8. UAT checklist.
9. Giữ bảng `sales_shipments` trong grace period.
10. Chỉ xóa khi có sign-off.

---

## 5) Rủi ro trọng yếu và cách giảm thiểu

- **Double stock posting** -> khóa trigger canonical + test idempotent.
- **Mất dữ liệu mapping** -> dry-run + reconcile + backup.
- **UI/permission miss** -> permission matrix + smoke test theo role.
- **Khó rollback** -> rollback script bắt buộc trước cutover.

---

## 6) Kết luận thực thi

Kế hoạch này đảm bảo:

- refactor thật sự được thực hiện,
- luồng SO/PO vẫn đúng sau refactor,
- chỉ xóa `sales_shipments` khi mọi thứ đã hoàn thiện và kiểm chứng trên staging.
