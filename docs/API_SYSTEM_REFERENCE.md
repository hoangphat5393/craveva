# API system reference

Tai lieu nay la diem tham chieu chung cho API/REST va cac kieu du lieu cot loi cua he thong. Dung file nay khi can tra loi:

- He thong hien co nhung endpoint `/api` nao?
- Muc do RESTful cua cac endpoint hien tai ra sao?
- Cac bang/field chinh cho product, client, inventory, warehouse, pricing va order gom nhung gi?

## 1. Ket luan nhanh ve API

| Cau hoi | Ket luan |
| --- | --- |
| He thong co API khong? | Co. He thong co route duoi prefix `/api` va mot so endpoint public/webhook rieng. |
| Co REST API tong quat cho toan ERP khong? | Chua. Chua co OpenAPI/JSON:API chuan hoa cho toan bo ERP. |
| Co phan nao gan REST khong? | Co. `ServerManager` co CRUD gan REST cho `hosting`/`domain`; AI order co REST path tao/cap nhat don hang qua `/api/integrations/orders`. |
| Co dung Froiden RestAPI `ApiRoute::resource` khong? | Co cau hinh trong codebase, nhung resource Payroll dang comment nen hien khong co resource REST dang bat qua `ApiRoute::resource` trong pham vi da ra soat. |

## 2. Endpoint API dang thay trong code

URL day du = `{APP_URL}/api` + duong dan trong bang, tru khi module tu them prefix con.

| Nguon | Duong dan / nhom | Auth | Ghi chu REST |
| --- | --- | --- | --- |
| `routes/api.php` | `GET purchased-module` | Theo cau hinh RestAPI/facade | Endpoint tien ich, khong phai CRUD tai nguyen. |
| `routes/api.php` | `GET integrations/__route_probe`, `POST integrations/orders`, `GET/PATCH/PUT/DELETE integrations/orders/{id}` | `ai.integration.auth`, `ai.integration.method` | Inbound AI / third-party Sales Order, REST path. Xem `docs/AI_ORDER_REST.md`. |
| `Modules/Warehouse/Routes/api.php` | `GET v1/warehouse/availability` | `auth:sanctum` | Doc ton kha dung, la operation API; co test `WarehouseAvailabilityApiTest`. |
| `Modules/Pricing/Routes/api.php` | `GET pricing/preview` | `auth:sanctum` | RPC preview, khong phai resource REST. |
| `Modules/ServerManager/Routes/api.php` | `server-manager/hosting`, `server-manager/domain` | `auth:sanctum` | Co GET/POST/PUT/DELETE theo `id`, gan REST cho hai tap tai nguyen. |
| `Modules/Webhooks/Routes/api.php` | `GET webhooks` | `auth:api` | Stub tra `$request->user()`. |
| `Modules/Recruit/Routes/api.php` | `GET recruit` | `auth:api` | Stub tra user. |
| `Modules/Policy/Performance/Onboarding/LineIntegration/DeveloperTools/Routes/api.php` | `GET v1/{moduleKey}` | `auth:sanctum` | Stub SPA/module. |
| `Modules/Biometric/Routes/api.php` | `GET` / `POST` `/iclock/...` | Thiet bi | Protocol may cham cong, khong phai REST. |
| `Modules/Payroll/Routes/api.php` | Block dang comment | - | Neu bat lai se co `ApiRoute::resource('payroll', ...)`, la mau REST resource ro rang. |
| `Modules/Production/Asset/EInvoice/QRCode/Subdomain/Sms/Zoom/LanguagePack/Routes/api.php` | File rong hoac chi `<?php` | - | Chua dang ky route API trong file. |

## 3. Cach hieu "REST" trong repo nay

- REST pragmatic: URL danh tu + HTTP verb map CRUD (`index/show/store/update/destroy`), JSON response, loi `401/403/422` ro rang.
- REST + versioning/auth B2B: thuong la `/api/v1/...` + Bearer/OAuth2/OpenAPI. Repo hien co mot so route `v1`, AI Order dung shared secret header tai `/api/integrations/orders`.
- OpenAPI / JSON:API: chua thay spec OpenAPI tap trung trong repo.

## 4. Data type cot loi theo phan he

Bang nay la danh sach canonical cho product, client, inventory, warehouse, tier pricing, order va order history.

| Phan he | Bang | Truong va kieu |
| --- | --- | --- |
| Product | `products` | `id:int`, `company_id:int`, `name:varchar`, `sku:varchar`, `price:decimal`, `wholesale_price:decimal`, `employee_price:decimal`, `shelf_life_days:int`, `expiry_date:date`, `track_inventory:enum`, `opening_stock:int`, `allow_purchase:tinyint`, `status:enum`, `unit_id:bigint`, `category_id:bigint`, `sub_category_id:bigint`, `description:text`, `specification:text` |
| Client | `users`, `client_details`, `client_contacts`, `client_categories`, `client_sub_categories` | **users:** `id:int`, `name:varchar`, `email:varchar`, `mobile:varchar`, `status:enum`, `login:enum`, `country_id:int`, `company_id:int`. **client_details:** `user_id:int`, `company_name:varchar`, `client_code:varchar`, `pricing_tier_id:bigint`, `default_warehouse_id:bigint`, `address:text`, `shipping_address:text`, `city:varchar`, `state:varchar`, `payment_terms:varchar`, `customer_grade:varchar`, `channel_type:varchar`, `business_type:varchar`, `business_closure_date:date`. **client_contacts:** `client_id:int`, `contact_name:varchar`, `phone:varchar`, `email:varchar`, `title:varchar`, `address:text` |
| Inventory | `purchase_inventory_adjustment`, `purchase_stock_adjustments`, `purchase_inventory_histories`, `purchase_stock_adjustment_reasons`, `purchase_inventory_files` | **purchase_inventory_adjustment:** `id:int`, `company_id:int`, `reason_id:int`, `type:enum`, `date:date`, `warehouse_id:bigint`. **purchase_stock_adjustments:** `inventory_id:int`, `product_id:int`, `warehouse_id:bigint`, `batch_number:varchar`, `type:enum`, `net_quantity:decimal`, `reserved_quantity:decimal`, `quantity_adjustment:int`, `manufacturing_date:date`, `expiration_date:date`, `status:enum`. **purchase_inventory_histories:** `inventory_id:int`, `user_id:int`, `product_name:varchar`, `net_quantity:decimal`, `quantity_adjustment:int`, `changed_value:decimal`, `adjusted_value:decimal`, `label:text`, `details:text` |
| Warehouse | `warehouses`, `warehouse_product_stock`, `warehouse_product_batches`, `stock_movements` | **warehouses:** `id:bigint`, `company_id:int`, `name:varchar`, `code:varchar`, `warehouse_type:varchar`, `is_default:tinyint`, `status:enum`. **warehouse_product_stock:** `warehouse_id:bigint`, `product_id:int`, `quantity:decimal`. **warehouse_product_batches:** `warehouse_id:bigint`, `product_id:int`, `batch_number:varchar`, `expiration_date:date`, `manufacturing_date:date`, `quantity:decimal`, `reserved_quantity:decimal`. **stock_movements:** `movement_type:varchar`, `warehouse_from_id:bigint`, `warehouse_to_id:bigint`, `batch_number:varchar`, `expiry_date:date`, `quantity:decimal`, `reference_type:varchar`, `reference_id:bigint`, `idempotency_key:varchar` |
| Tier pricing | `pricing_tiers`, `pricing_tier_items`, `client_product_pricing`, `company_customer_pricing`, `company_customer_product_pricing` | **pricing_tiers:** `id:bigint`, `company_id:bigint`, `name:varchar`, `discount_type:enum`, `discount_value:decimal`, `priority:int`, `valid_from:date`, `valid_to:date`, `is_active:tinyint`. **pricing_tier_items:** `pricing_tier_id:bigint`, `product_id:int`, `discount_type:enum`, `discount_value:decimal`, `is_active:tinyint`. **client_product_pricing:** `client_id:bigint`, `product_id:int`, `start_date:datetime`, `end_date:datetime`, `custom_price:decimal`, `discount_type:enum`, `discount_value:decimal`, `is_active:tinyint` |
| Order | `orders`, `order_items`, `order_item_images`, `order_carts`, `order_import_rows` | **orders:** `id:bigint`, `order_number:varchar`, `company_id:int`, `client_id:int`, `order_date:date`, `sub_total:decimal`, `discount:decimal`, `discount_type:enum`, `total:decimal`, `due_amount:decimal`, `status:enum`, `currency_id:int`, `note:varchar`. **order_items:** `order_id:bigint`, `product_id:int`, `item_name:varchar`, `quantity:decimal`, `unit_price:decimal`, `amount:decimal`, `taxes:varchar`, `unit_id:bigint`, `sku:varchar` |
| Order history | `purchase_order_histories` | `id:int`, `company_id:int`, `purchase_order_id:int`, `purchase_vendor_id:int`, `user_id:int`, `label:text`, `details:text`, `created_at:timestamp` |

## 5. Khuyen nghi neu chuan hoa API sau nay

1. Tao mot file OpenAPI 3 cho cac route `/api` dang dung that: Warehouse, ServerManager, AI Order.
2. Neu bat Payroll API thi can policy, validation va test truoc khi dua vao UAT.
3. Tach ro public webhook, API Sanctum va API shared-secret trong tai lieu van hanh.
4. Khi them endpoint moi, cap nhat file nay va neu co luong AI Order thi cap nhat `AI_ORDER_REST_RUNBOOK.md`.

## 6. Tai lieu lien quan

- `docs/AI_ORDER_REST.md`
- `docs/AI_ORDER_REST_SETUP.md`
- `FUNC_LOGIC/AI_ORDER_REST_RUNBOOK.md`
- `FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS.md`

## Changelog

| Date | Change |
| --- | --- |
| 2026-06-20 | Gop `API_REST_SYSTEM_SURVEY.md` va `API_SYSTEM_DATA_TYPES.md` thanh mot tai lieu tham chieu API/data chung. |
