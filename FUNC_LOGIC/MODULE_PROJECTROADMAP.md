# ProjectRoadmap Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: ProjectRoadmap
- Alias: projectroadmap
- Provider: Modules\ProjectRoadmap\Providers\ProjectRoadmapServiceProvider
- Source root: Modules/ProjectRoadmap/

## Business Purpose

Quản lý cấu hình hoặc màn hình roadmap dự án; cần xác nhận thêm phạm vi thực tế.

## Main Business Flow Draft

- Cấu hình roadmap.
- Hiển thị hoặc quản lý roadmap theo module.
- Cần xác nhận thêm nghiệp vụ thực tế từ UI.

## Code Evidence

### Routes

- Modules/ProjectRoadmap/Routes/web.php

### Route Entry Points Snapshot

- Modules/ProjectRoadmap/Routes/web.php:18 Route::resource('projectroadmap', ProjectRoadmapController::class);

### Controllers

- Modules/ProjectRoadmap/Http/Controllers/ProjectRoadmapController.php

### Entities / Models

- Modules/ProjectRoadmap/Entities/ProjectRoadmapSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/ProjectRoadmap/Resources/views/ajax/overview.blade.php
- Modules/ProjectRoadmap/Resources/views/ajax/tasks.blade.php
- Modules/ProjectRoadmap/Resources/views/components/doughnut-chart.blade.php
- Modules/ProjectRoadmap/Resources/views/components/horizontalbar-chart.blade.php
- Modules/ProjectRoadmap/Resources/views/components/polararea-chart.blade.php
- Modules/ProjectRoadmap/Resources/views/index.blade.php
- Modules/ProjectRoadmap/Resources/views/sections/sidebar.blade.php
- Modules/ProjectRoadmap/Resources/views/sections/work/sidebar.blade.php
- Modules/ProjectRoadmap/Resources/views/show.blade.php
- Modules/ProjectRoadmap/Resources/views/table/members-list.blade.php
- Modules/ProjectRoadmap/Resources/views/table/milestones-list.blade.php

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
