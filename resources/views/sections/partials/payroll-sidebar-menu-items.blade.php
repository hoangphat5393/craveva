<x-sub-menu-item :link="route('payroll.index')" :text="__('payroll::app.menu.payroll')" />
<x-sub-menu-item :link="route('employee-salary.index')" :text="__('payroll::app.menu.employeeSalary')" :permission="user()->permission('manage_employee_salary') == 'all'" />
<x-sub-menu-item :link="route('payroll-expenses.index')" :text="__('payroll::app.payrollExpenses')" />
<x-sub-menu-item :link="route('overtime-requests.index')" :text="__('payroll::modules.payroll.overtimeRequest')" />
@if (in_array('admin', user_roles()) && Route::has('payroll-reports.index'))
    <x-sub-menu-item :link="route('payroll-reports.index')" :text="__('app.menu.reports')" />
@endif
