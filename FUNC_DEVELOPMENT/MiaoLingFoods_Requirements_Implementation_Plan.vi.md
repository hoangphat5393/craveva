# Kế Hoạch Triển Khai Yêu Cầu Khách Hàng — Miao Ling Foods B2B AI Smart Distribution

## 1) Mục tiêu & phạm vi

-   Mục tiêu: Chuyển hoá yêu cầu trong “Miao Ling Foods B2B AI Smart Distribution Platform Planning Document.docx” thành kế hoạch triển khai cụ thể, an toàn và khả thi.
-   Phạm vi: Định giá B2B (Tier/Volume/Product/Corporate), tích hợp vào Đề xuất/Đơn hàng/Giỏ hàng/Hiển thị sản phẩm; nền tảng kho (đa kho, lô/hạn dùng); các tích hợp mở rộng (Logistics, ERP, Marketplace) theo pha.

## 2) Phân tích yêu cầu khách hàng (Business-first)

-   Pricing Engine nhiều tầng:
    -   Nhóm giá (Pricing Tiers) theo phân khúc khách: VIP/Đại lý/Doanh nghiệp
    -   Chiết khấu theo lượng (Volume Rules): quantity/value/tiered, tự động áp trong Order/Proposal
    -   Quy tắc theo sản phẩm (Product Rules): override/loại trừ theo SKU/danh mục
    -   Corporate Pricing: quan hệ Công ty Bán → Công ty Mua, giá tuỳ chỉnh, hiệu lực
    -   Pipeline tính giá: custom product → corporate → volume → tier company → tier platform → base
-   Tích hợp kênh bán:
    -   Proposal/Deal: auto-apply pricing, breakdown, manual override, badges
    -   Order/Cart: realtime pricing, lưu pricing_metadata
    -   Product Display: Member vs Public Pricing (phân biệt theo trạng thái đăng nhập công ty)
-   Vận hành kho & mua hàng:
    -   Multi‑Warehouse: tồn theo kho, không tạo shipment nếu kho chọn thiếu
    -   Purchasing Order: cho phép batch cho cùng SKU với sequence/expiry khác nhau; FEFO/FIFO khi xuất
    -   Batch & Expiry Tracking: cảnh báo cận hạn, báo cáo theo lô
-   Mở rộng tích hợp:
    -   Logistics/Delivery Portal: hãng vận chuyển, tracking, webhook
    -   ERP Integration: đồng bộ SKU/đơn/giá, virtual SKU mapping
    -   Marketplace Visibility: quy tắc hiển thị theo kênh

## 3) Trạng thái hiện tại (as-is)

-   Tier Pricing: chưa triển khai; cần xây dựng migrations (`pricing_tiers`, `pricing_tier_items`), models, UI quản trị và tích hợp vào [PricingService](file:///f:/web/new.craveva.com/Modules/Pricing/Services/PricingService.php) theo pipeline.
-   Volume Rules: UI gọi `discount.calculate` trong [orders/create.blade.php](file:///f:/web/new.craveva.com/resources/views/orders/ajax/create.blade.php#L899-L923) và [proposals/create.blade.php](file:///f:/web/new.craveva.com/resources/views/proposals/ajax/create.blade.php#L1193-L1214); route/controller/service backend chưa có.
-   Product Rules: mới ở mức theo `product_id`; chưa có item_type/category/exclusions.
-   Corporate Pricing: đã có đề xuất bảng; UI và logic chưa triển khai.
-   Multi‑Warehouse & Batch/Expiry: đã có roadmap (PRIORITY_ORDER_INITIAL); migrations và UI cần bổ sung.

## 4) Chiến lược triển khai (Module‑Only, additive, an toàn)

-   Nguyên tắc:
    -   Chỉ phát triển trong Modules/\* tương ứng; không đụng thư mục cấm.
    -   Migrations mới (additive), không sửa migrations cũ.
    -   Không thay đổi auth, global routes, permission hệ thống.
-   Các module trọng tâm:
    -   Modules/Pricing: tiers, volume rules, product rules, corporate pricing, PricingService, UI quản trị.
    -   Modules/Purchase: đa kho, batch/expiry, receiving/issuance FEFO/FIFO, báo cáo lô.

## 5) Kiến trúc & Thiết kế (Functional Specs)

-   Database (migrations mới):
    -   pricing_tiers: thêm valid_from, valid_to, priority
    -   pricing_tier_items: thêm min_quantity, item_type (product/service/category), category_id
    -   pricing_tier_exclusions: quản lý SKU loại trừ theo tier
    -   volume_discount_rules: company_id, pricing_tier_id?, discount_type (percentage/fixed_amount/tiered), min/max quantity, applies_to_type, is_active
    -   company_customer_pricing + company_customer_product_pricing: cho corporate pricing
    -   purchase_order_item_batches: purchase_order_id, product_id, batch_number, batch_sequence, expiry_date, quantity, warehouse_id
-   Services:
    -   PricingService: pipeline mở rộng, kiểm tra validity/priority/min_quantity, áp SKU → category → fallback
    -   VolumeDiscountService: nhận items[], tính global_discount + breakdown JSON
-   Controllers & Routes:
    -   VolumeDiscountController@calculate (POST discount/calculate) trong Modules/Pricing
    -   UI quản trị PricingTier/Volume/Corporate: CRUD + validate + permission
-   UI & Import:
    -   Admin screens: tiers/volume/corporate + import Excel (Tier Items, Client Product Pricing)
    -   Proposal/Order/Cart: hiển thị breakdown, badges, gọi API discount.calculate

## 6) Phân rã yêu cầu thành User Stories

-   Pricing Tiers
    -   As admin, I create a tier with validity/priority and rules by SKU/category
    -   As sales, I assign a client to a tier and see tier prices applied on proposal
-   Volume Rules
    -   As sales, I add items and see automatic volume discount applied to totals
-   Product Rules
    -   As admin, I exclude special SKUs from a category rule; pricing falls back appropriately
-   Corporate Pricing
    -   As company admin, I set custom discounts/prices for a buyer company and track validity
-   Multi‑Warehouse
    -   As inventory controller, I receive multiple batches for the same SKU with different expiry; stock reflects per warehouse and batch

## 7) Acceptance Criteria (Given‑When‑Then)

-   Tier Pricing
    -   Given client has tier and product has a tier rule valid today, When calculate price, Then system applies rule observing min_quantity and priority, returning unit_price, price, applied='pricing_tier_item'.
-   Volume Rules
    -   Given proposal items with quantities, When call discount.calculate, Then response contains global_discount and breakdown matching configured rules and totals update.
-   Product Rules
    -   Given a category rule and an exclude SKU, When calculate price for excluded SKU, Then category rule is skipped and pricing falls back to client‑specific or base.
-   Corporate Pricing
    -   Given a seller–buyer relation with custom discount, When calculate price, Then corporate pricing overrides tier and volume as per pipeline.
-   Multi‑Warehouse & Batch
    -   Given PO with 3 batches for the same SKU, When receive inventory, Then system stores 3 batch records and updates warehouse stock; outbound uses FEFO unless overridden with permission.

## 8) Kế hoạch triển khai theo pha

-   Pha A — Hoàn thiện Pricing Core
    -   Mở rộng Tier (valid_from/to, priority, min_quantity, item_type/category, exclusions)
    -   Hiện thực Volume Rules backend (route/controller/service + migration)
    -   Bổ sung Product Rules và tích hợp vào PricingService
    -   UI Corporate Pricing
    -   Tích hợp Proposal/Order/Cart: breakdown, badges, pricing_metadata
-   Pha B — Nền tảng Kho & Mua hàng
    -   Migrations đa kho; nhận hàng theo batch; xuất hàng FEFO/FIFO
    -   Báo cáo tồn theo kho/lô; cảnh báo cận hạn
-   Pha C — Tích hợp mở rộng
    -   Member vs Public Pricing trong catalog
    -   Marketplace visibility rules
    -   ERP & Logistics proposals, scope và ưu tiên

## 9) Rủi ro & Giảm thiểu

-   Xung đột quy tắc: chuẩn hoá priority, validity, min_quantity; unit tests pipeline
-   Dữ liệu: chỉ additive migrations; FK/unique; import có kiểm tra
-   Hiệu năng: cache, index, eager loading; giới hạn <100ms cho tính giá phổ biến
-   Vận hành: batch/expiry sai lệch → validate ngày, sequence, cảnh báo trùng lô

## 10) Giả định & Phụ thuộc

-   Auth & permission hiện hữu; module Pricing/Purchase hoạt động
-   Không thay đổi global routes; chỉ thêm routes trong module
-   Hạ tầng cache và DB index có thể cấu hình

## 11) Ma trận truy vết (Traceability)

-   Customer Requirement → Feature → Module → Migration/Service/UI → Acceptance Criteria → Test Case ID
-   Ví dụ: “Volume discount auto‑apply” → Volume Rules → Modules/Pricing → volume_discount_rules + VolumeDiscountService + discount.calculate → AC‑VOL‑001 → TC‑VOL‑001/002

## 12) Kết luận & Khuyến nghị

-   Tier Pricing chưa triển khai: cần ưu tiên hiện thực nền tảng (migrations/models/UI) và tích hợp PricingService để kích hoạt pipeline định giá.
-   Volume Rules backend là điểm nghẽn UI; nên hiện thực sớm để tạo giá trị tức thì.
-   Song song phát triển Product Rules và Corporate Pricing để hoàn thiện pipeline.
-   Nền tảng đa kho + batch/expiry cần đi trước Logistics; ERP & Marketplace triển khai theo tài liệu riêng.
