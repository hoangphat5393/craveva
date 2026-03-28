# Giải thích: luồng đơn hàng → hóa đơn bán & trừ tồn (Dingxin ERP vs Craveva)

**Mục đích file:** Ghi lại nội dung một cuộc trao đổi nội bộ (có nhắc Beary, Noreen) về **quy trình Dingxin (鼎新)** sau khi triển khai xong, để ai đọc cũng hiểu **“đây là gì”** và **Craveva cần quan tâm tới đâu**.

**Nguồn:** Bản dịch tiếng Anh từ mô tả nghiệp vụ thực tế tại chỗ dùng ERP Dingxin; **không** phải spec kỹ thuật của repo Craveva.

---

## 1) “Đây là gì” trong một câu

Đây là mô tả **cách một công ty vận hành bán hàng + kho trong hệ ERP Dingxin**:

- Hệ thống bên họ tạo **đơn hàng (order)**.
- Đơn được đưa vào Dingxin; **nhân viên bán hàng** chuyển đơn thành **hóa đơn bán (sales invoice)**.
- **Thời điểm trừ tồn** được chia làm hai ý (quan trọng):
    1. **Khi lưu hóa đơn bán:** trừ (hoặc “giữ”) phần **số lượng khả dụng (available)** — tức là chỗ họ coi là đã “ăn” vào tồn có thể bán được.
    2. **Khi xác nhận hóa đơn sau khi soạn hàng (picking) xong:** trừ tồn **thực tế như đã xuất kho (shipped / deducted thật)**.

Người viết còn nói: **điểm sớm nhất mà tồn bị ảnh hưởng là khi hóa đơn được lưu** (available giảm).

---

## 2) Các ý chính trong logic Dingxin (theo bản dịch)

| Ý                                            | Nội dung                                                                                                                                                                           |
| -------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Kho xuất**                                 | Do **nhân viên bán** chỉ định thủ công lúc chuyển đơn → hóa đơn. Có **kho mặc định** (gần khách); nếu kho đó không đủ hàng thì có thể **tách nhiều hóa đơn** để giao từ nhiều kho. |
| **Sửa đơn / hóa đơn**                        | Nếu hóa đơn **đã lưu** nhưng **chưa bắt đầu picking**: có thể **“bỏ lưu” (un-save)** hóa đơn và sửa đơn → **tồn khả dụng** được điều chỉnh lại.                                    |
| **Sau khi xác nhận hóa đơn** (picking xong): | **Không sửa** được nữa; có thể phải **trả hàng bán (sales return)** hoặc **hủy void** — tác giả nói **cần xác lại quy trình chính xác**.                                           |
| **Đã giao hàng thật**                        | Chỉ còn đường **sales return** (trả hàng).                                                                                                                                         |
| **Thông điệp gửi Craveva**                   | Phần logic trừ tồn chi tiết **diễn ra trong Dingxin**; Craveva **không cần hiểu sâu** — chỉ cần **đồng bộ số tồn mới nhất** và **trạng thái đơn** để ra quyết định bán hàng.       |

---

## 3) Vì sao người ta hỏi “có cần hiểu logic trừ tồn bên trong không?”

Ý là: miễn **số available / tồn hiển thị** luôn **khớp** với thực tế (dù ERP trừ ở bước “save invoice” hay “confirm after picking”), thì hệ bên ngoài (ví dụ Craveva) chỉ cần **đọc dữ liệu tồn và trạng thái** để quyết định có bán tiếp hay không — **không bắt buộc** phải copy y nguyên cơ chế nội bộ của Dingxin.

Điều này **không** có nghĩa là dev không cần biết gì: khi **tích hợp** (API/file/sync), vẫn phải thống nhất **định nghĩa** “tồn Craveva đang là available hay on-hand hay committed” để không lệch với Dingxin.

---

## 4) Liên hệ với Craveva / Scope B (warehouse trong repo này) — chỉ để đối chiếu

Trong codebase Craveva đã có hướng **Scope B**: trừ tồn kho vật lý khi **invoice** đạt trạng thái nhất định (v1: không draft, không credit note, sync sau khi lưu invoice).

**Điểm cần làm rõ với PM / Dingxin:**

- **Trigger trừ tồn** bên Craveva có khớp với **“save invoice”** hay với **“confirm sau picking”** không? Hai bên ERP có thể khác nhau.
- **“Available”** trên Craveva có phải là **đã trừ committed khi lưu invoice** (giống mô tả Dingxin sớm) hay chỉ **tồn thực tế** sau xuất kho?

File này **không** quyết định thay PM; chỉ giúp **dịch ngôn ngữ nghiệp vụ** sang chỗ có thể họp và chốt bảng mapping.

---

## 5) Tóm tắt cho người mới đọc (tiếng Việt)

1. **Đoạn văn gốc** = mô tả **quy trình Dingxin**: đơn → hóa đơn bán → lưu hóa đơn làm giảm tồn khả dụng → picking → xác nhận hóa đơn thì trừ tồn kiểu “đã xuất”.
2. **Craveva (trong câu chuyện đó)** được xem là hệ **chỉ cần lấy tồn + trạng thái đơn cập nhật**, không cần sao chép toàn bộ quy tắc nội bộ Dingxin.
3. **Beary, Noreen** = người được nhờ **đối chiếu xem mô tả logic có đúng thực tế vận hành không** (UAT / nghiệp vụ).
4. Phần **return / void sau khi đã confirm** = **chưa chốt** trong đoạn văn; cần xác minh thêm.

---

## 6) Thuật ngữ nhanh

| Thuật ngữ                 | Gợi ý nghĩa trong ngữ cảnh                                         |
| ------------------------- | ------------------------------------------------------------------ |
| **Order**                 | Đơn hàng (trước hoặc song song với hóa đơn tùy hệ).                |
| **Sales invoice**         | Hóa đơn bán trong Dingxin.                                         |
| **Save**                  | Lưu hóa đơn (chưa chắc đã xuất kho vật lý).                        |
| **Picking**               | Soạn hàng trong kho.                                               |
| **Confirm sales invoice** | Xác nhận sau picking — coi như đã xử lý xuất/chốt một bước.        |
| **Available inventory**   | Tồn có thể bán / được phép allocate (định nghĩa chính xác do ERP). |
| **Sales return**          | Trả hàng bán — điều chỉnh tồn theo chiều ngược.                    |

---

_Cập nhật: 2026-03-28 — file giải thích nội dung trao đổi, không thay thế tài liệu UAT chính thức._
