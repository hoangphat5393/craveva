# Biomixing / ERP — Multi-tenant: rủi ro B2B vs Production vs PO vs Kho

_Cập nhật: 2026-05-20 · Đọc trước khi bật module cho tenant pilot_

---

## 1. Nguyên tắc

Hệ thống phục vụ **nhiều công ty (tenant)** trên cùng codebase. Không phải tenant nào cũng:

| Profile tenant                   | Module thường bật                                          | Không bắt buộc                        |
| -------------------------------- | ---------------------------------------------------------- | ------------------------------------- |
| **B2B bán hàng**                 | Orders, Invoices, Clients, (Purchase tùy)                  | Production, `estimates_phase1_review` |
| **Gia công / xưởng (Biomixing)** | Estimates + Phase1 review, Production, Warehouse, Purchase | —                                     |

Mọi tính năng Biomixing phải **tắt mặc định** hoặc **ẩn UI** khi tenant không bật module tương ứng.

---

## 2. Cờ tenant (đã có trong code)

| Cờ                        | Bảng / API                | Mặc định nếu không có row        | Ảnh hưởng                         |
| ------------------------- | ------------------------- | -------------------------------- | --------------------------------- |
| `production`              | `module_settings`         | Menu/route Production **403**    | Lệnh SX, BOM, lô                  |
| `estimates_phase1_review` | `module_settings` (admin) | **Tắt** — báo giá như ERP thường | Duyệt 2 cấp, BOM báo giá, chặn SO |
| `purchase`                | `module_settings`         | Không thấy PO / GRN              | Đặt mua, gợi ý mua từ lệnh SX     |
| `warehouse`               | `module_settings`         | Không so tồn kho NL              | Cột tồn / thiếu trên lệnh SX      |

**Kiểm tra runtime:** `ProductionTenantAccess::tenantMayUseProduction()` (user + DB), `estimates_phase1_review_enabled()` (chỉ company hiện tại).

---

## 3. Điểm va chạm B2B ↔ Production (đã phân tích & xử lý)

### 3.1 Nút «Tạo lệnh sản xuất» trên Sales Order

| Rủi ro                          | Cách xử lý                                                                                                                                               |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Tenant B2B thấy nút dù không SX | Chỉ hiện khi `ProductionTenantAccess::tenantMayUseProduction()` **và** quyền `add_production_orders` **và** SO còn mở (`eligibleForProductionOrderLink`) |

### 3.2 Khóa giao hàng (Sales DO) khi lệnh SX chưa xong

| Rủi ro                                                                                                | Cách xử lý                                                                                          |
| ----------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| Config global `enforce_quality_lock_sales_do=true` chặn **mọi** tenant có dòng `production_orders` cũ | **2026-05-20:** Chỉ áp dụng nếu `ProductionTenantAccess::productionEnabledForCompanyId($companyId)` |
| Tenant B2B không bao giờ tạo lệnh SX nhưng bị khóa DO                                                 | Không khóa khi module Production **tắt** cho company đó                                             |

### 3.3 Báo giá Phase 1 (President / VP)

| Rủi ro                           | Cách xử lý                                                                                          |
| -------------------------------- | --------------------------------------------------------------------------------------------------- |
| Tenant bán lẻ bị bắt duyệt 2 cấp | `estimates_phase1_review` **mặc định OFF**; migration seed chỉ bật cho company đã có dữ liệu review |
| Chặn tạo SO nhầm                 | `Estimate::isCommercialConversionAllowed()` chỉ khi phase1 **bật** cho tenant                       |

### 3.4 Tổng nguyên liệu + gợi ý đặt mua

| Rủi ro                                      | Cách xử lý                                                                                     |
| ------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| Gọi class Warehouse khi tenant không có kho | Không hiện cột tồn/thiếu nếu module `warehouse` tắt                                            |
| Link PO khi không dùng Purchase             | Link «Tạo đơn đặt hàng» chỉ khi `purchase` trong `user_modules()` + quyền `add_purchase_order` |

### 3.5 Menu sidebar (Purchase hub)

Production submenu chỉ khi:

```text
ProductionTenantAccess::tenantMayUseProduction()
AND view_production_orders != none
```

---

## 4. Luồng dữ liệu chung (không conflict nếu đúng tenant)

```text
[B2B-only]
  Báo giá (chuẩn) → SO → Invoice / DO (warehouse nếu bật) → Giao

[Biomixing]
  Báo giá (+ BOM, duyệt nếu bật phase1) → SO → Lệnh SX → Lô → trừ NL / nhập TP
       ↘ PO (mua NL)          ↘ DO (xuất TP) — quality lock chỉ khi Production bật
```

**PO (Purchase):** Độc lập tenant; Production **tiêu thụ** tồn đã nhập qua GRN, không thay PO.

**Multi-warehouse:** Production gắn `rm_warehouse_id` / `fg_warehouse_id` per lệnh; chỉ đọc tồn/batch warehouse đó — không ghi đè kho tenant khác (`company_id` trên mọi bảng).

---

## 5. Checklist trước khi bật pilot (1 công ty)

1. **Module Settings** (admin): bật `production` chỉ tenant gia công; **không** bật trên tenant B2B thuần.
2. **Duyệt báo giá gia công:** chỉ tenant OEM (Biomixing).
3. Xác nhận tenant B2B: mở SO → **không** có «Tạo lệnh sản xuất»; ship DO **không** bị message production.
4. Tenant gia công: SO → lệnh SX → lô 5 bước; thiếu tồn → PO (nếu có Purchase).
5. Chạy `php artisan migrate` + `.\scripts\test.ps1` (hoặc filter Production + phase1).

---

## 6. Cấu hình global vs tenant

| Config                                 | File                                   | Ghi chú                                               |
| -------------------------------------- | -------------------------------------- | ----------------------------------------------------- |
| `phase2.enforce_quality_lock_sales_do` | `Modules/Production/Config/config.php` | Global default `true`; **logic đã gate theo company** |
| `phase2.enforce_variance_approval`     | idem                                   | Chỉ ảnh hưởng post FG khi dùng Production             |
| `yield_uom_shadow_enabled`             | idem                                   | Mặc định `false` — không đổi hành vi B2B              |

Tương lai (nếu PM cần): chuyển quality lock sang `module_settings` per company thay vì config file.

---

## 7. Kiểm tra local một lần (gợi ý)

| #   | Việc               | Tenant A (B2B)                     | Tenant B (Biomixing)                                    |
| --- | ------------------ | ---------------------------------- | ------------------------------------------------------- |
| 1   | Module Settings    | Production **off**, phase1 **off** | Production **on**, phase1 **on**                        |
| 2   | SO → menu Thao tác | Không «Tạo lệnh SX»                | Có nút                                                  |
| 3   | Báo giá            | Không workspace 4 vùng duyệt       | Có BOM + duyệt                                          |
| 4   | Sales DO ship      | Bình thường                        | Nếu có lệnh SX chưa xong → có thể chặn (đúng nghiệp vụ) |

URL local: `https://craveva-staging.test` (Herd) — DB theo `.env` máy bạn, không phải server remote.

---

_Tài liệu liên quan: `BIOMIXING_GAP_STATUS_VI.md`, `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`, `FUNC_LOGIC/ENV_LOCAL_VS_SERVER_HOSTNAMES_VI.md`._
