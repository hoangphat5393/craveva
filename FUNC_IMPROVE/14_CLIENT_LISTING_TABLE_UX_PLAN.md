# Client Listing Table UX — Decision Log & Backlog

**Cập nhật:** 2026-06-21  
**Trạng thái:** Core UX-006 **Done**; file đã compact từ bản kế hoạch dài ngày 2026-06-17.  
**Mục đích:** Giữ lại nghiệp vụ, mapping data, evidence và backlog còn mở cho Client list. Không còn là implementation plan từng phase.

---

## 1. Kết Luận Hiện Tại

Bảng Clients là màn hình **tóm tắt vận hành để scan nhanh**. Chi tiết pricing / contract / volume không hiện đầy đủ trên list, mà nằm ở Client detail và module Pricing.

Core đã làm:

| Hạng mục | Trạng thái |
| -------- | ---------- |
| Default columns theo PM | Done |
| Pricing Tier column | Done |
| Contract Pricing Active badge | Done |
| Outstanding Balance aggregate | Done, mặc định ẩn để tránh noise/perf risk |
| Client Detail Pricing tab | Done |
| Read-only status badge khi user không có `edit_clients` | Done |
| Saved filters bằng `localStorage` | Done |
| Preset cột Sales / Finance / Logistics | Done |
| Index `client_product_pricing` cho contract active lookup | Done |
| Category filter + edit custom field save regression | Done |

Regression mới nhất: `php artisan test --compact tests\Feature\ClientImportAndExportTest.php` -> **20 passed / 81 assertions** (2026-06-16).

---

## 2. Code Và Data Source Chính

| Thành phần | Đường dẫn / bảng |
| ---------- | ---------------- |
| DataTable list | `app/DataTables/ClientsDataTable.php` |
| View list | `resources/views/clients/index.blade.php` |
| Client detail | `resources/views/clients/show.blade.php` |
| Import | `app/Imports/ClientImport.php`, `app/Services/ClientImportProcessor.php` |
| Pricing tier | `client_details.pricing_tier_id` -> `pricing_tiers` |
| Contract pricing | `client_product_pricing` |
| Client custom field | Group **Client** -> `App\Models\ClientDetails` -> `custom_fields_data` |

Doc liên quan:

- `FUNC_LOGIC/CLIENT_BUSINESS.md`
- `FUNC_LOGIC/PRICING_BUSINESS.md`
- `FUNC_IMPROVE/07_PRICING_MODULE_DEV_TASKS.md`
- `FUNC_IMPROVE/UX_MENU_AND_SETTINGS.md` phần D / UX-006

---

## 3. Quyết Định Nghiệp Vụ Cần Giữ

### 3.1 DB Field Vs Custom Field

| Khái niệm PM | Nguồn chuẩn hiện tại | Ghi chú |
| ------------ | -------------------- | ------- |
| Pricing Tier | `client_details.pricing_tier_id` -> `pricing_tiers` | Khác với "Client Tier" nghiệp vụ. |
| Client Tier / Customer Grade | `client_details.customer_grade` | Không dùng custom field nếu cột DB đã có. |
| Payment Terms | `client_details.payment_terms` | Custom field trùng với DB đã được cleanup ở các company khác. |
| Channel / Business Type / Closure Date | `client_details.*` | Core commercial fields. |
| Salesperson / Assistant / Geography | `custom_fields_data` nếu admin tạo trong UI | Field đặc thù từng company. |
| Contract Pricing Active | `EXISTS client_product_pricing` active theo date window | Chỉ hiện badge, không list giá từng sản phẩm. |
| Outstanding Balance | Aggregate invoices unpaid/partial, bỏ credit note | Mặc định ẩn vì performance/noise. |

Fresh project không seed Client custom field Miaolin mặc định nữa. Hai migration seed CF demo ngày 2026-03-09 đã bị bỏ; core commercial DB columns vẫn được giữ. Trên staging cũ, script `scripts/cleanup_redundant_client_custom_fields.php --except-company=20 --force` đã xóa 28 CF trùng core DB ở các company khác, `custom_fields_data = 0`.

### 3.2 Default / Optional Columns

| Nhóm | Cột |
| ---- | --- |
| Default | Client Code, Name, Pricing Tier, Contract Pricing Active, Status |
| Available nhưng ẩn mặc định | Outstanding Balance, Email, Mobile, Category, Created At, Payment Terms, Customer Grade, Sales Assistant, Geography nếu có CF |
| Không đưa lên list | Chi tiết contract pricing, giá từng sản phẩm, volume rules |

Lý do: list dùng để scan nhanh; pricing intelligence chi tiết nằm ở Client detail / Pricing module.

---

## 4. Evidence Đã Có

| Ngày | Evidence |
| ---- | -------- |
| 2026-06-14 | Phase 1-5 done: visibility policy, Pricing Tier, Contract badge, stale DataTables state guard, Outstanding hidden aggregate, Client Detail Pricing tab; regression **13 passed / 46 assertions**; browser UAT `/account/clients` và `?tab=pricing` passed. |
| 2026-06-14 | Phase 6.1 done: status badge read-only cho user không có `edit_clients`; regression **14 passed / 50 assertions**. |
| 2026-06-14 | Phase 6.3 done: Client list filter persistence bằng `localStorage`. |
| 2026-06-14 | Phase 6.4 done: preset Sales / Finance / Logistics; Playwright MCP xác nhận preset đổi cột đúng, console error 0. |
| 2026-06-14 | Phase 7.2 done: migration index `(company_id, client_id, is_active, start_date, end_date)` cho Contract Pricing Active lookup. |
| 2026-06-15 | Cleanup CF staging: xóa 28 custom fields trùng core DB ở các company khác, không xóa data CF liên quan. |
| 2026-06-16 | Category filter query + edit client custom field save regression pass. |
| 2026-06-16 | `php artisan test --compact tests\Feature\ClientImportAndExportTest.php` -> **20 passed / 81 assertions**. |

---

## 5. Backlog Còn Mở

| ID | Việc | Trạng thái | Ghi chú |
| -- | ---- | ---------- | ------- |
| CL-01 | Sticky Client Code + Name | Optional/gated | Chỉ làm nếu project load DataTables `FixedColumns` plugin; không nên thêm plugin nếu chỉ vì 1 view. |
| CL-02 | Credit tab / Credit Limit | Optional/gated | Cần PM/Finance chốt `credit_limit` nguồn DB hay CF; chưa có field chuẩn. |
| CL-03 | Outstanding performance benchmark | Optional | Cột đang ẩn mặc định; nếu Finance muốn dùng hằng ngày với dataset lớn thì benchmark trước, sau đó mới tính cache/job. |
| CL-04 | DB vs CF consolidation sau cùng | Optional/high risk | Chỉ copy CF -> DB khi có backup, PM approve, test import full file và so sánh sample trước/sau. |

Không nên làm:

- Không hiện chi tiết contract pricing/giá từng sản phẩm trên list.
- Không đổi thứ tự ưu tiên `PricingService`.
- Không xóa custom field production đang có data.
- Không thêm `credit_limit` khi PM/Finance chưa chốt nguồn và rule.

---

## 6. Khi Nào Có Thể Retire File Này

File này có thể retire khi:

1. CL-01..CL-04 được chốt **bỏ scope** hoặc chuyển sang `UX_MENU_AND_SETTINGS.md` / Pricing backlog.
2. `FUNC_LOGIC/CLIENT_BUSINESS.md` đã có đầy đủ mapping DB vs CF và import rules.
3. `LEGACY_ARCHIVE.md` ghi lại ngày retire + doc thay thế.
