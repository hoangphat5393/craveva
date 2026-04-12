# Sales/Warehouse Docs Index (canonical)

Dùng file này để vào nhanh tài liệu mới nhất, tránh đọc nhầm file cũ.

## Canonical (ưu tiên đọc)

0. **`FUNC_LOGIC/HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md`** — mô hình đơn giản + **§3 đa kho** (kho trên PO/GRN/Sales DO, kho mặc định khách, chuyển kho) + ví dụ A/B/C; chi tiết đa kho → `multi_warehouse_audit_report.md`.
1. `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`
2. `FUNC_LOGIC/AUDIT_FLOWS_SO_PO_DO_GRN_INVOICE_WAREHOUSE_VI.md` (audit E2E + điều kiện ổn định)
3. `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
4. **`FUNC_LOGIC/SALES_RETURN_CREDIT_NOTE_STOCK_VI.md`** — trả hàng bán (Credit Note → nhập kho)
5. **`FUNC_LOGIC/PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md`** — trả hàng mua (Vendor Credit → xuất kho)
6. `PROJECT BIOMIXING/BIOMIXING_FLOW_CRACEVA_GAP.md`
7. `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`
8. `FUNC_LOGIC/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md` (runbook + WUP)
9. **`FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`** — checklist nghiệm thu Mua · Bán · Kho (E2E + phụ lục Warehouse; _stub tên cũ:_ `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`)

**Sơ đồ:** `DIAGRAM/Purchasing - Inventory - Sales End-to-End Current Flow.mmd` (đầy đủ) · `DIAGRAM/Purchasing - Inventory - Sales End-to-End Flow.mmd` (rút gọn).

## Legacy / redirect files (giữ để không gãy link)

- `FUNC_LOGIC/B2B_ERP_PO_DO_INVOICE_GUIDE.md` (stub → `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`)
- `FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` (stub → `UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`)

## Quy tắc dọn tài liệu

- Nếu file cũ trùng nội dung, đổi thành stub ngắn trỏ về canonical.
- Không xóa file nếu còn được link từ `README`, index, hoặc tài liệu PM/QA.
