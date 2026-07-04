# Phạm vi vai trò — Fullstack ERP + B2B

File này mô tả phạm vi công việc phù hợp với vai trò fullstack trong dự án ERP/B2B, dùng để phân biệt phần dev có thể xử lý trực tiếp và phần cần phối hợp thêm.

## Có thể làm trực tiếp

| Nhóm việc | Nội dung |
| --- | --- |
| Frontend + backend ERP | Xây UI, API, controller, service, validation và nghiệp vụ module. |
| Module nghiệp vụ | Client, product, inventory, sales, purchase, pricing và các luồng liên quan. |
| Database mức ứng dụng | CRUD, query, migration, quan hệ model, kiểm tra dữ liệu. |
| Import dữ liệu | Import Excel/CSV lớn, ví dụ client/product/inventory khoảng 20k dòng. |
| Tích hợp API | Webhook, AI API, LINE/WhatsApp, luồng Chat -> Webhook -> AI -> DB -> Response. |
| Test và debug | Kiểm tra chức năng, debug API, database, integration và lỗi UI/backend. |

## Có thể làm nhưng cần phối hợp

| Nhóm việc | Cần phối hợp với |
| --- | --- |
| Phân tích yêu cầu khách hàng | Business Analyst / Product Owner |
| Thiết kế flow nghiệp vụ sâu | Solution Architect / System Analyst |
| Tối ưu dữ liệu lớn | Data Engineer / DBA nếu vượt mức query ứng dụng |
| Kiểm thử nghiệp vụ chuyên sâu | QA / BA / người vận hành thực tế |

## Không nên tự nhận là phạm vi chính

| Nhóm việc | Ghi chú |
| --- | --- |
| GCP / cloud chuyên sâu | Fullstack có thể thao tác cơ bản, nhưng thiết kế/tối ưu cloud nên có DevOps/Cloud Engineer. |
| Production operations chuyên sâu | Deploy cơ bản được theo runbook; hardening, scaling, incident lớn cần DevOps. |
| Database optimization chuyên sâu | Index/query cơ bản được; tuning sâu cần DBA/Data Engineer. |

## Trọng tâm hiện tại của dự án

- Chuẩn hóa payload API để AI đọc đúng dữ liệu ERP.
- Cải thiện mapping dữ liệu giữa ERP và AI.
- Kiểm tra các trường hợp AI trả lời không khớp dữ liệu DB.
- Duy trì staging/production theo runbook và tránh sửa tay ngoài Git.
