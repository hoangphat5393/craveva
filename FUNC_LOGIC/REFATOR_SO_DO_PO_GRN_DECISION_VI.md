# Quyết định kiến trúc: Refactor thật sự sang `SO -> DO -> Invoice` và `PO -> GRN -> Bill`

**Ngày:** 2026-03-30  
**Trạng thái:** Đề xuất để ra quyết định (chưa triển khai)  
**Bối cảnh:** Hệ thống còn ở giai đoạn phát triển, chưa có dữ liệu production chính thức.

---

## 1) Mục tiêu nghiệp vụ muốn đạt

- Bán hàng rõ ràng theo ERP:
    - `SO -> DO (xuất kho) -> Invoice`
- Mua hàng rõ ràng theo ERP:
    - `PO -> GRN (nhập kho) -> Bill`
- Loại bỏ nhầm lẫn tên gọi hiện tại:
    - `Sales Shipment` (thực chất là sales DO)
    - `Delivery Orders` trong Purchase (thực chất là GRN inbound)

---

## 2) Tính khả thi (kết luận nhanh)

**Khả thi về kỹ thuật: CÓ.**  
**Khả thi để làm big-bang ngay (xóa bảng `sales_shipments` luôn): RỦI RO CAO.**

Vì hiện tại `sales_shipments` đã gắn vào:

- route/controller/view/permission riêng,
- service xuất kho (`SalesShipmentStockService`),
- DataTable + observer liên quan tồn kho,
- tài liệu + test nội bộ.

Nếu xóa thẳng ngay sẽ gây đứt flow và regression rộng.

---

## 3) Ưu điểm nếu refactor thật sự

- Ngôn ngữ nghiệp vụ thống nhất, user dễ hiểu hơn (DO bán và GRN mua tách bạch).
- Dễ training vận hành và audit quy trình kho.
- Giảm chi phí giải thích "Sales Shipment là gì" cho team business.
- Về dài hạn, code và quy trình đồng bộ tên gọi, giảm technical debt tên gọi.

---

## 4) Nhược điểm / rủi ro lớn

- Phạm vi ảnh hưởng rộng: DB, API, permission, menu, report, test, tài liệu, import/export.
- Nguy cơ lỗi tồn kho nếu chuyển sai điểm trigger (double-post hoặc miss-post).
- Mất lịch sử tham chiếu nếu xóa bảng cũ không có migration bridge chuẩn.
- Tăng thời gian chốt release trong ngắn hạn.

---

## 5) Tác động kỹ thuật chính nếu làm “xóa Sales Shipment”

1. **Database**

- Bỏ `sales_shipments`, `sales_shipment_items` hoặc đổi vai trò bảng.
- Thiết kế bảng DO bán mới (hoặc đổi tên/đổi nghĩa bảng hiện có) với khóa `order_id`.
- Tách rõ bảng GRN mua (tránh reuse mơ hồ cùng một bảng cho inbound/outbound).

2. **Business services**

- Chuyển outbound từ `SalesShipmentStockService` sang service mới cho DO bán.
- Giữ inbound GRN tách biệt để không đụng logic mua.

3. **Controller / Routes / Views**

- `SalesShipmentController` và route `sales-shipments.*` cần thay mới hoặc compatibility layer.
- Cập nhật menu, action button từ SO sang DO bán mới.

4. **Permissions**

- Bổ sung bộ quyền DO bán/GRN mới; migration map từ quyền cũ.

5. **Data migration**

- Nếu đã có dữ liệu dev/staging: cần map `sales_shipments` sang thực thể mới để không mất dấu lịch sử test/UAT.

6. **Testing**

- Viết lại các test cho flow mới: tạo DO bán, ship, reverse, invoice sync, inbound GRN.

---

## 6) Phương án triển khai đề xuất (an toàn nhất)

## Option A — Big bang (không khuyến nghị)

- Xóa ngay `sales_shipments` và chuyển toàn bộ sang DO bán.
- Ưu điểm: “sạch” nhanh về tên gọi.
- Nhược điểm: regression lớn, rollback khó.

## Option B — Bridge migration (khuyến nghị)

**Giai đoạn 1: Canonical hóa nghiệp vụ + tương thích**

- Giữ bảng cũ chạy bình thường.
- Tạo lớp compatibility:
    - UI hiển thị `Sales Shipment` thành `Sales DO`.
    - `Delivery Orders` (Purchase) hiển thị thành `GRN`.
- Giữ trigger tồn kho như hiện tại để không vỡ.

**Giai đoạn 2: Refactor kỹ thuật**

- Tạo thực thể kỹ thuật mới theo tên chuẩn (DO bán, GRN mua).
- Migrate data từ bảng cũ.
- Đặt deprecate cho endpoint cũ.

**Giai đoạn 3: Cutover**

- Chuyển toàn bộ UI/API sang endpoint mới.
- Chỉ khi test và UAT pass thì mới remove bảng/route cũ.

---

## 7) Khuyến nghị cho trạng thái dự án hiện tại

Vì hệ thống chưa production thật:

- **Nên làm refactor thật sự, nhưng theo Option B (bridge), không big-bang.**

Lý do:

- Bạn vẫn đạt mục tiêu tên gọi chuẩn ERP.
- Vẫn kiểm soát rủi ro tồn kho và regression.
- Có đường rollback rõ khi staging phát sinh lỗi.

---

## 8) Điều kiện Go / No-Go

**Go** khi đáp ứng đủ:

- Có mapping migration rõ từ `sales_shipments` -> DO bán mới.
- Có test outbound/inbound idempotent + reverse pass.
- UAT tay 2 flow chuẩn:
    - `SO -> DO -> stock out -> invoice`
    - `PO -> GRN -> stock in -> bill`
- Có rollback script DB + app.

**No-Go** nếu:

- Chưa chốt canonical trigger cho kho (outbound/inbound).
- Chưa có test regression đủ rộng.
- Chưa có migration kế thừa dữ liệu.

---

## 9) Ước lượng effort tương đối

- Chuẩn bị thiết kế + migration plan: **1-2 ngày**
- Refactor backend + view + permission + route: **3-6 ngày**
- Test + UAT + fix regression: **3-5 ngày**
- Cutover + theo dõi staging: **1-2 ngày**

**Tổng:** khoảng **8-15 ngày làm việc** tùy phạm vi rename kỹ thuật và mức tương thích ngược.

---

## 10) Quyết định đề xuất cho PM/Tech Lead

- Chấp thuận refactor theo **Bridge migration**.
- Không xóa ngay `sales_shipments` trong đợt đầu.
- Chốt mốc:
    1. hoàn tất compatibility + tài liệu,
    2. hoàn tất refactor kỹ thuật,
    3. mới remove artifact cũ.

---

## 11) Tài liệu triển khai liên quan

- Master plan: `FUNC_LOGIC/REFACTOR_SO_DO_PO_GRN_IMPLEMENTATION_PLAN_VI.md`
- Progress tracker: `FUNC_LOGIC/REFACTOR_SO_DO_PO_GRN_TRACKER_VI.md`
- Prompt thực thi cho AI: `FUNC_LOGIC/PROMPT_REFACTOR_SO_DO_PO_GRN_EXECUTION_VI.md`
