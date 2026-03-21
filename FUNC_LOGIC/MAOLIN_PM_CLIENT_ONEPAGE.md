# MAOLIN - Demo Daily Sync Summary (For Client/PM)

## 1) Mục tiêu demo

- Buổi sáng: Import dữ liệu từ ERP Maolin vào Craveva.
- Buổi tối: Export dữ liệu từ Craveva để Maolin import lại ERP.
- Chu kỳ lặp hằng ngày cho 3 nhóm dữ liệu: **Product, Inventory, Client**.

## 2) Hiện trạng nhanh

- **Client import:** chạy được, có logic cập nhật theo `client_code`.
- **Product import:** chạy được nhưng cần có cột giá (`price` hoặc `standard_price`).
- **Inventory import:** cần chốt lại mapping kho trước demo (tránh dữ liệu tồn sai kho).
- **Export chuẩn 3 nhóm dữ liệu:** chưa có bộ file chuẩn hoàn chỉnh cho vòng lặp tối export.

## 3) Kết luận file khách hiện tại

### Product (Craveva product.xlsx)

- Có: SKU, tên, đơn vị, brand, grade, thông tin bảo quản.
- Thiếu quan trọng: price/standard_price, product_source, category/sub-category.

### Inventory (Quote, unit price, inventory.xlsx)

- Có nhiều cột tồn/lô/kho.
- Chưa đủ rule mapping để import ổn định theo chuẩn hệ thống hiện tại.

### Client (Craveva customer.xlsx)

- Có: client_code, tên, MST, địa chỉ, điện thoại.
- Cần bổ sung/xác nhận: department; map region và designated warehouse.

## 4) Những việc cần chốt trước demo

1. Chốt **template chuẩn v1** cho 3 file Product/Inventory/Client.
2. Chốt **key đồng bộ**:
    - Product: `product_code` (SKU)
    - Client: `client_code`
    - Inventory: `warehouse_code + product_code (+ batch nếu có)`
3. Chốt **rule cập nhật** (insert/update/không overwrite) cho từng cột.
4. Chốt **nguồn giá chuẩn** (file nào là source of truth).
5. Chốt **cutoff thời gian** sync mỗi ngày và timezone.

## 5) Đề xuất scope demo (thực tế, ít rủi ro)

- **Phase Demo A (khuyến nghị):**
    - Morning import: Product + Client + Inventory snapshot cơ bản.
    - Evening export: Product + Client + Inventory theo đúng template đã chốt.
- **Chưa đưa vào demo đầu:**
    - Rule pricing nâng cao (tier/volume phức tạp)
    - Logic reservation/ATP nâng cao
    - Tự động hóa full đa luồng nếu chưa chốt nghiệp vụ

## 6) Rủi ro chính khi demo

- Thiếu cột giá ở Product -> import fail.
- Mapping kho chưa chốt -> tồn kho lệch.
- SKU/client_code không chuẩn hoặc không unique -> sai mapping.
- Date format không thống nhất -> lỗi import batch/expiry.

## 7) Quyết định cần PM/Khách xác nhận ngay

- Bộ cột chuẩn cuối cùng cho 3 file.
- Rule overwrite cụ thể (cột nào được cập nhật, cột nào giữ nguyên).
- File export đêm: full snapshot hay chỉ delta theo `updated_at`.
- Định nghĩa nghiệp vụ kho: snapshot tồn hay movement.

---

## Tài liệu chi tiết nội bộ

- `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`
- `FUNC_LOGIC/MAOLIN_DAILY_SYNC_DEMO_ANALYSIS.md`
- `FUNC_LOGIC/MAOLIN_ERP_B2B_GAP_ANALYSIS.md`
