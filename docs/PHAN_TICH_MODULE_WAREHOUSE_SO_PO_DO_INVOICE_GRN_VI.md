# Phân tích Module Warehouse + SO/PO/DO/Invoice/GRN

**Bản đầy đủ (cập nhật schema + legacy) đã gộp và duy trì trong repo tại:**

→ **[`FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`](../FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md)**

File đó là **nguồn chân lý** cho: **luồng bán hiện tại** (§1), bảng canonical vs legacy (§3), **DROP an toàn** (§4), class PHP (§5), lệnh migrate (§6), **audit gộp** (§7).

**Bản phân tích chi tiết (tiếng Việt) trước đây nằm trong lịch sử Git** của file này; giữ đường dẫn `docs/PHAN_TICH_…` để link cũ không gãy.

**Khác biệt quan trọng:** `GrnRuntime` / `SalesDoRuntime` đang **pin** dùng bảng mới (`return true` cố định). Biến `PURCHASE_DO_GRN_CUTOVER_ENABLED` chỉ ảnh hưởng **alias permission**, không tắt lại bảng cũ qua hai runtime trên — xem mục 3.3 trong file FUNC_LOGIC.
