# Ghi chép Cursor — Agent, Multitask, Subagent, token, fork (VI)

Tài liệu nội bộ tổng hợp giải thích đã trao đổi trong team (không thay cho tài liệu chính thức của Cursor; UI/tên tính năng có thể đổi theo phiên bản).

**Cập nhật:** 2026-05-09 · §2.1–2.2: ví dụ Agent / Ask / Multitask (Craveva) + triển khai theo Playbook

---

## 1) Multitask là gì (hai tầng hay gặp)

| Loại                                | Mô tả                                                                               | Có “tốn” token LLM không?                             |
| ----------------------------------- | ----------------------------------------------------------------------------------- | ----------------------------------------------------- |
| **Chạy nền / song song (terminal)** | Ví dụ: `php artisan test`, `graphify update`, `pnpm run production` chạy background | **Không** — chỉ CPU/IO máy dev                        |
| **Nhiều nhánh dùng AI**             | Một phiên có thể tách tác vụ: nhiều luồng **gọi model** (tool + reasoning)          | **Có** — mỗi lần gọi model tính usage theo gói Cursor |

**“Mỗi luồng vẫn chat với model”** nghĩa là: nhánh đó **gửi/nhận với dịch vụ LLM** (prompt + ngữ cảnh + kết quả tool), giống một đoạn hội thoại ẩn — khác với chỉ chạy lệnh shell không qua AI.

---

## 2) Agent mode vs Ask mode (Cursor)

- **Agent mode:** Phiên được phép **sửa file / chạy tool có ghi** trên workspace.
- **Ask mode:** Chủ yếu **đọc và giải thích**, không chỉnh repo thay bạn.

**Lưu ý:** Agent mode **không** đồng nghĩa “luôn có agent cha + subagent”. Nhiều phiên chỉ là **một** luồng model + tool, không tách con.

### 2.1) Khi nào **Ask** vs **Agent** vs **Multitask** (ví dụ trong repo này)

**Tách bài toán:** _Ask / Agent_ là **quyền có sửa file hay không**. _Multitask_ là **cách chạy lệnh nặng (và đôi khi tác vụ AI) song song / nền** — **không thay thế** Agent. Thực tế hay dùng: **Agent mode + (tùy chọn) bật Multitask** khi có lệnh lâu.

| Tình huống (Craveva)                                                                                   | Nên dùng                                        | Ghi chú                                                                                                                                                                                                                  |
| ------------------------------------------------------------------------------------------------------ | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Giải thích luồng PO/SO/kho từ `FUNC_LOGIC`, đối chiếu doc                                              | **Ask**                                         | Không ghi repo; ít rủi ro, thường ít vòng “sửa → test”.                                                                                                                                                                  |
| Audit MD: tìm link gãy, gộp file, cập nhật `INDEX.md`                                                  | **Agent**                                       | Cần ghi file; Multitask **không bắt buộc** trừ khi vừa chạy `graphify` / test dài.                                                                                                                                       |
| Sửa PHP + Pest + `vendor/bin/pint`                                                                     | **Agent**                                       | Bắt buộc Agent để ghi code; có thể **Multitask** để `php artisan test` hoặc `graphify` chạy **nền** trong lúc bạn đọc diff / trả lời tiếp.                                                                               |
| `pnpm run production` / build module lâu                                                               | **Agent** (nếu cần sửa asset) hoặc chỉ terminal | **Multitask / nền** giúp **không chặn** chat; **không** giảm token LLM cho bản thân lệnh build.                                                                                                                          |
| Live demo: MCP Browser kiểm UI                                                                         | **Agent** (thường)                              | Cần tool + đôi khi sửa nhỏ; Multitask chỉ hữu ích nếu **song song** với lệnh khác (vd. server `php artisan serve` đã chạy ngoài).                                                                                        |
| Triển khai tính năng mới theo Playbook đã phân tích (vd. `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`) | **Agent**                                       | Playbook giảm vòng “thiết kế” nhưng checklist vẫn map sang **ghi repo** (migrate, module, Pest, doc/log). **Multitask** khi test/build/`graphify` lâu. **Ask** chỉ khi chỉ **đọc–giải thích** Playbook, chưa triển khai. |

**Ví dụ “chỉ Agent, không cần Multitask”:** Sửa 2–3 file doc trong `FUNC_IMPROVE`, không chạy test/build lâu → một luồng Agent là đủ.

**Ví dụ “Agent + Multitask hợp lý”:** Agent vừa sửa code vừa bảo “chạy `php artisan test --compact tests/Feature/FooTest.php` nền”; trong lúc chờ bạn vẫn nhắn chỉnh hướng — **tiết kiệm thời gian chờ**, token LLM phụ thuộc **số vòng** agent phân tích log, **không** do Multitask tự giảm.

**Lỗi thường gặp:** Nghĩ phải **chọn** “Agent **hoặc** Multitask”. Đúng hơn: **Ask vs Agent** là lựa chọn chế độ; **Multitask** là **tùy chọn thêm** khi có việc **nền / song song** (chủ yếu terminal hoặc task không chặn UI).

### 2.2) Ví dụ **thực tế** Craveva — chọn chế độ chat vs Multitask

Ở Cursor, **Composer / chat** thường có **Ask** (chỉ đọc) hoặc **Agent** (ghi repo + tool). **Multitask** là **toggle / chế độ chạy song song & nền** — có thể **bật cùng Agent**, không phải “thay thế” Agent.

| Bạn định làm (ví dụ đúng path repo)                                                                                      | Chế độ chat nên chọn     | Multitask                                                                                                                                                                                           |
| ------------------------------------------------------------------------------------------------------------------------ | ------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Đọc `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md` hoặc `FUNC_LOGIC/WAREHOUSE_INDEX.md` và hỏi thứ tự nghiệp vụ | **Ask**                  | **Tắt** (đủ) — không có lệnh nặng bắt buộc                                                                                                                                                          |
| So sánh `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md` với `PROJECT BIOMIXING/README.md`, **không** sửa file                  | **Ask**                  | **Tắt**                                                                                                                                                                                             |
| Sửa vài link trong `FUNC_IMPROVE/INDEX.md`, `FUNC_INDEX.md`                                                              | **Agent**                | **Tắt** — chỉnh nhỏ, không cần nền                                                                                                                                                                  |
| Audit nhiều `FUNC_LOGIC/*.md` / `FUNC_IMPROVE/*.md`, cuối mẻ chạy **`graphify update .`** (thường vài chục giây trở lên) | **Agent**                | **Bật** nếu muốn **tiếp chat** trong lúc graphify chạy **nền**; **tắt** vẫn được nếu bạn chấp nhận chờ một lần                                                                                      |
| Sửa `Modules/Production/**/*.php` hoặc `Modules/Warehouse/**/*.php` + **`php artisan test --filter=…`** chạy lâu         | **Agent**                | **Nên bật** — test nền, agent đọc log khi xong                                                                                                                                                      |
| Đổi JS/CSS root rồi **`pnpm run production`** (Mix)                                                                      | **Agent** (hoặc cmd tay) | **Nên bật** — build lâu                                                                                                                                                                             |
| Live check UI theo rule MCP Browser (`craveva-staging.test`, snapshot, click theo `ref`)                                 | **Agent**                | **Tùy** — chỉ cần nếu **song song** lệnh dài (build/test/graphify)                                                                                                                                  |
| Cập nhật `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md` + `P0_EXECUTION_LOG.md` (chỉ doc)                                  | **Agent**                | **Tắt** trừ khi cùng lúc chạy lệnh nặng khác                                                                                                                                                        |
| Triển khai theo checklist trong `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md` (code + test + doc/log theo mẻ)             | **Agent**                | **Nên bật** nếu có `php artisan test` rộng / `pnpm run production` / `graphify update .` chạy lâu; **Tắt** nếu từng bước nhỏ, test ngắn. **Ask** khi chỉ hỏi nội dung Playbook, **không** sửa repo. |

**Một câu nhớ:** _Chỉ hỏi–đọc, không ghi repo_ → **Ask**. _Sửa file / code_ → **Agent**. _Không muốn chat bị “đứng” vì một lệnh terminal lâu_ → **bật Multitask** (thường để lệnh chạy **nền**). Multitask **không** thay Ask khi bạn vẫn cần **ghi** repo.

---

## 3) Agent cha vs Subagent (điều phối)

- **Agent cha:** Phiên bạn đang làm việc; giữ mục tiêu tổng, gộp kết quả.
- **Subagent / luồng con:** Tác vụ **tách** (thường hẹp phạm vi: explore, chuyên môn…) có thể **cũng gọi model + tool** rồi báo cáo về cha.

**Sidebar “Agents” (danh sách phẳng):** mỗi dòng thường là **một Agent / một phiên độc lập**, không phải “subagent” là một loại hàng riêng trên list. Subagent thường là **cơ chế nội bộ** khi có tách việc (có thể không hiển thị cây cha–con trong sidebar).

**Cơ chế cha–con có chỉ khi bật Multitask không?** **Không.** Cha–con là **mô hình điều phối khi có tách tác vụ**; Multitask là **ưu tiên song song / nền / ít chặn UI** — liên quan nhưng không trùng nghĩa 100%.

---

## 4) Hai ví dụ: cha + con (song song vs tuần tự)

### Ví dụ A — Song song / nền (đúng “vibe” multitask)

**Yêu cầu:** “Rà soát `Modules/Warehouse` và chạy test filter Warehouse, tóm tắt kết quả.”

- **Cha:** Giữ ngữ cảnh, có thể trả lời ngắn trong lúc chờ.
- **Con 1 (nền / song song):** Grep + đọc file warehouse → danh sách điểm cần chú ý.
- **Con 2 (nền / song song):** Chạy `php artisan test --filter=…` → pass/fail + log.

**Đặc điểm:** hai việc **cùng lúc**; UI **ít bị chặn** nếu chạy nền tốt.

### Ví dụ B — Tuần tự (vẫn cha–con, không nhấn mạnh song song)

**Yêu cầu:** “Tìm root cause bug X, chỉ sửa khi đã chứng minh.”

- **Cha:** Lên kế hoạch bước 1 → 2 → 3.
- **Con (pha 1):** Thu thập stack + call site → báo cáo.
- **Cha:** Quyết định hướng.
- **Con (pha 2):** Patch nhỏ + một file test → kết quả.
- **Cha:** Giải thích, gộp doc.

**Đặc điểm:** con **xong** mới tới bước tiếp; vẫn là **chia vai** nhưng không nhất thiết “multitask UI”.

---

## 5) Token / chi phí và Multitask

- **Multitask không tự làm tăng bill** nếu chủ yếu là **shell/build/test** nền; phần LLM chủ yếu ở bước **đọc log / quyết định / sửa** sau đó.
- **Dễ tốn tổng usage hơn** khi có **nhiều nhánh LLM song song** (mỗi nhánh mang context + reasoning), hoặc làm **trùng việc** giữa các nhánh.
- **Cài nhiều model trong settings** = nhiều **lựa chọn**; **không** có nghĩa mỗi lần multitask là **tất cả model** cùng chạy. Chi phí phụ thuộc **model nào được gọi**, **bao nhiêu lần**, **context mỗi lần**.

---

## 6) Fork chat vs file trên máy

- **Fork chat:** Nhánh **lịch sử hội thoại** — tách ngữ cảnh chat.
- **File workspace:** Vẫn là file **thật** trên disk; fork **không** tự tạo “bản nháp riêng” trừ khi dùng cơ chế sandbox/cloud riêng (tuỳ sản phẩm).
- Hai fork cùng sửa một file vẫn có rủi ro **ghi đè / conflict** như làm việc Git bình thường.

---

## 7) Tóm tắt một trang

| Khái niệm        | Ghi nhớ ngắn                                                              |
| ---------------- | ------------------------------------------------------------------------- |
| Multitask        | Ưu tiên **song song / nền / ít chặn**; không đồng nhất “luôn có subagent” |
| Subagent         | **Nhánh con khi có tách việc**; có thể tuần tự hoặc song song             |
| Agent mode       | **Quyền ghi** repo; không phải định nghĩa luôn có subagent                |
| “Chat với model” | **Có gọi API LLM** (tính usage); khác với chỉ chạy terminal               |
| Danh sách Agents | Thường là **các phiên agent độc lập**, không phải cây subagent            |

---

## 8) Tham chiếu nội bộ repo (không liên quan Cursor nhưng hay dùng cùng lúc)

- Chỉ mục doc gốc: `FUNC_INDEX.md`
- Cải tiến / P0 / Biomixing MD: `FUNC_IMPROVE/INDEX.md`

---

_Nếu cần đối chiếu chính sách billing/token chính xác theo tài khoản, dùng trang Usage/Billing của Cursor._

**Thói quen công việc cá nhân (audit / gộp MD / browser / SSH…):** [`MY_CURSOR_TASK_PATTERNS_VI.md`](MY_CURSOR_TASK_PATTERNS_VI.md)
