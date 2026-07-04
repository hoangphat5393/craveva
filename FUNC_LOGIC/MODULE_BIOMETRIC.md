# Biometric Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Biometric
- Alias: biometric
- Provider: Modules\Biometric\Providers\BiometricServiceProvider, Modules\Biometric\Providers\EventServiceProvider
- Source root: Modules/Biometric/

## Business Purpose

Quản lý máy chấm công sinh trắc học, nhân viên đồng bộ và dữ liệu attendance.

## Main Business Flow Draft

- Khai báo thiết bị sinh trắc học.
- Đồng bộ hoặc map nhân viên với thiết bị.
- Nhận attendance/command và đưa vào luồng chấm công.

## Code Evidence

### Routes

- Modules/Biometric/Routes/api.php
- Modules/Biometric/Routes/web.php

### Route Entry Points Snapshot

- Modules/Biometric/Routes/api.php:8 Route::get('/iclock/cdata', [ZKTecoController::class, 'handshake']);
- Modules/Biometric/Routes/api.php:10 Route::get('/iclock/test', [ZKTecoController::class, 'test']);
- Modules/Biometric/Routes/api.php:11 Route::post('/iclock/cdata', [ZKTecoController::class, 'handleAttendanceData']);
- Modules/Biometric/Routes/api.php:12 Route::post('/iclock/devicecmd', [ZKTecoController::class, 'handleDeviceCommand']);
- Modules/Biometric/Routes/api.php:13 Route::get('/iclock/getrequest', [ZKTecoController::class, 'handleGetRequest']);
- Modules/Biometric/Routes/api.php:14 Route::get('/iclock/ping', [ZKTecoController::class, 'handlePing']);
- Modules/Biometric/Routes/web.php:22 Route::post('biometric-devices/change-status', [BiometricDeviceController::class, 'changeStatus'])->name('biometric-devices.change-status');
- Modules/Biometric/Routes/web.php:23 Route::post('biometric-devices/sync-employees', [BiometricDeviceController::class, 'syncEmployees'])->name('biometric-devices.sync-employees');
- Modules/Biometric/Routes/web.php:24 Route::get('biometric-employees/get-employees-to-sync', [BiometricEmployeeController::class, 'getEmployeesToSync'])->name('biometric-employees.get-employees-to-sync');
- Modules/Biometric/Routes/web.php:25 Route::delete('biometric-employees/{id}/remove-from-device', [BiometricEmployeeController::class, 'removeFromDevice'])->name('biometric-employees.remove-from-device');
- Modules/Biometric/Routes/web.php:26 Route::get('biometric-commands', [BiometricDeviceController::class, 'commands'])->name('biometric-devices.commands');
- Modules/Biometric/Routes/web.php:27 Route::get('biometric-employees/get-info/{id}', [BiometricEmployeeController::class, 'getEmployeeInfo'])->name('biometric-employees.get-info');
- Modules/Biometric/Routes/web.php:28 Route::get('get-biometric-attendance', [BiometricAttendanceController::class, 'index'])->name('get-biometric-attendance');
- Modules/Biometric/Routes/web.php:29 Route::resource('biometric-devices', BiometricDeviceController::class);
- Modules/Biometric/Routes/web.php:30 Route::resource('biometric-employees', BiometricEmployeeController::class)->except(['show']);
- Modules/Biometric/Routes/web.php:32 Route::get('biometric-employees/fetch-biometric-data/{id?}', [BiometricEmployeeController::class, 'getEmployeeInfo'])->name('biometric-employees.fetch-biometric-data');
- Modules/Biometric/Routes/web.php:33 Route::get('biometric-employees/fetch-all', [BiometricEmployeeController::class, 'fetchAll'])->name('biometric-employees.fetch-all');

### Controllers

- Modules/Biometric/Http/Controllers/BiometricAttendanceController.php
- Modules/Biometric/Http/Controllers/BiometricController.php
- Modules/Biometric/Http/Controllers/BiometricDeviceController.php
- Modules/Biometric/Http/Controllers/BiometricEmployeeController.php
- Modules/Biometric/Http/Controllers/ZKTecoController.php

### Entities / Models

- Modules/Biometric/Entities/BiometricAttendance.php
- Modules/Biometric/Entities/BiometricCommands.php
- Modules/Biometric/Entities/BiometricDevice.php
- Modules/Biometric/Entities/BiometricEmployee.php
- Modules/Biometric/Entities/BiometricGlobalSetting.php
- Modules/Biometric/Entities/BiometricSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Biometric/Resources/views/attendance/index.blade.php
- Modules/Biometric/Resources/views/commands/index.blade.php
- Modules/Biometric/Resources/views/devices/ajax/create.blade.php
- Modules/Biometric/Resources/views/devices/create.blade.php
- Modules/Biometric/Resources/views/devices/create-url.blade.php
- Modules/Biometric/Resources/views/devices/index.blade.php
- Modules/Biometric/Resources/views/employee/edit.blade.php
- Modules/Biometric/Resources/views/sections/sidebar.blade.php

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
