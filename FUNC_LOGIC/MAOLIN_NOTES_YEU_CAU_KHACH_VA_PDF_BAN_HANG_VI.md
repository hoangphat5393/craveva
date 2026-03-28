# Ghi chú: Yêu cầu khách MAOLIN & nội dung PDF B2B (bán hàng)

**Mục đích:** Lưu lại tóm tắt đã phân tích trong phiên làm việc (không thay thế tài liệu gốc).  
**Cập nhật:** 2026-03-28

---

## 1. Phân tích yêu cầu khách (PROJECT MAOLIN / Miaolin)

### 1.1 Bối cảnh

- **DigiWin** = ERP chính (kế toán, fulfillment).
- **Craveva** = B2B / tích hợp; Phase 1 = **đồng bộ theo file**, không thay DigiWin ngay.
- Ghi chú khách (`PROJECT MAOLIN New/customer do.txt`): **sáng** import vào Craveva; **tối** export Craveva → import DigiWin; lặp **hằng ngày**.

### 1.2 Vận hành (runbook cấp cao)

| Khía cạnh     | Yêu cầu                                                                         |
| ------------- | ------------------------------------------------------------------------------- |
| Tần suất      | 1 lần/ngày; tài liệu kỹ thuật: trước **06:00** (lượt sáng).                     |
| Định dạng     | CSV, UTF-8, có header.                                                          |
| Thứ tự import | `customers` → `products` → `contract_prices` → `inventory` → (nếu có) `orders`. |

### 1.3 File bắt buộc / tùy chọn (theo `PROJECT MAOLIN/MB MIAOLIN_INTEGRATION_ANALYSIS_20260306.md`)

**Bắt buộc:** `customers.csv`, `products.csv`, `contract_prices.csv`, `inventory.csv`.  
**Thiếu mẫu:** `orders.csv` — cần Miaolin/DigiWin cung cấp định dạng chốt.

**Nguồn master (đã cập nhật):** tách Customer / Product riêng; product master ưu tiên file Miaolin Product; giá chuẩn đối soát `import_product.xlsx`; **tier** từ `import_Tier Pricing.xlsx`.

### 1.4 Nghiệp vụ ngầm

- B2B **đa giá** (chuẩn, sỉ, thùng, nhân viên + giá theo khách + hiệu lực).
- **Đa kho + lô**; khóa khuyến nghị: `warehouse_code` + `sku` + lô + HSD.
- **Last year net sales**: lịch sử bán theo ngày–khách–SKU (không phải tồn); nên coi required nếu muốn khớp DigiWin / history / gợi ý mua lại.
- **Chatbot:** file Excel hiện có **không thay** export **đơn** (`order_id` + dòng + trạng thái); báo giá + bán lũy kỳ không đủ cho “đơn thật” — xem `CHAT/maolin_new_folder_vs_chatbot_yeu_cau.md`.

### 1.5 GAP cần chốt với khách

- Mẫu **`orders.csv`** (header + lines, status, ngày…).
- **Truyền file** (SFTP / email / portal / tự động).
- Customer file mới: thiếu **部門**; có **地區別**, **指定庫別名稱** — map `default_warehouse` / custom.
- Product file mới: thiếu `product_source`, giá chuẩn/thùng — rule hợp nhất với file giá/quote.
- BRD `.docx` có thể không còn trong git — lấy bản gốc nếu cần trace pháp lý.

### 1.6 Tài liệu tham chiếu trong repo

| Nội dung                 | File                                                                             |
| ------------------------ | -------------------------------------------------------------------------------- |
| Mục lục MAOLIN           | `FUNC_LOGIC/MAOLIN_INDEX.md`                                                     |
| Gộp nghiệp vụ + sync     | `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`                                              |
| Chi tiết cột file New    | `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`                                |
| Mapping / readiness      | `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md`, `MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md` |
| Phân tích CSV 06/03      | `PROJECT MAOLIN/MB MIAOLIN_INTEGRATION_ANALYSIS_20260306.md`                     |
| Gap chatbot / import     | `CHAT/maolin_new_folder_vs_chatbot_yeu_cau.md`                                   |
| Ghi chú vận hành 2 chiều | `PROJECT MAOLIN New/customer do.txt`                                             |

---

## 2. PDF `Miaolin B2B AI Smart Distribution Platform (contract).pdf`

**Vị trí:** `PROJECT MAOLIN/Miaolin B2B AI Smart Distribution Platform (contract).pdf`

### 2.1 Bản chất tài liệu

- Tiêu đề thực tế trong file: **「苗林實業股份有限公司 B2B AI 智慧分銷平台規劃書」** (bản quy hoạch), **Craveva PTE LTD** gửi **苗林**, **2026-02**, **v5.0**.
- Đây là **đề xuất / phạm vi dự án từ Craveva**, không phải spec phần mềm nội bộ chỉ do Miaolin tự mô tả.

### 2.2 Có ghi chức năng liên quan “bán hàng” không?

**Có**, và khá chi tiết. Tóm tắt nhóm chức năng:

1. **Đơn hàng & ERP:** tự động hóa đơn; **SKU mapping** (đơn vị bán vs thùng ERP); SOP: sync sáng → **日間接單** (Line + Web) → tối **xuất đơn** → DigiWin.
2. **Định giá B2B:** 5 tầng (base, public, tier, hợp đồng khách, volume); đồng bộ từ ERP.
3. **Kênh:** B2B portal (quyền mua + giá); **Headless** — Catalog / Pricing / **Order API**.
4. **AI:** **採購助理** (đặt qua Line, NL, lịch sử đơn); **銷售業務** (Q&A sản phẩm, gợi ý); phân tích dữ liệu bán (nội bộ).
5. **Vận hành đơn:** chống đơn trùng, chặn SKU/batch sai, v.v.

**Mục PDF hữu ích khi trích dẫn:** §痛點, §3.1–3.3 (module + AI), §3.4 (API), §4 (SOP hằng ngày).

### 2.3 Tài liệu nội bộ liên quan pricing lớn hơn (Craveva)

- `PROJECT MAOLIN/MB B2B_PRICING_SYSTEM_PROPOSAL.md` — kiến trúc B2B pricing đề xuất nội bộ (không trùng nội dung từng chữ với PDF nhưng cùng hướng tier/corporate/volume).

---

## 3. Liên kết nhanh warehouse (nếu cùng phạm vi Miaolin)

- `FUNC_LOGIC/WAREHOUSE_INDEX.md`
- `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`
- `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`

---

_Ghi chú do phiên làm việc tạo; chi tiết số cột/mapping lấy từ các file nguồn trong bảng mục 1.6._
