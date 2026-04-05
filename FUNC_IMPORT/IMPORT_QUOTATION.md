# Import báo giá (Quotation) — file Maolin `報價單匯出`

**Nguồn file trong repo:** `PROJECT MAOLIN New/Craveva Quotetation_報價單匯出.csv`
(Lưu ý chính tả tên file: _Quotetation_ — giữ đúng tên gốc.)

**Tài liệu liên quan:** `FUNC_LOGIC/MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md` (báo giá = tham chiếu / adapter, không nằm chuỗi import chuẩn PO/DO/Invoice).

---

## Tóm tắt nhanh

| Câu hỏi                            | Trả lời ngắn                                                                                                                                                     |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| File phục vụ ERP hay B2B?          | **Cả hai:** chứng từ báo giá (ERP) + tham chiếu giá/điều kiện (B2B).                                                                                             |
| Craveva “Quotation” map model nào? | Module **Estimates** — bảng `estimates` / `estimate_items` (`App\Models\Estimate`).                                                                              |
| Hiện import được chưa?             | **Chưa** — không có job/class import quotation; **không phải** do CSV thiếu cột.                                                                                 |
| Cần thêm cột DB core không?        | **V1 không bắt buộc:** đa số cột “thừa” so với schema map vào `note`/`description`/`item_summary`, **Custom Field (CF) trên Estimate**, hoặc logic tính từ dòng. |

---

## 1) Vì sao chức năng import “chưa đủ”? Thiếu **cột** hay thiếu **code**?

### 1.1 Thiếu chính là **code & luồng**, không phải thiếu cột trong file CSV

- Trong codebase **không** có `EstimateImport` / `QuotationImport` (tương tự các import Client, Product, Sales History, …).
- File CSV Maolin **đã đủ thông tin** để _thiết kế_ map sang Estimate (gom nhóm theo `報價單號`, forward-fill header, chuẩn hóa số & ngày ROC).

### 1.2 Điều kiện để import **chạy được** (khi làm dev)

1. **Importer** (command/job + queue + quyền storage như runbook import).
2. **Master data:** khách (`客戶代號` → client), sản phẩm (`品號` → product), tiền tệ (`幣別` → `currencies`).
3. **Logic nghiệp vụ:** gom dòng, parse số có dấu phẩy, chuyển ngày ROC → `Y-m-d`, map thuế (`課稅別` / `品號稅別`) ↔ `calculate_tax` + JSON `taxes` trên dòng.
4. **Tùy chọn:** bảng **staging** nếu muốn reconcile trước khi ghi `estimates`.

### 1.3 “Thiếu cột” trên **schema Craveva** nghĩa là gì?

Bảng `estimates` / `estimate_items` **không có** nhiều cột tương ứng 1:1 với Maolin (ví dụ tỷ giá header, điều kiện thanh toán, ngày chứng từ riêng). Điều đó **không chặn** import: map vào trường có sẵn, **CF**, hoặc ghép text. Chi tiết ở mục 4.

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

**Lưu ý:** `EstimateItem` **không** dùng custom field trait trong model hiện tại — dữ liệu dòng “phụ” thường vào `item_summary` hoặc sau này mở rộng schema/CF nếu cần báo cáo sâu.

---

## 4) Mapping Maolin → Craveva (core vs CF) — cấp **HEADER** (cột 1–22)

| Cột CSV        | English (header label)   | Tiếng Việt (ý nghĩa)              | Gợi ý lưu Craveva                                                                                            |
| -------------- | ------------------------ | --------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `報價日期`     | Quotation date           | Ngày báo giá (ROC, vd. 114/12/01) | **Core:** có thể map `created_at` khi import **hoặc** **CF** `quotation_date` nếu cần tách khỏi `valid_till` |
| `報價單號`     | Quotation number         | Số báo giá, key gom dòng          | **Core:** `estimates.estimate_number`                                                                        |
| `客戶代號`     | Customer code            | Mã khách                          | **Core:** lookup → `client_id` (kèm master Client)                                                           |
| `客戶簡稱`     | Customer short name      | Tên tắt KH                        | **—** nếu đã match client; **CF/Text** nếu cần lưu tên gốc ERP                                               |
| `單據日期`     | Document date            | Ngày chứng từ                     | **CF** hoặc **Text** trong `note` (không có cột riêng trên `estimates`)                                      |
| `客戶全名`     | Customer full name       | Tên đầy đủ                        | **—** / **Text** (ưu tiên master client)                                                                     |
| `幣別`         | Currency                 | Loại tiền (NTD, …)                | **Core:** `currency_id`                                                                                      |
| `匯率`         | Exchange rate            | Tỷ giá                            | **CF** hoặc **Text** (`estimates` không có cột tỷ giá)                                                       |
| `報價金額`     | Quotation / total amount | Tổng tiền báo giá                 | **Core:** `total` (đối chiếu với tổng dòng + thuế)                                                           |
| `稅額`         | Tax amount               | Thuế                              | **Logic** từ dòng + **CF** header nếu cần lưu số thuế gốc Maolin                                             |
| `總數量`       | Total quantity           | Tổng SL                           | **Logic** = sum(`數量`) hoặc **CF** nếu cần giữ số export                                                    |
| `交貨日`       | Delivery / lead time     | Giao hàng (thường là text)        | **CF** hoặc **Text** trong `description`/`note`                                                              |
| `業務員`       | Salesperson              | NVKD                              | **CF** hoặc map `added_by` nếu match user (không bắt buộc)                                                   |
| `課稅別`       | Tax type / category      | Loại thuế (vd. 應稅外加)          | **Logic** ↔ `calculate_tax` + chọn `Tax` cho dòng                                                            |
| `確認`         | Internal confirmed (Y/N) | Xác nhận nội bộ                   | **CF**                                                                                                       |
| `客戶確認`     | Customer confirmed (Y/N) | Khách xác nhận                    | **CF** (hoặc gần với `status` — cần rule rõ)                                                                 |
| `價格條件`     | Price terms / Incoterms  | Điều kiện giá                     | **CF** / **Text**                                                                                            |
| `材積單位`     | Volume unit              | Đơn vị thể tích                   | **CF**                                                                                                       |
| `付款條件代號` | Payment terms code       | Mã TTTT                           | **CF**                                                                                                       |
| `付款條件名稱` | Payment terms name       | Tên TTTT                          | **CF**                                                                                                       |
| `總毛重(Kg)`   | Total gross weight (kg)  | Tổng trọng lượng                  | **CF**                                                                                                       |
| `總材積`       | Total volume             | Tổng thể tích                     | **CF**                                                                                                       |

---

## 5) Mapping — cấp **DÒNG** (cột 23–46)

| Cột CSV          | English (line label)         | Tiếng Việt            | Gợi ý lưu Craveva                                                      |
| ---------------- | ---------------------------- | --------------------- | ---------------------------------------------------------------------- |
| `品號`           | Product code / SKU           | Mã sản phẩm           | **Core:** lookup → `product_id` + `item_name`                          |
| `品名`           | Product name                 | Tên SP                | **Core:** `item_name`                                                  |
| `規格`           | Specification                | Quy cách              | **Core:** `item_summary`                                               |
| `數量`           | Quantity                     | Số lượng              | **Core:** `quantity`                                                   |
| `贈品量`         | Free / bonus qty             | SL tặng               | **Text** trong `item_summary` hoặc mở rộng schema nếu báo cáo bắt buộc |
| `單位`           | Unit of measure              | Đơn vị                | **Core:** `unit_id` (lookup `unit_types`)                              |
| `小單位`         | Sub-unit / alternate UoM     | Đơn vị nhỏ            | **Text** / **CF** (header hoặc ghi chú dòng)                           |
| `生效日期`       | Effective date               | Ngày hiệu lực giá     | **Text** / **CF**                                                      |
| `失效日期`       | Expiry date                  | Ngày hết hiệu lực     | **Text** / **CF**                                                      |
| `允收效期`       | Allowed validity (text)      | Mô tả hiệu lực        | **Text**                                                               |
| `天(月)`         | Days (months)                | Chu kỳ ngày/tháng     | **Text** / **CF**                                                      |
| `單價`           | Unit price                   | Đơn giá               | **Core:** `unit_price`                                                 |
| `金額`           | Line amount                  | Thành tiền dòng       | **Core:** `amount`                                                     |
| `分量計價`       | Tiered / partial qty pricing | Tính giá theo bậc     | **Text** / **CF**                                                      |
| `付款條件_0LD`   | Payment terms (legacy)       | Trường cũ / typo      | **—**                                                                  |
| `包裝方式`       | Packaging method             | Cách đóng gói         | **Text** → `item_summary`                                              |
| `包裝名稱`       | Packaging name               | Tên quy cách đóng gói | **Text** → `item_summary`                                              |
| `毛重(Kg)`       | Gross weight (kg), line      | Trọng lượng dòng      | **Text** / **CF**                                                      |
| `品號稅別`       | Item tax category            | Thuế theo mặt hàng    | **Logic** ↔ `taxes` (JSON id thuế)                                     |
| `材積`           | Volume (line)                | Thể tích dòng         | **Text** / **CF**                                                      |
| `應稅品未稅金額` | Taxable amount (excl. tax)   | Tiền hàng chịu thuế   | **Logic** / **Text** hỗ trợ đối soát                                   |
| `免稅品金額`     | Tax-exempt amount            | Tiền hàng miễn thuế   | **Logic** / **Text**                                                   |
| `標準價`         | Standard / list price        | Giá chuẩn             | **Text** / **CF**                                                      |
| `價格判定`       | Price type (std / special)   | Phân loại giá         | **Text** / **CF**                                                      |

**Ghi chú kỹ thuật:** Số có dấu phẩy nghìn (`"1,150"`) — chuẩn hóa khi parse. Ngày ROC cần quy tắc cố định (+1911) trước khi lưu DB.

---

## 6) Cần **thêm cột core** trong DB không? Khi nào nên dùng **CF**?

### 6.1 Khuyến nghị cho bản import **đầu tiên**

- **Không** mở rộng schema `estimates`/`estimate_items` trừ khi có yêu cầu báo cáo/lọc **bắt buộc** trên từng trường (vd. filter theo `exchange_rate` toàn hệ thống).
- Ưu tiên: **Core** cho khóa nghiệp vụ (`estimate_number`, `client_id`, `currency_id`, `sub_total`/`total`, `valid_till`, dòng `quantity`/`unit_price`/`amount`/tax) + **CF Estimate** cho: tỷ giá, điều kiện thanh toán, ngày chứng từ, salesperson, xác nhận, Incoterm, tổng trọng lượng/thể tích header, v.v.

### 6.2 Khi nào cân nhắc **thêm cột core** (migration)

Chỉ khi:

- Cần **index / report** chính thức trên trường đó (vd. `issued_at` / `quotation_date` tách biệt `valid_till` cho hàng nghìn báo cáo), hoặc
- Tích hợp module khác (payment term master, FX bảng tỷ giá) — lúc đó thêm FK chuẩn hơn là CF.

### 6.3 Gợi ý **CF** nên tạo trên `Estimate` (tên gợi ý)

`maolin_exchange_rate`, `maolin_document_date`, `maolin_payment_terms_code`, `maolin_payment_terms_name`, `maolin_salesperson`, `maolin_internal_confirm`, `maolin_customer_confirm`, `maolin_price_terms`, `maolin_delivery_note`, `maolin_total_gross_kg`, `maolin_total_volume`, `maolin_volume_uom` — chỉnh tên theo quy ước công ty.

---

## 7) “Quotation” trên UI Craveva vs export Maolin

- UI **Quotation** trỏ **Estimates** — schema **46 cột Maolin ≠** form Estimate hiện tại; import là **project map + code**, không phải copy 1:1 cột.

---

## 8) Ngoài **Quotation / Estimates**, còn chỗ nào trong hệ thống **phù hợp** với file `Craveva Quotetation_報價單匯出.csv`?

Tóm lại: **không có module import sẵn nào khác “khớp 1:1”** format 46 cột Maolin. Các hướng dưới đây là **mục đích nghiệp vụ khác nhau** — chỉ “phù hợp” nếu bạn **chấp nhận** mất bớt thông tin chứng từ hoặc phải **ETL / adapter** riêng.

| Hướng                                                                                                            | Phù hợp?                     | Ghi chú ngắn                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ---------------------------------------------------------------------------------------------------------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Estimates** (`estimates` / `estimate_items`)                                                                   | **Cao nhất**                 | Giữ được **số báo giá**, **khách**, **dòng hàng**, **đơn giá/thành tiền**, thuế (sau khi map). Đúng bản chất “chứng từ báo giá”.                                                                                                                                                                                                                                                                                                         |
| **Pricing — Client × Product** (`ClientProductPricingImport`: `customer_code`, `product_sku`, `custom_price`, …) | **Một phần**                 | Chỉ hợp lý nếu mục tiêu là **đồng bộ bảng giá riêng cho khách** (lấy từ cột `客戶代號` + `品號` + `單價`, có thể kèm rule **giá mới nhất** / theo `生效日期`). File CSV báo giá **không** trùng template import pricing hiện tại → cần **bước chuyển đổi** hoặc import tùy chỉnh. **Bảng giá list / workbook** trong Maolin vẫn là `Craveva_product__商品價格.csv` / `Quote_unit_price_inventory__產品價格表.csv` (đã có luồng pricing). |
| **Product** (giá chuẩn `標準價`)                                                                                 | **Rất hạn chế**              | Có thể dùng **phụ** để cập nhật/tham chiếu **list price** theo SKU, nhưng **không** thay được báo giá theo khách và **không** nên coi đây là “import quotation”.                                                                                                                                                                                                                                                                         |
| **Sales History** (`sales_histories` / `sales_history_lines`)                                                    | **Không khuyến nghị**        | Dữ liệu thiết kế cho **giao dịch đã xảy ra** (ngày giao, số lượng, doanh thu…), không phải **báo giá chưa chốt**. Nhét quotation vào đây sẽ **sai ngữ cảnh** báo cáo.                                                                                                                                                                                                                                                                    |
| **Sales Order**                                                                                                  | **Không**                    | Đơn đặt hàng — giai đoạn sau báo giá; CSV không phải đơn hàng.                                                                                                                                                                                                                                                                                                                                                                           |
| **Proposals** (lead)                                                                                             | **Kém phù hợp**              | Gắn **lead CRM**, trong khi Maolin thường là **khách đã có mã** (`客戶代號`).                                                                                                                                                                                                                                                                                                                                                            |
| **Bảng staging / JSON / model riêng**                                                                            | **Phù hợp “lưu tham chiếu”** | Đúng tinh thần `MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`: báo giá có thể chỉ **adapter + lưu raw** phục vụ đối soát, AI, báo cáo — **không** đẩy vào PO/DO/Invoice.                                                                                                                                                                                                                                                                      |

**Kết luận thực tế:** Nếu cần **một chỗ “chính thống” trong app** cho nội dung file này thì vẫn là **Estimates**. Các chỗ khác chỉ là **tách nhánh dữ liệu** (giá KH–SKU, hoặc archive) khi nghiệp vụ chấp nhận.

---

## 9) Checklist trước khi dev import (khi được ưu tiên)

- [ ] Mục tiêu: chỉ **lưu lịch sử** vs **tạo Estimate** gửi khách.
- [ ] Chuẩn hóa ngày ROC + số có dấu phẩy.
- [ ] Rule gom dòng + forward-fill theo `報價單號`.
- [ ] Match `客戶代號` / `品號` với master đã import.
- [ ] Quy ước thuế Maolin ↔ `calculate_tax` + `taxes` từng dòng.
- [ ] Tạo nhóm **CF** Estimate (và quy ước text `item_summary` cho dòng).
- [ ] Test staging + queue/storage theo runbook import.

---

_Cập nhật: 2026-04-03 — mục 8: so sánh Estimates vs Pricing / Sales History / Order / Proposal / staging; bổ sung cột tiếng Anh, tách core/CF._
