# Kế hoạch nâng cấp Warehouse (bản đã lọc ý lỗi thời PM)

**Nguồn đầu vào:** `FUINC_REPORT/PM report 1.md`, `FUINC_REPORT/PM report 2.md`
**Đã loại bỏ:** các nhận định lỗi thời (ví dụ “không có transfer”, “không có batch/HSD”, …)
**Mục tiêu:** chỉ giữ các hạng mục cần nâng cấp thực sự.

---

## 1) Hiện trạng xác nhận (giữ ngắn gọn)

- Hệ thống đã có nền đa kho: master kho, tồn theo kho, batch/HSD, movement, transfer.
- Hệ thống đã có service reservation, nhưng cần chuẩn hóa cách gắn với luồng bán.
- Cấu hình inbound/outbound đã có cờ env; cần khóa rõ quy trình để tránh trừ/nhập đôi.

---

## 2) Danh sách nâng cấp cần làm (đã lọc)

| Mã     | Hạng mục nâng cấp | Vấn đề hiện tại | Kết quả cần đạt |
| ------ | ----------------- | --------------- | --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| WUP-01 | Phân loại kho + rule nghiệp vụ | Chưa có `warehouse_type` + rule chặn xuất kho không hợp lệ | Có loại kho (`normal`, `locked`, `scrap`, `transit`...) và rule thực thi khi đặt hàng/xuất kho/chuyển |
| WUP-02 | Chuẩn hóa sellable/available | Chưa có định nghĩa dùng chung toàn hệ thống | Có công thức và API thống nhất `sellable` để web/AI/đơn hàng dùng chung |
| WUP-03 | Chuẩn hóa reserve -> outbound | Reservation có service nhưng chưa chốt quy trình business nhất quán | Có flow chuẩn: tạo đơn giữ chỗ, shipped/invoice trừ tồn, cancel/revert trả tồn |
| WUP-04 | Khóa cấu hình inbound/outbound | Dễ lỗi vận hành (PO + DO cùng nhập; invoice + shipment cùng xuất) | Có policy cấu hình “một nguồn nhập / một nguồn xuất” theo môi trường + checklist bật cờ |
| WUP-05 | API kiểm tra còn hàng cho AI/Line | Chưa có endpoint chuẩn cho nghiệp vụ YES/NO | Có endpoint/service trả về availability theo kho + quy đổi đơn vị |
| WUP-06 | Quy đổi đơn vị bán hàng | Chưa chốt một chuẩn conversion xuyên suốt | Có base unit chuẩn; trừ kho luôn qua quantity đã convert |
| WUP-07 | Đồng bộ ERP hằng ngày | Import có nhưng chưa đóng gói chuẩn idempotent + đối soát | Có job sync ổn định, idempotent, log lỗi và báo cáo chênh lệch |
| WUP-08 | Báo cáo vận hành theo kho | UI/báo cáo có thể chưa đủ góc nhìn đối soát | Có báo cáo tồn theo kho + movement theo reference + export phục vụ kế toán/vận hành |
| WUP-09 | Bin/location (giai đoạn 2) | Có field location ở movement nhưng chưa triển khai hoàn chỉnh | Có mô hình location rõ hoặc quyết định postpone chính thức |

---

## 3) Kế hoạch triển khai đề xuất

### Giai đoạn P0 (bắt buộc, chống lỗi nghiệp vụ)

| Sprint   | Hạng mục | Deliverable | Tiêu chí hoàn thành |
| -------  | -------- | ----------- | ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sprint 1 | WUP-01, WUP-04 | Schema + validation + policy config | Không thể xuất từ kho locked/scrap; không còn cấu hình nhập/xuất đôi |
| Sprint 1 | WUP-02 | Service/API `sellable` dùng chung | Mọi màn kiểm tồn gọi cùng 1 nguồn dữ liệu |
| Sprint 2 | WUP-03 | Flow reserve/outbound/release | Tạo đơn giữ chỗ, giao hàng trừ tồn, hủy đơn trả tồn đúng |

### Giai đoạn P1 (phục vụ Miaolin vận hành thật)

| Sprint     | Hạng mục | Deliverable | Tiêu chí hoàn thành |
| ---------- | -------- | ----------- | ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sprint 3   | WUP-05, WUP-06 | API availability + module conversion | AI/Line check hàng đúng, không lệch đơn vị |
| Sprint 3-4 | WUP-07 | Job sync ERP + reconciliation report | Sync lặp lại không nhân bản dữ liệu, có log lỗi và đối soát |
| Sprint 4   | WUP-08 | Bộ báo cáo tồn/movement theo kho | Ops và kế toán tự đối soát được theo chứng từ |

### Giai đoạn P2 (tối ưu sau khi ổn định)

| Sprint  | Hạng mục | Deliverable |
| ------  | -------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Backlog | WUP-09 | Thiết kế/location rollout hoặc chốt chưa làm |

---

## 4) UAT checklist bắt buộc sau nâng cấp

- Không bán được từ kho `locked/scrap`.
- Không oversell khi tạo 2 đơn gần đồng thời.
- Không nhập đôi cùng một lô khi bật sai cờ PO/DO.
- Không trừ đôi khi shipment và invoice cùng chạy.
- Một chứng từ phải trace được đầy đủ trên `stock_movements` (warehouse, ref, qty, time).
- API check hàng trả cùng kết quả với tồn thực tế sau quy đổi đơn vị.

---

## 5) Quy tắc dùng báo cáo PM từ nay

- Dùng báo cáo PM như danh sách rủi ro/test case.
- Không dùng lại các kết luận lỗi thời nếu đã có trong code.
- Mỗi sprint cập nhật trạng thái theo mã `WUP-xx` (Done / In Progress / Blocked).



# Tác động theo từng file của bạn
- Quote_inventory.csv → hưởng lợi nhiều nhất

    File này có đủ warehouse_code, batch, HSD, tồn đầu/kỳ/cuối, kho “đặc biệt” (báo phế, in-transit...).
    Khi có warehouse_type + rule + sellable, hệ thống sẽ:
    import đúng tồn theo kho/lô,
    không nhầm kho bán được với kho không bán,
    đối soát ERP dễ hơn.


- Craveva product info.csv → hưởng lợi trung bình-cao

    Có SKU, quy cách, đơn vị, storage days, nhiệt độ...
    Khi chuẩn hóa unit conversion + core field mapping, import sản phẩm sẽ ổn định hơn, ít lệ thuộc CF.


- Craveva product price.csv → hưởng lợi trung bình

    Chủ yếu là bảng giá theo SKU.
    Nâng cấp warehouse không tác động trực tiếp, nhưng có lợi gián tiếp nếu bạn chuẩn hóa SKU/base unit/idempotent import.


- Craveva_Customer Profile_客戶資料.csv → hưởng lợi trung bình

    Có customer_code, payment terms, designated_warehouse_name.
    Khi kho được phân loại/rule rõ, map khách -> kho mặc định sẽ an toàn hơn (tránh map vào kho không bán).


- Last year net sales.xlsx → hưởng lợi thấp (không trực tiếp)

    Đây thiên về phân tích doanh số lịch sử, không phải file master import kho chuẩn.
    Dùng tốt cho forecast/reorder, nhưng không cải thiện nhập tồn trực tiếp.


# Kết luận ngắn
    - Nếu mục tiêu là import vận hành thật cho Miaolin, nâng cấp sẽ giúp mạnh nhất ở:
        - Quote_inventory.csv
        - Craveva product info.csv
        - Craveva_Customer Profile_客戶資料.csv
    Hai file còn lại (product price, last year net sales) cần luồng import/báo cáo riêng, không phụ thuộc 100% vào warehouse core.


File thay đổi:

Modules/warehouse
Modules/Purchase


app/Models/StockMovement.php
tests/

---

## 6) Trạng thái triển khai (Apr 2026)

- `WUP-01` (P0): **Done**
  - Thêm `warehouse_type` (migration + index).
  - Chặn outbound/reserve từ kho không sellable (`locked/scrap/transit`) bằng policy tập trung.
- `WUP-02` (P0): **Done**
  - Có `WarehouseAvailabilityService` chuẩn hóa `on_hand/reserved/available/sellable`.
  - Có API `GET /api/v1/warehouse/availability` trả YES/NO và chi tiết theo kho.
- `WUP-03` (P0): **Done**
  - Chuẩn hóa lifecycle `confirm -> reserve`, `ship -> consume + outbound`, `cancel -> release`.
  - `reverse` từ shipped về confirmed sẽ re-reserve.
- `WUP-04` (P0): **Done**
  - Guard cấu hình inbound canonical (PO vs DO) + warning log khi mâu thuẫn.
  - Guard cấu hình outbound canonical (invoice vs shipment) + fail fast khi mode sai.
- `WUP-05` (P1 nền): **Partial**
  - Đã có nền API availability dùng cho AI/Line, chưa gắn toàn bộ call-site nghiệp vụ.
- `WUP-06` (P1 nền): **Done (nền)**
  - Bổ sung service conversion về base unit trước reserve/inbound/outbound.
  - Có cấu hình strict/fallback (`WAREHOUSE_STRICT_UNIT_CONVERSION`).
- `WUP-07` (P1 nền): **Done (tối thiểu)**
  - Bổ sung `idempotency_key` cho `stock_movements` để chống post trùng.
  - Có command reconciliation ngày: `warehouse:reconciliation-report` + log bảng đối soát.



