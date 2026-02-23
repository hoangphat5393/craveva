# Bản dịch tiếng Việt — Đặc tả yêu cầu hệ thống (SRS): Nền tảng Marketplace B2B đa thương nhân (Multi‑Merchant)

## 1.0 Giới thiệu

### 1.1 Mục đích và phạm vi

Tài liệu này cung cấp các yêu cầu kỹ thuật chính thức (authoritative) để thiết kế, phát triển và kiểm thử một nền tảng marketplace B2B đa thương nhân. Đây là tài liệu tham chiếu chính cho các bên liên quan (đội phát triển, QA và Product) nhằm đảm bảo hệ thống cuối cùng đáp ứng đúng mục tiêu chức năng và mục tiêu kinh doanh.

Phạm vi hệ thống bao phủ năng lực end‑to‑end, cung cấp môi trường mua hàng **an toàn và bắt buộc đăng nhập (có tài khoản)** cho cả người mua cá nhân và doanh nghiệp. Năng lực cốt lõi gồm:

- Công cụ định giá động nhiều tầng (dynamic multi‑level pricing engine)
- Xử lý đơn hàng đa thương nhân tự động (automated multi‑merchant order processing)
- Cổng giao nhận tích hợp với nhà cung cấp logistics bên thứ ba (integrated delivery portal)

Mọi tương tác trên nền tảng được quản trị bởi kiến trúc tài khoản người dùng vững chắc, làm nền cho định giá tùy biến, kiểm soát quyền và phạm vi hiển thị dữ liệu.

## 2.0 Kiến trúc tài khoản người dùng

Một hệ thống tài khoản dựa trên vai trò (Role‑Based) là nền tảng chiến lược của nền tảng. Nó đảm bảo an toàn, phân tách dữ liệu và trải nghiệm tùy biến theo từng đối tượng, bao phủ các tình huống quan trọng như: định giá theo khách hàng, quản lý đơn hàng và quản trị cấp thương nhân.

### 2.1 Loại tài khoản và vai trò

Nền tảng hỗ trợ 3 loại tài khoản chính, mỗi loại có ranh giới quyền hạn và năng lực rõ ràng:

#### Tài khoản khách hàng công khai (Public Customer Account)

- Mục đích: phục vụ người dùng cá nhân không gắn với công ty đã đăng ký.
- Quyền:
    - Có thể duyệt marketplace và xem sản phẩm.
    - Chỉ thấy **giá công khai tiêu chuẩn (Public Pricing)**.
    - Bắt buộc tạo tài khoản mới được mua; **không hỗ trợ guest checkout**.

#### Tài khoản người dùng doanh nghiệp (B2B) (Company User Account)

- Mục đích: phục vụ nhân viên/đại diện được ủy quyền của công ty khách hàng B2B.
- Quyền:
    - Được hưởng tier giá B2B ưu đãi và chiết khấu đã thương lượng.
    - Thấy bảng so sánh giá rõ ràng để làm nổi bật số tiền tiết kiệm.
    - Có thể đặt hàng thay mặt công ty.

#### Tài khoản công ty thương nhân (Merchant Company Account)

- Mục đích: phục vụ công ty bán sản phẩm/dịch vụ trên nền tảng.
- Quyền:
    - Quản lý thiết lập marketplace cấp công ty và danh mục sản phẩm niêm yết.
    - Thiết lập các tầng giá: giá công khai, tier giá B2B, giá theo số lượng/bậc thang.
    - Thiết lập **giá riêng theo khách hàng (Client‑Level Pricing / Client‑Specific Pricing)** cho từng khách hàng B2B.

### 2.2 Quy trình tạo tài khoản và xác thực

Nền tảng cung cấp onboarding khác nhau cho khách hàng công khai và khách hàng B2B để giảm ma sát đăng ký và bảo đảm gắn đúng quan hệ.

#### Luồng khách hàng công khai (Public Customer Flow)

1. Người dùng có thể duyệt marketplace mà không cần tài khoản.
2. Người dùng thêm sản phẩm vào giỏ.
3. Khi checkout, hệ thống bắt buộc người dùng phải tạo tài khoản.
4. Tài khoản chỉ được kích hoạt và cho phép thanh toán sau khi người dùng xác thực email.

#### Luồng khách hàng B2B (B2B Customer Flow)

1. Admin của công ty B2B gửi lời mời (invite) cho người dùng mới.
2. Người dùng tạo tài khoản qua link lời mời.
3. Tài khoản mới tự động liên kết với công ty mẹ và thỏa thuận giá/chiết khấu tương ứng.
4. Sau khi xác thực, vai trò của người dùng quyết định quyền lợi giá/chiết khấu; công cụ định giá sẽ quản lý và áp dụng thống nhất.

## 3.0 Công cụ định giá có thể tùy biến

Công cụ định giá linh hoạt là yếu tố sống còn. Đây là lợi thế cạnh tranh lõi giúp nền tảng hỗ trợ quan hệ giao dịch B2B phức tạp (giá thương lượng, chiết khấu theo lượng, hợp đồng theo khách hàng), vượt qua mô hình marketplace giao dịch đơn giản.

### 3.1 Logic phân cấp định giá (Pricing Hierarchy Logic)

Hệ thống phải áp dụng quy tắc định giá theo thứ tự ưu tiên rõ ràng để tính giá chốt cho người dùng. **Chiết khấu theo lượng (Volume Discounts)** được tính sau khi đã xác định giá cơ sở và sau đó áp dụng như bước cuối.

Thứ tự phân cấp:

- Level 1: **Giá cơ sở của sản phẩm (Base Product Price)** — giá mặc định, ưu tiên thấp nhất.
- Level 2: **Giá công khai (Public Pricing)** — ghi đè giá cơ sở, hiển thị cho người dùng chưa đăng nhập/khách công khai.
- Level 3: **Tier giá B2B (B2B Pricing Tier)** — ghi đè giá công khai, áp dụng cho người dùng B2B đã đăng nhập thuộc tier (ví dụ Enterprise, Premium).
- Level 4: **Giá riêng theo khách hàng (Client‑Specific Pricing)** — ưu tiên cao nhất; thương nhân cấu hình giá/chiết khấu cho một công ty khách hàng B2B cụ thể, ghi đè các cấu hình khác.
- Level 5: **Chiết khấu theo lượng (Volume Discounts)** — áp dụng như điều chỉnh cuối cùng trên giá đã xác định ở các level trên (ví dụ áp dụng “giá theo thùng/carton rate” sau khi đã chọn giá riêng).

### 3.2 Hiển thị giá và truyền tải giá trị (Pricing Display and Value Communication)

Nền tảng cung cấp cho thương nhân quyền kiểm soát cách hiển thị tier giá, đồng thời truyền tải rõ ràng giá trị và khoản tiết kiệm cho khách hàng B2B.

Hai cách cấu hình hiển thị tier:

- **Công khai tier giá trên website (Publish Pricing Tiers on Website)**: hiển thị tên tier và mức giảm (ví dụ “Enterprise Tier: giảm 20%”) để thúc đẩy chuyển đổi đăng ký B2B.
- **Giữ tier giá ở chế độ riêng tư (Keep Pricing Tiers Private)**: ẩn thông tin tier B2B với bên ngoài; chỉ khi khách đăng nhập và được gán tier thì mới hiển thị giá tương ứng.

Với người dùng B2B đã đăng nhập, hệ thống phải hiển thị **khối so sánh giá (Pricing Comparison Display)** nhằm thể hiện rõ giá trị của định giá ưu đãi, tối thiểu gồm:

- Sản phẩm: Widget X
- Giá gốc:
- Giá của bạn: 75.00 (Giá B2B)
- Bạn tiết kiệm: $25.00 (25%)
- Các ưu đãi đã áp dụng:
    - Giá doanh nghiệp: -$15.00 (15%)
    - Chiết khấu theo lượng: -$10.00 (10%)

Khi giá đã được xác định và thêm vào giỏ, hệ thống phải xử lý đơn theo logic đa thương nhân.

<!-- IMPORTAN NOTE -->

## 4.2 Yêu cầu tích hợp ERP

Hệ thống phải có khả năng tạo file xuất đơn hàng tương thích với hệ thống ERP của khách hàng. Mục tiêu tích hợp ưu tiên là **Digiwin (鼎新)**.

Yêu cầu này xuất phát từ tình huống cụ thể của khách hàng (ví dụ Miaolin Foods) để giải quyết bài toán **“Flat SKU”**. Vì vậy hệ thống cần triển khai **bản đồ SKU ảo (Virtual SKU Map)** để chuyển đổi số lượng đặt hàng phía front‑end thành SKU phía back‑end theo yêu cầu ERP.

Ví dụ: để hỗ trợ logic “**thùng/carton vs đơn vị/unit**” của Miaolin, khi số lượng đặt hàng của một sản phẩm đạt **từ 6 đơn vị trở lên**, hệ thống phải ánh xạ trong file xuất Digiwin sang **SKU dạng thùng (Box SKU)** tương ứng, bảo đảm tồn kho và fulfillment chính xác.

Để fulfil các đơn tách theo thương nhân đúng và hiệu quả, cần một hệ thống giao nhận tích hợp sâu và đủ mạnh.

<!-- END IMPORTAN NOTE -->

## 5.0 Kiến trúc cổng giao nhận (Delivery Portal Architecture)

Cổng tích hợp này kết nối API trực tiếp với các nhà cung cấp logistics bên thứ ba, cung cấp khả năng tính cước vận chuyển động trên toàn nền tảng. Nó quản trị tập trung các lựa chọn giao hàng, tính cước và theo dõi để tạo một giải pháp thống nhất và mở rộng được cho tất cả thương nhân.

### 5.1 Cấu hình SuperAdmin

SuperAdmin có một module “**Quản lý giao nhận (Delivery Management)**” với các năng lực:

#### Quản lý công ty giao nhận (Delivery Company Management)

- Thêm/sửa/xóa công ty giao nhận.
- Cấu hình API credential cho từng nhà cung cấp.
- Bật/tắt trạng thái sử dụng.
- Thiết lập thứ tự ưu tiên hiển thị (priority) để sắp xếp trên UI.

#### Cấu hình API (API Configuration)

Cung cấp bộ trường cấu hình động cho mỗi nhà cung cấp, bao gồm:

- API endpoint URL
- API key và/hoặc secret
- Cơ chế xác thực (ví dụ Bearer Token, API Key)
- Cấu hình rate limit
- Webhook URL để nhận cập nhật tracking

#### Tùy chọn giao hàng (Delivery Options)

Cấu hình các mức dịch vụ, ví dụ:

- Giao tiêu chuẩn (Standard Delivery)
- Giao nhanh (Express Delivery)
- Giao trong ngày (Same‑day Delivery)
- Giao theo lịch hẹn (Scheduled Delivery)

#### Quy tắc tính giá (Pricing Rules)

Hỗ trợ engine quy tắc linh hoạt, bao gồm:

- Cước nền
- Tính theo trọng lượng
- Tính theo khoảng cách
- Tính theo thể tích
- Ngưỡng đơn tối thiểu để miễn phí vận chuyển

### 5.2 Luồng tính cước vận chuyển (Delivery Cost Calculation Flow)

Hệ thống phải tính và hiển thị cước khi checkout theo quy trình tự động sau:

1. Khách hàng thêm sản phẩm từ một hoặc nhiều thương nhân vào giỏ và vào checkout.
2. Hệ thống xác định thương nhân duy nhất của từng sản phẩm.
3. Với từng “sub‑order” theo thương nhân, hệ thống tính thông tin kiện hàng (trọng lượng/kích thước) và gọi API nhà giao nhận kèm địa chỉ nhận.
4. Hệ thống tổng hợp phản hồi từ API và hiển thị các phương án giao hàng cùng phí tương ứng (ví dụ: Standard $5, Express $10).
5. Khách hàng chọn phương án giao hàng cho từng lô hàng theo thương nhân.
6. Sau khi xác nhận đơn, hệ thống gọi API nhà giao nhận để tạo đơn/đặt lịch lấy hàng và nhận **tracking number** trả lại cho khách.

## 6.0 Thiết kế Database Schema

Các schema dưới đây là nền tảng dữ liệu cho toàn hệ thống, nhằm bảo đảm tính nhất quán, khả năng mở rộng và thực thi đúng logic nghiệp vụ.

### 6.1 Các bảng mới (New Table Schemas)

#### Bảng: `delivery_companies`

| Field                 | Type     | Mô tả/Ghi chú                                           |
| --------------------- | -------- | ------------------------------------------------------- |
| id                    | INT      | Khóa chính                                              |
| name                  | VARCHAR  | Ví dụ “Ninja Van”, “Lalamove”                           |
| code                  | VARCHAR  | Ví dụ “ninja_van”, “lalamove”                           |
| api_provider          | ENUM     | Ví dụ `ninja_van`, `lalamove`, `grab_express`, `custom` |
| api_endpoint_url      | VARCHAR  | Base URL của API                                        |
| api_key               | VARCHAR  | API Key (lưu mã hóa)                                    |
| api_secret            | VARCHAR  | API Secret (lưu mã hóa)                                 |
| authentication_type   | ENUM     | Ví dụ `bearer_token`, `api_key`, `oauth`                |
| is_active             | BOOLEAN  | Cho phép sử dụng nhà cung cấp                           |
| priority              | INTEGER  | Thứ tự hiển thị trên UI                                 |
| supports_tracking     | BOOLEAN  | Có hỗ trợ tracking                                      |
| supports_webhooks     | BOOLEAN  | Có hỗ trợ webhook đẩy tracking                          |
| webhook_url           | VARCHAR  | URL nhận tracking update                                |
| rate_limit_per_minute | INTEGER  | Giới hạn gọi API/phút                                   |
| timeout_seconds       | INTEGER  | Timeout kết nối                                         |
| config                | JSON     | Cấu hình động bổ sung                                   |
| created_at            | DATETIME | Thời điểm tạo                                           |
| updated_at            | DATETIME | Thời điểm cập nhật                                      |

#### Bảng: `delivery_zones`

| Field               | Type     | Mô tả/Ghi chú                |
| ------------------- | -------- | ---------------------------- |
| id                  | INT      | Khóa chính                   |
| delivery_company_id | INT      | FK tới `delivery_companies`  |
| zone_name           | VARCHAR  | Ví dụ “Central”, “North”     |
| postal_code_ranges  | JSON     | Dải mã bưu chính của khu vực |
| base_cost           | DECIMAL  | Cước nền theo khu vực        |
| cost_per_kg         | DECIMAL  | Phụ phí mỗi kg               |
| cost_per_km         | DECIMAL  | Phụ phí mỗi km               |
| estimated_days_min  | INTEGER  | Số ngày giao tối thiểu       |
| estimated_days_max  | INTEGER  | Số ngày giao tối đa          |
| created_at          | DATETIME | Thời điểm tạo                |
| updated_at          | DATETIME | Thời điểm cập nhật           |

#### Bảng: `delivery_options`

| Field                | Type     | Mô tả/Ghi chú                     |
| -------------------- | -------- | --------------------------------- |
| id                   | INT      | Khóa chính                        |
| delivery_company_id  | INT      | FK tới `delivery_companies`       |
| option_name          | VARCHAR  | Ví dụ “Standard”, “Express”       |
| option_code          | VARCHAR  | Ví dụ “standard”, “express”       |
| estimated_hours      | INTEGER  | Số giờ giao dự kiến               |
| base_cost_multiplier | DECIMAL  | Hệ số nhân lên cước nền theo zone |
| is_active            | BOOLEAN  | Cho phép sử dụng option           |
| created_at           | DATETIME | Thời điểm tạo                     |
| updated_at           | DATETIME | Thời điểm cập nhật                |

#### Bảng: `order_deliveries`

| Field                   | Type     | Mô tả/Ghi chú                                                         |
| ----------------------- | -------- | --------------------------------------------------------------------- |
| id                      | INT      | Khóa chính                                                            |
| order_id                | INT      | FK tới bảng `orders`                                                  |
| merchant_company_id     | INT      | Công ty thương nhân chịu trách nhiệm fulfil                           |
| delivery_company_id     | INT      | Nhà giao nhận sử dụng                                                 |
| delivery_option_id      | INT      | FK tới `delivery_options`                                             |
| tracking_number         | VARCHAR  | Mã vận đơn từ API                                                     |
| shipping_cost           | DECIMAL  | Phí vận chuyển cuối cùng                                              |
| delivery_address        | TEXT     | Địa chỉ nhận đầy đủ                                                   |
| delivery_contact_name   | VARCHAR  | Tên người nhận                                                        |
| delivery_contact_phone  | VARCHAR  | SĐT người nhận                                                        |
| status                  | ENUM     | `pending`, `booked`, `picked_up`, `in_transit`, `delivered`, `failed` |
| estimated_delivery_date | DATETIME | Ngày giao dự kiến từ nhà cung cấp                                     |
| actual_delivery_date    | DATETIME | Ngày giao thực tế                                                     |
| api_response            | JSON     | Lưu phản hồi API lúc tạo đơn/đặt lịch                                 |
| webhook_data            | JSON     | Lưu dữ liệu cập nhật từ webhook                                       |
| created_at              | DATETIME | Thời điểm tạo                                                         |
| updated_at              | DATETIME | Thời điểm cập nhật                                                    |

#### Bảng: `product_pricing_settings`

| Field                         | Type     | Mô tả/Ghi chú                              |
| ----------------------------- | -------- | ------------------------------------------ |
| id                            | INT      | Khóa chính                                 |
| product_id                    | INT      | FK tới `products` (bắt buộc unique)        |
| public_price                  | DECIMAL  | Ghi đè giá cơ sở cho khách công khai       |
| use_base_price_for_public     | BOOLEAN  | Nếu `true` thì public dùng `product.price` |
| publish_tier_pricing          | BOOLEAN  | B2B tier có hiển thị công khai hay không   |
| allow_client_specific_pricing | BOOLEAN  | Bật/tắt giá riêng theo khách               |
| created_at                    | DATETIME | Thời điểm tạo                              |
| updated_at                    | DATETIME | Thời điểm cập nhật                         |

#### Bảng: `client_product_pricing`

| Field                 | Type     | Mô tả/Ghi chú                       |
| --------------------- | -------- | ----------------------------------- |
| id                    | INT      | Khóa chính                          |
| company_id            | INT      | ID công ty bán (seller)             |
| client_id             | INT      | ID công ty/người mua nhận giá riêng |
| product_id            | INT      | Sản phẩm áp dụng                    |
| custom_price          | DECIMAL  | Giá cố định riêng theo khách        |
| custom_discount_type  | ENUM     | Ví dụ `percentage`, `fixed_amount`  |
| custom_discount_value | DECIMAL  | Giá trị chiết khấu                  |
| is_active             | BOOLEAN  | Bật/tắt rule                        |
| valid_from            | DATE     | Ngày hiệu lực bắt đầu               |
| valid_to              | DATE     | Ngày hiệu lực kết thúc              |
| created_at            | DATETIME | Thời điểm tạo                       |
| updated_at            | DATETIME | Thời điểm cập nhật                  |

### 6.2 Sửa đổi bảng hiện có (Modifications to Existing Tables)

Để hỗ trợ các năng lực mới, cần bổ sung các trường sau:

#### Bảng `orders`

- `buyer_type` (ENUM: `public_customer`, `individual_customer`, `company_customer`)
- `buyer_user_id` (INT, nullable)
- `buyer_company_id` (INT, nullable)
- `seller_company_id` (INT)
- `marketplace_order_number` (VARCHAR, unique)
- `is_marketplace_order` (BOOLEAN)
- `order_split_by_merchant` (JSON)

#### Bảng `users`

- `user_type` (ENUM: `employee`, `client`, `public_customer`)
- `is_public_customer` (BOOLEAN)
- `company_id` (INT, nullable)

#### Bảng `products`

- `is_marketplace_listed` (BOOLEAN)
- `marketplace_visibility` (ENUM: `public`, `b2b_only`, `hidden`)
- `public_price` (DECIMAL, nullable)

## 7.0 Giai đoạn triển khai (Implementation Phases)

Dự án được triển khai theo từng giai đoạn để quản lý độ phức tạp, giảm rủi ro và tạo giá trị tăng dần; đồng thời thu thập phản hồi và kiểm chứng ở mỗi pha.

### Phase 1 (Tuần 1–3) — Năng lực nền tảng

- Phát triển module thiết lập cốt lõi cho thương nhân.
- Triển khai checkout bắt buộc đăng nhập.
- Xây dựng catalog marketplace cơ bản để khám phá sản phẩm.
- Thiết lập logic phân biệt giá công khai và giá B2B.

### Phase 2 (Tuần 4–6) — Hệ thống định giá

- Xây dựng pricing engine tùy biến đầy đủ (giá công khai, tier, giá riêng theo khách).
- Hiển thị so sánh giá và số tiền tiết kiệm cho người dùng.
- Tích hợp pricing engine với module quản lý khách hàng.

### Phase 3 (Tuần 7–9) — Cổng giao nhận

- Phát triển UI cấu hình giao nhận cho SuperAdmin.
- Tích hợp API các công ty giao nhận bên thứ ba.
- Tính phí vận chuyển realtime tại checkout.
- Theo dõi vận chuyển cho đơn hàng.

### Phase 4 (Tuần 10–11) — Đơn hàng đa thương nhân

- Nâng cấp giỏ hàng hỗ trợ sản phẩm từ nhiều thương nhân.
- Triển khai logic tự động tách đơn.
- Cơ chế gán giao nhận theo từng thương nhân.
- Cập nhật màn quản lý đơn cho khách hàng và thương nhân.

### Phase 5 (Tuần 12–13) — Tối ưu & kiểm thử

- Tối ưu UI/UX toàn nền tảng.
- Chạy kiểm thử end‑to‑end (tích hợp & hiệu năng).
- Tối ưu hiệu năng và hoàn thiện tài liệu.

## 8.0 Các câu hỏi cần làm rõ (Open Questions for Clarification)

Các câu hỏi cần xác nhận trước khi triển khai để tránh làm lại tốn kém:

- Cấu hình tài xế (Driver Settings): có cần hệ thống quản lý tài xế đầy đủ không, hay nền tảng sẽ phụ thuộc hoàn toàn vào API logistics bên thứ ba?
- Công ty giao nhận (Delivery Companies): giai đoạn đầu ưu tiên tích hợp những đơn vị giao nhận địa phương nào tại Singapore?
- Hiển thị giá (Pricing Display): UI/UX mục tiêu cho phần so sánh giá để B2B hiểu rõ và tăng chuyển đổi là gì?
