# Tóm tắt — Miao Lin: Đặc tả yêu cầu hệ thống (SRS) marketplace B2B đa thương nhân

## 1) Mục tiêu & phạm vi

-   Xây dựng marketplace B2B **đa thương nhân** với môi trường mua hàng **bắt buộc đăng nhập**.
-   Hỗ trợ cả người mua cá nhân (public customer) và người mua doanh nghiệp (B2B company user).
-   Trọng tâm năng lực: **định giá nhiều tầng**, **xử lý đơn đa thương nhân**, **tích hợp logistics qua API**, và **xuất đơn tương thích ERP (Digiwin/鼎新)**.

## 2) Nhóm người dùng & quyền chính

-   Public Customer: xem sản phẩm/giá công khai; không cho guest checkout.
-   B2B Company User: có tier giá B2B + chiết khấu; có hiển thị so sánh tiết kiệm; đặt hàng thay mặt công ty.
-   Merchant Company: quản lý niêm yết sản phẩm; cấu hình public price/tier price/volume price; cấu hình giá riêng theo khách hàng (client‑specific).

## 3) Định giá — thứ tự ưu tiên áp dụng

Thứ tự tính giá được mô tả theo tầng:

1. Base Product Price (giá cơ sở)
2. Public Pricing (giá công khai)
3. B2B Pricing Tier (tier giá B2B)
4. Client‑Specific Pricing (giá riêng theo khách hàng)
5. Volume Discounts (chiết khấu theo lượng) áp dụng như bước cuối

Yêu cầu UI:

-   Thương nhân có thể chọn **công khai tier** hoặc **giữ tier riêng tư**.
-   Với B2B đã đăng nhập: phải có **pricing comparison display** (giá gốc, giá của bạn, số tiền tiết kiệm, breakdown discount).

## 4) ERP integration (Digiwin/鼎新)

-   Hệ thống phải xuất file đơn hàng tương thích ERP.
-   Case “Miaolin Foods” yêu cầu **Virtual SKU Map** để giải quyết “Flat SKU”.
-   Rule minh họa: khi đặt >= 6 đơn vị, phải map sang **Box SKU** (carton) trong file xuất ERP.

## 5) Logistics — Delivery portal

-   Cổng giao nhận tích hợp API logistics; quản trị tập trung option/giá/tracking.
-   SuperAdmin có module cấu hình:
    -   quản lý công ty giao nhận (credential, active, priority)
    -   cấu hình API (endpoint, key/secret, auth, rate limit, webhook)
    -   delivery options (standard/express/same‑day/scheduled)
    -   pricing rules (base/weight/distance/volume/free‑shipping threshold)
-   Luồng checkout: tách theo merchant → gọi API tính phí cho từng sub‑order → khách chọn option theo merchant → tạo shipment/tracking.

## 6) Thay đổi dữ liệu (DB schema) — điểm đáng chú ý

### Bảng mới

-   `delivery_companies`: nhà giao nhận + thông tin API.
-   `delivery_zones`: vùng giao + dải postal code + công thức tính phí.
-   `delivery_options`: option dịch vụ (standard/express…) + multiplier.
-   `order_deliveries`: shipment theo merchant, tracking, phí ship, webhook.
-   `product_pricing_settings`: cấu hình giá công khai, publish tier, bật/tắt client‑specific.
-   `client_product_pricing`: giá/chiết khấu riêng theo khách hàng cho từng sản phẩm.

### Bổ sung trường vào bảng hiện có

-   `orders`: thêm buyer_type/buyer_user_id/buyer_company_id/seller_company_id + marketplace_order_number + is_marketplace_order + order_split_by_merchant.
-   `users`: thêm user_type/is_public_customer/company_id.
-   `products`: thêm is_marketplace_listed/marketplace_visibility/public_price.

## 7) Kế hoạch triển khai (theo doc)

-   Phase 1 (Tuần 1–3): nền tảng merchant + checkout bắt buộc login + catalog + public vs B2B logic.
-   Phase 2 (Tuần 4–6): pricing engine + so sánh giá + tích hợp khách hàng.
-   Phase 3 (Tuần 7–9): delivery portal + tích hợp API logistics + realtime shipping + tracking.
-   Phase 4 (Tuần 10–11): giỏ hàng đa merchant + auto split order + gán delivery theo merchant + cập nhật màn quản lý đơn.
-   Phase 5 (Tuần 12–13): UI/UX + end‑to‑end test + performance + hoàn thiện tài liệu.

## 8) Câu hỏi cần chốt trước

-   Có cần hệ thống quản lý tài xế riêng hay phụ thuộc hoàn toàn API logistics?
-   Giai đoạn đầu tích hợp những hãng giao nhận nào ở Singapore?
-   UI/UX mục tiêu của “pricing comparison display” để tối ưu chuyển đổi B2B là gì?
