# Phân tích: `zalo chat.txt`

_Tài liệu này tổng hợp nội dung file và ý định nghiệp vụ (theo đề xuất trong file)._

---

## 1. File này là gì?

- **Không phải** log chat Zalo thô (tin nhắn từng dòng).
- Nội dung là **một bản đề xuất kỹ thuật bằng tiếng Anh** về cách xử lý lỗi agent chatbot (gợi ý sai sản phẩm, không nhớ đơn hàng trước).
- Tên file `zalo chat.txt` gợi **nguồn lưu** (nội dung có thể được chép từ chat / gửi qua Zalo), **không** phải định dạng export chuẩn của Zalo.

---

## 2. Vấn đề cần xử lý (theo văn bản)

| Vấn đề               | Mô tả ngắn                                                                                                    |
| -------------------- | ------------------------------------------------------------------------------------------------------------- |
| Gợi ý sai SKU        | Ngôn ngữ tự nhiên (“bột”, tên thương hiệu, “giống lần trước”) khó map sang mã sản phẩm nếu metadata không đủ. |
| Thiếu “trí nhớ”      | LINE **không** đảm bảo đủ lịch sử chat cho bot → agent không có bộ nhớ đáng tin trừ khi hệ thống tự lưu.      |
| Đoán thay vì tra cứu | Không có chính sách retrieval + ngưỡng tin cậy → model dễ **đoán** và sai.                                    |

---

## 3. Hướng giải — chia trách nhiệm

### A) Phía Miaolin (dữ liệu / export)

Đây là “đòn bẩy” lớn nhất nếu dữ liệu sạch và đủ trường.

- **Product master:** `product_code` (SKU), tên CN/EN, brand, category, subcategory, pack + đơn vị, cờ active, **aliases / search_keywords** (từ đồng nghĩa, tiếng Trung…).
- **Đơn hàng:** tối thiểu header (`order_id`, `customer_code`, `order_date`, `status`); khuyến nghị thêm **`tracking_number`**, **`estimated_delivery`**; tốt nhất có **dòng đơn** (`order_id`, `product_code`, `qty`, `unit_price`) để “đặt lại như lần trước”.
- **Khách hàng:** `customer_code`, `auth_username`, `auth_phone` (chuẩn hóa format).
- **Tồn kho:** `product_code`, `qty_available` (và tùy chọn kho, tồn an toàn).

### B) Phía Craveva (triển khai hệ thống)

- **Truy vấn có cấu trúc trước:** với giá / tồn / trạng thái đơn → AI phải dựa trên kết quả DB (ví dụ MySQL); nếu mơ hồ → **hỏi làm rõ**, không đoán.
- **Pipeline match sản phẩm:** (1) nhận diện intent/entity (brand, category, pack size) → (2) tìm ứng viên trong DB (SKU khớp, alias, brand+category) → (3) xếp hạng (brand > category > fuzzy tên; ưu tiên còn hàng nếu cần đặt) → (4) **ngưỡng tin cậy** — thấp thì hỏi lại, không trả lời bừa.
- **Bộ nhớ phía Craveva:** log tin nhắn / sản phẩm vừa thảo luận / đơn gần đây (từ export), không phụ thuộc hoàn toàn lịch sử LINE.
- **An toàn:** rate-limit xác thực, không lộ dữ liệu khách khác, hết hàng thì gợi ý thay thế nhưng ghi rõ là gợi ý, xác nhận kèm SKU + quy cách.

### C) Gửi cho Miaolin (danh sách hành động)

Xác nhận/bổ sung trong export hàng ngày: cột sản phẩm đầy đủ, orders (header + lines nếu được), customers (auth), inventory.

### D) Kế hoạch thực hiện (4 bước)

1. Miaolin: bổ sung field + mẫu export đơn.
2. Craveva: retrieval chặt + luồng hỏi làm rõ.
3. Craveva: bảng/ghi nhớ + logic “mua lại”.
4. Test 30–50 câu thực tế (bột, brand, quy cách, hết hàng, trạng thái đơn).

---

## 4. “Sếp / team muốn làm gì?” — tóm tắt ý định nghiệp vụ

**Mục tiêu:** Chatbot B2B **đúng mã hàng**, trả lời được kiểu **“lần trước đặt gì” / đặt lại**, **giảm ảo giác của AI**.

**Cách tiếp cận:**

- Không chỉ “làm model thông minh hơn” mà **bắt có dữ liệu chuẩn từ Miaolin** (SKU, alias, lịch sử đơn, tồn).
- **Bắt phía mình** tra DB + quy tắc tin cậy + lưu memory + guardrail, rồi **test có kiểm soát** trước khi coi là production-ready.

**Lưu ý kênh:** Văn bản gốc nhắc **LINE** là kênh bot; tên file `zalo chat` chỉ phản ánh nơi lưu/trao đổi nội dung.

---

## 5. Gợi ý bước tiếp (trong file gốc)

Nếu có mẫu một dòng `products.csv` (hoặc ảnh cột), có thể chỉ ra **3–5 field đang thiếu** gây sai gợi ý và **câu chữ chính xác** để yêu cầu Miaolin.

---

---

## 6. Đây có phải “bản phân tích cuối” không?

| Tài liệu                                           | Vai trò                                                                                                                                                                |
| -------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`CHAT/zalo chat.txt`**                           | **Bản gốc** (tiếng Anh): đề xuất kỹ thuật đầy đủ — giữ làm chuẩn trích dẫn.                                                                                            |
| **`CHAT/zalo_chat_phan_tich.md`**                  | **Bản tóm tắt / phiên dịch có cấu trúc** (tiếng Việt) của nội dung trên — dùng khi cần đọc nhanh hoặc trình bày nội bộ. Nên **cập nhật cùng lúc** nếu sửa file `.txt`. |
| **`CHAT/maolin_new_folder_vs_chatbot_yeu_cau.md`** | **Đối chiếu dữ liệu thực tế** (`PROJECT MAOLIN New/`) với cùng một mục tiêu — không thay thế bản gốc, mà trả lời “file Excel hiện có đủ/thiếu gì”.                     |

**Cùng một nội dung văn bản** còn có thể lưu dưới tên khác (ví dụ `whatapp chat.txt` trong `CHAT/`) — nếu hai file trùng byte-for-byte thì chỉ cần **một bản gốc** để tránh nhầm lẫn khi chỉnh sửa.

---

_Tóm tắt từ nội dung `CHAT/zalo chat.txt` (đã đối chiếu lại cho khớp đoạn A2/B2)._
