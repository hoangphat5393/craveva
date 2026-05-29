@php
    $showEmployees = in_array('employees', user_modules()) && $sidebarUserPermissions['view_employees'] != 5 && $sidebarUserPermissions['view_employees'] != 'none' && isset($sidebarUserPermissions['view_employee_menu']) && $sidebarUserPermissions['view_employee_menu'] == 4;

    $showLeaves = in_array('leaves', user_modules()) && $sidebarUserPermissions['view_leave'] != 5 && $sidebarUserPermissions['view_leave'] != 'none';

    $showShiftRoster = in_array('attendance', user_modules()) && isset($sidebarUserPermissions['view_shift_roster']) && $sidebarUserPermissions['view_shift_roster'] != 5 && $sidebarUserPermissions['view_shift_roster'] != 'none';

    $showAttendance = in_array('attendance', user_modules()) && $sidebarUserPermissions['view_attendance'] != 5 && $sidebarUserPermissions['view_attendance'] != 'none';

    $showHoliday = in_array('holidays', user_modules()) && $sidebarUserPermissions['view_holiday'] != 5 && $sidebarUserPermissions['view_holiday'] != 'none';

    $showDesignation = isset($sidebarUserPermissions['view_designation']) && $sidebarUserPermissions['view_designation'] == 4;
    $showDepartment = isset($sidebarUserPermissions['view_department']) && $sidebarUserPermissions['view_department'] == 4;

    $showAppreciation = isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] != 5;

    $showAwards = isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] == 5 && isset($sidebarUserPermissions['manage_award']) && $sidebarUserPermissions['manage_award'] == 4;

    $viewPerformancePermission = user()->permission('view_performance_module');
    $showPeoplePerformanceGroup = module_enabled('Performance') && in_array(\Modules\Performance\Entities\PerformanceSetting::MODULE_NAME, user_modules()) && $viewPerformancePermission == 'all';

    $showHumanResourcesMenu = $showEmployees || $showLeaves || $showShiftRoster || $showAttendance || $showHoliday || $showDesignation || $showDepartment || $showAppreciation || $showAwards || $showPeoplePerformanceGroup;

    $viewPayrollPermission = user()->permission('view_payroll');
    $showPayrollMenu = in_array(\Modules\Payroll\Entities\PayrollSetting::MODULE_NAME, user_modules()) && $viewPayrollPermission != 'none' && Route::has('payroll.index');

    $peopleIconPath =
        'M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z';

    $payrollIconPath =
        'M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z';
@endphp

@if (!in_array('client', user_roles()) && $showHumanResourcesMenu)
    <x-menu-item icon="people" :text="__('app.menu.humanResources')">
        <x-slot name="iconPath">
            <path d="{{ $peopleIconPath }}" />
        </x-slot>
        <div class="accordionItemContent pb-2">
            @include('sections.partials.human-resources-sidebar-menu-items', ['showPeoplePerformanceGroup' => $showPeoplePerformanceGroup])
        </div>
    </x-menu-item>
@endif

@if (!in_array('client', user_roles()) && $showPayrollMenu)
    <x-menu-item icon="cash-coin" :text="__('app.menu.payrollSidebar')">
        <x-slot name="iconPath">
            <path d="{{ $payrollIconPath }}" />
        </x-slot>
        <div class="accordionItemContent pb-2">
            @include('sections.partials.payroll-sidebar-menu-items')
        </div>
    </x-menu-item>
@endif
