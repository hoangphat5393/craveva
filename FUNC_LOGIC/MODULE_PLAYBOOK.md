# Business Logic Module Playbook

Status: Active draft, created 2026-07-04.

## Goal

Tạo một bộ tài liệu logic nghiệp vụ theo từng Laravel module. Mỗi module có một file riêng để sau này audit sâu, sửa logic, viết test hoặc bàn giao cho BA/CTO mà không phải đọc lẫn trong nhiều file cũ.

## Folder Structure

- FUNC_LOGIC/MODULE_PLAYBOOK.md: playbook và nguyên tắc cập nhật.
- FUNC_LOGIC/MODULE_INDEX.md: danh sách module và link từng file.
- FUNC_LOGIC/MODULE_*.md: một file cho mỗi module trong Modules/.

## Method

1. Inventory từ source code: module.json, routes, controllers, entities, services và views.
2. Viết mục đích nghiệp vụ ở mức BA/CTO, nhưng đánh dấu rõ đây là draft nếu chưa xác nhận UI/runtime.
3. Không sửa code khi đang viết tài liệu nghiệp vụ.
4. Khi audit sâu một module, cập nhật file module đó với:
   - trạng thái chính,
   - luồng create/update/delete/status,
   - transaction/master data,
   - module liên quan,
   - URL test/UAT,
   - rủi ro và test cần có.
5. Nếu phát hiện logic sai, tạo issue/plan riêng trước khi sửa code.

## Update Rules

- Không ghi suy đoán như sự thật. Nếu chưa kiểm chứng, ghi ở mục Business Rules To Confirm.
- Dẫn đường dẫn code cụ thể khi xác nhận một rule.
- Với module lớn như Purchase, Warehouse, Production, Pricing, cần đối chiếu thêm các file FUNC_LOGIC cũ vì đã có nhiều audit trước đó.
- Mỗi lần hoàn tất audit sâu một module, đổi Status trong file module từ draft sang audited kèm ngày.

## Priority Recommendation

1. Purchase, Warehouse, Production, Pricing: ảnh hưởng BOM, kho, báo giá, order, invoice.
2. Payroll, Recruit, Performance: ảnh hưởng HR/payroll.
3. LanguagePack, EInvoice, Sms, Webhooks, Zoom: ảnh hưởng integration/notification.
4. Các module còn lại audit theo nhu cầu sử dụng thực tế.


