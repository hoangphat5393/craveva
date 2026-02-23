# Đối chiếu yêu cầu khách hàng vs đề xuất B2B Pricing

## Tài liệu tham chiếu

-   File khách hàng: FUNTIONNAL CUSTOMER REQUEST/Miao Ling Foods B2B AI Smart Distribution Platform Planning Document.docx
-   Tài liệu sếp gửi:
    -   B2B_PRICING_SYSTEM_PROPOSAL.md
    -   B2B_PRICING_SYSTEM_PROCESS.md

## Bảng đối chiếu tổng hợp

| Khu vực chức năng                               | Yêu cầu từ khách hàng (tóm tắt)                                                    | Đề xuất B2B_PRICING_SYSTEM_PROPOSAL.md                                                                     | Đề xuất B2B_PRICING_SYSTEM_PROCESS.md       | Trạng thái | Ghi chú/GAP                                                     |
| ----------------------------------------------- | ---------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------- | ---------- | --------------------------------------------------------------- |
| Pricing Tiers (giá theo phân khúc khách B2B)    | Cần phân cấp giá theo nhóm khách (đại lý/VIP/doanh nghiệp)                         | Mô tả đầy đủ: tạo/sửa tier, loại giảm (%, cố định, override), hiệu lực, ưu tiên, áp theo sản phẩm/danh mục | Không bổ sung thêm ngoài thứ tự áp dụng     | ĐÃ PHỦ     | Cần UI quản trị, migration và logic gán client vào tier         |
| Volume Rules (chiết khấu theo ngưỡng)           | Giảm giá theo số lượng/tổng tiền, tự động áp dụng                                  | Nêu rõ quantity/value/tiered, áp trong order/proposal, theo sản phẩm/danh mục                              | Ưu tiên triển khai đầu, nêu use case cụ thể | ĐÃ PHỦ     | Cần mapping với module Discount hiện có để tái sử dụng          |
| Product Rules (override/loại trừ theo sản phẩm) | Ngoại lệ/giá cố định/loại trừ theo SKU/danh mục                                    | Có phần áp dụng theo item/category, exclude item                                                           | Không bổ sung                               | ĐÃ PHỦ     | Định nghĩa ưu tiên: Product Rules > Tier > Volume               |
| Corporate Pricing (C2C)                         | Giá giữa công ty bán và công ty mua; gán tier hoặc giá tùy chỉnh cho khách công ty | Mô tả chi tiết bảng quan hệ, custom price/discount theo công ty                                            | Có use case: “Partner Pricing” cho đối tác  | ĐÃ PHỦ     | Cần màn quản trị quan hệ seller-buyer và hiệu lực               |
| Proposal/Deal Integration                       | Tự động áp đúng giá khi tạo đề xuất từ deal                                        | Nêu integration, breakdown, override thủ công, track rule applied                                          | Xác định thứ tự áp và use case              | ĐÃ PHỦ     | Cần hiển thị breakdown và badge trong UI proposal               |
| Company Login Special Pricing                   | Phân biệt giá public vs giá khi user công ty đăng nhập                             | Có yêu cầu hiển thị Member Pricing, ẩn public, áp tier công ty                                             | Có use case minh hoạ                        | ĐÃ PHỦ     | Cần đồng bộ với auth multi-company và catalog hiển thị          |
| Pricing Calculation Pipeline                    | Trình tự áp giá nhất quán                                                          | Đưa thứ tự: custom product → corporate → volume → tier company → tier platform → base                      | Nêu rõ cùng pipeline                        | ĐÃ PHỦ     | Cần unit test để xác nhận xung đột ưu tiên                      |
| Admin Panel cho Pricing                         | Màn hình quản trị tiers/volume/corporate                                           | Định nghĩa tính năng UI quản trị                                                                           | Không chi tiết thêm                         | ĐÃ PHỦ     | Cần thiết kế Blade/route/module theo quy tắc Modules/\*         |
| API & Integration Points                        | Tích hợp với đơn, đề xuất, giỏ hàng, hiển thị sản phẩm                             | Đưa các phương thức service và điểm tích hợp                                                               | Không bổ sung thêm                          | ĐÃ PHỦ     | Cần kiểm chứng với Catalog v2 endpoints hiện có                 |
| Migration & Backward Compatibility              | Chuyển đổi dữ liệu, giữ tương thích                                                | Đưa chiến lược migration, giữ dữ liệu cũ                                                                   | -                                           | ĐÃ PHỦ     | Cần tạo migration mới, không chỉnh sửa migration cũ             |
| Performance & Caching                           | Tối ưu tính giá, cache                                                             | Có khuyến nghị cache, index, eager loading                                                                 | -                                           | ĐÃ PHỦ     | Cần benchmark <100ms theo mục tiêu                              |
| Security & Audit                                | Phân quyền, audit thay đổi giá                                                     | Có cảnh báo phân quyền, audit                                                                              | -                                           | ĐÃ PHỦ     | Gắn vào permission hiện hữu; lưu lịch sử thay đổi               |
|                                                 |                                                                                    |                                                                                                            |                                             |            |                                                                 |
| AI Smart Distribution (phân phối thông minh)    | Tối ưu kho gần, tuyến giao, smart routing                                          | Không thuộc phạm vi hai file đề xuất                                                                       | Không thuộc phạm vi                         | CHƯA PHỦ   | Cần tài liệu/phân hệ riêng: logistics, multi-warehouse, routing |
| Logistics/Delivery Portal                       | Tích hợp hãng vận chuyển, tracking                                                 | Không thuộc phạm vi hai file đề xuất                                                                       | Không thuộc phạm vi                         | CHƯA PHỦ   | Cần module tích hợp vận chuyển, webhooks, SLA                   |
| ERP Integration (ví dụ Digiwin)                 | Đồng bộ SKU/đơn/giá, virtual SKU mapping                                           | Không thuộc phạm vi hai file đề xuất                                                                       | Không thuộc phạm vi                         | CHƯA PHỦ   | Cần module ERP riêng, mapping trường, hàng đợi đồng bộ          |
| Marketplace Listing & Visibility                | Quản lý hiển thị sản phẩm, public/member visibility                                | Đề cập ở phần login pricing (member vs public)                                                             | -                                           | MỘT PHẦN   | Cần bổ sung quy tắc hiển thị theo channel/marketplace           |

## Nhận định

-   Hai file của sếp **phủ đầy đủ mảng định giá B2B** (tiers, volume, product rules, corporate pricing, pipeline, UI, API, migration, performance, security). Đây là lõi đáp ứng phần “định giá B2B” trong tài liệu khách hàng.
-   Các mục liên quan **AI phân phối, logistics, ERP** không nằm trong phạm vi hai file; cần tài liệu/phân hệ bổ sung để đáp ứng trọn vẹn tài liệu khách.

## Đề xuất bước tiếp theo (triển khai theo pha)

1. Hoàn thiện Pricing Tiers + Product Rules (UI + migration + service).
2. Tích hợp Volume Rules với module Discount hiện có.
3. Corporate Pricing: màn quản trị quan hệ seller-buyer, áp giá tùy chỉnh.
4. Proposal/Order/Cart: gắn PricingService, hiển thị breakdown, badge.
5. Member vs Public Pricing: đồng bộ với auth multi-company và catalog.
6. Viết test pipeline và hiệu lực (valid_from/to, priority, exclude).
7. Lập tài liệu/đề xuất riêng cho AI Smart Distribution, Logistics, ERP.
