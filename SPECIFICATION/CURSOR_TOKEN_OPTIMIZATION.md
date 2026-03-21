# Tối ưu token khi dùng Cursor

Tài liệu nội bộ: cách **giảm lượng context** đưa vào model (tiết kiệm token, tránh cắt ngữ cảnh, phản hồi ổn định hơn).

---

## 1. Nguyên tắc chung

- Token **tỉ lệ thuận** với độ dài **text thực sự** được đưa vào cuộc hội thoại (file được đọc, nội dung paste, output công cụ).
- **Càng ít ký tự cần thiết** cho từng câu hỏi → càng tiết kiệm.
- File **rất lớn** (hàng chục nghìn dòng) thường **vượt hoặc gần vượt** giới hạn context — không nên kỳ vọng AI “đọc hết” trong một lượt.

---

## 2. File lớn (CSV, Excel, log, PDF, Word)

| Làm                                                                                                             | Tránh                                                             |
| --------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| Gửi **mẫu nhỏ**: vài chục / vài trăm dòng **đầu + cuối**, hoặc **random sample**                                | `@` hoặc paste **nguyên file 10k–20k+ dòng** để phân tích toàn bộ |
| Dùng script (Python, `head`, `wc`, pandas `describe` / `value_counts`) → chỉ paste **kết quả tóm tắt** vào chat | Nhờ AI sinh/đọc từng dòng trong chat                              |
| Chia việc: `grep` / filter ra **file nhỏ** (chỉ dòng lỗi) rồi mới nhờ AI                                        | Một prompt “sửa hết file 50MB”                                    |

**Word / Excel / PDF:** phần nào được trích thành text và đưa vào model vẫn tính token theo **độ dài nội dung** — vẫn áp dụng quy tắc “ít dòng hơn trong context”.

---

## 3. Cách dùng `@file` và paste

- Ưu tiên **`@đường_dẫn_file`** thay vì copy toàn bộ nội dung vào khung chat (tránh lỡ paste trùng + dễ kiểm soát file).
- Chỉ `@` **các file thực sự liên quan** tới câu hỏi hiện tại.
- Với file lớn: **mở file**, chỉ định **dòng** trong prompt (“xem khoảng dòng 120–180 trong `Foo.php`”) thay vì attach cả file nếu không cần.

---

## 4. Việc lặp: Word / CSV / Excel

- **CSV:** sửa bằng editor text hoặc Excel — không cần AI cho thao tác thuần chữ.
- **Word (.docx):** dùng **Pandoc** hoặc script có sẵn trong repo (ví dụ export Markdown → `.docx`) — **một lệnh terminal**, không tốn token chat.
- **Excel phức tạp:** làm trong Excel / script tái sử dụng; nhờ AI **một lần** viết script, các lần sau chỉ chạy lệnh.

---

## 5. Chọn model (gợi ý ngắn)

- Việc **nhẹ** (đổi tên, vài dòng, gợi ý đơn giản): model **nhỏ / nhanh** (ví dụ `gpt-4o-mini`) — thường rẻ hơn.
- **Sửa nhiều file / Composer:** dùng **Composer** (ví dụ Composer 2 Fast) đúng workflow của Cursor.
- **Debug / logic nặng:** model mạnh hơn (ví dụ Claude Sonnet); **Opus** khi cần suy luận rất sâu — đổi lại chi phí/latency cao hơn.

Không cần bật quá nhiều model cùng lúc — chỉ giữ **vài** lựa chọn bạn thực sự dùng.

---

## 6. Checklist nhanh trước khi gửi prompt

1. Có thể **rút gọn** dữ liệu hoặc chỉ gửi **phần lỗi** không?
2. Đã thử **grep / script** để thu hẹp phạm vi chưa?
3. Có đang **@** hoặc paste **trùng** nội dung không cần thiết không?
4. Câu hỏi đã **một mạch, có file + hành động mong muốn** chưa (tránh chat quá nhiều vòng mơ hồ)?

---

_Tài liệu tham khảo nội bộ; cập nhật khi Cursor đổi giới hạn context hoặc tính năng._
