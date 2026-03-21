# Dữ liệu Miaolin — `PROJECT MAOLIN New/` và yêu cầu chatbot

Tham chiếu đề xuất kỹ thuật: `CHAT/zalo chat.txt`.

_Nguồn kiểm tra: đọc **dòng 1** các file `.xlsx` (file nặng chỉ đọc vài dòng đầu). Lần đầu chỉ xem **sheet mặc định (active)**; **mục 7** liệt kê **tất cả sheet** trong từng file. File `Craveva full inventory.xlsx` có vài dòng tiêu đề báo cáo; **hàng tiêu đề cột dữ liệu nằm khoảng dòng 8** (產品料號, 庫存量, 庫別名稱…)._

---

## 1. Các file dữ liệu trong `PROJECT MAOLIN New/`

| File                                  | Loại                 | Ghi chú                                                                                                                                      |
| ------------------------------------- | -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| **Craveva product.xlsx**              | Master sản phẩm      | Tiêu đề cột song ngữ (`品號 \| SKU`, `品名 \| Product Name`, 規格, 品牌類別, 保存天數, 備貨型態, 儲存溫層, 失效日期…)                        |
| **Craveva customer.xlsx**             | Khách hàng           | 客戶代號, 客戶簡稱, 統一編號, 業務員, 業務助理, 分級, 通路/型態/地區, 送貨地址, TEL_NO(一)(二), 交易條件, 最近交易, 歇業日期, 指定庫別名稱   |
| **Craveva full inventory.xlsx**       | Tồn theo batch / kho | Báo cáo「批號庫存狀況」; dữ liệu từ ~dòng 8: 產品料號, 產品名稱, 有效日期, 批號, 庫存量, 剩餘有效天數, 庫別名稱 (mẫu kiểm tra có 最後更新日) |
| **Quote, unit price, inventory.xlsx** | Báo giá + dòng hàng  | 報價日期/單號, 客戶代號/簡稱/全名, 幣別, 報價金額, 品號/品名/規格, 數量, 單價, 金額…                                                         |
| **Last year net sales.xlsx**          | Bán hàng theo kỳ     | 出貨/銷退日, 客戶編號, 產品料號, 淨銷售量, 銷售淨額(本幣/未稅)                                                                               |
| File `.pdf` / `.docx`                 | Hợp đồng, BRD        | Không thay thế master data cho bot                                                                                                           |

**Không có** trong folder: export **đơn hàng** định dạng `order_id` + trạng thái + tracking + **order lines** như trong đề xuất `zalo chat.txt`.

---

## 2. Đối chiếu nhanh với mục tiêu chatbot

| Lĩnh vực                       | Trong các file trên                                   | Ghi chú                                                                     |
| ------------------------------ | ----------------------------------------------------- | --------------------------------------------------------------------------- |
| SKU + tên + quy cách + brand   | **Đủ** (`Craveva product.xlsx`)                       | Ví dụ tra cứu: 山茶花, 25KG/包, 日清製粉…                                   |
| Tồn “còn bao nhiêu theo mã”    | **Có chi tiết batch** (`Craveva full inventory.xlsx`) | Bot cần **tổng hợp theo 產品料號** hoặc xin thêm file tồn tổng SKU          |
| Giá / báo giá                  | **Có** (`Quote, unit price, inventory.xlsx`)          | Là **báo giá**, không tương đương đơn giao hàng đầy đủ trạng thái           |
| “Đặt lại / đã mua gì” chi tiết | **Chỉ aggregate** (`Last year net sales.xlsx`)        | Hỗ trợ “đã mua trong kỳ”; **không** thay thế **từng đơn** + trạng thái giao |
| Alias / từ khóa đa ngôn ngữ    | **Không có cột riêng**                                | Craveva sinh từ 品名 + 品牌 + 規格 hoặc Miaolin thêm cột                    |
| Khách ↔ LINE/Zalo              | **Không có trong Excel**                              | Map sau xác thực SĐT trong Craveva                                          |

---

## 3. Phía Craveva cần triển khai (ngoài file Excel)

1. **Đồng bộ** từ `PROJECT MAOLIN New/`: product, tồn (aggregate nếu cần), báo giá, dữ liệu bán theo kỳ.
2. **Tìm sản phẩm** có điểm tin cậy; không chắc thì **hỏi lại** khách.
3. **Alias / search_keywords** lưu phía Craveva nếu Miaolin chưa cấp cột.
4. **Bộ nhớ hội thoại** (tin gần đây, SKU đã chốt; đơn gần nhất khi đã có nguồn đơn).
5. **An toàn:** không lộ dữ liệu khách khác; xác nhận kèm SKU + quy cách; rate-limit xác thực SĐT.
6. **Nguồn “đơn”:** cho đến khi có export đơn chuẩn, thống nhất với nghiệp vụ có dùng **bán theo kỳ** / **báo giá đã chốt** làm proxy hay không.

---

## 4. Đề xuất nhờ Miaolin bổ sung (export / cột)

1. **Đơn hàng:** header + **order lines** (`order_id`, `customer_code`, `order_date`, `status`, `product_code`, `qty`, `unit_price`; nếu được thêm `tracking`, `estimated_delivery`).
2. **search_keywords / aliases** trên master sản phẩm (hoặc chấp nhận Craveva tự sinh).
3. (Tùy) **Tồn tổng theo SKU** (một sheet/file) để giảm bước SUM batch.

---

## 5. Script kiểm tra trong repo

```text
php scripts/read_maolin_new_folder_headers.php
php scripts/peek_maolin_sheet.php "PROJECT MAOLIN New/Craveva full inventory.xlsx"
```

---

## 6. Chốt hạ: Miaolin cần bổ sung **import nào** — và **vì sao**

Căn cứ: bộ file hiện có trong `PROJECT MAOLIN New/` + mục tiêu trong `CHAT/zalo chat.txt`.

### 6.1 Nên coi là **bắt buộc bổ sung** (hoặc thống nhất nguồn tương đương)

| Nội dung import                                                                           | Lý do                                                                                                                                                                                                                                                                                                                        |
| ----------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Đơn hàng — ít nhất 2 tầng: header + dòng hàng**                                         | Hiện có **báo giá** (`Quote, unit price…`) và **bán theo kỳ** (`Last year net sales`) — **không thay** được nhu cầu: “đơn nào”, “trạng thái đơn”, “lần trước đặt **đúng** những dòng nào”. Không có `order_id` + dòng `product_code` + `qty` theo từng đơn thì bot **không thể** trả lời đúng kiểu CRM/đặt lại như đơn thật. |
| **Trường tối thiểu trên header đơn:** `order_id`, `customer_code`, `order_date`, `status` | `status` để trả lời “đơn đang xử lý / đã giao / hủy”; ngày để sắp “đơn gần nhất”.                                                                                                                                                                                                                                            |
| **Trên dòng đơn:** `order_id`, `product_code`, `qty`, `unit_price` (hoặc amount line)     | Liên kết khách ↔ SKU ↔ số lượng — là nền cho “buy again” và kiểm tra đơn.                                                                                                                                                                                                                                                    |

**Khuyến nghị thêm (nếu ERP cho phép):** `tracking_number`, `estimated_delivery` — lý do: giảm ticket hỏi “hàng đi đâu”, bot trả lời có căn cứ.

---

### 6.2 **Nên bổ sung** (tăng độ chính xác chatbot, không bắt buộc ERP nếu thống nhất khác)

| Nội dung import                                                                                      | Lý do                                                                                                                                                                                                                                          |
| ---------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Cột `search_keywords` / `aliases` trên master sản phẩm**                                           | Khách nói “flour / 高筋 / tên lóng” — map sang SKU cần **từ khóa** ngoài 品名. Miaolin nhập tay hoặc rule BI sẽ **ổn định hơn** chỉ dựa model đoán. Nếu không: Craveva vẫn có thể **sinh alias** từ 品名+品牌+規格 (chậm hơn / cần chỉnh dần). |
| **File hoặc sheet “tồn tổng theo SKU”** (`product_code`, `qty_available`, có thể kèm `warehouse_id`) | `Craveva full inventory` là **batch + kho** — đủ dữ liệu nhưng Craveva phải **SUM**. Export sẵn tổng theo mã **giảm lỗi tổng hợp**, nhẹ job, dễ audit.                                                                                         |

---

### 6.3 **Đã đủ trong import hiện tại** — không cần Miaolin “thêm mới” cho mục tối thiểu sau (trừ khi muốn chuẩn hóa tên cột)

| Nội dung                                                | Ghi chú ngắn                                                                                                                                            |
| ------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Master sản phẩm** (`Craveva product.xlsx`)            | Đã có SKU, tên, 規格, 品牌, đơn vị, điều kiện bảo quản… — đủ làm nền tra cứu & match. Thiếu chủ yếu là **alias** (mục 6.2), không phải thiếu cả master. |
| **Khách** (`Craveva customer.xlsx`)                     | Đã có mã khách, SĐT — đủ cho **xác thực** theo SĐT.                                                                                                     |
| **Tồn** (`Craveva full inventory.xlsx`)                 | Đủ **số liệu**; chỉ là **dạng chi tiết** → nên SUM phía Craveva hoặc xin thêm bản tổng (6.2).                                                           |
| **Giá / báo giá** (`Quote, unit price, inventory.xlsx`) | Đủ cho **giá/báo giá**; không thay **đơn hàng** (6.1).                                                                                                  |

---

### 6.4 **Không kỳ vọng từ file ERP** (không phải “thiếu import Miaolin” theo nghĩa Excel)

| Việc                                              | Lý do                                                                                                                                                                  |
| ------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **LINE User ID / Zalo OA ID map `customer_code`** | ID do nền tảng chat cấp sau khi user tương tác — **lưu ở Craveva**, không xuất sẵn từ ERP như một cột master khách.                                                    |
| **`auth_username` kiểu “tài khoản chat”**         | Trong `zalo chat.txt` là khái niệm **xác thực**; thực tế thường map **SĐT đã chuẩn hóa** + session bot — không nhất thiết là cột riêng trong export ERP nếu đã có SĐT. |

---

### 6.5 Một câu chốt cho họp

**Miaolin cần bổ sung quan trọng nhất cho chatbot B2B là export **đơn hàng (header + dòng đơn + trạng thái)**;** các file hiện tại chủ yếu phục vụ **báo giá / bán lũy kỳ / tồn batch**, không thay được “đơn thật”. Phần còn lại: **alias sản phẩm** (hoặc để Craveva sinh) và **tồn tổng SKU** (tùy) để giảm sai số và chi phí xử lý.

**Bản tiếng Anh (Word):**

- **Toàn bộ tài liệu này (bảng + mục 1–6):** `CHAT/Miaolin_New_Folder_vs_Chatbot_Requirements_EN.docx` — `scripts/generate_maolin_new_folder_vs_chatbot_en_docx.py`
- **Chỉ phần chốt hạ / yêu cầu import (mục 6 tương đương):** `CHAT/Miaolin_Import_Supplement_Request_EN.docx` — `scripts/generate_maolin_import_supplement_en_docx.py`

---
