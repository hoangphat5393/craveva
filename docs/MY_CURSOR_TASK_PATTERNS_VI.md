# Thói quen công việc với Cursor / AI (cá nhân) — bản ghi

File này lưu **kiểu việc bạn thường nhờ** để mở phiên Agent nhanh, prompt nhất quán, và tránh quên bước (audit doc, gộp MD, browser, SSH…).

**Cập nhật:** 2026-05-09 — _bổ sung dần khi nhớ thêm._ **Graphify:** xem quy ước mới trong [`.cursor/rules/graphify.mdc`](../.cursor/rules/graphify.mdc) (doc batch → tối đa một lần; code → sau mảng thay đổi).

**Tham chiếu Cursor (Agent / Multitask / token):** [`CURSOR_AGENT_MULTITASK_AND_USAGE_NOTES_VI.md`](CURSOR_AGENT_MULTITASK_AND_USAGE_NOTES_VI.md)

---

## 1) Audit thư mục (documentation / cấu trúc)

**Mục đích:** Rà soát một thư mục doc (`FUNC_LOGIC`, `FUNC_IMPROVE`, `FUNC_TEST`, …): file lỗi thời, trùng, link gãy, thiếu mục lục.

**Gợi ý prompt ngắn:**

> Audit thư mục `@<ĐƯỜNG_DẪN>`: liệt kê file, chỉ ra trùng/lỗi thời/link gãy, đề xuất gộp/xóa; cập nhật INDEX/master doc nếu cần; **không** mở rộng ngoài phạm vi.

**Checklist sau audit:**

- [ ] Cập nhật `INDEX.md` / `README.md` tương ứng
- [ ] `graphify update .` **tối đa một lần** ở **cuối** mẻ chỉnh doc (theo `.cursor/rules/graphify.mdc`); không chạy sau từng file `.md` / `.mmd`
- [ ] Chạy test tối thiểu nếu có chỉnh code (thường doc-only thì smoke test)

---

## 2) Audit dự án (tổng thể / theo chủ đề)

**Mục đích:** Không chỉ một thư mục — ví dụ luồng SO/PO/kho, Biomixing, performance, security theo đợt.

**Gợi ý prompt:**

> Audit dự án theo chủ đề **\<chủ đề\>**: đối chiếu doc `FUNC_*` với code/route hiện tại; nêu lệch; đề xuất doc hoặc code (chỉ phạm vi đồng ý).

---

## 3) Gộp file Markdown + xóa file thừa sau khi gộp

**Mục đích:** Giảm số file, một nguồn canonical, redirect hoặc cập nhật link (repo này thường **không** dùng stub redirect cho doc kho — cập nhật link trực tiếp).

**Gợi ý prompt:**

> Gộp nội dung từ `A.md` + `B.md` vào **`CANONICAL.md`** (mục rõ ràng), sau đó **xóa** `A.md` / `B.md` nếu không còn tham chiếu; `rg` toàn repo và sửa link.

**Lưu ý:** Trước khi xóa — chắc chắn không còn `git`/doc ngoài repo trỏ tới tên cũ (hoặc ghi vào mục “đã gộp” trong INDEX).

---

## 4) Hỏi luồng nghiệp vụ

**Mục đích:** Hiểu PO/DO/SO/Invoice/Warehouse, Production, import… theo doc master.

**Gợi ý:** Dùng **Ask mode** nếu chỉ đọc–giải thích; **Agent mode** nếu cần sửa doc/code sau câu trả lời.

**Điểm vào doc thường dùng trong repo:**

- Kho / bán mua: `FUNC_LOGIC/WAREHOUSE_INDEX.md`, `FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`
- Cải tiến / Biomixing: `FUNC_IMPROVE/INDEX.md`, `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`
- Test matrix: `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`

---

## 5) Cursor MCP Browser — tự kiểm lỗi trên trình duyệt

**Mục đích:** Demo / regression UI thật (theo rule dự án: dùng MCP `cursor-ide-browser` khi cần **live** kiểm tra).

**Gợi ý prompt:**

> Dùng **Cursor Browser MCP**: mở tab → navigate URL → snapshot (interactive nếu cần) → click/fill theo `ref` từ snapshot; ghi lại bước reproduce và kết quả Pass/Fail.

**Điều kiện:** App/local URL phải chạy được; login/session nếu màn hình cần auth.

---

## 6) SSH vào server hub / staging để sửa lỗi

**Mục đích:** Sửa cấu hình, log, service trên môi trường từ xa (không phải mọi phiên Agent đều có SSH sẵn — phụ thuộc máy bạn / quyền).

**Gợi ý prompt:**

> SSH `<user>@<host>` (staging/hub), chẩn đoán `<triệu chứng>`, chỉ sửa trong phạm vi **\<file/dịch vụ\>**; ghi lại lệnh đã chạy và rollback nếu có.

**Lưu ý an toàn:** Xác nhận đúng host (tránh nhầm production); backup config trước khi sửa.

---

## 7) Việc nhớ thêm (để điền sau)

| Việc (gợi nhớ) | Ghi chú / prompt mẫu |
| -------------- | -------------------- |
| _(trống)_      |                      |

---

## 8) Mẫu mở đầu phiên (copy–paste)

```
Bối cảnh: Craveva staging, Laravel.
Việc hôm nay: <1 trong các mục 1–6 ở trên>.
Phạm vi: <thư mục / module / URL>.
Ràng buộc: chỉ sửa file cần thiết; cập nhật INDEX/link; chạy test tối thiểu nếu đụng code.
```

---

_Khi nhớ thêm kiểu việc, thêm vào §7 hoặc tạo mục § mới (9, 10…)._
