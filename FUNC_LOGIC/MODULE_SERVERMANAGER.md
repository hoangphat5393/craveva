# ServerManager Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: ServerManager
- Alias: servermanager
- Provider: Modules\ServerManager\Providers\ServerManagerServiceProvider, Modules\ServerManager\Providers\EventServiceProvider
- Source root: Modules/ServerManager/

## Business Purpose

Quản lý provider, server hosting, domain, log và cấu hình server.

## Main Business Flow Draft

- Khai báo provider/server/domain.
- Theo dõi hosting và server log.
- Cấu hình server manager global/tenant setting.

## Code Evidence

### Routes

- Modules/ServerManager/Routes/api.php
- Modules/ServerManager/Routes/web.php

### Route Entry Points Snapshot

- Modules/ServerManager/Routes/api.php:10 Route::get('/statistics', [ServerManagerController::class, 'getStatistics']);
- Modules/ServerManager/Routes/api.php:11 Route::get('/activities', [ServerManagerController::class, 'getRecentActivities']);
- Modules/ServerManager/Routes/api.php:14 Route::get('/', [HostingController::class, 'index']);
- Modules/ServerManager/Routes/api.php:15 Route::post('/', [HostingController::class, 'store']);
- Modules/ServerManager/Routes/api.php:16 Route::get('/{id}', [HostingController::class, 'show']);
- Modules/ServerManager/Routes/api.php:17 Route::put('/{id}', [HostingController::class, 'update']);
- Modules/ServerManager/Routes/api.php:18 Route::delete('/{id}', [HostingController::class, 'destroy']);
- Modules/ServerManager/Routes/api.php:22 Route::get('/', [DomainController::class, 'index']);
- Modules/ServerManager/Routes/api.php:23 Route::post('/', [DomainController::class, 'store']);
- Modules/ServerManager/Routes/api.php:24 Route::get('/{id}', [DomainController::class, 'show']);
- Modules/ServerManager/Routes/api.php:25 Route::put('/{id}', [DomainController::class, 'update']);
- Modules/ServerManager/Routes/api.php:26 Route::delete('/{id}', [DomainController::class, 'destroy']);
- Modules/ServerManager/Routes/web.php:27 Route::get('/', [ServerManagerController::class, 'index'])->name('server-manager.index');
- Modules/ServerManager/Routes/web.php:28 Route::get('statistics', [ServerManagerController::class, 'getStatistics'])->name('server-manager.statistics');
- Modules/ServerManager/Routes/web.php:29 Route::get('activities', [ServerManagerController::class, 'getRecentActivities'])->name('server-manager.activities');
- Modules/ServerManager/Routes/web.php:35 Route::get('export-all', [HostingController::class, 'exportAllHostings'])->name('server-manager.hosting.export_all');
- Modules/ServerManager/Routes/web.php:36 Route::post('apply-quick-action', [HostingController::class, 'applyQuickAction'])->name('server-manager.hosting.apply_quick_action');
- Modules/ServerManager/Routes/web.php:37 Route::post('change-status', [HostingController::class, 'changeStatus'])->name('server-manager.hosting.change_status');
- Modules/ServerManager/Routes/web.php:40 Route::resource('hosting', HostingController::class);
- Modules/ServerManager/Routes/web.php:46 Route::get('export-all', [DomainController::class, 'exportAllDomains'])->name('server-manager.domain.export_all');
- Modules/ServerManager/Routes/web.php:47 Route::post('apply-quick-action', [DomainController::class, 'applyQuickAction'])->name('server-manager.domain.apply_quick_action');
- Modules/ServerManager/Routes/web.php:48 Route::post('change-status', [DomainController::class, 'changeStatus'])->name('server-manager.domain.change_status');
- Modules/ServerManager/Routes/web.php:51 Route::get('{id}/dns-details', [DomainController::class, 'getDnsDetails'])->name('server-manager.domain.dns-details');
- Modules/ServerManager/Routes/web.php:52 Route::get('{id}/dns-health', [DomainController::class, 'getDnsHealth'])->name('server-manager.domain.dns-health');
- Modules/ServerManager/Routes/web.php:56 Route::resource('domain', DomainController::class);
- Modules/ServerManager/Routes/web.php:62 Route::get('export-all', [ProviderController::class, 'exportAllProviders'])->name('server-manager.provider.export_all');
- Modules/ServerManager/Routes/web.php:63 Route::get('get-url', [ProviderController::class, 'getProviderUrl'])->name('server-manager.provider.get-url');
- Modules/ServerManager/Routes/web.php:64 Route::post('apply-quick-action', [ProviderController::class, 'applyQuickAction'])->name('server-manager.provider.apply_quick_action');
- Modules/ServerManager/Routes/web.php:65 Route::post('change-status', [ProviderController::class, 'changeStatus'])->name('server-manager.provider.change_status');
- Modules/ServerManager/Routes/web.php:68 Route::resource('provider', ProviderController::class);

### Controllers

- Modules/ServerManager/Http/Controllers/DomainController.php
- Modules/ServerManager/Http/Controllers/HostingController.php
- Modules/ServerManager/Http/Controllers/ProviderController.php
- Modules/ServerManager/Http/Controllers/ServerManagerController.php

### Entities / Models

- Modules/ServerManager/Entities/ServerDomain.php
- Modules/ServerManager/Entities/ServerHosting.php
- Modules/ServerManager/Entities/ServerLog.php
- Modules/ServerManager/Entities/ServerManagerGlobalSetting.php
- Modules/ServerManager/Entities/ServerProvider.php
- Modules/ServerManager/Entities/ServerSetting.php
- Modules/ServerManager/Entities/ServerType.php

### Services

- Modules/ServerManager/Services/DnsLookupService.php
- Modules/ServerManager/Services/DomainService.php
- Modules/ServerManager/Services/HostingService.php
- Modules/ServerManager/Services/ServerManagerServiceProvider.php

### Views Snapshot

- Modules/ServerManager/Resources/views/dashboard/index.blade.php
- Modules/ServerManager/Resources/views/dashboard/recent-activities.blade.php
- Modules/ServerManager/Resources/views/domain/ajax/create.blade.php
- Modules/ServerManager/Resources/views/domain/ajax/edit.blade.php
- Modules/ServerManager/Resources/views/domain/ajax/show.blade.php
- Modules/ServerManager/Resources/views/domain/create.blade.php
- Modules/ServerManager/Resources/views/domain/index.blade.php
- Modules/ServerManager/Resources/views/domain/show.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/activities.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/associated-domains.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/billing.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/create.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/edit.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/hosting-information.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/show.blade.php
- Modules/ServerManager/Resources/views/hosting/ajax/statistics.blade.php
- Modules/ServerManager/Resources/views/hosting/create.blade.php
- Modules/ServerManager/Resources/views/hosting/index.blade.php
- Modules/ServerManager/Resources/views/hosting/show.blade.php
- Modules/ServerManager/Resources/views/provider/ajax/create.blade.php
- Modules/ServerManager/Resources/views/provider/ajax/create_provider.blade.php
- Modules/ServerManager/Resources/views/provider/ajax/edit.blade.php
- Modules/ServerManager/Resources/views/provider/ajax/show.blade.php
- Modules/ServerManager/Resources/views/provider/create.blade.php
- Modules/ServerManager/Resources/views/provider/index.blade.php
- Modules/ServerManager/Resources/views/provider/show.blade.php
- Modules/ServerManager/Resources/views/sections/sidebar.blade.php
- Modules/ServerManager/Resources/views/test-sidebar.blade.php
- Modules/ServerManager/Resources/views/test-translations.blade.php

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
