@php
    $moduleNotInPackageHint = __('messages.moduleNotInPackage');
    if ($moduleNotInPackageHint === 'messages.moduleNotInPackage') {
        $moduleNotInPackageHint = str_starts_with(strtolower((string) app()->getLocale()), 'vi')
            ? 'Module chưa được gói cấp phép (is_allowed = 0). Thêm developertools vào JSON module_in_package của gói mà company đang dùng (Super Admin → Packages), rồi chạy: php artisan packages:modules activate --module=developertools'
            : 'Not in package (is_allowed = 0). Add developertools to module_in_package for this company\'s plan (Super Admin → Packages), then run: php artisan packages:modules activate --module=developertools';
    }
@endphp
<x-cards.data class="w-100">
    <div class="row">
        @foreach ($modulesData as $setting)
            <div class="col-lg-3 col-md-4 col-6">
                <div class="form-group mb-4">
                    <x-forms.label :fieldId="'module-' . $setting->id" :fieldLabel="__('modules.module.' . $setting->module_name)">
                    </x-forms.label>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" @if ($setting->status == 'active') checked @endif @if ($setting->module_name == 'settings' || (int) $setting->is_allowed !== 1) disabled @endif class="cursor-pointer custom-control-input change-module-setting" id="module-{{ $setting->id }}" data-setting-id="{{ $setting->id }}" data-module-name="{{ $setting->module_name }}"
                            @if ((int) $setting->is_allowed !== 1) title="{{ e($moduleNotInPackageHint) }}" @endif>
                        <label class="custom-control-label @if ((int) $setting->is_allowed === 1) cursor-pointer @endif" for="module-{{ $setting->id }}"></label>
                    </div>
                    @if ((int) $setting->is_allowed !== 1 && $setting->module_name === 'developertools')
                        <small class="form-text text-muted">{{ $moduleNotInPackageHint }}</small>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-cards.data>
