# Prompt triển khai refactor (dùng cho AI Agent)

Sao chép nguyên prompt dưới đây khi bắt đầu implementation:

---

Bạn hãy triển khai refactor hệ thống theo kế hoạch:

- Bán hàng: `SO -> DO (stock out) -> Invoice`
- Mua hàng: `PO -> GRN (stock in) -> Bill`

Ràng buộc bắt buộc:

1. **Không xóa `sales_shipments` ngay từ đầu.**
2. Chỉ được xóa `sales_shipments` và artifact thừa **sau khi**:
    - flow mới hoàn thiện,
    - test pass,
    - UAT pass,
    - rehearsal migrate staging pass,
    - có rollback plan.
3. Bảo đảm sau refactor xong, luồng SO và PO vẫn đúng và không double-post kho.
4. Mọi bước triển khai phải cập nhật vào file tracker:
    - `FUNC_LOGIC/REFACTOR_SO_DO_PO_GRN_TRACKER_VI.md`
5. Bám kế hoạch master:
    - `FUNC_LOGIC/REFACTOR_SO_DO_PO_GRN_IMPLEMENTATION_PLAN_VI.md`

Yêu cầu thực thi theo phase:

- **Phase 1:** Foundation & Compatibility
    - Chuẩn hóa naming UI/SOP, permission matrix, route mapping.
    - Không phá flow hiện tại.
- **Phase 2:** Build flow mới end-to-end
    - Tạo/chuẩn hóa DO bán và GRN mua đầy đủ lifecycle.
    - Đảm bảo stock outbound/inbound canonical + idempotent + reverse.
- **Phase 3:** Data migration rehearsal
    - Viết migration + dry-run + reconciliation report.
- **Phase 4:** Staging cutover
    - Backup -> deploy -> migrate -> reconcile -> UAT.
- **Phase 5:** Remove legacy
    - Chỉ khi đủ điều kiện mới remove `sales_shipments` và code thừa.

Yêu cầu báo cáo mỗi phase:

- Đã sửa những file nào.
- Đã chạy test nào và kết quả.
- Rủi ro còn lại.
- Tiêu chí để chuyển phase tiếp.

Ưu tiên chất lượng:

- Không dùng shortcut phá dữ liệu.
- Không dùng force/destructive git trừ khi được yêu cầu rõ.
- Có rollback scripts trước mọi thay đổi irreversible.

---
