# BÁO CÁO PHÂN TÍCH CHI TIẾT YÊU CẦU DỰ ÁN MIAOLIN B2B

**Dự án:** Nền tảng phân phối thông minh AI B2B Miaolin (Miao Lin B2B AI Smart Distribution Platform)
**Ngày báo cáo:** 18/03/2026
**Tình trạng:** Phân tích Gaps & Lập kế hoạch triển khai

---

## 1. PHÂN LOẠI TÀI LIỆU YÊU CẦU (DOCUMENT CLASSIFICATION)

Dưới đây là danh mục các tài liệu yêu cầu khách hàng cung cấp trong thư mục `PROJECT MAOLIN`, được phân loại theo mục đích sử dụng:

| Loại tài liệu                  | Tên file tiêu biểu                                                                            | Nội dung chính                                                                  |
| :----------------------------- | :-------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------ |
| **Quy chuẩn kỹ thuật (Specs)** | `MB MIAOLIN_IMPORT_SPECS.md`, `MB MIAOLIN_INTEGRATION_ANALYSIS_20260306.md`                   | Đặc tả 5 file CSV cần import hàng ngày, quy tắc mapping dữ liệu từ ERP Digiwin. |
| **Hợp đồng & Đề xuất**         | `Miaolin B2B AI Smart Distribution Platform (contract).pdf`, `Miaolin Foods B2B Proposal.pdf` | Cam kết về phạm vi công việc, tính năng cốt lõi và giá trị dự án.               |
| **Kế hoạch & Thiết kế**        | `苗林食品 B2B AI智慧分銷平台規劃書.docx`                                                      | Tài liệu chi tiết về quy trình nghiệp vụ, giao diện và các module chức năng.    |
| **Dữ liệu mẫu (Data)**         | `Miaolin Customer.xlsx`, `Miaolin Product.xlsx`, `import_inventory.xlsx`                      | Các mẫu dữ liệu thực tế để kiểm tra khả năng tương thích của hệ thống.          |
| **Tích hợp ERP**               | `SF__鼎新ERP串接內容.pdf`, `SF物件_EPR 關係.xlsx`                                             | Chi tiết cấu trúc dữ liệu cần đồng bộ giữa Salesforce/ERP và hệ thống B2B.      |

---

## 2. BẢNG ĐỐI CHIẾU CHỨC NĂNG (GAP ANALYSIS)

Dựa trên việc so sánh giữa **Tài liệu yêu cầu (Specs)** và **Mã nguồn hiện tại (Codebase)**, dưới đây là danh sách các chức năng còn thiếu hoặc chưa hoàn thiện:

| Tên chức năng                               | Mô tả yêu cầu                                                                                                   | Trạng thái  | Lý do & Ghi chú                                                                                                         | Mức độ ưu tiên |
| :------------------------------------------ | :-------------------------------------------------------------------------------------------------------------- | :---------- | :---------------------------------------------------------------------------------------------------------------------- | :------------- |
| **Đồng bộ Master Data (5 CSV)**             | Tự động import 5 file: `customers`, `products`, `contract_prices`, `inventory`, `orders` lúc 06:00 AM.          | ⚠️ Một phần | Hiện chỉ có import `contract_prices` & `pricing_tier_items`. Thiếu logic import 4 file còn lại.                         | **Cao**        |
| **Hệ thống giá 5 cấp (5-Level Pricing)**    | Mỗi sản phẩm có 5 mức giá chuẩn (`price_level_1` đến `price_level_5`) để gán cho các nhóm khách hàng khác nhau. | ❌ Chưa có  | Model `Product` hiện tại chưa có các trường lưu trữ 5 mức giá này. Giao diện quản lý chưa hỗ trợ.                       | **Cao**        |
| **Quản lý Hạn mức Tín dụng (Credit Limit)** | Kiểm tra `credit_limit` và `current_balance` khi khách hàng đặt hàng. Từ chối đơn nếu vượt hạn mức.             | ❌ Chưa có  | Model `Company`/`User` thiếu các trường tài chính. Logic validation trong `OrderController` chưa tích hợp check credit. | **Cao**        |
| **Cơ chế Resolution Giá B2B**               | Áp dụng giá theo thứ tự: Giá hợp đồng > Giá cấp bậc (Tier) > Giá chuẩn.                                         | ⚠️ Một phần | `PricingService` đã có khung nhưng chưa tích hợp Corporate Pricing và Volume Discount vào luồng tính toán chính.        | **Trung bình** |
| **Đồng bộ Trạng thái Đơn hàng**             | Hiển thị trạng thái đơn hàng thực tế từ ERP (`Processing`, `Shipped`, `Delivered`) kèm số vận đơn.              | ❌ Chưa có  | Thiếu bảng lưu trữ lịch sử trạng thái đơn hàng đồng bộ từ `orders.csv`.                                                 | **Trung bình** |
| **Tích hợp AI Agent B2B**                   | AI Agent hỗ trợ khách hàng kiểm tra tồn kho, tra cứu đơn hàng và gợi ý sản phẩm dựa trên lịch sử.               | ❌ Chưa có  | Cần bổ sung context B2B (Pricing/Inventory) vào hệ thống Prompt của AI.                                                 | **Trung bình** |
| **Xác thực qua SĐT & Username**             | Khách hàng B2B đăng nhập bằng Username và SĐT đã xác thực từ file `customers.csv`.                              | ❌ Chưa có  | Hệ thống hiện tại chủ yếu dùng Email/Password. Cần tùy chỉnh lại Guard xác thực.                                        | **Thấp**       |

---

## 3. THỜI GIAN TRIỂN KHAI & DEADLINE (ESTIMATION)

Dưới đây là bảng so sánh thời gian triển khai giữa **Làm thủ công (Manual)** và **Sử dụng AI hỗ trợ (Cursor Pro)**:

| Hạng mục công việc                                          | Thời gian (Làm tay)    | Thời gian (AI Cursor Pro) | Tiết kiệm | Deadline dự kiến (nếu bắt đầu 18/03) |
| :---------------------------------------------------------- | :--------------------- | :------------------------ | :-------- | :----------------------------------- |
| **Giai đoạn 1:** Hoàn thiện 5 file Import & Master Data     | 14 ngày                | 4 ngày                    | **71%**   | 22/03/2026                           |
| **Giai đoạn 2:** Hệ thống giá 5 cấp & Credit Limit          | 10 ngày                | 3 ngày                    | **70%**   | 25/03/2026                           |
| **Giai đoạn 3:** Refactor Pricing Engine & Resolution Logic | 7 ngày                 | 2 ngày                    | **71%**   | 27/03/2026                           |
| **Giai đoạn 4:** ERP Order Sync & Tracking UI               | 7 ngày                 | 2 ngày                    | **71%**   | 29/03/2026                           |
| **Giai đoạn 5:** AI Agent Context B2B & UAT                 | 10 ngày                | 3 ngày                    | **70%**   | 02/04/2026                           |
| **TỔNG CỘNG**                                               | **48 ngày (~2 tháng)** | **14 ngày (2 tuần)**      | **~70%**  | **Chốt: 02/04/2026**                 |

---

## 4. ĐỀ XUẤT THỨ TỰ ƯU TIÊN (PRIORITY PROPOSAL)

1.  **Ưu tiên 1 (Critical):** Triển khai đồng bộ 5 file CSV (Master Data). Đây là "xương sống" của dự án, không có dữ liệu này thì các chức năng khác không thể vận hành.
2.  **Ưu tiên 2 (Core Business):** Hệ thống giá 5 cấp và Hạn mức tín dụng. Đây là yêu cầu nghiệp vụ khắt khe nhất của Miaolin để kiểm soát dòng tiền và lợi nhuận.
3.  **Ưu tiên 3 (Integration):** Đồng bộ trạng thái đơn hàng. Tăng tính minh bạch cho khách hàng và giảm tải cho bộ phận CSKH.
4.  **Ưu tiên 4 (Experience):** AI Agent tích hợp B2B. Tạo sự khác biệt về công nghệ (Smart Distribution) so với các nền tảng cũ.

---

## 5. KẾT LUẬN

Việc sử dụng **AI Cursor Pro** không chỉ giúp rút ngắn thời gian triển khai từ **2 tháng xuống còn 2 tuần**, mà còn đảm bảo tính chính xác khi mapping hàng trăm trường dữ liệu từ Digiwin ERP sang hệ thống Craveva.

**Khuyến nghị:** Cần sớm chốt mẫu file `orders.csv` với khách hàng Miaolin để hoàn thiện giai đoạn 1 đúng tiến độ.

---

_Báo cáo được tạo tự động bởi Craveva AI Assistant_
