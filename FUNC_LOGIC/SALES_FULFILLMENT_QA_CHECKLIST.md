# Sales / Purchase / Warehouse QA Checklist

**Phạm vi:** SO -> Sales DO -> Invoice, PO/GRN -> Bill, tồn kho batch/expiry, đối chiếu movement.

## 1. Trạng thái hiện tại

| Hạng mục | Trạng thái | Ghi chú |
| -------- | ---------- | ------- |
| Sales DO batch identity | Đã triển khai | Sales DO reserve/ship lưu `warehouse_batch_id`, `batch_number`, `expiration_date`; UI bắt buộc chọn batch khi ship qty > 0. |
| Sales outbound | Đã triển khai theo mode | `shipment`: tồn giảm khi ship Sales DO; invoice không trừ tồn lần 2. `invoice`: invoice có thể post outbound theo config. |
| Purchase inbound | Đã triển khai theo 1 nguồn canonical | PO delivered hoặc GRN received; không bật đồng thời 2 nguồn inbound cho cùng một lần nhận. |
| Purchase Bill | Đã chốt nghiệp vụ | Bill NCC là AP, không tự động tạo movement kho. |
| Full UI/UAT E2E | Cần chạy tay theo checklist | Automated tests có coverage core service/observer; chưa coi là 100% UI E2E nếu chưa chạy UAT trên browser/staging. |

**Kết luận:** flow core đã có automated coverage, nhưng file này **không đánh dấu 100% hoàn thành UI/UAT** vì còn phụ thuộc test tay trên UI/staging.

## 2. Quy ước nghiệp vụ dùng để QA

- `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`: tồn kho giảm khi **Ship Sales DO**; invoice không trừ tồn lần 2.
- `WAREHOUSE_SALES_OUTBOUND_MODE=invoice`: invoice không draft có thể trừ tồn theo config legacy.
- Purchase inbound chỉ dùng **một** sự kiện canonical:
    - PO delivered, hoặc
    - GRN received.
- Đối chiếu tồn kho theo thứ tự:
    1. UI chứng từ.
    2. `stock_movements`.
    3. `warehouse_product_batches`.
    4. KPI/datatable tồn kho.

## 3. Automated Tests Liên Quan

| Test | Coverage chính |
| ---- | -------------- |
| `WarehouseUpgradeP0Test` | SO không trực tiếp trừ tồn; confirm -> ship tạo outbound gắn `SalesDo`; mode `shipment` không post outbound từ invoice. |
| `PurchaseInboundStockFlowTest` | Inbound từ GRN received hoặc PO delivered, và tắt khi config off. |
| `InvoiceWarehouseStockScopeBTest` | Mode `shipment`/`invoice`, `InvoiceWarehouseStockService`, legacy observer skip khi warehouse outbound bật. |
| `SalesDoServiceLifecycleTest` | Lifecycle Sales DO và persistence service. |
| `SalesDoUpdateReservesStockTest` | Update Sales DO reserve stock đúng rule. |
| `SalesDoInvoiceUatTest` | Guard tạo invoice liên quan Sales DO. |
| `SalesDoInvoiceGuardServiceTest` | Rule chặn/cho tạo invoice theo trạng thái Sales DO. |
| `SalesDoStatusBadgeTest` | Status badge UI logic. |

**Lưu ý:** các test trên không thay thế cho browser UAT đầy đủ.

## 4. Checklist Pass/Fail Trước Go-Live

- [ ] Ship DO tạo outbound movement đúng qty, kho, batch/expiry.
- [ ] Reservation consumed sau ship.
- [ ] Inventory KPI giảm/tăng đồng bộ với movement.
- [ ] Invoice không double-deduct trong mode `shipment`.
- [ ] Inbound mua không bị nhập đôi.
- [ ] Bill NCC không tạo movement kho.
- [ ] UI hiện lỗi rõ khi ship qty > remaining.
- [ ] UI hiện lỗi rõ khi ship qty > 0 nhưng chưa chọn batch hợp lệ.
- [ ] Browser/UAT tạo đủ flow SO -> DO -> Ship -> Invoice và PO -> GRN/PO delivered -> Bill.

## 5. Lỗi Nghiệp Vụ Hay Gặp

| Lỗi | Nguyên nhân | Cách xử lý |
| --- | ----------- | ---------- |
| `Ship quantity cannot exceed remaining quantity` | Ship qty lớn hơn remaining qty | Giảm ship qty hoặc tạo DO khác theo remaining. |
| `Please select a valid batch` | Dòng ship > 0 nhưng batch identity thiếu/không hợp lệ | Chọn batch từ dropdown theo kho + sản phẩm. |
| Tồn UI chưa đổi | UI/table có thể chưa refresh | Refresh và đối chiếu `stock_movements`. |

## 6. Demo PM/UAT Nhanh

Chi tiết đã gộp vào `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md` phụ lục D.

Luồng tối thiểu:

1. Tạo SO có sản phẩm tracked inventory.
2. Tạo Sales DO, chọn kho + batch, nhập ship qty.
3. Confirm/Ship.
4. Ship DO -> tồn giảm, movement outbound.
5. Tạo Invoice -> mode `shipment` không trừ tồn thêm.
6. Tạo PO/GRN hoặc PO delivered -> tồn tăng.
7. Tạo Bill NCC -> AP, không tạo movement kho.

## 7. Tài Liệu Liên Quan

| Mục đích | File |
| -------- | ---- |
| Quy trình nghiệp vụ | `SALES_BUSINESS.md` |
| Schema/cutover | `SALES_FULFILLMENT_SCHEMA_MATRIX.md` |
| UAT E2E | `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md` |
| Luồng code SO/PO/Invoice/Stock | `SALES_BUSINESS.md` §5.1 |

## 8. Lịch Sử Rút Gọn

| Ngày | Ghi chú |
| ---- | ------- |
| 2026-04-23 | Gộp checklist UAT cũ vào `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`. |
| 2026-06-20 | Rút gọn file QA, bỏ audit dài trùng lặp; giữ trạng thái, test coverage và checklist UAT cần chạy. |
| 2026-06-21 | Chuẩn hóa tiếng Việt có dấu, bỏ mojibake. |
