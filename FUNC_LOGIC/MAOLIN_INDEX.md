# MAOLIN — Mục lục tài liệu (điểm vào duy nhất)

**Mục đích:** Giảm rối khi trong `FUNC_LOGIC/` có nhiều file MAOLIN/Miaolin. **Bắt đầu từ file này.**

**Quy ước:** Tài liệu **warehouse** (đa kho, Miaolin, Scope B) — **bắt đầu từ** [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md). Quy trình tổng **PO / DO / SO / Invoice / kho:** [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md). Cập nhật nghiệp vụ chỉ module kho vào [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md); **runbook vận hành + kế hoạch nâng cấp (WUP)** vào [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md); kỹ thuật DB/URL/permission vào [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md); trạng thái PM/QA + audit + prompt UAT vào [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md).

**Trạng thái hệ thống (2026-03):** Chức năng **đa kho (multi-warehouse)** đã có trong codebase: master kho, movement/lô, `default_warehouse_id` khách, cột `warehouse_id` / `batch_number` / ngày trên luồng Purchase Inventory, v.v. Các ghi chú cũ viết _trước_ khi bật đa kho vẫn có giá trị cho **ngữ cảnh** nhưng phải đọc kèm:

- [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) — mục lục warehouse; luồng nghiệp vụ: [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md); runbook + WUP: [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md); kỹ thuật: [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md).

---

## 0) Bắt đầu từ 1 file duy nhất

- **[`MAOLIN_MASTER_GUIDE.md`](MAOLIN_MASTER_GUIDE.md)** — tài liệu **đã gộp** (khuyến nghị đọc thay vì mở 5–10 file).

---

## 1) Nên đọc theo thứ tự này (khi cần đào sâu)

| Thứ tự | File                                                                                                       | Khi nào mở                                                                                                     |
| ------ | ---------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| **1**  | **[`MAOLIN_MASTER_GUIDE.md`](MAOLIN_MASTER_GUIDE.md)**                                                     | Đọc 1 file là đủ: multi-warehouse + mapping + checklist.                                                       |
| **2**  | **[`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md)**                                                 | Map cột Excel → field import / core DB (vận hành import ngay).                                                 |
| **3**  | **[`MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`](MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md)**                   | Kiểm tra “đủ/chưa đủ để import” + thứ tự import theo B2B guide.                                                |
| **4**  | **[`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md)**                         | Phân tích chi tiết từng sheet/cột file trong `PROJECT MAOLIN New/` (bản đầy đủ nhất).                          |
| **4b** | **[`PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md`](PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md)**               | Chỉ **giá & tier pricing:** file nào trong `PROJECT MAOLIN New/`, thống kê dòng, gap vs import Pricing module. |
| **5**  | **[`MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md`](MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md)** | Ghi chú tóm tắt: yêu cầu khách (sync file, GAP) + PDF B2B 規劃書 (chức năng bán hàng).                         |

**Ghi chú:** Các file phân tích chi tiết/legacy/contract đã được **gộp** vào `MAOLIN_MASTER_GUIDE.md` và đã xóa khỏi repo để giảm số lượng file.

---

## 2) Tóm tắt phạm vi 4 nhóm (Product / Client / Inventory / giá)

_Nội dung gộp từ bản tóm `MAOLIN_FOCUSED_SCOPE_\*` (đã xóa để trùng lặp).\_

- **Client:** Khóa `client_code`; kho ưu tiên map qua `designated_warehouse_code` / `designated_warehouse_name` → `default_warehouse_id`. Cột `region` (地區別) vẫn có thể là custom hoặc bổ sung sau.
- **Product:** Khóa `sku`; cần cột giá (`price` / `standard_price`) để import không báo lỗi; sheet giá trong file quote có thể bổ sung wholesale/box/employee.
- **Inventory:** Khóa `warehouse_code` + `sku` (+ lô nếu có); tồn/lô map vào core (`warehouse_id`, `batch_number`, ngày SX/HSD) thay vì custom field kho.
- **Giá / tier:** Bảng giá sheet `產品價格表`; file CSV trong `PROJECT MAOLIN New/` — bảng thống kê [`PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md`](PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md); tier theo khách/kênh — thêm [`FLOW_Pricing_Module_VI.md`](FLOW_Pricing_Module_VI.md), [`PRICING_MODULE_DEV_TASKS.md`](PRICING_MODULE_DEV_TASKS.md).

**Phân loại migrate DB (đã xử lý phần lớn):** Kho + lô trên luồng điều chỉnh tồn mua hàng đã chuyển dần sang cột DB; chi tiết cập nhật trong `WAREHOUSE_MASTER_GUIDE.md` và code migration `purchase_stock_adjustments`.

---

## 3) Các file đã gộp / thay thế (đừng tìm trong repo)

| Cũ                                                      | Việc làm                                                                                                   |
| ------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| `MAOLIN_PM_CLIENT_ONEPAGE.md`                           | Gộp vào `MAOLIN_MASTER_GUIDE.md` (và đã xóa file cũ).                                                      |
| `MAOLIN_FOCUSED_SCOPE_PRODUCT_CLIENT_INVENTORY_TIER.md` | Nội dung tóm lược nằm trong **mục 2** của file này.                                                        |
| `MAOLIN_MULTI_WAREHOUSE_ANALYSIS_AND_PLAN.md`           | Không dùng tên này; dùng [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md).                         |
| `MAOLIN_IMPORT_MAPPING_TEMPLATE_READY_TO_USE.md`        | Không tách file; nội dung “ready to use” nằm trong [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md). |

---

## 4) Tài liệu liên quan ngoài thư mục MAOLIN-only

| File                                                                                                                                                                             | Ghi chú                                                 |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| [`B2B_ERP_PO_DO_INVOICE_GUIDE.md`](B2B_ERP_PO_DO_INVOICE_GUIDE.md)                                                                                                               | PO/DO/Invoice B2B (nghiệp vụ).                          |
| [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) + [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md) + [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md) | Warehouse: mục lục + flow nghiệp vụ + runbook kỹ thuật. |
| [`CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md`](CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md)                                                                                           | Import client (kỹ thuật).                               |

---

_Cập nhật khi thêm/xóa file MAOLIN: sửa bảng mục 1 và mục 3._
