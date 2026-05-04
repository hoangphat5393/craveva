# Biomixing flow vs Craveva — Bảng đối chiếu & ghi chú module

**Nguồn sơ đồ:** `manual_mixing_250kg_flowchart.mmd` / PDF 加工流程圖 2025.09.08 (manual mix 250KG).  
**Mục đích:** Liệt kê chức năng trong flow khách hàng, so với Craveva hiện tại, và gợi ý cách đóng gói module.

> **2026-04:** Một số ô “Craveva có?” (đặc biệt **kho / batch / Sales DO**) đã **được cập nhật** trong file (dòng #28, §“Đã có”, footer). **Chuẩn đối chiếu** vẫn là **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md` §3** khi lên kế hoạch **Production**. Các bước **HACCP / CCP / BOM / rework** trong bảng vẫn dùng được.

---

## 1. Danh sách chức năng trong sơ đồ Biomixing

| #                             | Bước trong sơ đồ                               | Chức năng ERP tương ứng                | Craveva có?  | Ghi chú                                                                                                                                                                                     |
| ----------------------------- | ---------------------------------------------- | -------------------------------------- | ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Nhập liệu & kiểm tra**      |
| 1                             | (9) Personnel & vehicle access control         | Kiểm soát ra vào người/xe (log, PRP)   | **Không**    | Ghi nhật ký thủ công, chưa số hóa trong ERP                                                                                                                                                 |
| 2                             | Raw materials / packaging in (Building A or B) | Nhập nguyên liệu / bao bì (PO receipt) | **Một phần** | Purchase: PO receipt; Warehouse: stock in (đa kho đã có). Building A/B = đặt tên kho / location chi tiết (nếu cần map vật lý)                                                               |
| 3                             | Raw material & packaging inspection            | Kiểm tra nhập hàng (pass/fail)         | **Không**    | Chưa có QC tại receiving; chưa có quarantine                                                                                                                                                |
| 4                             | Return / reject                                | Trả hàng / từ chối nhập                | **Một phần** | Purchase có return; chưa tích hợp rõ với QC                                                                                                                                                 |
| 5                             | Receive & store                                | Thu nhận và lưu kho                    | **Có**       | Warehouse nhập kho theo PO / stock in                                                                                                                                                       |
| **Lưu kho & công thức**       |
| 6                             | (1) Material & packaging warehousing           | Putaway, nhiều vị trí kho              | **Một phần** | Warehouse: đa kho, chuyển kho, tồn theo lô; **chưa** location type chi tiết như “恆溫空調室”                                                                                                |
| 7                             | Raw material warehouse                         | Kho nguyên liệu                        | **Có**       | Warehouse                                                                                                                                                                                   |
| 8                             | Temperature-controlled room                    | Phòng / kho kiểm soát nhiệt độ         | **Không**    | Chưa có location type; chưa tracking nhiệt độ                                                                                                                                               |
| 9                             | Company standard formula                       | Công thức tiêu chuẩn công ty           | **Không**    | Chưa có BOM / Recipe Management                                                                                                                                                             |
| 10                            | Custom product formula                         | Công thức sản phẩm tùy chỉnh           | **Không**    | Cùng vấn đề trên                                                                                                                                                                            |
| **Giai đoạn sản xuất**        |
| 11                            | (2) Manual weighing                            | Cân thủ công                           | **Một phần** | Projects/Tasks mô tả; chưa có Production batch record gắn task                                                                                                                              |
| 12                            | Confirm weight & materials                     | Xác nhận trọng lượng và nguyên liệu    | **Không**    | Chưa có checkpoint số hóa tại bước cân                                                                                                                                                      |
| 13                            | Handwritten records                            | Ghi chép thủ công                      | **Một phần** | Task notes; chưa có form HACCP chuẩn hóa                                                                                                                                                    |
| 14                            | Poor quality (NG)                              | Xử lý hàng lỗi                         | **Không**    | Chưa workflow NG → quarantine / rework / huỷ                                                                                                                                                |
| 15                            | Manual feeding port                            | Đầu nạp liệu thủ công                  | **Không**    | Mô tả trong task, không có checkpoint                                                                                                                                                       |
| 16                            | Raw material screening — CCP(2)                | Sàng nguyên liệu (CCP)                 | **Không**    | Chưa CCP checkpoint bắt buộc                                                                                                                                                                |
| 17                            | 250KG mixer                                    | Máy trộn 250KG                         | **Một phần** | Asset có thể ghi thiết bị; chưa link batch production                                                                                                                                       |
| 18                            | (10) Magnetic separation                       | Từ tính / loại bỏ kim loại             | **Không**    | Chưa checkpoint riêng                                                                                                                                                                       |
| **QC & đóng gói**             |
| 19                            | Physical inspection                            | Kiểm tra vật lý (物理性判定)           | **Một phần** | Quality Lock (task QC xong mới Delivery) — xem `BIOMIXING_GAP_ANALYSIS.md`                                                                                                                  |
| 20                            | Sampling                                       | Lấy mẫu                                | **Không**    | Chưa quản lý sampling                                                                                                                                                                       |
| 21                            | Outsourced lab testing (CLA / Xinhua)          | Gửi lab bên ngoài                      | **Không**    | Chưa upload COA / kết quả lab, chưa block release khi thiếu                                                                                                                                 |
| 22                            | (6) Packaging                                  | Đóng gói                               | **Một phần** | Task trong Project; chưa có production output → FG stock đầy đủ                                                                                                                             |
| 23                            | Leftover material                              | Nguyên liệu thừa                       | **Không**    | Chưa track riêng                                                                                                                                                                            |
| 24                            | (5) Rework                                     | Gia công lại                           | **Không**    | Chưa workflow rework (reason, approval, link batch gốc)                                                                                                                                     |
| **Xuất kho & vận chuyển**     |
| 25                            | (7) Finished goods warehouse                   | Kho thành phẩm                         | **Có**       | Warehouse                                                                                                                                                                                   |
| 26                            | (8) Shipping / transport                       | Vận chuyển / giao hàng                 | **Có**       | Delivery Order                                                                                                                                                                              |
| 27                            | (9) Personnel & vehicle access control (out)   | Ra vào khi xuất                        | **Không**    | Tương tự bước 1                                                                                                                                                                             |
| **Traceability & compliance** |
| 28                            | Batch / lot traceability                       | Truy xuất lô nguyên liệu → TP → khách  | **Một phần** | Hub: tồn batch + Sales DO đã gắn lô/HSD (**2026**). **Chưa** khép: RM theo lệnh SX → batch TP → dòng DO (thiếu **Production**). Xem `BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md` §3. |
| 29                            | CCP mandatory gate                             | Gate bắt buộc CCP                      | **Không**    | Chưa logic “không pack/ship nếu chưa pass CCP”                                                                                                                                              |
| 30                            | QA release before ship                         | QA release trước khi xuất              | **Một phần** | Quality Lock theo task; chưa block theo COA/lab                                                                                                                                             |

---

## 2. Tóm tắt thiếu / một phần / đã có

### Thiếu hoàn toàn (ưu tiên cao cho flow Biomixing)

- Receiving inspection / disposition (pass/fail, return, quarantine)
- Recipe / BOM (công thức chuẩn + tùy chỉnh)
- CCP checkpoint (ít nhất screening CCP(2), có thể thêm magnetic separation)
- Rework workflow (reason, approval, link batch)
- Sampling + COA / lab result + block release khi thiếu
- PRP (personnel & vehicle access logs)
- Location loại temperature-controlled

### Một phần — cần mở rộng

- Batch traceability end-to-end
- Quality Lock: mở rộng theo COA/lab
- Production batch record: gắn recipe + task + CCP + output
- Warehouse locations (RM / FG / nhiệt độ ổn định)

### Đã có — chủ yếu cấu hình / tích hợp

- Purchase (PO, receipt; inbound canonical theo cấu hình)
- Warehouse (đa kho, stock in/out/transfer, **batch/HSD**, movement; KPI tồn theo batch)
- **Sales DO** (confirm = reserve, ship = outbound; chọn **batch + expiry** trên dòng — **2026**)
- Projects / Tasks (mô phỏng lệnh SX / QC task, chưa thay Production)
- Delivery Order / Order / Invoice (luồng bán — xem `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`)

### Thứ tự triển khai gợi ý

1. Recipe / BOM
2. Receiving QC + disposition
3. CCP checkpoint (ít nhất CCP(2) screening)
4. Production batch record (điện tử)
5. Rework workflow
6. Sampling + COA + block QA release
7. PRP / access control logs (nếu khách yêu cầu ISO 22000)

---

## 3. Có phải “một module Production” không?

**Không nhất thiết là một module đơn lẻ tên Production** — vì flow Biomixing/HACCP trải trên nhiều lớp:

| Lớp                | Nội dung                                                                               | Thường đóng gói trong Craveva như thế nào                 |
| ------------------ | -------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| **Sản xuất / MES** | Batch record, BOM/recipe, routing từng bước (cân → sàng → trộn → từ), CCP gate, rework | **Production** (hoặc Manufacturing)                       |
| **Chất lượng**     | Sampling, COA, lab ngoài, QA release, NG disposition                                   | **Quality** / QMS (có thể là submodule hoặc module riêng) |
| **Kho**            | Receiving QC, quarantine, FG hold, location                                            | Mở rộng **Warehouse** (đã có nền)                         |
| **Tuân thủ**       | PRP (người/xe), nhật ký                                                                | **Compliance** hoặc form tùy chỉnh / module nhỏ           |

**Cách pitch hợp lý:**

1. **Một “Production” module (marketing / license)**
    - Bao gồm: **Production Orders + Batch Record + Recipe/BOM + CCP + Rework** (phần “làm hàng” trên sàn).
    - **Không** gom hết receiving QC, lab COA, PRP vào cùng một codebase nếu không muốn module quá nặng — nhưng có thể **một menu “Production”** với tab/con trỏ sang Warehouse QC, Quality, v.v.

2. **Tách kỹ thuật (recommended)**
    - `Production` (hoặc mở rộng `Projects`): batch, steps, CCP, rework.
    - `Quality` hoặc extension: sampling, COA, release.
    - `Warehouse`: đã có — thêm receiving disposition, location, FG hold.
    - `Purchase`: đã có — liên kết receiving inspection.

**Kết luận ngắn:**

- Với khách hàng: có thể gọi là **một giải pháp “Production & traceability”** hoặc **“Production module”** (gói sản phẩm).
- Với kiến trúc: **nên coi là một _vùng chức năng_ (domain)** gồm Production + Quality + mở rộng Warehouse/Purchase, **không** ép thành một class/module Laravel duy nhất nếu không muốn technical debt.

Tham chiếu thêm: `BIOMIXING_GAP_ANALYSIS.md` (gap tổng thể; đọc notice 2026-04), **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`** (nền SO/PO/DO/kho), `FUNC_LOGIC/WAREHOUSE_INDEX.md`, `manual_mixing_250kg_flowchart.mmd` (flow đã verify PDF).
