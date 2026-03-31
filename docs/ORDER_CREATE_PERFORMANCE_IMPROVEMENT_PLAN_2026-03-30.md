# Kế hoạch cải thiện hiệu năng màn Create Order (Local/Staging)

## 1) Bối cảnh hiện tại

- Dữ liệu lớn:
    - `clients ~ 17,193`
    - `products ~ 2,490`
- Màn `Create Order` hiện render dropdown client/product với số option lớn, gây:
    - mở modal chậm,
    - dropdown lag khi focus/gõ/search,
    - browser bị nặng do DOM lớn.

## 2) Mục tiêu cải thiện

- Giảm thời gian mở `Create Order`.
- Giữ đầy đủ chức năng tìm kiếm client/product.
- Không phá nghiệp vụ tạo đơn hàng hiện tại.
- Ưu tiên thay đổi an toàn, rollback dễ.

## 3) Phương án chính (khuyến nghị): Server-side search + phân trang

### Mô tả

- Dropdown client/product đổi sang tải dữ liệu theo trang (`top N`, ví dụ 30).
- Khi gõ từ khóa: query server-side theo keyword.
- Khi scroll cuối list: gọi trang tiếp theo (infinite scroll).

### Hành vi tìm kiếm

- **Client**: tìm theo `name`, `company_name`, `email`, `mobile`, `client_code`.
- **Product**: tìm theo `name`, `sku` (mở rộng barcode nếu có).
- Có debounce (250-400ms), cache theo từ khóa/trang, và hủy request cũ khi gõ tiếp.

### Ưu điểm

- Giảm mạnh tải ban đầu và DOM browser.
- Trải nghiệm tìm kiếm tốt hơn trên dữ liệu lớn.
- Scale tốt khi dữ liệu tăng thêm.

### Nhược điểm

- Tăng số request nhỏ (open/search/scroll), cần cấu hình debounce + cache hợp lý.

## 4) Các hướng giải quyết khác (bổ sung)

## Hướng A - Giữ dropdown hiện tại nhưng giới hạn danh sách ban đầu

- Chỉ nạp sẵn `top 100` khách hàng/sản phẩm gần dùng.
- Nút "Tìm nâng cao" mới gọi API search.
- **Ưu:** sửa nhanh, ít đụng code.
- **Nhược:** UX tìm kiếm toàn bộ không liền mạch bằng server-side dropdown.

## Hướng B - Tách bước chọn đối tượng (wizard 2 bước)

- Bước 1 chọn `Client` (màn/quick modal riêng), bước 2 mới load form item.
- Product chỉ load khi đã chọn client hoặc category.
- **Ưu:** giảm tải đáng kể lúc mở form.
- **Nhược:** thay đổi UX lớn hơn, cần đào tạo ngắn cho user.

## Hướng C - Bộ lọc mặc định + lazy load theo điều kiện

- Product chỉ load khi chọn category hoặc nhập >= 2 ký tự.
- Client chỉ load danh sách gần đây, phần còn lại qua search API.
- **Ưu:** cân bằng giữa tốc độ và thay đổi nhỏ.
- **Nhược:** cần logic fallback rõ để tránh "không thấy dữ liệu".

## Hướng D - Tối ưu DB/index cho query tìm kiếm

- Bổ sung index phù hợp:
    - `users(name, email, mobile, status, company_id)`
    - `client_details(company_name, client_code, user_id)`
    - `products(name, sku, company_id)`
- **Ưu:** tăng tốc query search rõ rệt.
- **Nhược:** không giải quyết được DOM nặng nếu vẫn render toàn bộ option.

## Hướng E - Cache danh sách phổ biến theo company/user

- Cache top clients/products được dùng nhiều trong 15-60 phút.
- Kết hợp search API cho phần còn lại.
- **Ưu:** nhanh cho thao tác lặp lại.
- **Nhược:** cần chiến lược invalidate cache.

## 5) Đề xuất triển khai thực tế (ít rủi ro)

### Phase 1 (nhanh, an toàn)

- Áp dụng server-side search cho `Client` trước.
- Giữ nguyên hành vi submit order.
- Theo dõi:
    - thời gian mở modal,
    - thời gian có kết quả search đầu tiên,
    - số lỗi request 4xx/5xx.

### Phase 2

- Áp dụng tương tự cho `Product`.
- Bổ sung index cho query hay dùng.

### Phase 3

- Thêm cache top N + tuning debounce/page size theo log thực tế.

## 6) KPI kiểm chứng

- Thời gian mở `Create Order`: giảm ít nhất 50%.
- Time-to-first-results khi search: < 500-800ms (local mục tiêu mềm).
- Không phát sinh regression ở:
    - tạo order,
    - chọn client + tự fill địa chỉ,
    - thêm item product.

## 7) Quy tắc nghiệp vụ phải giữ nguyên

- `Order` chỉ cần `client_id` để lưu.
- Dữ liệu hiển thị là phục vụ UX, không được làm thay đổi business flow.
- Không thay đổi logic tính tiền, thuế, submit order hiện có.

## 8) Ghi chú mở rộng: các màn ngoài Order cũng có rủi ro tương tự

Kết quả rà soát codebase cho thấy ngoài `Order`, còn nhiều màn đang gọi `User::allClients()` và/hoặc `Product::all()` để đổ dropdown lớn.

### Nhóm ưu tiên cao (cùng pattern nhập item + chọn client/product)

- `InvoiceController` + `resources/views/invoices/ajax/create.blade.php`, `edit.blade.php`
- `EstimateController` + `resources/views/estimates/ajax/create.blade.php`, `edit.blade.php`
- `RecurringInvoiceController` + `resources/views/recurring-invoices/ajax/create.blade.php`, `edit.blade.php`
- `ProposalController` + `resources/views/proposals/ajax/create.blade.php`, `edit.blade.php`
- `DealController` (các màn create/edit liên quan product list)

### Nhóm ưu tiên trung bình (dropdown lớn nhưng tần suất thấp hơn)

- `CreditNoteController`
- `EstimateTemplateController`
- `ProposalTemplateController`
- `LeadContactController`, `LeadBoardController`
- Một số màn trong `Modules/Purchase` (ví dụ `VendorCreditController`)

### Nhóm chỉ cần tối ưu client list (không nhất thiết product picker)

- Các controller/report/calendar/task/ticket/event dùng `User::allClients()` để lọc:
    - `ProjectController`, `ContractController`, `TaskController`, `TicketController`, `PaymentController`, `SalesReportController`, `FinanceReportController`, `EventCalendarController`, `TaskBoardController`, `TaskCalendarController`, `MessageController`, `Timelog*Controller`, ...

## 9) Kế hoạch rollout theo diện rộng

### Wave 1 (ưu tiên nghiệp vụ tài chính/bán hàng, tác động lớn)

- Order (đang xử lý)
- Invoice
- Estimate
- Recurring Invoice

### Wave 2

- Proposal / Deal / Credit Note
- Lead Contact liên quan multi-select product

### Wave 3

- Các màn còn lại chỉ dùng client filter/list (report/calendar/task)

Ghi chú vận hành:

- Triển khai theo feature flag UI nếu cần (`lazy_client_product_search=true`) để rollback nhanh.
- Mỗi wave cần smoke test:
    - mở form,
    - search client/product,
    - submit thành công,
    - không đổi dữ liệu nghiệp vụ lưu DB.

## 10) Cập nhật mới (Orders list performance + UI consistency)

### 10.1 Phát hiện mới: Trang `Orders` load chậm ngay khi mở

- Nguyên nhân chính không chỉ ở popup `Create Order`, mà còn ở trang list `Orders`:
    - `OrderController@index` gọi `User::allClients()` để render filter client.
    - Với dữ liệu hiện tại ~`17,193` clients, dropdown filter tạo DOM rất lớn.
    - `select-picker` phải init danh sách lớn + DataTable draw => cảm giác tải chậm.
- Đo nhanh local:
    - `User::allClients()` ~ `497.6ms` (query)
    - `Project::allProjects()` ~ `1.6ms`
    - => bottleneck chính là preload clients cho filter.

### 10.2 Cập nhật UI đã làm

- Đã đồng nhất cột Action của `Order` theo pattern `PO`:
    - bỏ nút `View` đứng riêng ngoài cột,
    - dùng menu 3 chấm và đưa `View` vào dropdown.
- Mục tiêu: đồng nhất giao diện thao tác giữa `Order` và `PO`.

### 10.3 Hành động tiếp theo (ưu tiên ngay)

- Tối ưu filter client ở trang `Orders` tương tự hướng server-side:
    - không render toàn bộ 17k option khi tải trang,
    - load top N ban đầu (ví dụ 30),
    - search + scroll gọi API phân trang.
- Giữ nguyên nghiệp vụ filter:
    - lọc theo `clientID` vẫn hoạt động trên DataTable server-side.

### 10.5 Trạng thái triển khai Order (đã làm)

- Đã giới hạn tải ban đầu `top 100` cho:
    - client list ở `OrderController@index` (filter trang Orders),
    - client list ở `OrderController@create` (form Add Order),
    - product list ở `OrderController@create` + `OrderController@edit`.
- Đã thêm endpoint search server-side cho Order:
    - `orders.search_clients`
    - `orders.search_products`
- Đã gắn remote search + infinite scroll (debounce) cho:
    - filter client ở trang Orders (`#clientID`),
    - dropdown client trong Add Order (`#client_list_id`),
    - dropdown product trong Add/Edit Order (`#add-products`).
- Kết quả mong đợi:
    - mở trang Orders và mở form Add/Edit Order nhẹ hơn đáng kể với dữ liệu lớn.

### 10.4 Tiêu chí xác nhận sau tối ưu trang `Orders`

- Thời gian mở trang `Orders` giảm rõ rệt (mục tiêu >= 40-60%).
- Không lỗi ở:
    - lọc client,
    - tìm kiếm text,
    - đổi trạng thái order,
    - action dropdown (view/download/edit/delete).

## 11) Cập nhật mới: Wave kế tiếp cho `Invoice` (Local)

### 11.1 Phạm vi đã triển khai

- Tối ưu cùng pattern với `Order`, chỉ làm trên local:
    - giới hạn tải ban đầu `top 100`,
    - remote search + infinite scroll cho dropdown lớn,
    - mỗi lần load thêm `50` records.

### 11.2 Backend đã cập nhật

- `InvoiceController@index`:
    - client filter chỉ preload `100` clients.
- `InvoiceController@create`, `InvoiceController@edit`:
    - preload `100` clients + `100` products.
- Thêm API mới:
    - `invoices.search_clients`
    - `invoices.search_products`
- Rule tìm kiếm:
    - client: `name`, `email`, `mobile`, `company_name`, `client_code`
    - product: `name`, `sku`, có hỗ trợ `category_id`
- Với luồng Purchase:
    - chỉ trả product `service` hoặc product có `track_inventory=1` và còn tồn kho (`purchase_stock_adjustments.net_quantity > 0`).

### 11.3 Frontend đã cập nhật

- `invoices/index`:
    - filter client `#clientID` chuyển sang remote search + scroll.
- `invoices/ajax/create`:
    - `#client_list_id` remote search + scroll.
    - `#add-products` remote search + scroll.
    - filter category product dùng API phân trang (`invoices.search_products`) thay vì nạp full list.
- `invoices/ajax/edit`:
    - `#client_list_id` remote search + scroll.
    - `#add-products` remote search + scroll.
    - filter category product dùng API phân trang.

### 11.4 Kỳ vọng sau thay đổi

- Mở list `Invoices` và popup create/edit invoice nhẹ hơn đáng kể khi dữ liệu lớn.
- Không đổi nghiệp vụ lưu dữ liệu (`client_id`, item, tax, total).
- UX search client/product vẫn giữ nguyên, chỉ đổi cơ chế nạp dữ liệu.
