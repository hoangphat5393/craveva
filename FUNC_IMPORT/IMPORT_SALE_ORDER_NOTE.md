Đã triển khai xong luồng IMPORT Sales Order (SO) theo file Last year net sales.xlsx và theo pattern import hiện có của hệ thống.

Những gì đã làm
Khảo sát luồng SO hiện tại:
routes/web.php + OrderController + Order/OrderItems + OrdersDataTable
Flow liên kết client_details.client_code và products.sku
Thêm luồng import SO đầy đủ:
upload -> map cột -> batch/chunk queue -> progress -> lỗi theo row
Đọc dữ liệu workbook nhiều sheet (theo tháng):
thêm import class đa sheet cho Last year net sales.xlsx
Thêm idempotent + dedupe:
tạo bảng order_import_rows với unique (company_id, source_hash)
Thêm tài liệu mapping + checklist:
FUNC_IMPORT/IMPORT_SALE_ORDER.md
có mục “Thiếu dữ liệu / đề xuất CF (KHÔNG tự tạo)” đúng yêu cầu
File đã sửa / thêm
app/Http/Controllers/OrderController.php
thêm importOrder, importStore, importProcess
dùng ImportExcel trait + chunk import
routes/web.php
thêm routes:
orders.import
orders.import.store
orders.import.process
resources/views/orders/index.blade.php
thêm nút Import Excel trong trang Orders
resources/views/orders/ajax/import.blade.php (new)
resources/views/orders/ajax/import_progress.blade.php (new)
app/Imports/SalesOrderImport.php (new)
app/Imports/SalesOrderSheetImport.php (new)
app/Jobs/ImportSalesOrderChunkJob.php (new)
database/migrations/2026_04_02_180000_create_order_import_rows_table.php (new)
app/Http/Controllers/ImportController.php
allow queue name SalesOrderImport
tests/Feature/ImportSalesOrderChunkJobTest.php (new)
FUNC_IMPORT/IMPORT_SALE_ORDER.md (new content)
Quy tắc import đã áp dụng
Mapping cột chuẩn:
shipment_return_date
customer_number -> match client_details.client_code
product_part_number -> match products.sku
net_sales_volume
net_sales_amount
Return (âm):
nếu qty/amount âm -> status = refunded
lưu quantity/amount theo trị tuyệt đối để tương thích cấu trúc SO hiện tại
Dedupe/idempotent:
hash theo company + date + customer + sku + raw qty + raw amount
import lại không tạo SO trùng
Numeric parse:
hỗ trợ 1,220, -1,220, rỗng
Test đã chạy
php artisan test tests/Feature/ImportSalesOrderChunkJobTest.php ✅ (2 tests passed)
php -l cho các file mới/chính ✅
Pint format + lint check file đã sửa ✅
Cách test lại UI (manual)
Chạy migration mới:
php artisan migrate
Vào Orders -> bấm Import Excel
Upload PROJECT MAOLIN New/Last year net sales.xlsx
Bật Contains headings
Map 5 cột đúng theo doc FUNC_IMPORT/IMPORT_SALE_ORDER.md
Submit và theo dõi progress
Kiểm tra:
Orders được tạo
Row lỗi hiển thị ở exception table (nếu client_code hoặc sku không match)
Import lại cùng file không bị nhân đôi dữ liệu
Nếu bạn muốn, mình làm tiếp bước 2:

thêm filter/tag trên Orders để phân biệt SO import từ net sales (ví dụ theo note hoặc cờ import),
và xuất report đối soát số dòng imported/skipped/failed theo batch.

Nếu xét nghiệp vụ chuẩn ERP thì có 2 hướng:

1.Hướng nhanh (đã làm): import vào Orders

Phù hợp migration dữ liệu lịch sử.
Dễ tra cứu trên màn Orders.
Không cần tạo module mới.

2. Hướng chuẩn báo cáo dài hạn: bảng snapshot riêng (sales_history)

Dữ liệu history tách biệt với đơn vận hành thật.
Báo cáo sạch hơn, tránh lẫn order vận hành hiện tại.
Nhưng cần thêm màn/report mới.
Khuyến nghị cho bạn lúc này:

Nếu mục tiêu gần hạn là nạp dữ liệu để dùng ngay: tiếp tục import vào Orders.
Nếu sau này cần BI/report nâng cao và tách bạch dữ liệu lịch sử: mình làm phase 2 chuyển sang snapshot table.
Nếu bạn muốn, mình có thể đề xuất ngay một rule rõ ràng:

file history -> Orders (status completed/refunded),
file đơn mới phát sinh hằng ngày -> luồng SO chuẩn khác (không dùng history import).

Khuyến nghị cho bạn lúc này:

Nếu mục tiêu gần hạn là nạp dữ liệu để dùng ngay: tiếp tục import vào Orders.
Nếu sau này cần BI/report nâng cao và tách bạch dữ liệu lịch sử: mình làm phase 2 chuyển sang snapshot table.
Nếu bạn muốn, mình có thể đề xuất ngay một rule rõ ràng:

file history -> Orders (status completed/refunded),
file đơn mới phát sinh hằng ngày -> luồng SO chuẩn khác (không dùng history import).
