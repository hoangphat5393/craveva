# Production Material Shortage Summary — Kế hoạch triển khai (Phase 1)

_Cập nhật: **26/05/2026** · Liên quan: [`PHASE2_PM_PLAN_VI.md`](./PHASE2_PM_PLAN_VI.md), [`16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`](./16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md)_

---

## 1. Mục tiêu nghiệp vụ

PM cần một màn tổng hợp để trả lời câu hỏi:

- Với **100 lệnh sản xuất** đang mở, nguyên liệu như `Sugar`, `Powder`, `Milk`, ... đang **thiếu tổng bao nhiêu**?
- Thiếu tại **kho nguyên liệu nào**?
- Thiếu do **những lệnh sản xuất nào** tạo ra nhu cầu?

**Không chấp nhận** cách user mở từng lệnh sản xuất rồi tự cộng tay.

Màn mới phải đóng vai trò là:

**Cross-order material shortage summary**  
dựa trên:

- **current stock**
- **reserved stock**
- **selected production orders**

---

## 2. Quyết định UX / điều hướng

### 2.1 Không thêm sidebar ở Phase 1

Phase 1 chỉ thêm **1 nút nhỏ** trên màn `Production orders`.

Lý do:

- Ít xâm lấn UI hiện có
- Dễ review với PM
- Nếu dùng thường xuyên mới nâng cấp thành menu sidebar ở Phase 2

### 2.2 Tên nút đề xuất

Ưu tiên:

1. **Material shortage summary**
2. `Shortage summary`
3. `Material planning summary`

**Khuyến nghị chốt:** `Material shortage summary`

### 2.3 Màn hiển thị

Tạo **1 view mới** trong module `Production`, không nhét vào bảng `Production orders`.

Khuyến nghị route / màn:

- Route name: `production.material-shortages.index`
- URL: `/account/production/material-shortages`
- View: `production::material-shortages.index`

---

## 3. Phạm vi Phase 1

### 3.1 Có trong Phase 1

- Màn **Material shortage summary**
- DataTable server-side
- Gom shortage theo:
    - `component_product_id`
    - `rm_warehouse_id`
- Dùng:
    - `WarehouseProductStock.quantity`
    - `WarehouseProductBatch.reserved_quantity`
    - BOM snapshot hoặc BOM hiện tại
- Có filter trạng thái lệnh sản xuất
- Có action `View orders` để drill-down các lệnh liên quan

### 3.2 Chưa làm trong Phase 1

- Không làm MRP đầy đủ theo thời gian
- Không tính incoming PO / ETA
- Không làm soft allocation engine mới
- Không làm summary snapshot table
- Không thêm menu sidebar riêng

---

## 4. Điều kiện dữ liệu nền để màn này đáng tin

Màn summary chỉ đáng tin khi các tầng inventory nền hoạt động đúng:

1. **Inventory transaction**  
   Nhập/xuất kho đi qua stock engine chuẩn của hệ thống

2. **Inventory balance**  
   `WarehouseProductStock.quantity` phản ánh đúng tồn hiện tại

3. **Available stock**  
   Tính đúng từ:
    - on hand
    - trừ `reserved_quantity`

### Kết luận kỹ thuật

Codebase hiện tại **đủ nền để làm Phase 1**, vì đã có:

- `StockMovementService` làm stock truth
- `WarehouseProductStock` làm balance hiện tại
- `reserved_quantity` ở `WarehouseProductBatch`

### Cảnh báo

Nếu dữ liệu kho nền đang lệch, thì summary này vẫn chạy được nhưng **sẽ lệch nghiệp vụ**.

---

## 5. Quy tắc tính toán đề xuất

### 5.1 Default orders được tính

Mặc định chỉ tính:

- `released`
- `in_progress`

Có thể thêm filter để bật:

- `draft`

### 5.2 Chọn nguồn BOM

- Order `released` / `in_progress` -> dùng **BOM snapshot**
- Order `draft` -> dùng **BOM hiện tại**

### 5.3 Công thức aggregate đúng

**Không được** cộng `shortfall` của từng order rồi cộng lại.

Phải tính theo logic:

1. Chọn tập orders cần tính
2. Bung danh sách component của từng order
3. Quy đổi về base unit
4. Gom theo `component_product_id + rm_warehouse_id`
5. Tính:
    - `total_required`
    - `available_stock`
    - `shortage = max(total_required - available_stock, 0)`

### 5.4 Group key

Mỗi dòng summary phải đại diện cho:

- một **nguyên liệu**
- trong một **kho nguyên liệu**

Tức là key aggregate:

- `component_product_id`
- `rm_warehouse_id`

---

## 6. Thiết kế màn hình đề xuất

### 6.1 Màn chính: Material shortage summary

DataTable với các cột:

| Cột                    | Ý nghĩa                          |
| ---------------------- | -------------------------------- |
| Material               | Nguyên liệu                      |
| Raw material warehouse | Kho nguyên liệu                  |
| Total required         | Tổng cần của các order đang chọn |
| Available stock        | Tồn khả dụng hiện tại            |
| Shortage to procure    | Số lượng thiếu cần mua           |
| Affected orders        | Số lệnh tạo ra nhu cầu           |
| Base unit              | Đơn vị chuẩn                     |
| Action                 | Xem các lệnh liên quan           |

### 6.2 Filter

Khuyến nghị có:

- `Production status`
- `Warehouse`
- `Material`
- `Only shortage > 0`

### 6.3 Drill-down

Nút `View orders` mở modal / page con hiển thị:

- Order ID / code
- Status
- Manufactured product
- Planned quantity
- Required quantity của nguyên liệu đó
- Source:
    - BOM snapshot
    - BOM current

---

## 7. Kiến trúc code đề xuất

### 7.1 Route

Thêm route mới trong `Modules/Production/Routes/web.php`:

- `production.material-shortages.index`

### 7.2 Controller

Tạo controller mới:

- `ProductionMaterialSummaryController`

Method:

- `index(ProductionMaterialShortagesDataTable $dataTable)`

### 7.3 Service

Tên service chốt:

- **`ProductionMaterialSummaryService`**

Lý do:

- Ngắn hơn `ProductionMaterialPlanningSummaryService`
- Không quá khóa vào một metric
- Vẫn đủ rõ vì đang nằm trong namespace `Modules\Production\Services`

Method gợi ý:

- `summaries(array $filters): array`
- `detailForMaterial(int $productId, int $warehouseId, array $filters): array`

### 7.4 DataTable

Tạo:

- `ProductionMaterialShortagesDataTable`

Pattern giống `ProductionOrdersDataTable` hiện có.

### 7.5 View

Tạo:

- `Modules/Production/Resources/views/material-shortages/index.blade.php`

### 7.6 Nút vào màn mới

Thêm 1 nút secondary ở action bar của `Production orders`:

- `Material shortage summary`

---

## 8. Có cần bảng DB mới không?

### Phase 1: Không cần

Không tạo migration mới trong Phase 1.

Làm realtime từ dữ liệu sẵn có:

- `production_orders`
- BOM snapshot / BOM items
- `WarehouseProductStock`
- `WarehouseProductBatch`

### Khi nào mới cần table mới?

Chỉ cân nhắc summary table / cached snapshot khi:

- số lượng order tăng lớn
- hiệu năng realtime không còn ổn
- cần lịch sử shortage theo ngày
- cần dashboard cực nhanh cho buyer/planner

---

## 9. Mức độ khó và rủi ro

### 9.1 Độ khó

- **Kỹ thuật:** trung bình
- **Nghiệp vụ:** trung bình đến cao

### 9.2 Phần khó nhất

Không phải UI hay DataTable, mà là chốt rule:

- status nào được tính
- snapshot vs current BOM
- group theo warehouse
- tránh double-count tồn khả dụng

### 9.3 Rủi ro chính

1. Tồn kho nền không đáng tin -> summary sai
2. Dùng nhầm BOM current thay vì snapshot -> số liệu sai với order released
3. Cộng `shortfall` per-order thay vì aggregate total required -> sai nghiệp vụ

---

## 10. Kế hoạch triển khai đề xuất

### Bước 1 — Chốt nghiệp vụ

Chốt với PM / buyer:

- mặc định tính `released + in_progress`
- có filter thêm `draft`
- group theo material + raw material warehouse
- drill-down bằng modal trước

### Bước 2 — Service aggregate

Tạo `ProductionMaterialSummaryService`:

- lấy selected orders
- resolve BOM snapshot / BOM current
- aggregate theo product + warehouse
- tính available và shortage

### Bước 3 — DataTable + Controller + View

- route mới
- controller mới
- DataTable mới
- view index mới

### Bước 4 — Drill-down

Action `View orders`:

- modal AJAX hoặc page con

Khuyến nghị Phase 1:

- modal AJAX

### Bước 5 — Button từ Production orders

Thêm nút secondary để mở summary.

### Bước 6 — Test

Viết test cho:

- 2 orders cùng material, cùng warehouse
- 2 orders cùng material, khác warehouse
- released order dùng snapshot
- draft order dùng BOM current
- reserved quantity làm giảm available
- shortage = 0 thì không highlight

---

## 11. Definition of Done (Phase 1)

- [ ] Có nút `Material shortage summary` trên màn `Production orders`
- [ ] Có màn summary riêng trong module `Production`
- [ ] Summary gom đúng theo `material + warehouse`
- [ ] Default filter dùng `released + in_progress`
- [ ] Có action xem các orders liên quan
- [ ] Không tạo table DB mới
- [ ] Có test aggregate logic
- [ ] PM xem 1 case thật và xác nhận số liệu hợp lý

---

## 12. Khuyến nghị chốt để bắt đầu code

Đề xuất cuối cùng để triển khai:

- **Button name:** `Material shortage summary`
- **View mới:** Có
- **Sidebar:** Chưa thêm ở Phase 1
- **Service:** `ProductionMaterialSummaryService`
- **DB table mới:** Không
- **Default scope:** `released + in_progress`
- **Group key:** `material + raw material warehouse`
- **Drill-down:** modal `View orders`

---

_Nếu Phase 1 được PM duyệt và user dùng thường xuyên, Phase 2 mới cân nhắc thêm sidebar, cache summary, hoặc mở rộng thành planning dashboard._

---

## 13. Ghi chú triển khai thực tế (26/05/2026)

### 13.1 Vị trí nút vào summary

Trên màn `Production orders`, nút:

- `New Production Order`
- `Material shortage summary`

được đặt theo thứ tự:

1. `New Production Order`
2. `Material shortage summary`

Mục tiêu là giữ action chính của user ở trước, còn summary là action tra cứu đứng sau.

### 13.2 Hiện đang tổng hợp những status nào

Ở bản triển khai hiện tại, màn `Material shortage summary`:

- mặc định tổng hợp từ các order có status `released + in_progress`
- có hiển thị note ngay trên màn để user biết scope đang được tính
- cho phép đổi scope bằng bộ lọc `Status`

### 13.3 Ý nghĩa note trên màn summary

Note trên màn cần nói rõ:

- số liệu thiếu nguyên liệu hiện đang được cộng từ tập orders nào
- user có thể đổi tập đó bằng filter phía trên

Mục đích:

- tránh hiểu nhầm rằng màn này luôn cộng tất cả order trong hệ thống
- giúp buyer / planner biết ngay số liệu đang dựa trên scope nào trước khi ra quyết định mua hàng
