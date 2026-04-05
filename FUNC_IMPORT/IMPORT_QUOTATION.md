# Import báo giá (Quotation) — file Maolin `報價單匯出`

**Nguồn file trong repo:** `PROJECT MAOLIN New/Craveva Quotation_報價單匯出.csv` (và bản mẫu tải xuống UI: `public/sample-import/quotation-sample.csv`).

**Tài liệu liên quan:** `FUNC_LOGIC/MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md` (báo giá = tham chiếu / adapter, không nằm chuỗi import chuẩn PO/DO/Invoice).

---

## Tóm tắt nhanh


| Câu hỏi                            | Trả lời ngắn                                                                                                                                                                                                                                                                                                                                                        |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| File phục vụ ERP hay B2B?          | **Cả hai:** chứng từ báo giá (ERP) + tham chiếu giá/điều kiện (B2B).                                                                                                                                                                                                                                                                                                |
| Craveva “Quotation” map model nào? | Module **Estimates** — bảng `estimates` / `estimate_items` (`App\Models\Estimate`).                                                                                                                                                                                                                                                                                 |
| Hiện import được chưa?             | **Có (V1):** luồng giống Client — upload → map cột → queue batch `EstimateImport` / `EstimateImport-chunked` (`EstimateImport`, `ImportEstimateChunkJob`, `EstimateImportProcessor`). UI: `estimates.import` (GET), `estimates.import.store` (POST), `estimates.import.process` (POST). Worker: queue tên `**EstimateImport`** (xem `ImportController` + `Kernel`). |
| Cần thêm cột DB core không?        | **Đã bổ sung (2026-04):** migration `2026_04_03_120000_add_quotation_core_fields_to_estimates_and_items.php` — cột header trên `estimates` + `free_quantity`, `line_effective_date`, `line_expiry_date` trên `estimate_items`. UI tạo/sửa/xem + import ghi các trường này; `sub_total`/`total` vẫn cộng từ dòng.                                                    |


---

## 1) Phạm vi import V1 vs tài liệu mapping đầy đủ

### 1.1 Trước đây thiếu **code & luồng**; hiện V1 đã có importer — CSV vẫn giàu cột hơn schema core

- Đã có `App\Imports\EstimateImport`, job chunk `App\Jobs\ImportEstimateChunkJob`, xử lý `App\Services\EstimateImportProcessor` (forward-fill header theo số báo giá, ngày ROC, lookup client/tiền tệ/sản phẩm tùy map).
- File CSV Maolin **đã đủ thông tin** để *thiết kế* map sang Estimate (gom nhóm theo `報價單號`, forward-fill header, chuẩn hóa số & ngày ROC).

### 1.2 Điều kiện để import **chạy được** (khi làm dev)

1. **Importer** (command/job + queue + quyền storage như runbook import).
2. **Master data:** khách (`客戶代號` → client), sản phẩm (`品號` → product), tiền tệ (`幣別` → `currencies`).
3. **Logic nghiệp vụ:** gom dòng, parse số có dấu phẩy, chuyển ngày ROC → `Y-m-d`, map thuế (`課稅別` / `品號稅別`) ↔ `calculate_tax` + JSON `taxes` trên dòng.
4. **Tùy chọn:** bảng **staging** nếu muốn reconcile trước khi ghi `estimates`.

### 1.3 “Thiếu cột” trên **schema Craveva** nghĩa là gì?

Phần lớn **header** Maolin đã có cột **core** tương ứng (mục 4 & 6). Cột dòng / phụ vẫn có thể thiếu 1:1 → **CF**, `item_summary`, hoặc bỏ qua. Chi tiết ở mục 4–5.

---

## 2) Cấu trúc file CSV (logic nghiệp vụ)

- **Một phiếu báo giá** = nhiều **dòng**; cột **header** (1–22) thường điền ở dòng đầu block, các dòng sau có thể **trống** (cần **forward-fill** theo `報價單號`).
- Cột **dòng hàng** bắt đầu từ `品號` trở đi (SKU, SL, đơn giá, …).

---

## 3) Ký hiệu dùng trong bảng mapping


| Ký hiệu   | Ý nghĩa                                                                                    |
| --------- | ------------------------------------------------------------------------------------------ |
| **Core**  | Map trực tiếp (hoặc sau lookup) vào cột chuẩn `estimates` / `estimate_items`.              |
| **CF**    | Nên lưu qua **Custom Field** gắn model `Estimate` (bảng estimates có `CustomFieldsTrait`). |
| **Text**  | Gộp vào `note`, `description` (header) hoặc `item_summary` (dòng).                         |
| **Logic** | Không cột tĩnh: suy ra từ dòng (tổng SL, tổng tiền) hoặc rule thuế.                        |
| **—**     | Bỏ qua / legacy / ít dùng trừ khi nghiệp vụ yêu cầu.                                       |


**Lưu ý:** `EstimateItem` **không** có CF; các cột dòng **core** mới: `free_quantity`, `line_effective_date`, `line_expiry_date`. Các cột Maolin khác (包裝, 標準價, …) vẫn có thể map **CF Estimate** hoặc gộp `item_summary`.

---

## 4) Mapping Maolin → Craveva (core vs CF) — cấp **HEADER** (cột 1–22)


| Cột CSV   | English (header label)   | Tiếng Việt (ý nghĩa)              | Gợi ý lưu Craveva                                                                                              |
| --------- | ------------------------ | --------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| `報價日期`    | Quotation date           | Ngày báo giá (ROC, vd. 114/12/01) | **Core:** `estimates.quotation_date` (import + form); `valid_till` vẫn lấy từ dòng hiệu lực hoặc +30 ngày      |
| `報價單號`    | Quotation number         | Số báo giá, key gom dòng          | **Core:** `estimates.estimate_number`                                                                          |
| `客戶代號`    | Customer code            | Mã khách                          | **Core:** lookup → `client_id` (kèm master Client)                                                             |
| `客戶簡稱`    | Customer short name      | Tên tắt KH                        | **—** / **CF** nếu cần lưu tên gốc ERP                                                                         |
| `單據日期`    | Document date            | Ngày chứng từ                     | **Core:** `estimates.document_date`                                                                            |
| `客戶全名`    | Customer full name       | Tên đầy đủ                        | **—** / **CF/Text** (ưu tiên master client)                                                                    |
| `幣別`      | Currency                 | Loại tiền (NTD, …)                | **Core:** `currency_id`                                                                                        |
| `匯率`      | Exchange rate            | Tỷ giá                            | **Core:** `estimates.exchange_rate`                                                                            |
| `報價金額`    | Quotation / total amount | Tổng tiền báo giá                 | **Core (nguồn):** `estimates.header_quotation_amount`; **tính toán:** `sub_total`/`total` cộng dòng            |
| `稅額`      | Tax amount               | Thuế                              | **Core (nguồn):** `estimates.header_tax_amount`; thuế dòng vẫn **Logic** / `taxes` JSON (chưa auto map Maolin) |
| `總數量`     | Total quantity           | Tổng SL                           | **Core (nguồn):** `estimates.header_total_quantity`                                                            |
| `交貨日`     | Delivery / lead time     | Giao hàng (thường là text)        | **Core:** `estimates.delivery_note`                                                                            |
| `業務員`     | Salesperson              | NVKD                              | **Core:** `estimates.salesperson_name`                                                                         |
| `課稅別`     | Tax type / category      | Loại thuế (vd. 應稅外加)              | **Core (nhãn):** `estimates.tax_type_label`; **Logic** thuế dòng ↔ `taxes` / `calculate_tax` (TODO nâng cao)   |
| `確認`      | Internal confirmed (Y/N) | Xác nhận nội bộ                   | **Core:** `estimates.confirm_internal`                                                                         |
| `客戶確認`    | Customer confirmed (Y/N) | Khách xác nhận                    | **Core:** `estimates.confirm_customer`                                                                         |
| `價格條件`    | Price terms / Incoterms  | Điều kiện giá                     | **Core:** `estimates.price_terms`                                                                              |
| `材積單位`    | Volume unit              | Đơn vị thể tích                   | **Core:** `estimates.volume_unit`                                                                              |
| `付款條件代號`  | Payment terms code       | Mã TTTT                           | **Core:** `estimates.payment_terms_code`                                                                       |
| `付款條件名稱`  | Payment terms name       | Tên TTTT                          | **Core:** `estimates.payment_terms_name`                                                                       |
| `總毛重(Kg)` | Total gross weight (kg)  | Tổng trọng lượng                  | **Core:** `estimates.total_gross_weight_kg`                                                                    |
| `總材積`     | Total volume             | Tổng thể tích                     | **Core:** `estimates.total_volume`                                                                             |


---

## 5) Mapping — cấp **DÒNG** (cột 23–46)


| Cột CSV    | English (line label)         | Tiếng Việt            | Gợi ý lưu Craveva                                                                                    |
| ---------- | ---------------------------- | --------------------- | ---------------------------------------------------------------------------------------------------- |
| `品號`       | Product code / SKU           | Mã sản phẩm           | **Core:** lookup → `product_id` + `item_name`                                                        |
| `品名`       | Product name                 | Tên SP                | **Core:** `item_name`                                                                                |
| `規格`       | Specification                | Quy cách              | **Core:** `item_summary`                                                                             |
| `數量`       | Quantity                     | Số lượng              | **Core:** `quantity`                                                                                 |
| `贈品量`      | Free / bonus qty             | SL tặng               | **Core:** `estimate_items.free_quantity`                                                             |
| `單位`       | Unit of measure              | Đơn vị                | **Core:** `unit_id` (lookup `unit_types`)                                                            |
| `小單位`      | Sub-unit / alternate UoM     | Đơn vị nhỏ            | **Text** / **CF** (header hoặc ghi chú dòng)                                                         |
| `生效日期`     | Effective date               | Ngày hiệu lực giá     | **Core:** `estimate_items.line_effective_date` (đồng thời ảnh hưởng `valid_till` header khi tạo mới) |
| `失效日期`     | Expiry date                  | Ngày hết hiệu lực     | **Core:** `estimate_items.line_expiry_date`                                                          |
| `允收效期`     | Allowed validity (text)      | Mô tả hiệu lực        | **Text**                                                                                             |
| `天(月)`     | Days (months)                | Chu kỳ ngày/tháng     | **Text** / **CF**                                                                                    |
| `單價`       | Unit price                   | Đơn giá               | **Core:** `unit_price`                                                                               |
| `金額`       | Line amount                  | Thành tiền dòng       | **Core:** `amount`                                                                                   |
| `分量計價`     | Tiered / partial qty pricing | Tính giá theo bậc     | **Text** / **CF**                                                                                    |
| `付款條件_0LD` | Payment terms (legacy)       | Trường cũ / typo      | **—**                                                                                                |
| `包裝方式`     | Packaging method             | Cách đóng gói         | **Text** → `item_summary`                                                                            |
| `包裝名稱`     | Packaging name               | Tên quy cách đóng gói | **Text** → `item_summary`                                                                            |
| `毛重(Kg)`   | Gross weight (kg), line      | Trọng lượng dòng      | **Text** / **CF**                                                                                    |
| `品號稅別`     | Item tax category            | Thuế theo mặt hàng    | **Logic** ↔ `taxes` (JSON id thuế)                                                                   |
| `材積`       | Volume (line)                | Thể tích dòng         | **Text** / **CF**                                                                                    |
| `應稅品未稅金額`  | Taxable amount (excl. tax)   | Tiền hàng chịu thuế   | **Logic** / **Text** hỗ trợ đối soát                                                                 |
| `免稅品金額`    | Tax-exempt amount            | Tiền hàng miễn thuế   | **Logic** / **Text**                                                                                 |
| `標準價`      | Standard / list price        | Giá chuẩn             | **Text** / **CF**                                                                                    |
| `價格判定`     | Price type (std / special)   | Phân loại giá         | **Text** / **CF**                                                                                    |


**Ghi chú kỹ thuật:** Số có dấu phẩy nghìn (`"1,150"`) — chuẩn hóa khi parse. Ngày ROC cần quy tắc cố định (+1911) trước khi lưu DB.

---

## 6) Cột **core** đã thêm & khi nào vẫn dùng **CF**

### 6.1 Đã triển khai trên DB (cùng migration trên)

`**estimates`:** `quotation_date`, `document_date`, `exchange_rate`, `header_quotation_amount`, `header_tax_amount`, `header_total_quantity`, `delivery_note`, `salesperson_name`, `tax_type_label`, `payment_terms_code`, `payment_terms_name`, `confirm_internal`, `confirm_customer`, `price_terms`, `volume_unit`, `total_gross_weight_kg`, `total_volume`.

`**estimate_items`:** `free_quantity`, `line_effective_date`, `line_expiry_date`.

- **Import:** `EstimateImportProcessor::applyQuotationHeaderFromImportRow()` ghi header **chỉ khi tạo estimate mới** (dòng đầu của số báo giá); các dòng tiếp theo không ghi đè header.
- **UI:** form tạo/sửa (`estimates.partials.quotation-extra-fields`, dòng meta từng item), xem (`estimates/ajax/show`).
- **Ngôn ngữ:** `lang/*/modules.php` → nhóm `estimates.`* (`quotationDate`, `lineFreeQuantity`, …).

### 6.2 Khi nào vẫn cần **CF**

- Cột Maolin **không** có cột core tương ứng (vd. `小單位`, `允收效期`, `標準價`, `價格判定`, …) → map **CF** Estimate hoặc `item_summary` / `note`.
- Báo cáo tùy chỉnh theo trường chưa có trên schema → thêm CF hoặc migration bổ sung.

### 6.3 Gợi ý CF (nếu vẫn muốn trùng tên Maolin song song core)

Không bắt buộc vì header đã có core; CF chỉ khi cần **bản sao** theo quy ước tên nội bộ (`maolin_`*).

---

## 7) “Quotation” trên UI Craveva vs export Maolin

- UI **Quotation** trỏ **Estimates** — schema **46 cột Maolin ≠** form Estimate hiện tại; import là **project map + code**, không phải copy 1:1 cột.

---

## 8) Ngoài **Quotation / Estimates**, còn chỗ nào trong hệ thống **phù hợp** với file `Craveva Quotetation_報價單匯出.csv`?

Tóm lại: **không có module import sẵn nào khác “khớp 1:1”** format 46 cột Maolin. Các hướng dưới đây là **mục đích nghiệp vụ khác nhau** — chỉ “phù hợp” nếu bạn **chấp nhận** mất bớt thông tin chứng từ hoặc phải **ETL / adapter** riêng.


| Hướng                                                                                                            | Phù hợp?                     | Ghi chú ngắn                                                                                                                                                                                                                                                                                                                                                                                                        |
| ---------------------------------------------------------------------------------------------------------------- | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Estimates** (`estimates` / `estimate_items`)                                                                   | **Cao nhất**                 | Giữ được **số báo giá**, **khách**, **dòng hàng**, **đơn giá/thành tiền**, thuế (sau khi map). Đúng bản chất “chứng từ báo giá”.                                                                                                                                                                                                                                                                                    |
| **Pricing — Client × Product** (`ClientProductPricingImport`: `customer_code`, `product_sku`, `custom_price`, …) | **Một phần**                 | Chỉ hợp lý nếu mục tiêu là **đồng bộ bảng giá riêng cho khách** (lấy từ cột `客戶代號` + `品號` + `單價`, có thể kèm rule **giá mới nhất** / theo `生效日期`). File CSV báo giá **không** trùng template import pricing hiện tại → cần **bước chuyển đổi** hoặc import tùy chỉnh. **Bảng giá list / workbook** trong Maolin vẫn là `Craveva_product__商品價格.csv` / `Quote_unit_price_inventory__產品價格表.csv` (đã có luồng pricing). |
| **Product** (giá chuẩn `標準價`)                                                                                    | **Rất hạn chế**              | Có thể dùng **phụ** để cập nhật/tham chiếu **list price** theo SKU, nhưng **không** thay được báo giá theo khách và **không** nên coi đây là “import quotation”.                                                                                                                                                                                                                                                    |
| **Sales History** (`sales_histories` / `sales_history_lines`)                                                    | **Không khuyến nghị**        | Dữ liệu thiết kế cho **giao dịch đã xảy ra** (ngày giao, số lượng, doanh thu…), không phải **báo giá chưa chốt**. Nhét quotation vào đây sẽ **sai ngữ cảnh** báo cáo.                                                                                                                                                                                                                                               |
| **Sales Order**                                                                                                  | **Không**                    | Đơn đặt hàng — giai đoạn sau báo giá; CSV không phải đơn hàng.                                                                                                                                                                                                                                                                                                                                                      |
| **Proposals** (lead)                                                                                             | **Kém phù hợp**              | Gắn **lead CRM**, trong khi Maolin thường là **khách đã có mã** (`客戶代號`).                                                                                                                                                                                                                                                                                                                                           |
| **Bảng staging / JSON / model riêng**                                                                            | **Phù hợp “lưu tham chiếu”** | Đúng tinh thần `MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`: báo giá có thể chỉ **adapter + lưu raw** phục vụ đối soát, AI, báo cáo — **không** đẩy vào PO/DO/Invoice.                                                                                                                                                                                                                                                 |


**Kết luận thực tế:** Nếu cần **một chỗ “chính thống” trong app** cho nội dung file này thì vẫn là **Estimates**. Các chỗ khác chỉ là **tách nhánh dữ liệu** (giá KH–SKU, hoặc archive) khi nghiệp vụ chấp nhận.

---

## 9) Checklist trước khi dev import (khi được ưu tiên)

- Mục tiêu: chỉ **lưu lịch sử** vs **tạo Estimate** gửi khách.
- Chuẩn hóa ngày ROC + số có dấu phẩy.
- Rule gom dòng + forward-fill theo `報價單號`.
- Match `客戶代號` / `品號` với master đã import.
- Quy ước thuế Maolin ↔ `calculate_tax` + `taxes` từng dòng.
- Cột core estimates / estimate_items + UI + import header (dòng đầu) + map `EstimateImport::fields()`.
- (Tùy chọn) CF cho cột Maolin chưa có core; map thuế dòng tự động từ `課稅別` / `品號稅別`.
- Test staging + queue/storage theo runbook import.

---

*Cập nhật: 2026-04-03 — migration core quotation fields + UI + import processor; cập nhật bảng mapping mục 4–5–6.*