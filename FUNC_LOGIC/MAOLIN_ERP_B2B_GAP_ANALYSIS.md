```markdown
# Phân tích ERP + B2B + Đa kênh (Maolin / Craveva) — Chức năng, GAP, DB, Rủi ro

**Vai trò:** Tech Lead / BA / System Architect (ERP + B2B thực tế)  
**Stack tham chiếu:** Laravel 10, MySQL, codebase `craveva-staging` (đã khảo sát migration + module chính).  
**Ngày:** 2026-03-13

**Lưu ý thuật ngữ PO/DO:** Trong codebase hiện tại, **Purchase Order (PO)** = đơn **mua hàng từ nhà cung cấp** (`Modules\Purchase\Entities\PurchaseOrder`). **Delivery Order (DO)** trong DB gắn `purchase_order_id` → luồng **nhận hàng / logistics mua hàng**, **không** phải phiếu giao hàng bán cho khách (sales DO). Nếu Maolin dùng PO/DO theo nghĩa **đơn bán / phiếu giao hàng khách**, cần **làm rõ mapping** với `orders` + tài liệu nghiệp vụ (mục F).

---

## A. Danh sách chức năng cần có (target — ERP B2B + chatbot + đồng bộ)

### A.1. Quy trình bán hàng B2B

| #   | Chức năng                                | Mô tả ngắn                                                                       |
| --- | ---------------------------------------- | -------------------------------------------------------------------------------- |
| 1   | Master khách hàng (B2B)                  | Mã KH, địa chỉ giao, điều khoản thanh toán, NVKD, phân cấp, kênh…                |
| 2   | Master sản phẩm / NVL                    | SKU, quy cách, đơn vị, giá (list / theo KH / tier), hạn dùng, điều kiện bảo quản |
| 3   | Giá & chiết khấu B2B                     | Bảng giá theo KH, tier, số lượng (volume)                                        |
| 4   | Đặt hàng (sales order)                   | Tạo đơn bán, trạng thái, duyệt (nếu có), gắn KH                                  |
| 5   | Kiểm tra tồn / giữ hàng (tùy nghiệp vụ)  | Available to promise, reservation — **mức độ tùy yêu cầu**                       |
| 6   | Xuất kho / giao hàng (sales fulfillment) | Phiếu xuất, gắn kho, lô, hạn dùng (FEFO/FIFO nếu cần)                            |
| 7   | Hóa đơn / công nợ                        | Invoice, thanh toán, công nợ theo đơn                                            |
| 8   | Báo cáo bán / tồn                        | Theo KH, SKU, kho, kỳ                                                            |

### A.2. Đặt hàng đa kênh (WhatsApp / LINE + AI)

| #   | Chức năng            | Mô tả ngắn                                                                     |
| --- | -------------------- | ------------------------------------------------------------------------------ |
| 1   | Kênh messaging       | Webhook / API nhận tin nhắn, xác thực KH (số điện thoại / LINE ID / Maolin ID) |
| 2   | NLU / intent         | Nhận diện SKU, số lượng, địa chỉ giao, xác nhận đơn                            |
| 3   | Tích hợp đặt hàng    | Tạo hoặc cập nhật **sales order** trong ERP (idempotent, audit)                |
| 4   | Thông báo trạng thái | Gửi lại KH khi duyệt / giao / hủy                                              |
| 5   | Fallback nhân viên   | Chuyển sang CS khi bot không chắc                                              |

### A.3. Quản lý tồn kho

| #   | Chức năng                   | Mô tả ngắn                                                 |
| --- | --------------------------- | ---------------------------------------------------------- |
| 1   | Đa kho                      | Nhiều warehouse, tồn theo kho + SKU                        |
| 2   | Nhập / xuất / điều chỉnh    | Movement có reference (PO, DO, bán hàng, kiểm kê)          |
| 3   | Lô & hạn dùng (nếu F&B/NVL) | Batch, expiry, quy tắc xuất                                |
| 4   | Đồng bộ với đơn bán         | Trừ tồn khi xác nhận xuất / giao — **theo rule nghiệp vụ** |

### A.4. Xử lý đơn hàng (theo nghĩa Maolin — cần xác nhận)

| #   | Chức năng                        | Ghi chú                                                                    |
| --- | -------------------------------- | -------------------------------------------------------------------------- |
| 1   | Sales order (đơn bán)            | Thường map `orders` + `order_items`                                        |
| 2   | PO (nếu = đơn mua NCC)           | Đã có module Purchase                                                      |
| 3   | DO (nếu = phiếu giao từ PO nhập) | `delivery_orders` gắn `purchase_order_id`                                  |
| 4   | DO (nếu = phiếu giao cho khách)  | **Chưa rõ** trong DB hiện tại có entity tương đương hay dùng chứng từ khác |

### A.5. Quản lý khách hàng

| #   | Chức năng                         | Mô tả ngắn                            |
| --- | --------------------------------- | ------------------------------------- |
| 1   | Client + chi tiết công ty         | users + client_details, custom fields |
| 2   | Đăng nhập / phân quyền B2B portal | Client role, quyền xem đặt hàng       |
| 3   | Import / đồng bộ                  | Excel import client (đã có luồng)     |

---

## B. Hệ thống hiện tại đã đáp ứng gì (theo codebase đã khảo sát)

| Lĩnh vực            | Đã có              | Chi tiết (tham chiếu)                                                                                               |
| ------------------- | ------------------ | ------------------------------------------------------------------------------------------------------------------- |
| **Sales order**     | Có                 | `orders`, `order_items`, status: pending / on-hold / processing / completed / canceled / …                          |
| **Khách hàng B2B**  | Có                 | `users` (client), `client_details`, custom fields, import                                                           |
| **Sản phẩm / NVL**  | Có                 | `products` (sku, unit, brand, shelf_life, inventory flags, …), category/sub_category                                |
| **Giá B2B**         | Có (module)        | `Modules\Pricing` — ClientProductPricing, PricingTier, volume rules (cần cấu hình dữ liệu)                          |
| **Giỏ hàng client** | Có                 | `OrderCart` (OrderObserver xóa cart khi tạo đơn)                                                                    |
| **Đa kho**          | Có (DB + module)   | `warehouses`, `warehouse_product_stock` (warehouse_id + product_id + quantity)                                      |
| **Chuyển kho**      | Có (UI/controller) | `WarehouseTransferController`, cập nhật `WarehouseProductStock`                                                     |
| **Movement tồn**    | Có (schema)        | `stock_movements` (movement_type, warehouse_from/to, batch_number, expiry_date, reference_type/id, FEFO/FIFO field) |
| **PO (mua NCC)**    | Có                 | `Modules\Purchase` — PurchaseOrder, items, warehouse_id (migration)                                                 |
| **DO gắn PO mua**   | Có                 | `delivery_orders` + `purchase_order_id`, items                                                                      |
| **Webhook sự kiện** | Có                 | `Modules\Webhooks` — kích hoạt theo entity (có `Order`, `Warehouse`, `PurchaseOrder`, …)                            |
| **Import Excel**    | Có                 | Client / Product / inventory import (tùy module)                                                                    |

**Chưa thấy trong phần đã grep (cần xác minh sâu hơn nếu bắt buộc):**

- Liên kết tự động **`orders` (bán) → trừ `warehouse_product_stock`** trong `app\Observers\OrderObserver` — **Chưa rõ** đã implement ở service khác hay chưa.
- **API công khai** REST để chatbot tạo đơn — `routes/api.php` hiện **rất tối giản** (không thấy route Order).

---

## C. Những chức năng còn thiếu hoặc chưa đủ (GAP)

| #   | Hạng mục                                       | Trạng thái                   | Ghi chú                                                                                                                 |
| --- | ---------------------------------------------- | ---------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| 1   | **WhatsApp / LINE + AI chatbot**               | Thiếu                        | Không thấy integration trong codebase (tìm keyword không có). Cần service riêng + API nội bộ an toàn.                   |
| 2   | **API đặt hàng cho kênh ngoài**                | Thiếu / yếu                  | Không có REST Order chuẩn trong `routes/api.php` cho bot/partner.                                                       |
| 3   | **Đồng bộ “PO/DO” theo nghĩa bán hàng Maolin** | **Chưa rõ / lệch thuật ngữ** | DO hiện gắn **PurchaseOrder**; sales fulfillment có thể chưa có entity “Sales DO” tách bạch.                            |
| 4   | **Gán kho khi bán**                            | **Chưa rõ**                  | Bảng `orders` (migration gốc) không thấy `warehouse_id`; xuất kho bán theo kho nào — cần rule + có thể cần cột/quan hệ. |
| 5   | **Reservation / ATP**                          | **Chưa rõ**                  | Có tồn theo kho; có giữ hàng khi “pending order” hay không — cần nghiệp vụ.                                             |
| 6   | **Idempotency đơn từ bot**                     | Thiếu                        | Tránh đặt trùng khi webhook lặp — cần thiết kế (external_id, dedupe key).                                               |

---

## D. Thiếu gì trong database (và đề xuất hướng — không bắt buộc triển khai ngay)

### D.1. Đã hỗ trợ (có bảng/cột liên quan)

| Nhu cầu                        | Hỗ trợ?         | Ghi chú                                        |
| ------------------------------ | --------------- | ---------------------------------------------- |
| Multi warehouse                | **Có**          | `warehouses` + `warehouse_product_stock`       |
| Tracking movement              | **Có (schema)** | `stock_movements` với batch, expiry, reference |
| Lifecycle sales order (cơ bản) | **Có**          | `orders.status` enum                           |

### D.2. Có thể thiếu hoặc cần làm rõ (tùy BRD)

| Hạng mục                    | Mô tả                                  | Ghi chú                                                                                                                 |
| --------------------------- | -------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| **Sales order ↔ warehouse** | `orders` không thấy warehouse mặc định | Nếu bắt buộc “đơn bán theo kho” → cân nhắc `warehouse_id` hoặc bảng allocation                                          |
| **Sales DO / shipment**     | DO hiện thuộc luồng PO mua             | Nếu cần “phiếu giao cho khách” → entity mới hoặc mở rộng `delivery_orders` (polymorphic) — **cần quyết định kiến trúc** |
| **Chatbot identity**        | Map LINE/WhatsApp user → `client_id`   | Có thể cần bảng `channel_identities` (channel, external_id, user_id)                                                    |
| **Đơn nguồn kênh**          | Audit                                  | Cột `source` / `external_conversation_id` trên `orders` — **đề xuất** khi làm bot                                       |

### D.3. Refactor / rủi ro thiết kế

- **Trùng khái niệm PO/DO** giữa Purchase và Sales — dễ hiểu sai khi triển khai; nên **document + naming** rõ (Sales Order vs Purchase Order vs Inbound DO vs Outbound DO).

---

## E. Thiếu gì từ phía khách hàng (file & thông tin nghiệp vụ)

Tham chiếu chi tiết file: **`FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`**.

| Hạng mục          | Đủ chưa?    | Thiếu / cần bổ sung                                                                                                                  |
| ----------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| Master KH         | Một phần    | File customer **thiếu cột 部門**; thiếu map hệ thống cho **地區別**, **指定庫別名稱** (custom field)                                 |
| Master SP         | Một phần    | **Không có danh mục** trong Excel; **không có giá** trong `Craveva product.xlsx` (giá ở file khác)                                   |
| Tồn kho / lô      | Một phần    | `Quote, unit price, inventory.xlsx` có batch, kho, movement; **chưa có spec map** sang `warehouse_product_stock` + `stock_movements` |
| Quy tắc giá       | **Chưa đủ** | Ai được giá nào (tier / KH / kênh)? Làm tròn? Thuế?                                                                                  |
| Luồng duyệt đơn   | **Chưa rõ** | Bot tạo đơn → pending → ai duyệt? SLA?                                                                                               |
| PO/DO nghĩa nào   | **Chưa rõ** | Trùng với hệ thống Purchase hay cần Sales DO?                                                                                        |
| Phân quyền        | **Chưa rõ** | Client chỉ đặt / xem tồn / xem giá?                                                                                                  |
| Đồng bộ real-time | **Chưa rõ** | Tần suất; xử lý conflict khi ERP và bot cùng sửa                                                                                     |

**Kết luận:** File khách **chưa đủ** để implement end-to-end bot + đồng bộ tồn **mà không có thêm BRD/workshop** (đặc biệt: mapping file tồn, quy tắc giá, lifecycle đơn, định nghĩa PO/DO).

---

## F. Những câu hỏi cần hỏi khách hàng

1. **PO** trong câu chuyện Maolin là **đơn mua NCC** hay **đơn đặt hàng của khách** (sales order)?
2. **DO** là **phiếu nhập/giao từ NCC** hay **phiếu giao hàng cho khách**?
3. Đặt qua bot: đơn vào trạng thái nào ngay? Có **bước duyệt** không?
4. Trừ tồn tại thời điểm nào: **khi đặt**, **khi duyệt**, hay **khi xuất kho**?
5. Một SKU có **nhiều lô/hạn** — xuất theo **FEFO/FIFO** đã cố định chưa?
6. Giá lấy từ đâu: **list**, **theo KH**, **theo tier**, **theo kênh**?
7. **WhatsApp Business API** vs **LINE Messaging API** — đã có tài khoản developer / số điện thoại official chưa?
8. Xác thực khách trên chat: theo **SĐT**, **mã KH**, hay **đăng nhập OAuth**?
9. File tồn (`Quote, unit price, inventory`): **nguồn truth** là ERP hay file export định kỳ? Chu kỳ?
10. Có cần **OMS** tách riêng hay mọi thứ trong Laravel ERP hiện tại?

---

## G. Độ phức tạp (ước lượng)

| Hạng mục                                        | Độ phức tạp     | Lý do ngắn                                    |
| ----------------------------------------------- | --------------- | --------------------------------------------- |
| Hoàn thiện master + import + giá (đã có module) | **Low–Medium**  | Chủ yếu dữ liệu + mapping cột                 |
| Nối tồn đa kho với sales order + rule xuất lô   | **Medium–High** | Nghiệp vụ + transaction + không double-deduct |
| API + bảo mật + idempotency cho bot             | **Medium**      | Auth, rate limit, audit                       |
| Tích hợp WhatsApp + LINE + AI (NLU, fallback)   | **High**        | Nhiều vendor, chất lượng intent, vận hành     |
| Làm rõ & mở rộng Sales DO / shipment nếu thiếu  | **Medium–High** | Thiết kế DB + luồng chứng từ                  |

---

## Phụ lục: Liên kết tài liệu trong repo

| Tài liệu                                                    | Nội dung                                      |
| ----------------------------------------------------------- | --------------------------------------------- |
| `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`           | File Excel khách, cột, thiếu/thừa vs hệ thống |
| `FUNC_LOGIC/MIAOLIN_IMPORT_FIELDS_DB_VS_CUSTOM_ANALYSIS.md` | Import client/product vs DB / custom field    |
| `FUNC_LOGIC/MIAOLIN_CONTRACT_ANALYSIS_EN.md`                | Hợp đồng / planning (nếu cần)                 |
| `SPECIFICATION/CRAVEVA_PARTNER_TECH_SPEC.md`                | Stack / API surface tổng quan (mức cao)       |

---

_Tài liệu này dựa trên khảo sát có chừng mực trên codebase; các mục ghi **Chưa rõ** cần xác minh thêm (grep toàn repo, đọc service Purchase/Warehouse khi xuất/nhập)._
```
