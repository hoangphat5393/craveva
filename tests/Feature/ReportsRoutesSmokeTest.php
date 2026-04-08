<?php

use Illuminate\Support\Facades\Route;

it('registers ERP Reports menu route names', function () {
    $names = [
        'task-report.index',
        'time-log-report.index',
        'time-log-weekly-report.index',
        'finance-report.index',
        'income-expense-report.index',
        'leave-report.leave_quota',
        'attendance-report.index',
        'expense-report.index',
        'lead-report.index',
        'sales-report.index',
        'audit-report.index',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route: {$name}");
    }
});
