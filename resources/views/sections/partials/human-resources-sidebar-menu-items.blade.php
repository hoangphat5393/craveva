@if (in_array('employees', user_modules()) && $sidebarUserPermissions['view_employees'] != 5 && $sidebarUserPermissions['view_employees'] != 'none' && isset($sidebarUserPermissions['view_employee_menu']) && $sidebarUserPermissions['view_employee_menu'] == 4)
    <x-sub-menu-item :link="route('employees.index')" :text="__('app.menu.employees')" />
@endif
@if (in_array('leaves', user_modules()) && $sidebarUserPermissions['view_leave'] != 5 && $sidebarUserPermissions['view_leave'] != 'none')
    <x-sub-menu-item :link="route('leaves.index')" :text="__('app.menu.leaves')" />
@endif
@if (in_array('attendance', user_modules()) && isset($sidebarUserPermissions['view_shift_roster']) && $sidebarUserPermissions['view_shift_roster'] != 5 && $sidebarUserPermissions['view_shift_roster'] != 'none')
    <x-sub-menu-item :link="route('shifts.index')" :text="__('app.menu.shiftRoster')" />
@endif
@if (in_array('attendance', user_modules()) && $sidebarUserPermissions['view_attendance'] != 5 && $sidebarUserPermissions['view_attendance'] != 'none')
    <x-sub-menu-item :link="route('attendances.index')" :text="__('app.menu.attendance')" />
@endif
@if (in_array('holidays', user_modules()) && $sidebarUserPermissions['view_holiday'] != 5 && $sidebarUserPermissions['view_holiday'] != 'none')
    <x-sub-menu-item :link="route('holidays.index')" :text="__('app.menu.holiday')" />
@endif
@if (isset($sidebarUserPermissions['view_designation']) && $sidebarUserPermissions['view_designation'] == 4)
    <x-sub-menu-item :link="route('designations.index')" :text="__('app.menu.designation')" />
@endif
@if (isset($sidebarUserPermissions['view_department']) && $sidebarUserPermissions['view_department'] == 4)
    <x-sub-menu-item :link="route('departments.index')" :text="__('app.menu.department')" />
@endif
@if (isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] != 5)
    <x-sub-menu-item :link="route('appreciations.index')" :text="__('app.menu.appreciation')" />
@endif
@if (isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] == 5 && isset($sidebarUserPermissions['manage_award']) && $sidebarUserPermissions['manage_award'] == 4)
    <x-sub-menu-item :link="route('awards.index')" :text="__('app.menu.appreciation')" />
@endif

@foreach ($cravevaPlugins as $item)
    @includeIf(strtolower($item) . '::sections.hr.sidebar')
@endforeach

@if ($showPeoplePerformanceGroup ?? false)
    <x-sub-menu-item :link="route('performance-dashboard.index')" :text="__('app.menu.performanceDashboard')" />
    <x-sub-menu-item :link="route('objectives.index')" :text="__('app.menu.okrObjectives')" />
    <x-sub-menu-item :link="route('okr-scoring.index')" :text="__('performance::app.okrScoring')" />
    <x-sub-menu-item :link="route('meetings.index')" :text="__('app.menu.meetings')" />
@endif
