# Kế Hoạch Triển Khai Kết Hợp — B2B Pricing (Theo B2B_PRICING_SYSTEM_PROPOSAL_v1)

## 1) Tóm tắt

-   Mục tiêu: Kết hợp tài liệu B2B_PRICING_SYSTEM_PROPOSAL_v1 với kế hoạch triển khai thực tế, đảm bảo pipeline định giá B2B hoạt động, tích hợp vào đề xuất/đơn hàng/giỏ hàng/hiển thị sản phẩm.
-   Cách tiếp cận: Module‑Only Development, migrations bổ sung (additive), không sửa migrations cũ; không chạm global routes/auth.

## 2) Liên kết trực tiếp sang Proposal

-   Tài liệu gốc: [B2B_PRICING_SYSTEM_PROPOSAL_v1.md](B2B_PRICING_SYSTEM_PROPOSAL_v1.md)
-   Tài liệu khách hàng: `Miao Lin-系统需求规格.docx` (Tham chiếu yêu cầu Delivery/Shipping)
-   Phần quan trọng:
    -   Database schema: mục 1.2 — Pricing Tiers, Pricing Tier Items, Volume Discount Rules, Corporate Pricing ([tham chiếu](B2B_PRICING_SYSTEM_PROPOSAL_v1.md#L42))
    -   Pricing calculation logic: mục 3 — thứ tự áp giá ([tham chiếu](B2B_PRICING_SYSTEM_PROPOSAL_v1.md#L279))
    -   API & Integration Points: mục 5 — phương thức service và điểm tích hợp ([tham chiếu](B2B_PRICING_SYSTEM_PROPOSAL_v1.md#L387))
    -   Implementation Phases: mục 6 — pha triển khai ([tham chiếu](B2B_PRICING_SYSTEM_PROPOSAL_v1.md#L387))

## 3) Phạm vi triển khai

Hệ thống được chia thành **3 Module riêng biệt** để đảm bảo khả năng mở rộng (theo góp ý tách nhỏ chức năng của khách hàng):

1.  **Modules/Pricing**:

-   **Pricing Engine**: Tier Pricing, Volume Rules, Product Rules, Corporate Pricing.
-   **Trách nhiệm**: Tính toán giá bán (Input: Product + Client -> Output: Price).
-   **Cấu trúc Tier**: Số cấp tier và quy tắc chi tiết sẽ được khách hàng chốt trong tuần tới; schema hiện tại giữ linh hoạt (priority, valid_from/to, min_quantity) để tránh phải sửa migration.

2.  **Modules/Warehouse**:

-   **Nền tảng kho**: Multi-Warehouse, Batch & Expiry (FEFO/FIFO).
-   **PO Batch Entry**: Hỗ trợ nhập kho từ Purchase Order với cùng 1 SKU nhưng tách thành nhiều dòng/lô khác nhau (khác batch number/sequence/expiry date) - _Giải quyết Task #8_.
-   **Near-expiry context**: Đánh dấu lô hàng cận date để Pricing có thể áp thêm near-expiry discount (liên quan Purchase, Restock, Inventory).
-   **Unit System**: Lưu trữ đơn vị và quy đổi đơn vị (ví dụ 10 box = 1 carton) để đảm bảo số lượng sử dụng cho Pricing/Order luôn ở đơn vị chuẩn.
-   **Trách nhiệm**: Quản lý tồn kho, hạn dùng và đơn vị hàng hóa.

3.  **Modules/Delivery**:

-   **Delivery System (Basic)**: Tính phí vận chuyển (Flat/Table Rate), Free Ship Rules, Chọn phương thức giao hàng.
-   **Tax cho phí ship**: Cho phép cấu hình phí ship có chịu thuế hay không và áp dụng rule thuế giống phần Quotation/Order.
-   **Trách nhiệm**: Tính phí vận chuyển dựa trên kho xuất phát, địa chỉ nhận và thiết lập thuế tương ứng.

## 4) Thiết kế dữ liệu (migrations bổ sung)

### Modules/Pricing

-   `pricing_tiers`: company_id, name, description, is_active, valid_from/to, priority.
-   `pricing_tier_items`: pricing_tier_id, product_id, item_type, discount_type, discount_value, min_quantity.
-   `volume_discount_rules`: company_id, pricing_tier_id, name, discount_type, min/max quantity, discount_value.
-   `company_customer_pricing`: seller company ↔ buyer company, pricing_tier_id, custom_discount.
-   (Near-expiry discount sẽ sử dụng thêm metadata từ Warehouse/Batches; chi tiết bảng có thể được tách sang tài liệu thiết kế Pricing/Warehouse chuyên sâu.)

### Modules/Delivery

-   `shipping_zones`: company_id, name, province_ids (JSON).
-   `shipping_methods`: company_id, name, type (flat/table/free).
-   `shipping_rates`: shipping_zone_id, shipping_method_id, weight/price brackets.
-   Cấu hình `shipping_tax_behavior` (taxable/non-taxable hoặc tax class) ở mức company để đồng bộ với thiết lập Quotation/Order.

### Modules/Warehouse & Unit System

-   Bảng Batch & Expiry (được mô tả trong kế hoạch Multi-Warehouse) lưu expiry_date, quantity, warehouse_id.
-   Ngưỡng near-expiry có thể được cấu hình per sản phẩm/kho để hệ thống đánh dấu lô cận date.
-   Hệ thống đơn vị & quy đổi đơn vị (Unit/UnitType, conversion rate) được quản lý trong Inventory/Purchase; B2B Pricing đọc số lượng đã quy đổi về đơn vị chuẩn.

### Core/Orders (Update)

-   Thêm cột: `shipping_method_id`, `shipping_fee`, `tracking_code`.

Lưu ý: Tạo migration mới trong đúng thư mục Module tương ứng; không sửa migration cũ.

## 5) Dịch vụ & API (theo Proposal mục 5.1)

-   **PricingService** (Modules/Pricing)
    -   `calculatePrice(productId, companyId, customerCompanyId, quantity)`
    -   `getApplicableTiers(companyId, customerCompanyId)`
-   **VolumeDiscountService** (Modules/Pricing)
    -   `apply(items[], companyId)`: trả về global discount và breakdown.
-   **ShippingService** (Modules/Delivery)
    -   `calculateShipping(warehouseId, destinationAddress, weight)`

## 6) Pipeline áp giá (theo Proposal mục 3)

Thứ tự:

1. Company Customer Product Pricing (đặc thù nhất)
2. Company Customer Pricing (corporate)
3. Volume Discount Rules
4. Pricing Tier (company‑specific)
5. Pricing Tier (platform‑wide)
6. Base Product Price

Kiểm tra hiệu lực/ưu tiên/ngưỡng tại mỗi bước (valid_from/to, priority, min_quantity). Product Rules (danh mục/loại trừ) được xét trong bước Tier.

## 7) Tích hợp UI (theo Proposal mục 5.2)

### 7.1. Proposal/Estimate & Invoice Creation (Backend Admin UI)

-   **Hook Points**:
    -   Event `change` trên `#add-products` (Product Select):
        -   Khi chọn sản phẩm, gọi AJAX `pricing.calculate(productId, clientId, 1)` để lấy đơn giá B2B ban đầu thay vì giá gốc.
    -   Event `keyup/change` trên input `quantity` (trong bảng items):
        -   Debounce 500ms, gọi AJAX `pricing.calculate` lại để check Volume Discount/Tier mới.
        -   Cập nhật `unit_price` và hiển thị badge "Tier Price Applied" hoặc "Volume Discount".
    -   **Manual Override**:
        -   Nếu user tự sửa giá (`unit_price`), hiện cảnh báo "Custom Price Active" và tắt auto-calc cho dòng đó (trừ khi user bật lại).

### 7.2. Delivery/Shipping Integration

-   **Estimate/Proposal**:
    -   Thêm section "Shipping Estimates" dưới phần items.
    -   Nút "Calculate Shipping" gọi API check kho (Multi-Warehouse) và bảng `shipping_rates`.
-   **Invoice**:
    -   Hiển thị phí ship dòng riêng hoặc trong tổng cộng.
    -   Lưu `warehouse_id` và `batch_id` vào `invoice_items` (ẩn/hiện tùy cấu hình).
    -   **Shipping Tax**: Cho phép user chọn phí ship có chịu thuế hay không (hoặc tax class) giống cấu hình Quotation/Order.

### 7.3. Order Creation/Cart (Frontend Member)

-   Realtime pricing, lưu pricing_metadata.
-   **Delivery**: Chọn kho xuất hàng, tính phí ship chính xác.

### 7.4. Documents (PDF/View)

-   **Invoice/Estimate PDF**:
    -   Bảng items thêm cột "Original Price" và "Discount" (nếu cấu hình hiện).
    -   Dòng tổng kết: Hiển thị rõ "Total Volume Discount" nếu có.

## 8) Kế hoạch triển khai theo pha (kết hợp Proposal mục 6)

-   Pha 1 — Core Pricing Tiers (Modules/Pricing)
    -   Tạo bảng pricing_tiers, pricing_tier_items
    -   PricingTier model/relationships; admin UI quản trị tiers
    -   Basic PricingService tính giá theo client‑specific → tier → base; thêm valid_from/to, priority, min_quantity
-   Pha 2 — Volume Discount System (Modules/Pricing)
    -   Backend: route/controller/service discount.calculate
    -   Bảng volume_discount_rules; áp tự động trong proposal/order
-   Pha 3 — Product Rules (Modules/Pricing)
    -   Mở rộng tier items: item_type/category, exclusions; logic áp theo danh mục và loại trừ
-   Pha 4 — Corporate Pricing (Modules/Pricing)
    -   Bảng company_customer_pricing, company_customer_product_pricing
    -   UI quản trị quan hệ seller–buyer; override tier/platform
-   Pha 5 — Tích hợp & Hiển thị (Core App)
    -   Proposal/Estimate & Order/Cart: breakdown, badges, pricing_metadata
    -   Invoice: hiển thị chi tiết giảm giá trên PDF/View
    -   Member vs Public Pricing: hiển thị phân biệt
-   Pha 6 — Nền tảng kho (Modules/Warehouse)
    -   Multi‑Warehouse; Batch & Expiry (FEFO/FIFO) để đảm bảo vận hành ổn định
    -   **Task #8**: Tích hợp Purchase Order cho phép nhập nhiều batch/expiry cho cùng 1 SKU.
-   Pha 6.1 — Near-expiry Discount (Modules/Warehouse + Modules/Pricing)
    -   Warehouse: Chuẩn hoá batch/expiry data và cơ chế đánh dấu "near-expiry" theo ngưỡng cấu hình.
    -   Pricing: Bổ sung rule near-expiry (giảm giá theo số ngày còn lại) và thứ tự ưu tiên áp dụng trong pipeline.
-   Pha 6.2 — Unit System & Unit Conversion (liên quan Inventory/Purchase)
    -   Cho phép cấu hình đơn vị và tỷ lệ quy đổi (ví dụ 10 box = 1 carton).
    -   Bắt buộc quy đổi về đơn vị chuẩn trước khi áp Tier/Volume Rules để tránh sai lệch.
-   **Pha 7 — Delivery System (Modules/Delivery)**
    -   Tạo bảng shipping_zones, shipping_methods, shipping_rates.
    -   UI cấu hình phí vận chuyển cho Seller.
    -   UI chọn vận chuyển khi đặt hàng (Buyer) & Tính phí tự động.
    -   Bổ sung cấu hình thuế cho phí ship đồng bộ với Quotation/Order.

## 9) Acceptance Criteria (Given‑When‑Then)

-   Tier Pricing
    -   Given client có tier và rule hợp lệ, When tính giá, Then trả về unit_price, price, applied='pricing_tier_item' với kiểm tra valid_from/to, min_quantity, priority.
-   Volume Rules
    -   Given items có quantity, When gọi discount.calculate, Then response có global_discount và breakdown, tổng cập nhật đúng.
-   Product Rules
    -   Given rule theo category và exclude SKU, When tính giá SKU exclude, Then bỏ qua rule category, fallback hợp lệ.
-   Corporate Pricing

    -   Given quan hệ seller–buyer có custom discount, When tính giá, Then corporate pricing override theo pipeline.

-   Near-expiry Discount

    -   Given lô hàng còn <= N ngày tới hạn, When tạo Order/Estimate, Then hệ thống áp giảm giá near-expiry theo rule và hiển thị breakdown.

-   Unit Conversion
    -   Given sản phẩm có quy đổi 10 box = 1 carton, When user nhập 2 carton, Then hệ thống quy đổi đúng ra 20 box để tính Tier/Volume.

## 10) Rủi ro & Giảm thiểu

-   Xung đột quy tắc: chuẩn hoá priority/validity/min_quantity, viết unit tests pipeline.
-   Dữ liệu: migrations additive, FK/unique, import kiểm tra dữ liệu.
-   Hiệu năng: cache, index, eager loading; đặt mục tiêu <100ms cho tính giá phổ biến.

## 11) Ma trận truy vết

-   Proposal section → Feature → Module → Migration/Service/UI → Acceptance Criteria → Test Case ID
-   Ví dụ: “Volume Discount System” → Volume Rules → Modules/Pricing → volume_discount_rules + VolumeDiscountService + discount.calculate → AC‑VOL‑001 → TC‑VOL‑001/002

## 12) Kết luận

-   Kế hoạch kết hợp bám sát B2B_PRICING_SYSTEM_PROPOSAL_v2, triển khai theo pha an toàn, kích hoạt pipeline định giá và tích hợp UI. Kiến trúc module hóa (Pricing, Warehouse, Delivery) đảm bảo khả năng mở rộng.
