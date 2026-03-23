# Đề xuất: Vận hành và mở rộng nền tảng ERP + B2B Laravel quy mô lớn

**Đối tượng:** Lãnh đạo kỹ thuật / Product owner  
**Bối cảnh:** ERP đa công ty, kho, PO/DO, AI trên dữ liệu, import Excel lớn (20k+ dòng)

---

## 1. ĐÁNH GIÁ DỰ ÁN

### Độ phức tạp (nhìn thẳng)

| Khía cạnh              | Mức            | Lý do                                                                              |
| ---------------------- | -------------- | ---------------------------------------------------------------------------------- |
| **Nghiệp vụ**          | Cao            | ERP + B2B: đơn hàng, kho, tài chính, workflow—nhiều quy tắc và trường hợp biên.    |
| **Đa tenant**          | Cao            | Sai `company_id` hoặc scope → lộ dữ liệu giữa tenant hoặc hỏng dữ liệu âm thầm.    |
| **Đồng thời**          | Trung bình–Cao | Giữ chỗ tồn kho, dòng PO/DO, import song song với người dùng online.               |
| **Khối lượng dữ liệu** | Trung bình–Cao | Import 20k dòng gây áp lực PHP, DB, lock, queue—không phải quy mô “CRUD đơn giản”. |
| **Gánh vận hành**      | Cao            | Một người không thể ôm trọn nghiệp vụ + hiệu năng + bảo mật + release.             |

**Kết luận:** Đây là **độ phức tạp sản phẩm trung–lớn**, tương đương hệ core của một công ty sản phẩm nhỏ—**không thể** một mình duy trì codebase lâu dài nếu không có quy trình và con người.

### Các vùng rủi ro chính

**Nhất quán dữ liệu**

- Ghi dở dang khi import dài (timeout, hết RAM, job giữa chừng).
- Trùng lặp phân bổ tồn kho nếu hai luồng không dùng cùng quy tắc (API vs batch vs chỉnh tay).
- **Đa tenant:** quên global scope trên một đường query → hiển thị/cập nhật công ty sai.

**Hiệu năng**

- Truy vấn N+1 trên danh sách (sản phẩm, đơn, tồn kho).
- Excel lớn: xử lý đồng bộ, PhpSpreadsheet tốn RAM, thiếu chunk/queue.
- Thiếu index trên bộ lọc `(company_id, …)` và đường join trong báo cáo.

**Khả năng mở rộng**

- Monolith + module nặng: rủi ro deploy tăng khi team lớn nếu không ranh giới rõ.
- Một DB + worker đồng bộ: đổ import có thể làm nghệt traffic tương tác nếu không tách (queue, worker, giới hạn).

**Bảo mật (đa tenant)**

- Lỗi kiểu IDOR (`/resource/{id}` không kiểm tra công ty).
- URL export/download không xác minh lại tenant.
- AI/chatbot **truy cập DB trực tiếp** → prompt injection + đọc quá rộng = rủi ro nghiêm trọng.

---

## 2. CẤU TRÚC ĐỘI (TỐI THIỂU + LÝ TƯỞNG)

### Tối thiểu để vận hành an toàn (không phải “một anh hùng”)

| #   | Vai trò                                         | FTE      | Trách nhiệm                                            |
| --- | ----------------------------------------------- | -------- | ------------------------------------------------------ |
| 1   | **Tech lead / Senior backend (Laravel)**        | 1.0      | Kiến trúc, code review, bug khó, an toàn tenant/import |
| 2   | **Backend mid**                                 | 1.0      | Tính năng, sửa lỗi, migration, service                 |
| 3   | **QA / Test**                                   | 0.5–1.0  | Hồi quy, kịch bản đa tenant, import/load test          |
| 4   | **DevOps / SRE (có thể bán thời gian lúc đầu)** | 0.25–0.5 | CI/CD, staging, backup, giám sát                       |

**Tối thiểu bền vững:** **3–3.5 FTE** (2 dev + QA bán phần + phần DevOps).  
**Một dev** có thể xây; **không thể** gánh chất lượng + vận hành + bảo mật ở quy mô này lâu dài.

### Lý tưởng (giai đoạn tăng trưởng)

| Vai trò                          | FTE   | Ghi chú                                         |
| -------------------------------- | ----- | ----------------------------------------------- |
| Tech lead                        | 1     | Vẫn code; sở hữu kiến trúc                      |
| Backend                          | 2–3   | Sở hữu theo module (vd. kho vs thanh toán)      |
| Frontend (nếu Blade + JS nặng)   | 0.5–1 | DataTables, UX import, hiệu năng                |
| QA                               | 1     | Tự động + khám phá; ma trận tenant              |
| DevOps                           | 0.5–1 | CI/CD, quan sát, DR                             |
| **Product / BA (bán thời gian)** | 0.25  | Quy tắc PO/DO, tiêu chí nghiệm thu—giảm làm lại |

**Frontend:** Nếu chủ yếu Blade + jQuery/DataTables, **0.5 FTE** có thể đủ; nếu chuyển SPA hoặc dashboard nặng, **1 FTE**.

---

## 3. QUY TRÌNH PHÁT TRIỂN

### Luồng làm việc

- **Kanban** cho sửa lỗi đều + release nhỏ; **Scrum nhẹ** (sprint 2 tuần) khi ship tính năng lớn.
- **Giới hạn WIP** cột “Đang làm” để import/refactor dở không tồn đọng.

### Theo dõi bug

- Một nguồn sự thật: **Jira**, **Linear**, hoặc **Azure DevOps**.
- Mức độ: **S0** lộ tenant / thanh toán / mất dữ liệu → **S3** giao diện.
- Mỗi bug production: ghi **nguyên nhân gốc** + **phạm vi tenant** (tránh lặp lại).

### Quy trình release

1. **Staging** = tập dữ liệu giống production (hoặc bản ẩn danh).
2. **Checklist release:** migration, queue worker, config cache, rà soát route đổi có `company_id`.
3. **Kế hoạch rollback:** migration tương thích ngược khi có thể; feature flag cho thay đổi rủi ro.

### Git

- **Trunk-based** hoặc **GitFlow đơn giản:** `main` + nhánh ngắn `feature/*`, `fix/*`.
- **Bảo vệ `main`:** bắt buộc PR, 1 approval cho thay đổi không tầm thường.
- **Không push thẳng** nhánh production.

### CI/CD

- **CI mỗi PR:** `composer install`, `phpunit`/`pest`, **phân tích tĩnh** (PHPStan/Psalm), **Laravel Pint** (style).
- **Tùy chọn:** `composer audit`, rà dependency.
- **CD:** deploy staging khi merge; production **duyệt tay** hoặc khung giờ cố định.

### Chiến lược test (thực dụng)

- **Unit:** quy tắc nghiệp vụ thuần (tính tồn, bước giá)—nhỏ, nhanh.
- **Feature / HTTP:** đường quan trọng với header/session tenant đúng.
- **Smoke sau deploy:** đăng nhập, đổi công ty một lần, một luồng PO/DO OK, một mẫu import.
- **Load:** không cần hằng ngày—chạy trước release lớn cho import và danh sách nặng.

---

## 4. KIẾN TRÚC KỸ THUẬT

### Cấu trúc Laravel

- **Tầng service ứng dụng** cho luồng không tầm thường: `App\Services\Inventory\`, `App\Services\Purchase\`—controller mỏng.
- **Repository:** dùng khi **giảm trùng lặp** (cùng query ở 5 chỗ)—không bắt buộc mọi nơi.
- **Domain events** (events/listeners Laravel) cho tác dụng phụ: “Xác nhận DO” → giữ/giảm tồn—một luồng quy tắc.
- **Ranh giới module** (`nwidart` hoặc thư mục): ghi rõ **module nào sở hữu** PO vs DO vs kho để tránh phụ thuộc vòng.

### Cơ sở dữ liệu (đa tenant)

- **Quy tắc:** hầu hết bảng nghiệp vụ có `company_id`; index ghép `(company_id, status, …)` cho màn danh sách.
- **Tránh** join xuyên công ty trong báo cáo nếu không có ngữ cảnh super-admin rõ ràng.
- **Soft delete / audit:** cân nhắc `updated_by` hoặc bảng audit cho bảng rủi ro cao (điều chỉnh tồn, giá).

### Tầng API cho AI (quan trọng)

- **Không** để runtime AI chạy SQL tùy ý hoặc Eloquent thô từ câu người dùng.
- **Mẫu:** API **chỉ đọc** với endpoint cố định, vd. `GET /api/internal/ai/summary?company_id=&scope=` trả về **tổng hợp và ID** mà user được phép xem.
- **Phân quyền:** cùng policy với UI; service AI dùng **tài khoản dịch vụ + ngữ cảnh công ty** từ session đã xác thực.
- **Giới hạn tốc độ + chi phí truy vấn** trên endpoint AI; log prompt (đã làm sạch) để phát hiện lạm dụng.

### Excel 20k+ dòng

- **Bất đồng bộ:** job queue, đọc chunk, **bulk insert** hoặc upsert theo lô; tiến độ lưu (DB hoặc cache) cho UX.
- **Idempotency:** khóa job import (hash file + công ty + user) tránh chạy trùng.
- **Queue riêng** `imports` + giới hạn đồng thời để web vẫn responsive.

---

## 5. GỢI Ý CÔNG CỤ

| Lĩnh vực                      | Công cụ (chọn một bộ)                                                                       |
| ----------------------------- | ------------------------------------------------------------------------------------------- |
| **Quản lý dự án / công việc** | Jira, Linear, Azure Boards                                                                  |
| **Tài liệu / ADR**            | Confluence, Notion, GitHub Wiki (ADR ngắn cho tenant + import)                              |
| **Giám sát**                  | **Sentry** (lỗi + hiệu năng Laravel), **Laravel Telescope** (chỉ staging)                   |
| **Log**                       | Tập trung: **CloudWatch / Datadog / ELK**—log JSON có `company_id`, `request_id`, `user_id` |
| **APM / query chậm**          | slow query log DB + **Sentry Performance** hoặc agent APM                                   |
| **Uptime**                    | Pingdom, UptimeRobot, health check LB cloud                                                 |

**Mức tối thiểu:** Sentry + log có cấu trúc + staging giống production + backup test restore định kỳ (vd. hàng quý).

---

## 6. RỦI RO & ƯU TIÊN

### Top 10 rủi ro (loại hệ này)

1. **Truy cập dữ liệu xuyên tenant** (thiếu scope hoặc sai ID).
2. **Hỏng dữ liệu import** (lô dở, SKU trùng, sai công ty).
3. **Sự thật tồn kho** (nhận PO vs tồn vs giữ chỗ không khớp).
4. **AI truy cập DB** (đọc quá nhiều, injection, tuân thủ).
5. **Hiệu năng sụt** (N+1, báo cáo không index, import lớn đồng bộ).
6. **Mù vận hành** (không trace khi “chậm” hoặc “số sai”).
7. **Phụ thuộc một người** (bus factor = 1).
8. **Lệch schema** giữa môi trường (bỏ migration, SQL tay).
9. **Lỗi thanh toán / billing** (nếu tích hợp Stripe, cổng nội địa…).
10. **Nợ bảo mật** (dependency cũ, route admin mở, lạm upload file).

### Làm TRƯỚC (trước khi “scale” tính năng)

1. **Ghi lại mô hình tenant:** một trang: cách set `company_id`, global scope, ngoại lệ (super-admin).
2. **Sentry + correlation request** trên production; cảnh báo khi 5xx tăng đột biến.
3. **Diễn tập backup → restore** (restore lên staging, chạy smoke test).
4. **Ổn định import:** queue, chunk, idempotency, trạng thái hiển thị cho user—**không** 20k dòng sync trong một request HTTP.
5. **Khóa AI sau API** có policy—không SQL thô từ LLM.
6. **Thuê / hợp đồng** thêm senior backend + QA bán thời gian—quy trình đi sau con người.

---

## 7. TÓM TẮT CHO LÃNH ĐẠO

- **Độ phức tạp** đủ cao để **một developer không thể** gánh chất lượng + bảo mật + tốc độ triển khai vô hạn.
- **Đội tối thiểu:** ~**3–3.5 FTE** (2 backend + phần QA + phần DevOps) với phân công rõ.
- **Đặt cược kỹ thuật lớn:** **an toàn tenant**, **pipeline import**, **AI giới hạn qua API**, **quan sát hệ thống**.
- **Ưu tiên đầu tư:** giám sát, backup, kiến trúc import, rào AI—sau đó mới scale tính năng.

---

_Dự thảo sẵn sàng trình ban lãnh đạo; có thể gắn ước lượng effort và roadmap 90 ngày theo từng luồng (ổn định vs tính năng)._

---

_Tài liệu: `docs/CTO_ERP_SCALING_PROPOSAL.md`_
