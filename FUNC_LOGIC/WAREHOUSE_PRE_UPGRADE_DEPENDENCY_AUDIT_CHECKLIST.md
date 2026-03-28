# Audit trước khi triển khai / nâng cấp Warehouse

**Mục đích:** Đảm bảo các chức năng **hiện có** (Product, Client, PO, DO, Inventory, Order, Invoice, Payment…) **ổn định và đúng kỳ vọng** _trước_ khi bật rộng module Warehouse, migration posting, hoặc `WAREHOUSE_SALES_OUTBOUND_ENABLED`.  
**Phạm vi:** Chỉ hệ thống Craveva — **không** audit DigiWin / file đối tác.  
**Cập nhật:** 2026-03-28

**Quy trình triển khai team:** Code xong → **chạy ổn trên local** (migrate, PHPUnit, smoke tay các luồng checklist nếu có DB local) → **push lên git** → staging **pull** về. **Không bắt buộc** hoàn tất toàn bộ checklist chỉ trên staging trước push; **bắt buộc** local không lỗi và test liên quan pass trước khi push. Staging sau pull: smoke nhanh + UAT form nếu cần bằng chứng cho PM.

---

## 1) Tài liệu FUNC_LOGIC cần đọc khi audit (tham chiếu chéo)

| File                                                                                                                       | Dùng khi kiểm tra                                                                                                                                              |
| -------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md)                                                                         | Luồng SO → Invoice → outbound Scope B; PO → delivered → inbound; DO inbound; `PaymentObserver` / legacy                                                        |
| [`B2B_ERP_PO_DO_INVOICE_GUIDE.md`](B2B_ERP_PO_DO_INVOICE_GUIDE.md)                                                         | Khái niệm PO/DO/Invoice; lưu ý mô hình “kho vs tài chính”                                                                                                      |
| [`FLOW_ADD_PRODUCT.md`](FLOW_ADD_PRODUCT.md)                                                                               | Product CRUD/import, SKU, custom field                                                                                                                         |
| [`FLOW_ADD_INVENTORY.md`](FLOW_ADD_INVENTORY.md)                                                                           | Purchase Inventory form → `PurchaseStockAdjustment` + warehouse movement                                                                                       |
| [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md)                                                       | Rủi ro đa kho / legacy; **lưu ý:** mục Invoice “không gọi StockMovementService” **lỗi thời** nếu đã bật Scope B — **chuẩn hiện tại:** `SALES_PURCHASE_FLOW.md` |
| [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)                                                                   | URL, permission, DB warehouse                                                                                                                                  |
| [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)                                                   | Luồng kho tiếng Việt                                                                                                                                           |
| [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md)                                                               | Flag env, Scope B v1, Go/No-Go                                                                                                                                 |
| [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)                                                 | UAT sau khi audit pass (mục A–I)                                                                                                                               |
| [`SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md`](SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md) | Client / `client_details` nếu audit khách + `default_warehouse_id`                                                                                             |
| [`SYSTEM_DATABASE_OVERVIEW_REPORT_VI.md`](SYSTEM_DATABASE_OVERVIEW_REPORT_VI.md)                                           | Tổng quan bảng (nếu cần SQL kiểm tra)                                                                                                                          |
| [`MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md`](MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md)                     | Thứ tự import master (optional, không bắt buộc cho audit nội bộ)                                                                                               |

---

## 2) Checklist audit (baseline — ưu tiên **local** trước push; staging sau pull)

**Quy ước:** `[ ]` chưa kiểm — `[x]` pass — ghi **FAIL + mô tả** nếu lỗi. Ghi **N/A** nếu tenant không dùng luồng đó. Các bước tay (UI) chạy trên **môi trường bạn đang dùng** (local với DB dev, hoặc staging sau khi đã pull).

### A. Môi trường & cấu hình baseline

- [ ] Ghi nhận: branch/commit, PHP/Laravel; DB **local** (hoặc staging sau pull).
- [ ] Module **Warehouse** trạng thái: **tắt** (audit pure core) **hoặc** **bật nhưng chưa** `WAREHOUSE_SALES_OUTBOUND_ENABLED` — **ghi rõ** trạng thái đang audit.
- [ ] Ghi nhận `.env` liên quan: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED` (chỉ **một** nguồn inbound chuẩn trên prod target).
- [ ] `WAREHOUSE_ALLOW_NEGATIVE_STOCK` (khuyến nghị `false` trước go-live kho).

### B. Product (master)

- [ ] Tạo sản phẩm mới (UI): SKU, đơn vị, loại hàng **hàng hóa** (không phải service-only nếu cần test tồn).
- [ ] Import sản phẩm mẫu (nếu dùng import): 1 dòng mới không trùng SKU — không lỗi 500, hiển thị đúng list.
- [ ] Sản phẩm **service** (nếu có): xác nhận không bị tính vào outbound warehouse (theo rule Scope B).
- [ ] (Tuỳ chọn) Custom field Product: tạo/sửa không làm vỡ form sau save.

### C. Client & kho mặc định khách

- [ ] Tạo/sửa Client: lưu thành công; `client_details` có `company_id` đúng tenant.
- [ ] Gán **default warehouse** (nếu UI có): kho thuộc đúng company; lưu và reload đúng.
- [ ] Import client (nếu dùng): cột designated warehouse map đúng — xem `WAREHOUSE_MASTER_GUIDE` / `multi_warehouse_audit_report`.

### D. Purchase Order (PO) → nhập kho

- [ ] Tạo PO có `warehouse_id`, dòng có `product_id`, số lượng hợp lệ.
- [ ] Chuyển `delivery_status` → **delivered** (theo UI/flow thực tế): **mong đợi** inbound qua `StockMovementService` **chỉ khi** `inbound_from_purchase_order_delivered=true` và module warehouse bật.
- [ ] **Không** tăng tồn hai lần cho cùng một lần nhận (nếu vừa PO vừa DO cho cùng lô — chỉ test nếu policy cho phép).

### E. Delivery Order (DO) — inbound

- [ ] **N/A** hoặc: DO **inbound**, `status` → **received**, `inbound_from_delivery_order_received=true`: có inbound movement, cờ `inbound_stock_applied` (hoặc tương đương) chống lặp.
- [ ] Xác nhận **không** bật đồng thời hai inbound canonical trên cùng môi trường prod mục tiêu nếu cùng chứng từ vật lý.

### F. Purchase Inventory (điều chỉnh / sync tồn tuyệt đối)

- [ ] Tạo phiếu inventory: chọn kho + sản phẩm + số đích — delta movement đúng, reference trỏ PurchaseInventory (xem `FLOW_ADD_INVENTORY.md`).
- [ ] Import inventory (nếu dùng `ImportInventoryJob`): 1 file mẫu — warehouse resolve đúng, không duplicate batch sai company.

### G. Order (SO) — **không** phụ thuộc kho nếu chưa có invoice outbound

- [ ] Tạo đơn: pending → completed (hoặc flow chuẩn tenant): **không** crash; `order_items` có `product_id` khi cần xuất sau này.
- [ ] **Ghi nhận giới hạn sản phẩm:** 1 SO → tối đa 1 Invoice gắn `order_id` (xem `SALES_PURCHASE_FLOW.md` §2.1).

### H. Invoice (bán) & Payment — **quan trọng trước bật Scope B**

**Trước khi bật `WAREHOUSE_SALES_OUTBOUND_ENABLED`:**

- [ ] Tạo/sửa Invoice (không draft): lưu OK; báo cáo tồn legacy (nếu UI dùng) không 500.
- [ ] Xóa / chuyển draft: không orphan dữ liệu nghiêm trọng.

**Sau khi chuẩn bị bật Scope B (có thể tách phiên audit):**

- [ ] Bật flag + migrate `invoice_warehouse_stock_postings`: tạo invoice không draft → **có** outbound movement, tồn kho giảm đúng kho resolve.
- [ ] Sửa invoice (qty/sp line): reverse + post lại đúng posting.
- [ ] Xóa invoice: reversal movement / tồn khôi phục.
- [ ] `PaymentObserver`: khi flag ON — **không** còn điều chỉnh legacy stock gây lệch đa kho (xem `WAREHOUSE_TOM_TAT_NOI_BO.md`).

### I. Module Warehouse (smoke trước “nâng cấp” rộng)

Chạy khi module đã bật (có thể song song mục D–F):

- [ ] Danh sách kho, tạo 2 kho, 1 default.
- [ ] Điều chỉnh tồn + / − ; chuyển kho; màn movement lọc theo kho + inbound/outbound.
- [ ] Xóa kho có tồn: **bị chặn** + thông báo rõ.

### J. Hồi quy (regression) chung

- [ ] Đăng nhập, đổi company (nếu multi-company): không thấy kho/đơn company khác.
- [ ] Quyền: user không có quyền warehouse không vào được màn kho/stock/movement (theo policy).
- [ ] Log lỗi (storage/logs) trong session audit: không spike exception chưa xử lý.

---

## 3) Các chức năng **bổ sung** so với “chỉ đa kho” (nhắc lại — từ UAT)

Dùng để đối thoại PM: UAT warehouse **không** chỉ là nhiều kho.

| Nhóm       | Bổ sung                                                              |
| ---------- | -------------------------------------------------------------------- |
| Master kho | CRUD + import Excel + bulk + guard xóa                               |
| Vận hành   | Điều chỉnh tay, chuyển kho, FEFO (service; UI lô/HSD có thể hạn chế) |
| Sổ cái     | Movement ledger, filter, search                                      |
| Mua hàng   | PO **hoặc** DO inbound (một chuẩn), Purchase Inventory sync          |
| Bảo mật    | Permission, company scope, validate server                           |
| Bán hàng   | Scope B: invoice → outbound (khi bật flag)                           |

Chi tiết bảng PM “cơ bản vs UAT”: [`WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md`](WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md) § _PM nói multi warehouse_.

---

## 4) Thứ tự khuyến nghị

1. **Local:** `php artisan migrate`, test PHPUnit liên quan warehouse/invoice, smoke checklist **A→J** (phần có thể làm trên local) **trước khi push**.
2. **Push git** → staging **pull** → smoke nhanh (và lặp các mục cần dữ liệu/URL staging nếu chưa làm local).
3. Baseline **trước** khi bật Scope B trên môi trường đích (trừ mục H phần “sau bật flag”).
4. Migrate + bật flag Scope B → lặp **H (phần outbound)** + **I** đầy đủ.
5. [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md) cho sign-off (có thể chủ yếu trên staging sau pull).
6. Prompt dev: [`WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md`](WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md).

---

## 5) Sign-off audit

| Vai trò    | Tên | Ngày | Ghi chú |
| ---------- | --- | ---- | ------- |
| QA / Owner |     |      |         |
| Tech       |     |      |         |

---

_Liên kết mục lục warehouse: [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)._
