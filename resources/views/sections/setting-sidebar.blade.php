{{-- SAAS --}}
@if (user()->is_superadmin)
    @if (\Route::has('developertools.index'))
        <a class="nav-item nav-link f-15 @if ($activeSettingMenu == 'developertools') active @endif"
            href="{{ route('developertools.index') }}">
            <i class="fa fa-code mr-2"></i> @lang('Developer Tools')
        </a>
    @endif

    @if (\Route::has('developertools.codemap'))
        <a class="nav-item nav-link f-15 @if ($activeSettingMenu == 'codemap') active @endif"
            href="{{ route('developertools.codemap') }}">
            <i class="fa fa-sitemap mr-2"></i> @lang('CodeMap')
        </a>
    @endif
@else
    <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
@endif

