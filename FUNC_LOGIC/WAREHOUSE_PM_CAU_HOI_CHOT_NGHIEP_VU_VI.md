# Câu hỏi gửi PM — Chốt nghiệp vụ kho & bán hàng (Craveva ↔ ERP / Miaolin)

**Mục đích:** Team kỹ thuật cần PM **chốt rõ nghiệp vụ** để cấu hình và test đúng. Phần kho / bán hàng / đồng bộ ERP **khá phức tạp**; nếu không làm rõ, dễ **trừ tồn sai chỗ** hoặc **trùng với hệ khác**.

**Cách dùng:** Copy nội dung phần dưới gửi PM (email/Slack), hoặc họp 20 phút điền bảng.

---

## A) Ai là “chủ” dữ liệu?

| #   | Câu hỏi                                                                                                                         | PM trả lời (gạch đầu dòng hoặc một câu) |
| --- | ------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| A1  | **Đơn hàng (order)** được tạo **chính** ở đâu: chỉ Craveva, chỉ ERP khác (vd. Dingxin), hay cả hai?                             |                                         |
| A2  | **Hóa đơn bán** được **lập và chốt** chủ yếu ở đâu: Craveva, ERP khác, hay cả hai?                                              |                                         |
| A3  | **Tồn kho “đúng nhất”** để ra quyết định bán hàng: hệ nào là **nguồn sự thật (master)** — Craveva hay ERP khác?                 |                                         |
| A4  | Craveva có cần **tự trừ tồn kho** khi lưu hóa đơn **trong Craveva**, hay chỉ cần **hiển thị tồn sync từ ERP** (ERP đã trừ sẵn)? |                                         |

---

## B) Thời điểm trừ tồn (nếu vẫn trừ trong Craveva)

| #   | Câu hỏi                                                                                                                                                | PM chọn / ghi chú |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------- |
| B1  | Trừ tồn khi **lưu hóa đơn** (không còn nháp) là **đúng quy trình** chưa, hay phải đợi **đã thanh toán** / **đã giao hàng** / **xác nhận sau picking**? |                   |
| B2  | Nếu sau này có bước **“xác nhận xuất kho”** riêng (sau picking): có **bắt buộc** không, hay giai đoạn này **chưa cần**?                                |                   |

---

## C) Chọn kho xuất hàng

| #   | Câu hỏi                                                                                                                                               | PM trả lời |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- |
| C1  | Kho xuất lấy theo **kho mặc định của khách** → **kho mặc định công ty** là **đủ** chưa?                                                               |            |
| C2  | Có cần **chọn kho từng dòng** trên hóa đơn (một đơn nhiều kho) **bắt buộc** không?                                                                    |            |
| C3  | Khi một kho **không đủ tồn**: quy trình chuẩn là **tách nhiều hóa đơn** (như mô tả Dingxin) — Craveva có **bắt buộc** hỗ trợ giống vậy trên UI không? |            |

---

## D) Sửa / hủy / trả hàng

| #   | Câu hỏi                                                                                                                     | PM trả lời |
| --- | --------------------------------------------------------------------------------------------------------------------------- | ---------- |
| D1  | Sau khi hóa đơn đã **chốt** (không sửa được nữa): xử lý sai sót bằng **hủy**, **phiếu trả hàng**, hay **chỉ làm trên ERP**? |            |
| D2  | Nếu hàng **đã giao**: chỉ còn **trả hàng** — luồng này **chỉ trên ERP** hay **cũng phải có** trong Craveva?                 |            |

---

## E) Đồng bộ với ERP khác (nếu có)

| #   | Câu hỏi                                                                                                               | PM trả lời |
| --- | --------------------------------------------------------------------------------------------------------------------- | ---------- |
| E1  | Craveva và ERP khác: **một chiều** (Craveva → ERP) hay **hai chiều** (ERP cập nhật tồn về Craveva)?                   |            |
| E2  | Tần suất / sự kiện sync mong muốn: **real-time**, **theo lô**, **cuối ngày**? (có thể ghi “chưa rõ — cần IT đối tác”) |            |

---

## F) Phạm vi sign-off UAT (Miaolin)

| #   | Câu hỏi                                                                                                                                   | PM chọn                                                  |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| F1  | Lần này sign-off **chỉ kho vận hành** (nhập/xuất tay, chuyển kho, nhập mua…) hay **bắt buộc** luôn cả **bán hàng trừ tồn trong Craveva**? | ☐ Chỉ kho vận hành &emsp; ☐ Bắt buộc cả bán hàng trừ tồn |
| F2  | Ngày mục tiêu có bằng chứng UAT (staging)?                                                                                                |                                                          |

---

## Ghi chú ngắn cho PM (không bắt buộc đọc)

- Trong code Craveva đã có **phiên bản 1** trừ tồn khi lưu hóa đơn (bật bằng cấu hình). Nếu PM chốt **không** trừ trong Craveva mà chỉ **sync từ ERP**, team sẽ điều chỉnh cấu hình / phạm vi cho khớp — **không thể đoán thay PM**.
- Nếu **cả Craveva và ERP** đều trừ tồn cùng một lô hàng mà không có quy ước → rủi ro **tồn âm hoặc lệch số**.

---

**Người soạn gửi:** …  
**Ngày:** …

_Có thể đặt file trong repo: `FUNC_LOGIC/WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md` — chỉnh tên/sign-off trước khi gửi._

---

## Phụ lục — Trả lời PM (Miaolin xác nhận flow Dingxin, align Scope B)

**Nguồn:** PM / Miaolin — align Craveva Scope B với Dingxin (**không** sao chép toàn bộ logic Dingxin trong code, nhưng **map trạng thái** và **tránh double-count**).

### Phụ lục b — Vận hành Miaolin (bổ sung từ team)

**Mô tả:** Khách **Miaolin** dùng **Dingxin** là hệ chính cho nghiệp vụ (hóa đơn, picking, confirm…). **Sáng 6h** có **import vào Craveva** (ERP này); sau đó team **export từ Craveva → import vào Dingxin** của khách.

**Câu hỏi: “Nhiêu đây đã trả lời đủ các câu hỏi A–F chưa?” → Chưa đủ**, nhưng **đã làm rõ thêm** vài ý sau:

| Mục                                                   | Sau khi biết import 6h + export → Dingxin                                                                                                                                                                                                                                                                                                           |
| ----------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **E1 (một/two-way)**                                  | **Một phần:** Có **luồng định kỳ** Craveva → Dingxin (export/import). Chiều **6h import vào Craveva** = có thêm dữ liệu **vào** Craveva (cần làm rõ nguồn: file Miaolin, kho khác, hay tổng hợp). **Hai chiều** theo lịch, **không** nhất thiết real-time.                                                                                          |
| **E2 (tần suất)**                                     | **Một phần:** Có nhịp **6h** cho import; tần suất **export → Dingxin** (hàng ngày? sau khi duyệt?) — **chưa** ghi trong mô tả ngắn, nên vẫn hỏi PM.                                                                                                                                                                                                 |
| **A1 (order ở đâu)**                                  | **Một phần:** Có **khâu xử lý order/dữ liệu trong Craveva** (ít nhất sau import 6h). **Chưa** nói order **sinh ra** chỉ ở Craveva hay copy từ hệ khác trước khi import.                                                                                                                                                                             |
| **A2 (HĐ ở đâu)**                                     | **Vẫn mở:** Export sang Dingxin **có thể** là đơn/dòng hàng để bên đó lập HĐ, hoặc đã là HĐ — **cần một câu PM** xác nhận.                                                                                                                                                                                                                          |
| **A3 / A4 (master tồn & có trừ trong Craveva không)** | **Vẫn quan trọng nhất:** Dingxin là **hệ chính** → **xu hướng** master tồn **sau save/confirm** là Dingxin. Craveva **có nguy cơ double-count** nếu vừa `recordOutbound` vừa Dingxin trừ cùng một lô. **Bắt buộc** chốt với PM: trong Craveva chỉ **reserve / hiển thị** hay cũng **ghi movement xuất** và **khớp** với file import ngược (nếu có). |

**Tóm một dòng:** Thông tin **6h + export → Dingxin** giúp hiểu **pipeline tích hợp**, nhưng **không thay** được câu trả lời đầy đủ cho **A2–A4** và **E2**; cần **một vòng PM** ngắn (hoặc cập nhật vào bảng mục A/E phía trên).

### Phụ lục c — Chỉ SO ở Craveva (không trừ kho) + HĐ ở Dingxin + import sáng mới trừ (giải thích & chốt câu hỏi)

**Mô tình team mô tả:** Ở **Craveva** chỉ tạo **đơn bán (SO)** — **không** ghi nhận trừ tồn. Ở **Dingxin** tạo **SO + hóa đơn bán** (invoice) theo quy trình thực tế. **Buổi sáng**, file dữ liệu từ Dingxin **import vào Craveva** — **tại bước import** hệ thống mới **trừ kho** (hoặc tạo HĐ + movement tương ứng). Cần **luồng chuẩn** từ **SO** (và có thể **PO** mua — file nhập hàng) để Miaolin có **template import** khớp.

**Vì sao “SO không trừ” mà “import mới trừ” vẫn hợp lý?**

- **SO** trong nhiều ERP chỉ là **cam kết / kế hoạch bán** — chưa phải **xuất kho thật**. Không gắn `stock_movements` khi lưu SO → **tồn trong Craveva không đổi**.
- **Dingxin** là nơi **chốt nghiệp vụ bán** (lập HĐ, picking, confirm…). File export Dingxin mang theo **dòng đã chốt** (vd. số HĐ, SKU, số lượng, kho, ngày, trạng thái đủ điều kiện xuất…).
- **Job import** đọc file → **khớp** với SO Craveva (theo mã đơn / dòng) hoặc tạo bản ghi HĐ trong Craveva → **chỉ lúc đó** gọi logic trừ tồn (một lần cho mỗi dòng đủ điều kiện, có **idempotency** để import lại không trừ hai lần).

**Luồng SO / PO “chuẩn để import” nghĩa là gì?**  
Là có **quy ước cột + khóa** (số SO, dòng, SKU, kho, số lượng, số tham chiếu HĐ Dingxin…) để file sáng **map** được vào đơn trong Craveva; **PO** thường dùng cho **mua hàng / nhập kho** — file import mua tương tự nếu Miaolin nhập từ hệ khác.

**Rủi ro cần PM chốt một câu:** Nếu **Dingxin đã trừ tồn kho vật lý** (kho thật quản ở Dingxin) **và** import vào Craveva **cũng trừ** cùng một lần xuất → **trừ hai lần**. Cần xác nhận: Dingxin trừ là **sổ kế toán / kho logic** hay **cùng một kho** với Craveva? Nếu **cùng kho**, thường chỉ **một hệ** post movement, hệ kia **mirror** hoặc chỉ nhận **số tồn sau cùng**.

**Như vậy đã đủ trả lời bảng A–F chưa?**  
**Gần đủ hơn nhiều** cho **A1–A2, A4 (theo hướng SO không trừ, trừ khi import), B1 (thời điểm trừ = sau import / theo dòng file), E (batch một chiều Dingxin → Craveva cho phần “chốt bán”**. **Vẫn cần** PM ghi rõ: **A3** (master tồn hiển thị / quyết định bán), **tránh double với Dingxin**, **C1–C3** (chọn kho trên SO vs trên file), **D1–D2**, **F1–F2**, và **định dạng file / tần suất** (E2).

### Ánh xạ sang bảng câu hỏi phía trên

| Mục                     | Đã trả lời được?                    | Tóm tắt theo PM                                                                                                                                                                                                                                                                                                                                                  |
| ----------------------- | ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **A1–A4** (chủ dữ liệu) | **Nhiều phần** (+ **Phụ lục c**)    | **A1:** SO chủ yếu **Craveva** (không trừ kho). **A2:** HĐ **Dingxin**; Craveva nhận qua **import**. **A4:** Craveva **không** trừ khi chỉ lưu SO; trừ khi **import** áp dụng. **A3** (master tồn): **vẫn cần** một câu — xem rủi ro double-count ở **Phụ lục c**.                                                                                               |
| **A4** cụ thể           | **Đã có hướng** (theo Phụ lục c)    | Nếu đúng mô hình “trừ tại import”: **tắt** trừ kho trên SO; import tạo/cập nhật HĐ hoặc shipment rồi mới movement. **Vẫn** cần chốt với PM: Dingxin có trừ **cùng kho** không — nếu có thì cần quy ước **một nơi trừ** hoặc **mirror**.                                                                                                                          |
| **B1–B2**               | **Một phần / hai kịch bản**         | **Kịch bản import (Phụ lục c):** thời điểm trừ trong Craveva = **khi job import** áp dụng dòng từ Dingxin (batch), **không** phải lúc lưu SO. **Kịch bản Dingxin đầy đủ (trước đó):** Saved vs Confirmed — nếu file import mang **đúng trạng thái**, map tương ứng (reserve vs outbound). **B2** vẫn hỏi PM có cần bước xác nhận xuất riêng trong Craveva không. |
| **C1**                  | **Mở / tinh chỉnh**                 | PM: kho do **sales chọn thủ công** lúc order→invoice; default **kho gần khách**. Khác với rule kỹ thuật v1 hiện tại (client default → company default). **Cần** UI/quy tắc: **ưu tiên kho user chọn** + fallback “closest” nếu PM định nghĩa trong Craveva.                                                                                                      |
| **C2**                  | **Gián tiếp Có**                    | Không bắt buộc **một HĐ nhiều kho**; **tách nhiều hóa đơn** theo kho/batch khi không đủ tồn.                                                                                                                                                                                                                                                                     |
| **C3**                  | **Có**                              | Quy trình chuẩn Dingxin: **tách invoice** — Craveva nên **hỗ trợ** (ít nhất cho phép nhiều HĐ / line rõ kho), PM chưa ghi “bắt buộc UI phase nào”.                                                                                                                                                                                                               |
| **D1**                  | **Một phần**                        | Saved **chưa** Confirmed → **unsave** sửa, reservation điều chỉnh. **Confirmed** → **không sửa**; void/cancel vs return — **Miaolin chưa chốt 100%**, sẽ confirm theo chuẩn Dingxin.                                                                                                                                                                             |
| **D2**                  | **Có**                              | Đã giao → **Sales Return**; implementation: **không double-adjust** khi return/void — bám **thay đổi trạng thái Dingxin**.                                                                                                                                                                                                                                       |
| **E1–E2**               | **Một phần → rõ hơn với Phụ lục c** | **E1:** Chiều **Dingxin → Craveva** (file sáng) để **chốt trừ kho trong Craveva**; có thể thêm chiều Craveva → Dingxin cho SO/template — PM ghi **sơ đồ một/two-way**. **E2:** Có nhịp **sáng**; cần **format file**, **giờ chạy**, xử lý **import lỗi / import lại**.                                                                                           |
| **F1–F2**               | **Chưa**                            | Không nêu trong đoạn PM — giữ checklist UAT / ngày staging như cũ.                                                                                                                                                                                                                                                                                               |

### Khoảng cách so với code Scope B v1 (hiện trạng Craveva)

- **v1 hiện tại:** Một bước — invoice **không draft** → đã gọi **outbound** (`recordOutbound`) tương đương **trừ on-hand ngay**, **chưa** tách **reservation** vs **confirm/shipped**.
- **Theo PM:** Cần **hai lớp**: (1) **save** → chỉ **reserve/available**; (2) **confirm sau picking** → **outbound thật** (movement shipped).  
  → **Bước tiếp theo kỹ thuật:** thiết kế **trạng thái invoice (hoặc shipment) trong Craveva** map Dingxin **Saved / Confirmed**, cộng **StockReservationService** hoặc tương đương cho bước (1), và **chỉ** `recordOutbound` ở bước (2); reversal tương ứng unsave vs return.

### Một câu vẫn nên hỏi lại PM ngắn

1. **Cùng một lần xuất:** Dingxin có **đang trừ tồn kho thật** không? Nếu **có**, Craveva khi import có **chỉ mirror** hay **cũng post movement** — làm sao **không trừ hai lần**?
2. File sáng: mang **một dòng = đủ điều kiện outbound**, hay có cả dòng **chỉ reserved** — cần **map** sang bao nhiêu bước trong Craveva?

---

## Phụ lục — Bản tiếng Anh gửi PM (cùng câu hỏi A–F)

_(Gộp từ `WAREHOUSE_PM_BUSINESS_QUESTIONS_EN.md` — copy email/Slack từ đây.)_

**Purpose:** Engineering needs **clear business decisions** from PM so configuration and testing are correct. Warehouse, sales, and ERP sync are **easy to get wrong**; without alignment we risk **incorrect stock deductions** or **double-counting with another system**.

**How to use:** Copy the tables below to PM (email/Slack), or use a 20-minute working session to fill them in.

### A) System of record (“who owns” the data?)

| #   | Question                                                                                                                                                      | PM response |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| A1  | Where are **sales orders** **primarily created**: Craveva only, another ERP only (e.g. Dingxin), or both?                                                     |             |
| A2  | Where are **sales invoices** **created and finalized**: Craveva, another ERP, or both?                                                                        |             |
| A3  | For **authoritative stock** used to decide whether you can sell: which system is the **source of truth** — Craveva or the other ERP?                          |             |
| A4  | Should Craveva **deduct stock itself** when an invoice is saved **in Craveva**, or only **display stock synced from the other ERP** (which already deducted)? |             |

### B) When to deduct stock (if Craveva still posts movements)

| #   | Question                                                                                                                                    | PM choice / notes |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------- | ----------------- |
| B1  | Is deducting stock on **invoice save** (non-draft) correct, or must we wait until **paid** / **shipped** / **confirmed after picking**?     |                   |
| B2  | If we later add a separate **“confirm shipment / outbound”** step (after picking): is it **required** now, or **not needed** in this phase? |                   |

### C) Choosing the shipping warehouse

| #   | Question                                                                                                                                                                                             | PM response |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| C1  | Is **client default warehouse → company default warehouse** enough for outbound, or not?                                                                                                             |             |
| C2  | Is **per-line warehouse selection** on one invoice (one order, multiple warehouses) **mandatory**?                                                                                                   |             |
| C3  | When one warehouse **does not have enough stock**: the standard process is **split into multiple invoices** (as in Dingxin) — must Craveva **mandatorily** support the same on the UI in this phase? |             |

### D) Edits, voids, returns

| #   | Question                                                                                                                                   | PM response |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------ | ----------- |
| D1  | After an invoice is **finalized** (no longer editable): how do we fix mistakes — **void**, **return note**, or **only in the other ERP**?  |             |
| D2  | If goods are **already shipped**: only **sales return** applies — should that flow exist **only in the other ERP** or **also in Craveva**? |             |

### E) Sync with the other ERP (if applicable)

| #   | Question                                                                                                                     | PM response |
| --- | ---------------------------------------------------------------------------------------------------------------------------- | ----------- |
| E1  | Craveva vs other ERP: **one-way** (Craveva → ERP) or **two-way** (ERP also updates stock back into Craveva)?                 |             |
| E2  | Desired sync **frequency / trigger**: **real-time**, **batch**, **end of day**? (You may answer “TBD — needs IT / partner”.) |             |

### F) UAT sign-off scope (Miaolin)

| #   | Question                                                                                                                                                               | PM choice                                                         |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| F1  | For this sign-off: **operational warehouse only** (manual in/out, transfers, purchase receipts, etc.) or **must include** **sales-driven stock deduction in Craveva**? | ☐ Operational warehouse only ☐ Must include sales stock deduction |
| F2  | Target date for UAT evidence on **staging**?                                                                                                                           |                                                                   |

**Short notes for PM (optional):** Craveva has **v1** stock deduction on invoice save (feature flag). If PM decides Craveva should **not** deduct and only **show synced stock**, engineering will align — **we cannot guess**. If **both** systems deduct the **same** movement without a rule → **negative or inconsistent stock**.

**Prepared by:** … **Date:** …

_Bản alignment Dingxin / appendix chi tiết tiếng Anh dài: đối chiếu **Phụ lục b–c** phía trên (tiếng Việt); nội dung EN đầy đủ từng bảng mapping có trong lịch sử git trước khi gộp file._

---

_Cập nhật phụ lục khi Miaolin chốt void vs return (D1), spec file/sync đầy đủ (E), và khi chốt master tồn / tránh double-count Dingxin ↔ Craveva (A3 + Phụ lục c)._
