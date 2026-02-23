# Hướng dẫn sử dụng Tier Pricing (Định giá theo cấp)

Tài liệu này hướng dẫn quản trị viên thiết lập và sử dụng chức năng Tier Pricing trong hệ thống.

## 1. Khái niệm

- Tier (Cấp định giá): Nhóm quy tắc định giá áp dụng cho một tập khách hàng hoặc toàn công ty.
- Tier Item (Quy tắc sản phẩm): Quy tắc chi tiết theo từng sản phẩm trong một Tier.
- Global Tier Discount: Giảm giá cấp Tier áp dụng cho tất cả sản phẩm (nếu không có quy tắc sản phẩm cụ thể).

## 2. Quy trình thiết lập

1. Tạo Tier

- Vào menu Pricing → Tiers.
- Tạo mới một Tier với tên và mô tả.
- Tùy chọn (nếu cần): nhập loại giảm giá của Tier (percentage/fixed/specific_price), giá trị giảm, thời gian hiệu lực (valid_from/valid_to), và độ ưu tiên (priority).

2. Thêm quy tắc sản phẩm (Tier Items)

- Trong chi tiết Tier, thêm các dòng quy tắc cho sản phẩm:
    - discount_type: percentage | fixed | specific_price
    - discount_value: giá trị giảm
    - is_active: bật/tắt quy tắc

3. Gán Tier cho khách hàng

- Vào Pricing → Client Tiers.
- Chọn khách hàng và gán `pricing_tier_id`.
- Lưu ý: Có thể thêm `client_code` để đồng bộ import.

## 3. Thứ tự áp dụng giá

Khi hệ thống tính giá một sản phẩm cho khách hàng:

1. Client Product Pricing (định giá riêng khách hàng–sản phẩm)
2. Pricing Tier Item (quy tắc sản phẩm trong Tier)
3. Global Tier Discount (giảm giá cấp Tier nếu được cấu hình)
4. Base Price (giá gốc sản phẩm)

Ghi chú:

- Tier chỉ áp dụng nếu Tier đang `is_active` và nằm trong khoảng thời gian `valid_from` → `valid_to` (nếu được cấu hình).

## 4. Ví dụ

- Sản phẩm A: giá gốc 100.000
- Tier VIP: discount_type = percentage, discount_value = 10
- Tier VIP có Item riêng cho Sản phẩm A: discount_type = fixed, discount_value = 5.000

Kết quả:

- Do có Tier Item cho Sản phẩm A → đơn giá = 100.000 - 5.000 = 95.000

Nếu bỏ Tier Item, còn Global Tier Discount 10% → đơn giá = 90.000

## 5. Lưu ý vận hành

- Ưu tiên cấu hình Tier Item trước, Global Tier Discount là fallback.
- Có thể dùng `valid_from/valid_to` để cài đặt chương trình khuyến mãi theo mùa.
- Đảm bảo bật `is_active` cho Tier và cho từng Tier Item.

## 6. Câu hỏi thường gặp

- Hỏi: Nếu khách có định giá riêng (Client Product Pricing) thì Tier còn tác dụng không?
    - Đáp: Không. Client Product Pricing có độ ưu tiên cao nhất.

- Hỏi: Hệ thống có áp dụng giảm theo số lượng (Volume Discount) không?
    - Đáp: Có hỗ trợ qua bảng `volume_discount_rules`, nhưng không nằm trong phạm vi hướng dẫn Tier cơ bản này.

## 7. Tham chiếu mã

- Service tính giá: [PricingService.php](file:///f:/web/new.craveva.com/Modules/Pricing/Services/PricingService.php)
- Model Tier: [PricingTier.php](file:///f:/web/new.craveva.com/Modules/Pricing/Entities/PricingTier.php)
- Bảng Tier: [2026_01_01_create_pricing_tiers_table.php](file:///f:/web/new.craveva.com/Modules/Pricing/Database/Migrations/2026_01_07_000001_create_pricing_tiers_table.php)
- Bổ sung cột Tier: [2026_01_28_add_columns_to_pricing_tiers_table.php](file:///f:/web/new.craveva.com/Modules/Pricing/Database/Migrations/2026_01_28_000010_add_columns_to_pricing_tiers_table.php)
- Bảng Tier Items: [2026_01_07_000002_create_pricing_tier_items_table.php](file:///f:/web/new.craveva.com/Modules/Pricing/Database/Migrations/2026_01_07_000002_create_pricing_tier_items_table.php)
