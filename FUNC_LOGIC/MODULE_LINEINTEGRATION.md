# LineIntegration Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: LineIntegration
- Alias: lineintegration
- Provider: Modules\LineIntegration\Providers\LineIntegrationServiceProvider
- Source root: Modules/LineIntegration/

## Business Purpose

Tích hợp LINE; cần xác nhận thêm luồng nghiệp vụ vì module hiện có ít entity.

## Main Business Flow Draft

- Nhận hoặc gửi dữ liệu qua LINE integration.
- Controller xử lý entrypoint tích hợp.
- Cần xác nhận lại case nghiệp vụ chính.

## Code Evidence

### Routes

- Modules/LineIntegration/Routes/api.php
- Modules/LineIntegration/Routes/web.php

### Route Entry Points Snapshot

- Modules/LineIntegration/Routes/api.php:18 Route::get('lineintegration', fn (Request $request) => $request->user())->name('lineintegration');
- Modules/LineIntegration/Routes/web.php:18 Route::resource('lineintegration', LineIntegrationController::class)->names('lineintegration');

### Controllers

- Modules/LineIntegration/Http/Controllers/LineIntegrationController.php

### Entities / Models

- Chưa thấy entity/model riêng trong module.

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/LineIntegration/Resources/views/index.blade.php
- Modules/LineIntegration/Resources/views/layouts/master.blade.php

## Business Rules To Confirm

- Những trạng thái chính của từng object trong module là gì.
- Object nào là master data, object nào là transaction data.
- Có cần ràng buộc company/tenant, role, permission hoặc approval riêng không.
- Có phát sinh dữ liệu kế toán, kho, invoice, payroll hoặc notification qua module khác không.
- Xóa/sửa record trong module này có ảnh hưởng module nào khác không.

## Integration Points To Audit

- Controllers gọi service/helper/model ngoài module.
- Routes hoặc menu trong core app trỏ vào module này.
- Language keys trong Modules/LanguagePack hoặc lang.
- Tests hiện có liên quan module này.
- Seed/migration và permission/module setting liên quan.

## Related Existing Docs

- Chưa map tài liệu liên quan.

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
