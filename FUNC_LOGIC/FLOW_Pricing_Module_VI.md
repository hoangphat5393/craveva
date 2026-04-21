# Module Pricing — Phân tích (ghi chú tiếng Việt)

**Dự án:** Craveva (Laravel, kiến trúc module)  
**Thư mục module:** `Modules/Pricing`  
**Ngày phân tích:** 23/03/2026

Tài liệu mô tả luồng route → controller → service → model → CSDL, đồng thời rà soát logic, lỗi tiềm ẩn, hiệu năng và bảo mật. **Không chỉnh sửa mã nguồn** trong phạm vi phân tích này.

> **Ghi chú hợp nhất:** Bản EN đã được gộp vào tài liệu VI này; đây là bản chuẩn để tiếp tục cập nhật.

---

## 0. Đọc nhanh cho business (Tier Pricing)

### 0.1 Giá nào đang là "public price"?

- Hiện tại hệ thống **chưa có tầng Public Price tách riêng** trong module Pricing.
- Giá mặc định đang dùng là **`products.price`**.
- Vì vậy, khi chưa có rule nào cao hơn, đơn giá lấy từ `products.price`.

### 0.2 Tier có 2 loại giảm giá và không cộng dồn

Trong Tier Pricing có hai chỗ dễ nhầm:

1. **Tier-level discount** (ở màn Edit Tier): giảm giá mặc định cho toàn bộ sản phẩm trong tier.
2. **Product rule** (`pricing_tier_items`): giảm giá riêng cho từng SKU trong tier.

Khi tính giá:

- Nếu có `Product rule` cho SKU đó thì **ưu tiên Product rule**.
- Nếu không có Product rule thì mới dùng **Tier-level discount**.
- Hai mức này **không cộng dồn** với nhau.

### 0.3 Thứ tự ưu tiên tổng thể (đúng với code hiện tại)

> Tầng cuối cùng luôn có thể bị điều chỉnh thêm bởi volume discount theo số lượng.

1. **Client Product Pricing** (Contract theo client + product + ngày hiệu lực)
2. **Company-Customer Pricing** (hợp đồng doanh nghiệp, gồm cả nhánh tier trong hợp đồng nếu có)
3. **Client Assigned Tier** (`client_details.pricing_tier_id`)
4. **Base Price** (`products.price`)
5. **Volume Discount** (áp sau khi đã có đơn giá từ các bước trên)

---

## 1. Tóm tắt module

### Mục đích

Module **Pricing** triển khai **cách tính giá kiểu B2B** cho sản phẩm: giá riêng theo khách + sản phẩm, hợp đồng công ty–khách, **bậc giá (pricing tiers)** (có thể có dòng giá theo từng sản phẩm trong tier), và **chiết khấu theo khối lượng**. Lớp trung tâm **`PricingService`** dùng thuật toán **hai giai đoạn**:

1. **Giai đoạn 1 — Đơn giá:** ưu tiên từ cao xuống thấp: **giá theo khách + sản phẩm** → **hợp đồng doanh nghiệp** (`company_customer_pricing` + tùy chọn `company_customer_product_pricing`) → **tier gán cho khách** (`client_details.pricing_tier_id` + `pricing_tiers` / `pricing_tier_items`) → **giá gốc trên `products`**.
2. **Giai đoạn 2 — Chiết khấu theo số lượng:** áp dụng quy tắc trong **`VolumeDiscountService`** (bảng `volume_discount_rules`) lên dòng (số lượng × đơn giá sau giai đoạn 1).

Module còn cung cấp **giao diện CRUD** (web, đã đăng nhập) cho tier, giá theo khách, giá công ty–khách, gán tier cho khách, quy tắc volume, và **nhập Excel** (job xếp hàng).

### Tính năng chính

| Khu vực                        | Mô tả                                                                                                                                                                                                                                  |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Pricing tiers**              | `PricingTierController` — CRUD tier, dòng tier (`PricingTierItem`), thao tác nhanh, bật/tắt trạng thái.                                                                                                                                |
| **Giá theo khách + sản phẩm**  | `ClientPricingController` — theo user (`users.id`) + sản phẩm, khoảng ngày (`start_date` / `end_date`), kiểm tra chồng lấn.                                                                                                            |
| **Giá công ty–khách**          | `CompanyPricingController` — một dòng cho mỗi `(company_id, client_id)` + tier/chiết khấu tùy chọn; ghi đè theo sản phẩm nằm ở `company_customer_product_pricing` (được `PricingService` dùng; controller không CRUD đầy đủ bảng này). |
| **Gán tier cho khách**         | `ClientTierController` — cập nhật `client_details.pricing_tier_id` và `client_code`.                                                                                                                                                   |
| **Quy tắc volume**             | `VolumeRuleController` — CRUD `volume_discount_rules`.                                                                                                                                                                                 |
| **API chiết khấu volume (UI)** | `VolumeDiscountController::calculate` — JSON, gọi `VolumeDiscountService`.                                                                                                                                                             |
| **API xem trước giá**          | `GET /api/pricing/preview` — `PricingController::preview` trả JSON từ `PricingService::calculate`.                                                                                                                                     |
| **Import**                     | `PricingImportController` + `ImportClientProductPricingJob` / `ImportPricingTierItemsJob`.                                                                                                                                             |
| **Giỏ hàng**                   | `ProductController::addCartItem` gọi `PricingService::calculate` với user có role `client`.                                                                                                                                            |

**Ghi chú:** Entity `DealProposalPricing` (bảng `deal_proposal_pricing`) **không thấy chỗ nào khác trong repo gọi** — có thể **chưa dùng / dự phòng**.

---

## 2. Sơ đồ luồng (dạng text)

### 2.1 Request web điển hình (đã xác thực)

```
Người dùng (trình duyệt)
  → Middleware: web, auth
  → Route: Modules/Pricing/Routes/web.php (prefix account/pricing)
  → Controller: ví dụ ClientPricingController, PricingTierController, …
  → (CRUD không tách lớp Service — gọi trực tiếp Eloquent trên Entity)
  → Model: ClientProductPricing, PricingTier, CompanyCustomerPricing, VolumeDiscountRule, …
  → CSDL (xem mục 3)
  → Phản hồi: Blade hoặc Reply:: JSON (ajax)
```

### 2.2 Tính giá (giỏ hàng / preview)

```
Người dùng hoặc client API
  → ProductController::addCartItem (role client) HOẶC GET /api/pricing/preview
  → PricingService::calculate($productId, $clientId, $quantity)
      → Product, User, ClientProductPricing, CompanyCustomerPricing,
         CompanyCustomerProductPricing, ClientDetails, PricingTier,
         PricingTierItem, VolumeDiscountService::calculate
  → CSDL: nhiều lần đọc (xem §3)
  → Mảng / JSON: unit_price, price (tổng dòng sau CK volume), nguồn áp dụng, tier_id, volume_discount
```

**Sơ đồ ASCII**

```
User → Route → Controller → [PricingService / VolumeDiscountService] → Model → DB → JSON / Reply
```

**CRUD web (không qua PricingService)**

```
User → Route → Controller → Entity (Eloquent) → DB → View / Reply
```

---

## 3. Luồng cơ sở dữ liệu

### 3.1 Bảng liên quan module Pricing

| Bảng                               | Entity / cách dùng                                                                                                        |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `client_product_pricing`           | `ClientProductPricing` — theo user khách + sản phẩm, phạm vi company (`HasCompany`), khoảng ngày.                         |
| `company_customer_pricing`         | `CompanyCustomerPricing` — công ty bán `company_id` + `client_id` (id user sau migration).                                |
| `company_customer_product_pricing` | `CompanyCustomerProductPricing` — giá/CK tùy sản phẩm trong hợp đồng.                                                     |
| `pricing_tiers`                    | `PricingTier` — định nghĩa tier; `company_id` có thể null (nền tảng vs công ty).                                          |
| `pricing_tier_items`               | `PricingTierItem` — ghi đè theo sản phẩm trong tier; **không** dùng `HasCompany` (phạm vi qua tier).                      |
| `volume_discount_rules`            | `VolumeDiscountRule` — ngưỡng số lượng, `applies_to_type` all/products.                                                   |
| `deal_proposal_pricing`            | `DealProposalPricing` — **không thấy dùng** trong codebase.                                                               |
| `client_details`                   | `App\Models\ClientDetails` — `pricing_tier_id`, `client_code` (không nằm trong module Pricing nhưng cần để resolve tier). |
| `users`                            | Danh tính khách (`client_id` trong bảng pricing = `users.id`).                                                            |
| `products`                         | Giá gốc và `company_id` (công ty bán).                                                                                    |

### 3.2 Quan hệ (khái niệm)

- `client_product_pricing` → `users`, `products`, `companies`.
- `company_customer_pricing` → `companies`, `users`, `pricing_tiers` (tuỳ chọn).
- `company_customer_product_pricing` → `company_customer_pricing`, `products`.
- `pricing_tier_items` → `pricing_tiers`, `products`.
- `volume_discount_rules` → `companies` (tuỳ chọn), `products` qua `applies_to_product_id`.

### 3.3 Đọc / ghi / join / lọc

- **Đọc trong `PricingService::calculate`:** tra cứu tuần tự — product, user, client pricing, chuỗi corporate, `ClientDetails`, tier, tier item, rồi vòng lặp gọi `VolumeDiscountService` (mỗi dòng `items` có thể một truy vấn rules).
- **Ghi:** `store` / `update` / `destroy` trong controller; import qua job.
- **Join:** `ClientTiersDataTable` join `users`, `role_user`, `roles`, `client_details`, `pricing_tiers`.
- **Lọc:** `CompanyScope` trên model `HasCompany`; một số chỗ `where('company_id', user()->company_id)`; `PricingService` lọc ngày, `is_active`, số lượng.

---

## 4. Luồng request (ví dụ chi tiết)

### 4.1 `GET /api/pricing/preview`

| Bước       | File                             | Hàm         | Diễn giải                                                                         |
| ---------- | -------------------------------- | ----------- | --------------------------------------------------------------------------------- |
| Route      | `Modules/Pricing/Routes/api.php` | —           | `pricing/preview` dưới prefix `api`, middleware `api` **không có `auth`**.        |
| Controller | `PricingController.php`          | `preview`   | Đọc `product_id`, `client_id`, `quantity`; `new PricingService`; gọi `calculate`. |
| Service    | `PricingService.php`             | `calculate` | Tính đủ hai giai đoạn.                                                            |
| Model      | Service + Entity                 | —           | Truy vấn Eloquent như trên.                                                       |
| Response   | `PricingController`              | —           | `response()->json($result)`.                                                      |

### 4.2 `POST /account/pricing/client-pricing` (lưu mới)

| Bước       | File                          | Hàm     | Diễn giải                                                                                   |
| ---------- | ----------------------------- | ------- | ------------------------------------------------------------------------------------------- |
| Route      | `web.php`                     | —       | `auth`, prefix `account/pricing`.                                                           |
| Controller | `ClientPricingController.php` | `store` | Kiểm quyền, validate, kiểm chồng lấn, tạo `ClientProductPricing`, `Reply::successWithData`. |
| Service    | —                             | —       | Không có.                                                                                   |
| Model      | `ClientProductPricing.php`    | —       | `save()`.                                                                                   |

### 4.3 Khách thêm vào giỏ

| Bước       | File                    | Hàm           | Diễn giải                                                                                              |
| ---------- | ----------------------- | ------------- | ------------------------------------------------------------------------------------------------------ |
| Route      | Routes app              | —             | Dẫn tới `ProductController::addCartItem`.                                                              |
| Controller | `ProductController.php` | `addCartItem` | Role `client`: `PricingService::calculate` với `user()->id`; suy ra đơn giá từ `price` / `unit_price`. |

### 4.4 `POST /account/pricing/discount/calculate`

| Bước       | File                           | Hàm         | Diễn giải                                                                                                                                             |
| ---------- | ------------------------------ | ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Controller | `VolumeDiscountController.php` | `calculate` | Chỉ truyền `items` vào `VolumeDiscountService::calculate($items)` — **không** truyền `company_id`; ngữ cảnh công ty lấy từ `company()` trong service. |
| Service    | `VolumeDiscountService.php`    | `calculate` | Mỗi dòng: tìm rule phù hợp, cộng dồn chiết khấu.                                                                                                      |

---

## 5. Phân tích logic nghiệp vụ

### 5.1 `PricingService::calculate`

- **Giá khách + sản phẩm** thắng nếu đúng `client_id`, `product_id`, `is_active`, và ngày hiện tại nằm trong `[start_date, end_date]` (xem rủi ro biên ở mục 6).
- **Nhánh corporate** `getClientContractPricing`: cần bản ghi `CompanyCustomerPricing`; sau đó ưu tiên giá custom theo sản phẩm, hoặc chiết khấu cấp hợp đồng, hoặc tier gắn hợp đồng (có kiểm tra tier + tier item).
- **Nhánh tier:** `ClientDetails` theo `user_id = $clientId`; tier phải active và trong `valid_from` / `valid_to`; `company_id` của tier phải `null` **hoặc** trùng `company_id` của sản phẩm; rồi tier item hoặc chiết khấu cấp tier.
- **Mặc định:** `products.price`, sau đó giai đoạn 2.

### 5.2 `applyDiscount`

- Hỗ trợ `custom_price`, `percentage`, `fixed`, `specific_price`.

### 5.3 `VolumeDiscountService::calculate`

- Mỗi dòng input: query rule active, lọc công ty (tuỳ ngữ cảnh), all vs sản phẩm, số lượng trong [min, max]; chọn **một** rule sau `orderByDesc('minimum_quantity')->orderBy('id')` — **không** phải “chiết khấu tốt nhất” nếu nhiều rule cùng khớp.

### 5.4 Validation (ví dụ)

- **ClientPricingController:** `client_id`, `product_id`, ngày, trường chiết khấu; truy vấn chồng lấn khoảng ngày.
- **PricingTierController:** trường tier; `storeItem` kiểm `product_id` tồn tại trong `products`.
- **VolumeRuleController:** tên rule, số lượng, chiết khấu, `applies_to_type`, `product_id` tuỳ chọn.

---

## 6. Vấn đề đã phát hiện

### 6.1 Logic / đúng sai nghiệp vụ

| Vấn đề                                      | Chi tiết (tiếng Việt)                                                                                                                                            |
| ------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`calculatePrice` dùng `clientId = 0`**    | Gọi `calculate($productId, 0, …)` — user id 0 không hợp lệ; hành vi corporate/tier sai. Hàm được ghi chú legacy nhưng vẫn nguy hiểm nếu còn chỗ gọi.             |
| **Bỏ qua ngày hiệu lực hợp đồng corporate** | `valid_from` / `valid_to` có trong model nhưng `getClientContractPricing` **không** lọc theo các cột này.                                                        |
| **Điều kiện `custom_discount_value`**       | `if (... && $relation->custom_discount_value)` coi `0` là không có chiết khấu — có thể đúng hoặc sai tùy yêu cầu.                                                |
| **Kiểu rule `tiered`**                      | DB cho phép `tiered`; service chỉ xử lý `percentage` và `fixed_amount` — `tiered` **không** ra chiết khấu.                                                       |
| **Import và cột ngày**                      | `ImportClientProductPricingJob` có thể **không** gán `start_date` / `end_date` sau khi migration bắt buộc — nguy cơ **lỗi lưu** hoặc phụ thuộc default không rõ. |
| **`firstOrNew` khi import**                 | Cho phép nhiều dòng cùng `(client_id, product_id)` — `firstOrNew` có thể cập nhật **bản ghi bất kỳ**, mơ hồ khi nhiều khoảng ngày.                               |
| **`DealProposalPricing`**                   | Có vẻ **chưa dùng** — code chết hoặc tính năng dở.                                                                                                               |

### 6.2 Thiếu validation / phân quyền

| Vấn đề                                    | Chi tiết                                                                                                                                                |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **API preview không đăng nhập**           | Ai cũng có thể gửi `product_id`, `client_id`, `quantity` và nhận JSON giá — **lộ thông tin giá**, không thấy rate limit trong module.                   |
| **Gán tier khách**                        | `ClientTierController` dùng `User::findOrFail($id)` mà **chưa** chứng minh user là **khách thuộc công ty hiện tại** — rủi ro **IDOR** nếu đoán được id. |
| **Thao tác hàng loạt**                    | `row_ids` từ request — nên validate kiểu / quyền sở hữu rõ hơn (global scope đã giúp một phần).                                                         |
| **`PricingTierController::changeStatus`** | Không thấy check permission rõ trong method (phụ thuộc UI).                                                                                             |

### 6.3 Điều kiện mong manh

| Vấn đề                   | Chi tiết                                                                                  |
| ------------------------ | ----------------------------------------------------------------------------------------- |
| **Ngày giờ vs timezone** | So sánh `now()` với cột datetime — nên thống nhất timezone với `company()` trên toàn app. |
| **Kiểm tra chồng lấn**   | Không loại `is_active = false` — bản ghi **đã tắt** vẫn có thể **chặn** tạo khoảng mới.   |

### 6.4 Hiệu năng

| Vấn đề                                | Chi tiết                                                                                                                   |
| ------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **N+1 trong `VolumeDiscountService`** | **Một query rules cho mỗi phần tử** trong mảng `items` — payload nhiều dòng sẽ chậm.                                       |
| **Nhiều lần `find`**                  | Với một sản phẩm một lần tính thì chấp nhận được; có thể gộp sau nếu cần.                                                  |
| **Index**                             | Đã có index trên một số cột; nếu profiling chậm, cân nhắc composite cho đúng pattern `where` + `orderBy` của volume rules. |

### 6.5 Bảo mật

| Vấn đề                                           | Chi tiết                                                                                                                                                                             |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **SQL injection**                                | Eloquent + validate — **rủi ro thấp** với luồng thông thường.                                                                                                                        |
| **Preview API không auth**                       | **Rủi ro cao** nếu giá nhạy cảm.                                                                                                                                                     |
| **`CompanyScope` khi `company()` null**          | Model `HasCompany` **không** lọc `company_id` — có thể thấy dữ liệu **tenant khác** trong ngữ cảnh super-admin / session lỗi — cần kiểm tra route admin Pricing luôn có `company()`. |
| **`VolumeDiscountService` khi `companyId` null** | Nếu không truyền `companyId` và `company()` null, có thể lấy rule **mọi công ty**. `PricingService` khi tính giỏ hàng truyền `company_id` **công ty bán** — **ổn** cho luồng đó.     |

---

## 7. Gợi ý sửa (lần triển khai sau)

1. **Bảo vệ `GET /api/pricing/preview`:** thêm `auth:sanctum` / token, hoặc throttle + URL ký, hoặc chuyển vào `account` + policy — theo mô hình đe dọa của sản phẩm.
2. **`calculatePrice`:** sửa để truyền đúng `clientId` hoặc xóa / không dùng; ghi chú caller.
3. **Ngày hợp đồng corporate:** áp dụng `valid_from` / `valid_to` trong `getClientContractPricing` nếu đang dùng thực tế.
4. **Job import:** luôn gán `start_date` / `end_date`; khớp quy tắc chồng lấn.
5. **Volume:** xử lý kiểu `tiered` hoặc cấm trong validation; ghi rõ cách chọn rule (orderBy vs chiết khấu tối ưu).
6. **IDOR gán tier:** giới hạn `User` chỉ khách thuộc công ty (giống `User::allClients()`).
7. **Chồng lấn:** tuỳ chọn bỏ qua bản ghi `is_active = false` khi kiểm tra.
8. **Hiệu năng:** preload rules một lần rồi lọc trong PHP, hoặc một query có semantics rõ ràng.
9. **`changeStatus` tier:** thống nhất check permission + scope công ty như các action khác.

---

## 8. Bảng tra cứu file nhanh

| Vai trò           | Đường dẫn                                                                                                                                                                                             |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Web routes        | `Modules/Pricing/Routes/web.php`                                                                                                                                                                      |
| API routes        | `Modules/Pricing/Routes/api.php`                                                                                                                                                                      |
| Đăng ký route     | `Modules/Pricing/Providers/RouteServiceProvider.php`                                                                                                                                                  |
| Engine tính giá   | `Modules/Pricing/Services/PricingService.php`                                                                                                                                                         |
| Chiết khấu volume | `Modules/Pricing/Services/VolumeDiscountService.php`                                                                                                                                                  |
| Preview JSON      | `Modules/Pricing/Http/Controllers/PricingController.php`                                                                                                                                              |
| CRUD              | Các controller: `ClientPricingController`, `CompanyPricingController`, `PricingTierController`, `ClientTierController`, `VolumeRuleController`, `VolumeDiscountController`, `PricingImportController` |
| Jobs              | `ImportClientProductPricingJob`, `ImportPricingTierItemsJob`                                                                                                                                          |
| Tích hợp giỏ      | `app/Http/Controllers/ProductController.php` (`addCartItem`)                                                                                                                                          |

---

_Hết ghi chú phân tích (tiếng Việt)._
