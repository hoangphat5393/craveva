# Thứ tự ưu tiên ban đầu — Kế hoạch triển khai không‑AI

Mục tiêu: đảm bảo nền tảng quản lý hàng hóa chính xác trước, sau đó áp chính sách giá B2B.

## P1 — Nền tảng Đa kho (ưu tiên cao)

-   Migrations
    -   Tạo `warehouses` (code, name, location, status, company_id)
    -   Tạo `product_warehouse_stock` (product_id, warehouse_id, on_hand, reserved)
    -   Mở rộng `purchase_stock_adjustments`: thêm `warehouse_id`
-   Service & UI
    -   Mở rộng InventoryService yêu cầu `warehouseId` cho nhập/xuất/chuyển
    -   Form nhập/xuất/chuyển: thêm dropdown chọn kho; trang tồn kho theo kho
-   Acceptance Criteria
    -   Tạo/đổi kho được; tồn kho hiển thị đúng theo từng kho
    -   Không tạo shipment nếu kho chọn không đủ hàng

## P2 — Batch & Expiry Tracking (ưu tiên cao) (task #8)

-   Migrations
    -   Thêm `batch_number`, `expiry_date` vào `purchase_stock_adjustments`
-   Nghiệp vụ
    -   Cảnh báo cận hạn dùng; tùy chọn FEFO/FIFO
-   Acceptance Criteria
    -   Lưu/hiển thị số lô và hạn dùng; báo đỏ khi cận hạn

## P3 — Đa Giá Cơ Bản (Base Multi‑Price) (ưu tiên trung bình → cao)

-   Migrations
    -   `product_prices` (price_type: standard/distributor/carton/employee)
    -   Nhóm khách (Customer Groups) và mapping nhóm → loại giá
-   UI & Service
    -   Quản trị bảng giá; áp giá theo nhóm
-   Acceptance Criteria
    -   Cùng một SKU hiển thị đúng giá theo nhóm khách và loại giá

## P4 — B2B Pricing Core (ưu tiên trung bình)

-   Migrations & Models
    -   `pricing_tiers`, `pricing_tier_items`, `volume_discount_rules`
    -   `company_customer_pricing`, `company_customer_product_pricing`
-   Service
    -   PricingService với pipeline: custom product → corporate → volume → tier company → tier platform → base
-   Tích hợp
    -   Proposal/Order/Cart: áp giá tự động; breakdown; badge “Corporate Pricing Applied”
-   Acceptance Criteria
    -   Tính giá đúng theo pipeline; ghi pricing_metadata vào chứng từ

### Ưu tiên triển khai trong 3 phần Tier Pricing

1. Volume Rules — ưu tiên đầu tiên
    - Lý do: có thể tái sử dụng nền tảng Discount hiện có; mang lại giá trị nhanh cho các đơn số lượng lớn.
    - Kết quả: hoạt động chiết khấu theo ngưỡng quantity/value/tiered trong Order/Proposal.
2. Pricing Tiers — ưu tiên thứ hai
    - Lý do: thiết lập khung phân khúc giá theo nhóm khách (VIP/Đại lý/Doanh nghiệp), làm nền cho corporate pricing.
    - Kết quả: gán khách vào tier, áp giá cơ bản theo tier và thời gian hiệu lực.
3. Product Rules — ưu tiên thứ ba
    - Lý do: cung cấp ngoại lệ/override/loại trừ ở mức SKU/danh mục; triển khai sau khi có nền tảng tier và volume.
    - Kết quả: đảm bảo các SKU đặc biệt luôn đúng giá, tránh chồng quy tắc gây sai lệch.

## P5 — Import/Mapping dữ liệu (ưu tiên trung bình)

-   Import tồn kho: map SKU + warehouse + quantity (+ batch/expiry)
-   Import giá: map Base/nhóm khách/carton/nhân viên; Customer Code → nhóm/price tier
-   Acceptance Criteria
    -   Import chạy an toàn, không phá dữ liệu hiện tại; đối chiếu kiểm kê OK

## P6 — (Tuỳ chọn) Warehouse‑Aware Pricing

-   Mở rộng PricingService nhận `warehouseId` nếu cần chính sách giá theo kho
-   Acceptance Criteria
    -   Giá có thể khác nhau theo kho (nếu bật), không ảnh hưởng pipeline mặc định

Tham chiếu hữu ích:

-   Đa kho & GAP: [gap_analysis.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/gap_analysis.md)
-   Kế hoạch đa kho: [Plan_Phase2_MultiWarehouse.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/Plan_Phase2_MultiWarehouse.md)
-   B2B Pricing: [B2B_PRICING_SYSTEM_PROPOSAL.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/B2B_PRICING_SYSTEM_PROPOSAL.md), [B2B_PRICING_SYSTEM_PROCESS.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/B2B_PRICING_SYSTEM_PROCESS.md)
