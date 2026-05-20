<!-- Đã chuyển đổi từ PM request.rtf qua pandoc để đọc AI/dev. Nguồn RTF không thay đổi. -->

# Yêu cầu PM — Biomixing (bản tiếng Việt)

_Bản gốc EN: [`PM_REQUEST.md`](./PM_REQUEST.md) · RTF: [`PM request.rtf`](./PM%20request.rtf) · Dịch máy: `scripts/bulk_translate_file.py --mode md-whole` (2026-05-20)._

**Bản gộp tiếng Việt có dấu (Gary + Phase 1 chat, không AI):** [`PM_YEU_CAU_TONG_HOP_VI.md`](./PM_YEU_CAU_TONG_HOP_VI.md) — dùng file đó làm spec chính; file này giữ làm bản dịch thô từ RTF.\_

---

**Yêu cầu về quy trình sản xuất & BOM của ERP cho Craveva**

Phiên bản: Bản nháp dành cho Dev TeamChuẩn bị bởi: GaryNgày: 15 tháng 5 năm 2026

**Mục tiêu**

Xây dựng quy trình sản xuất phù hợp trong Craveva ERP để:

Lệnh bán hàng → Lệnh sản xuất → Tiêu thụ BOM → Cập nhật hàng tồn kho →
Lệnh giao hàng → Hóa đơn

Trường hợp sử dụng ví dụ:

Khách hàng:

- Cà phê trắng Oldtown

Sản phẩm:

- Cà phê 3 trong 1 Custom 150g

Công thức:

- Đường: 50g

- Kem: 30g

- Cà phê Arabica: 70g

Số lượng đặt hàng:

- 3000 gói

**Vấn đề hiện tại**

Màn hình BOM hiện tại đang hiển thị:

- TẤT CẢ các sản phẩm trộn lẫn với nhau

- Không có sự ngăn cách giữa:
    - Thành phẩm

    - Nguyên liệu thô

    - Bao bì

    - Dịch vụ

Điều này tạo ra:

- UX kém

- chọn sai BOM

- sự nhầm lẫn trong sản xuất

- tồn kho không chính xác

**Kiến trúc sản xuất ERP đúng**

**1. Master sản phẩm (Product Master) phải tập trung một chỗ**

Tất cả các mục trước tiên phải được tạo trong:

Hoạt động → Sản phẩm

BOM KHÔNG nên tạo ra sản phẩm.

BOM chỉ nên tham khảo sản phẩm.

**Các loại sản phẩm được đề xuất**

Cần một trường loại sản phẩm thích hợp.

**Các loại sản phẩm được đề xuất**

---

**Loại sản phẩm** **Mục đích**

nguyên liệu thô
thành phẩm_hàng hóa Sản phẩm sản xuất có thể bán được
bán thành phẩm Sản phẩm trung gian
bao bì Vật liệu đóng gói

dịch vụ Dịch vụ phi chứng khoán

---

**Mẫu thiết lập sản phẩm**

**Nguyên liệu thô**

---

**Tên sản phẩm** **Loại sản phẩm**

Đường thô_nguyên liệu
Nguyên liệu làm kem

Nguyên liệu cà phê Arabica

---

**Thành phẩm**

---

**Tên sản phẩm** **Loại sản phẩm**
Cà phê trắng Oldtown 3 trong 1 150g đã hoàn thành_goods

---

**Khái niệm BOM**

BOM = Công thức/công thức sản xuất.

định nghĩa:

Cần những nguyên liệu gì để sản xuất ra 1 đơn vị sản phẩm hoàn chỉnh.

**Ví dụ BOM**

**Hoàn thành tốt**

Cà phê trắng Oldtown Custom 3 trong 1 150g

**Đầu ra**

1 gói

**Thành phần**

---

**Thành phần** **Số lượng** **UOM**

Đường 50 g
Kem 30 g

Cà phê Arabica 70 g

---

Tổng số:

- 150g

**Thiết kế màn hình BOM được đề xuất**

**Tiêu đề BOM**

---

**Trường** **Ví dụ**

Mã BOM BOM-COF-001
Cà phê trắng Goodtown Oldtown Custom 3 trong 1 150g
Phiên bản BOM V1
Số lượng đầu ra 1
Gói UOM

Trạng thái hoạt động

---

**Bảng thành phần BOM**

---

**Thành phần** **Số lượng** **UOM** **Lãng phí %**

Đường 50 g 0
Kem 30 g 0

Cà phê Arabica 70 g 0

---

**Cần cải thiện UX quan trọng**

**Vấn đề hiện tại**

Trình đơn thả xuống BOM hiện tại:

- hiển thị TẤT CẢ sản phẩm

- rất khó chọn

- không thể mở rộng quy mô

**Lọc thả xuống bắt buộc**

**Đã hoàn thành việc thả xuống tốt**

Chỉ phải hiển thị:

loại sản phẩm = \'đã hoàn thành_hàng\'

**Thả xuống các thành phần BOM**

Chỉ phải hiển thị:

sản phẩm_loại TRONG (

\'raw_material\',

\'bán_hoàn thành\',

\'đóng gói\'

)

**Quy trình đặt hàng sản xuất**

**Ví dụ về lệnh sản xuất**

Đơn đặt hàng của khách hàng:

- 3000 gói

ERP sẽ tự động tính toán:

---

**Chất liệu** **Công thức**

Đường 50g × 3000
Kem 30g × 3000

Cà Phê Arabica 70g×3000

---

**Kết quả mong đợi**

---

**Vật liệu** **Tổng số yêu cầu**

Đường 150kg
Kem 90kg

Cà phê Arabica 210kg

---

**Logic hàng tồn kho**

**Sau khi hoàn thành sản xuất**

Hệ thống nên:

**Khấu trừ nguyên vật liệu**

---

**Mặt hàng** **Số lượng**

Đường -150kg
Kem -90kg

Cà phê Arabica -210kg

---

**Tăng thành phẩm**

---

**Mặt hàng** **Số lượng**
Cà phê trắng Oldtown Custom 3 trong 1 150g +3000 gói

---

**Cải tiến màn hình lệnh sản xuất**

Vấn đề màn hình hiện tại được quan sát:

\"Không có BOM nào được liên kết với đơn đặt hàng này.\"

Đây là hành vi đúng đắn.

Tuy nhiên cần cải tiến:

**Các trường đặt hàng sản xuất được đề xuất**

---

**Trường** **Mục đích**

Lệnh sản xuất Không theo dõi
Đã hoàn thành Sản phẩm được sản xuất tốt
BOM được liên kết đã chọn BOM
Số lượng dự kiến Số lượng mục tiêu
Số lượng sản xuất Số lượng thực tế
Kho nguyên liệu Kho tiêu thụ
Điểm đến FG kho thành phẩm
Truy xuất nguồn gốc mã lô

Trạng thái Bản nháp / Đã phát hành / Đang tiến hành / Đã hoàn thành

---

**Luồng trạng thái được đề xuất**

**Đơn đặt hàng**

Dự thảo → Đã xác nhận

**Lệnh sản xuất**

bản nháp

→ Đã phát hành

→ Đang sản xuất

→ Đã hoàn thành

→ Đã đóng cửa

** Lệnh giao hàng **

Đang chờ xử lý

→ Chọn

→ Đã giao hàng

**Hóa đơn**

bản nháp

→ Đã gửi

→ Đã trả tiền

**Quy trình ERP toàn diện được đề xuất**

**Bước 1**

Tạo khách hàng

Bán hàng → Khách hàng

**Bước 2**

Tạo nguyên liệu thô

Hoạt động → Sản phẩm

Ví dụ:

- Đường

- Kem

- Cà Phê Arabica

**Bước 3**

Tạo thành phẩm

Hoạt động → Sản phẩmcts

Ví dụ:

- Cà phê trắng Oldtown Custom 3 trong 1 150g

**Bước 4**

Tạo BOM

Sản xuất → Hóa đơn vật liệu

Liên kết:

- Thành phẩm

- Nguyên liệu thô

**Bước 5**

Tạo đơn bán hàng

Hoạt động → Đơn đặt hàng bán hàng

Khách hàng:

- Cà phê trắng Oldtown

Số lượng:

- 3000 gói

**Bước 6**

Tạo lệnh sản xuất

ERP nên:

- liên kết BOM

- tự động tính toán vật liệu

- hàng dự trữ

**Bước 7**

Sản xuất hoàn chỉnh

ERP nên:

- khấu trừ nguyên vật liệu

- thêm hàng tồn kho thành phẩm

**Bước 8**

Tạo lệnh giao hàng

Hoạt động → Lệnh giao hàng bán hàng

**Bước 9**

Tạo hóa đơn

Tài chính → Hóa đơn

**Khuyến nghị bổ sung**

**1. Thêm chuyển đổi UOM**

Cần hỗ trợ cho:

- g

- kg

- gói

- thùng carton

Ví dụ:

- 1000g = 1kg

**2. Truy xuất nguồn gốc hàng loạt**

Cần:

- mã lô

- ngày sản xuất

- ngày hết hạn

Đặc biệt quan trọng đối với:

- Sản xuất thực phẩm

- Hàng tiêu dùng nhanh

- sản phẩm cà phê

**3. Phiên bản BOM**

Cần:

- V1

- V2

- BOM đã lưu trữ

Hữu ích khi công thức thay đổi.

**4. Hỗ trợ BOM đóng gói**

Cần hỗ trợ cho:

- gói

- túi

- thùng carton

- nhãn

1 trường đóng gói FG như thế nào

Ví dụ:

---

**Thành phần** **Loại**

Nguyên liệu đường
Nguyên liệu cà phê
Bao bì gói Bao bì

Bao bì hộp carton

---

**Khuyến nghị cuối cùng**

Thiết kế ERP cốt lõi nên tuân theo:

Sản phẩm chính

├── Nguyên liệu thô

├── Bao bì

├── Bán thành phẩm

└── Thành phẩm

BOM

└── Công thức / Lớp công thức

Lệnh sản xuất

└── Lớp thực thi sản xuất

Hàng tồn kho

└── Lớp chuyển động chứng khoán

Lệnh bán hàng

└── Lớp thương mại

**Yêu cầu thay đổi mức độ ưu tiên**

**Ưu tiên cao**

- Phân loại loại sản phẩm

- Lọc thả xuống BOM

- Cấu trúc BOM phù hợp

- Logic tiêu thụ hàng tồn kho

- Tự động tính toán sản xuất

**Ưu tiên trung bình**

- Theo dõi hàng loạt

- Phiên bản BOM

- Chuyển đổi UOM

- Hỗ trợ đóng gói

**Kết quả mong đợi**

Sau khi thực hiện:

- BOM UX sạch hơn

- ERP sản xuất có khả năng mở rộng

- truy xuất nguồn gốc hàng tồn kho thích hợp

- lập kế hoạch vật liệu tự động

- Kế toán sản xuất phù hợp

- quy trình sản xuất FMCG có thể sử dụng
