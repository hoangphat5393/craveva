# Ghi chú nghiệp vụ — UAT Warehouse (Miaolin): ý nghĩa, thao tác UI, liên hệ module

**Mục đích:** Giúp người mới (hoặc PM/QA) hiểu **mỗi phần trong checklist UAT nói về nghiệp vụ gì**, **làm gì trên màn hình**, và **liên quan Product / module khác ra sao** — không đi sâu code.

**Tham chiếu checklist:** `FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`

---

## 1) Bức tranh tổng thể (1 phút)

- **Sản phẩm (Product)** trong hệ thống này là **danh mục hàng** (tên, SKU, giá…). **Không bắt buộc** gắn “thuộc kho nào” trên từng sản phẩm.
- **Kho (Warehouse)** là **địa điểm** cất hàng.
- **Tồn kho thực tế** = theo cặp **(Kho + Sản phẩm [+ lô/HSD nếu có])**, được cập nhật khi:
    - nhập/xuất/điều chỉnh tay,
    - nhận hàng mua (PO/DO),
    - đồng bộ từ Purchase Inventory,
    - (tương lai / gap) bán hàng trừ tồn theo kho.

**Tóm lại:** Checklist PM gửi kiểm tra **“kho + số lượng + lịch sử phát sinh”** có đúng và an toàn không; **Product** chỉ là **đối tượng** được cộng/trừ tồn, không phải “chỗ chọn kho” khi tạo sản phẩm (trừ khi sau này sản phẩm có thêm field mặc định — hiện không nằm trong checklist cốt lõi).

---

## 2) Các module liên quan (ai làm việc gì)

| Module / khu vực                               | Vai trò trong UAT này                                                                                                                                                                                 |
| ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Warehouse**                                  | Danh sách kho, thêm/sửa/xóa kho; điều chỉnh tồn; chuyển kho; xem ledger (lịch sử phát sinh).                                                                                                          |
| **Products**                                   | Tạo/sửa **master** sản phẩm (SKU…). UAT cần có vài sản phẩm để **chọn khi nhập/xuất tồn** — không kiểm tra “gắn kho trên form Product”.                                                               |
| **Purchase — Purchase Order / Delivery Order** | Luồng **mua hàng → nhận hàng → nhập kho** (theo cấu hình PO delivered hoặc DO received).                                                                                                              |
| **Purchase — Inventory (Purchase Inventory)**  | Điều chỉnh / đồng bộ **số tồn mục tiêu** theo kho + sản phẩm (absolute sync), có thể tạo movement inbound/outbound để khớp số.                                                                        |
| **Client (Khách hàng)**                        | Có thể có **kho mặc định** cho khách (metadata phục vụ giao hàng / tương lai chọn kho bán). Checklist UAT cốt lõi **không bắt buộc** test Client, nhưng gap “bán hàng theo kho” sẽ liên quan sau này. |
| **Sales / Invoice / Payment**                  | **Không nằm trong phần “đã xong” của checklist** cho Miaolin inventory-aware sales: gap report nói **chưa** trừ tồn kho đúng chuẩn warehouse khi bán — cần làm thêm sau.                              |

---

## 3) Giải thích từng nhóm trong checklist — nghiệp vụ là gì, UI làm sao

> Ghi chú menu: tên menu có thể khác theo ngôn ngữ / cấu hình; thường nằm nhóm **Operations** hoặc **Warehouse** (All warehouses, Stock adjustment, Stock movements, Transfer…).

### A. Preconditions / Setup — Chuẩn bị trước khi test

**Nghiệp vụ:** Đảm bảo môi trường test **đúng quyền**, **đúng cấu hình**, **có dữ liệu mẫu**.

**Việc cần làm (không phải một màn hình duy nhất):**

- Bật module Warehouse, chạy migration (kỹ thuật).
- Gán quyền cho user UAT (xem kho, thêm tồn, chuyển kho, xem movement…).  
  _Trong tài liệu PM có thể ghi `warehouse_view` — trên hệ thống thực tế tên quyền có thể là dạng `view_warehouses`, `add_warehouse_stock`… cần đối chiếu bảng permission._
- **Cấu hình quan trọng:**
    - `WAREHOUSE_ALLOW_NEGATIVE_STOCK`: thường **tắt** để không cho bán/xuất âm tồn (an toàn).
    - **Chỉ chọn một** nguồn nhập chính: **PO delivered** _hoặc_ **DO received** — tránh **nhập đôi** cùng một lô hàng.
- Chuẩn bị: ít nhất **2 kho**, 1 kho **mặc định**; vài **sản phẩm** có SKU; (tuỳ quy trình) sản phẩm có lô/HSD.

**Liên quan Product:** Cần có sản phẩm trong **Products** để sau này chọn trên màn điều chỉnh tồn — **không** yêu cầu gắn kho lúc tạo Product.

---

### B. Warehouse Master — Quản lý danh sách kho

**Nghiệp vụ:** Tạo “địa điểm kho” trong công ty: tên, mã, trạng thái, kho mặc định.

**Thao tác UI (ý chính):**

- Vào màn **danh sách kho** → **Tạo kho** → điền Name, Code (nếu có), Active/Inactive, có thể tick **Default**.
- **Sửa** tên/trạng thái.
- **Xóa kho trống** được; **xóa kho đang có tồn / có phát sinh / có giữ chỗ** bị **chặn** (bảo vệ dữ liệu).
- (Nếu có trong build) **chọn nhiều dòng** → đổi trạng thái / xóa hàng loạt; **import Excel** kho.

**Liên quan Product:** Không trực tiếp. Product không “thuộc” một kho cố định trên master; tồn được ghi ở lớp kho.

---

### C. Stock Adjustment — Điều chỉnh tồn tay (nhập thêm / xuất bớt)

**Nghiệp vụ:** Ghi nhận **tăng** hoặc **giảm** số lượng tại **một kho** cho **một sản phẩm** (kiểm kê, hư hỏng, điều chỉnh sai số…).

**Thao tác UI:**

- Vào **Stock adjustment** (hoặc tương đương) → **Add stock** / form điều chỉnh.
- Chọn **Kho**, **Sản phẩm**, **Số lượng**, **Lý do** (nếu có).
- Chọn hành động **Thêm** (inbound) hoặc **Giảm** (outbound).

**Kỳ vọng nghiệp vụ:**

- Tồn trên màn tổng hợp tăng/giảm đúng.
- Có dòng trên **Stock movements** (nhập/xuất).
- Nếu không đủ tồn mà cấu hình không cho âm → **báo lỗi**, không đổi số liệu.

**Liên quan Product:** Chỉ **chọn** sản phẩm đã có trong **Products**. Không sửa master Product.

---

### D. Stock Transfer — Chuyển kho

**Nghiệp vụ:** Chuyển số lượng từ **kho nguồn** sang **kho đích** (cùng sản phẩm).

**Thao tác UI:**

- Vào **Transfer stock** → chọn **Từ kho**, **Đến kho**, **Sản phẩm**, **Số lượng**, mô tả.

**Kỳ vọng:** Nguồn trừ, đích cộng đúng số; **không** cho chuyển khi **trùng kho nguồn và đích**; giao dịch **atomic** (cả hai bên cùng thành công hoặc rollback).

**Liên quan Product:** Chọn sản phẩm có tồn ở kho nguồn.

---

### E. Stock Movements — Sổ cái / lịch sử phát sinh

**Nghiệp vụ:** **Xem lại** mọi biến động (audit): ai/đâu biết hàng nhập hay xuất, tham chiếu chứng từ nào.

**Thao tác UI:**

- Vào **Stock movements** → lọc theo kho, loại phát sinh, tìm theo tên/SKU.

**Liên quan Product:** Tìm theo sản phẩm; không chỉnh sửa Product tại đây.

---

### F. Purchase → Warehouse Inbound — Nhập kho từ mua hàng

**Nghiệp vụ:** Khi **nhận hàng mua**, hệ thống **tự (hoặc bán tự động)** ghi **nhập kho** vào kho đã chọn.

**Hai cách vận hành (chọn một làm chuẩn — checklist nhấn mạnh):**

| Cách                        | Ý nghĩa nghiệp vụ                                                                  | Thao tác UI (tóm tắt)                                                                                                                                                      |
| --------------------------- | ---------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Option 1 — PO delivered** | Coi **trạng thái giao hàng của PO** là sự kiện “đã nhận đủ / ghi nhận nhập”.       | Tạo **Purchase Order**, chọn **kho** trên PO (nếu form có), thêm dòng hàng → khi đủ điều kiện nghiệp vụ, đổi **delivery_status** sang **delivered** → kỳ vọng có nhập kho. |
| **Option 2 — DO received**  | Coi **phiếu giao hàng nhận (Delivery Order inbound)** là chứng từ nhận hàng chuẩn. | Tạo **Delivery Order** loại inbound, chọn kho, nhận hàng → đổi **status** sang **received** → nhập kho.                                                                    |

**Double count prevention:** Không bật **đồng thời** hai cơ chế cho cùng một quy trình nhận hàng — dễ **nhập đôi** số lượng.

**Liên quan Product:** Dòng hàng trên PO/DO trỏ tới **sản phẩm** đã có trong **Products**.

---

### G. Purchase Inventory → Absolute sync — Đồng bộ tồn theo “số đích”

**Nghiệp vụ:** Người dùng (hoặc import) đặt **tồn mục tiêu** cho **một kho + một sản phẩm**; hệ thống tính **chênh lệch** và tạo các movement **nhập/xuất** để khớp đúng số đó.

**Thao tác UI:**

- Vào khu **Purchase Inventory** (điều chỉnh tồn theo kho — tên menu có thể là Inventory / Purchase inventory tùy cấu hình).
- Chọn kho + sản phẩm + nhập **số lượng tuyệt đối** mục tiêu (hoặc luồng tương đương trong build).

**Liên quan Product:** Sản phẩm phải tồn tại; tồn theo kho được cập nhật qua service kho.

---

### H. Permissions & multi-tenant — Quyền và đa công ty

**Nghiệp vụ:** Đúng người được làm đúng việc; dữ liệu **công ty A** không lộ sang **công ty B**.

**Thao tác UI:** Thử login user khác quyền / công ty khác và xác nhận không thấy kho/tồn của người khác; thử thao tác trái quyền bị chặn.

**Liên quan Product:** Product cũng theo phạm vi công ty (thông thường); nhưng test này tập trung **kho và movement**.

---

### I. UX / Regression — Trải nghiệm

**Nghiệp vụ:** Form dùng được (modal, lỗi rõ, mobile tạm ổn), danh sách lớn không treo.

**Không gắn một module cụ thể** — là kiểm tra chất lượng dùng.

---

## 4) Phần Gap Report trong cùng file — “thiếu” nghĩa là gì (nghiệp vụ)

### Critical — Bán hàng chưa trừ tồn kho chuẩn Warehouse

**Ý PM:** Khi bán (Order/Invoice/Payment), **tồn tại kho** (warehouse) **chưa** được trừ đúng qua cơ chế movement chuẩn — nên **không thể** coi là “bán hàng có kiểm soát tồn theo kho” đủ cho Miaolin.

**Liên quan module:** Sales / Invoice / Payment / (sau này) Warehouse outbound.

### High — Nhập đôi (config) & legacy tồn khi thanh toán

- **Hai cờ nhập PO+DO:** dễ **cộng tồn hai lần** nếu cấu hình sai.
- **Thanh toán chỉnh tồn legacy** không theo kho: dễ **lệch** với sổ warehouse.

### Medium / Low — FEFO đầy đủ, sắp xếp kho, link chứng từ

Cải thiện vận hành và audit, không chặn “kho chạy cơ bản” nếu đã test xong A–G.

---

## 5) Liên hệ với Product — trả lời thẳng câu hỏi thường gặp

| Câu hỏi                             | Trả lời ngắn                                                                                                                                                                   |
| ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Tạo Product có phải chọn kho không? | **Thường không.** Product là danh mục; tồn theo kho nằm ở module kho / giao dịch.                                                                                              |
| Vậy Product dùng ở đâu trong UAT?   | Là **đối tượng** được chọn khi điều chỉnh tồn, chuyển kho, PO/DO, inventory sync.                                                                                              |
| Client có liên quan không?          | Có thể có **kho mặc định** cho khách; phần checklist cốt lõi **không** bắt buộc test Client, nhưng **gap bán hàng** sau này sẽ cần quy tắc chọn kho (có thể dùng default này). |

---

## 6) Gợi ý cách đọc checklist khi làm việc với PM

1. **Wave 1:** A → E + F/G + H (ổn định kho, nhập mua, đồng bộ inventory) — “kho vận hành được”.
2. **Wave 2:** Gap **sales outbound** + chỉnh legacy — “bán đúng tồn theo kho”.
3. **Wave 3:** Medium/Low (FEFO UI, deep link…) — “mượt và kiểm soát tốt hơn”.

---

_Tài liệu giải thích nghiệp vụ theo checklist PM; chi tiết kỹ thuật và tên route xem thêm `WAREHOUSE_MASTER_GUIDE.md` / `multi_warehouse_audit_report.md`._
