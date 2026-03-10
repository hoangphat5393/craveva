{{-- Super Admin: full settings sidebar (Language Settings, etc.); normal user: standard sidebar --}}
@if (user()->is_superadmin)
    <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
@else
    <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
@endif

