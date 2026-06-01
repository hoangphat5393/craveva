<?php

it('does not use bare Week translation key for datatable titles', function (string $locale) {
    app()->setLocale($locale);

    expect(__('Week'))->toBeArray('lang/{locale}/Week.php makes __("Week") an array; use modules.employees.WeekRange');
    expect(__('modules.employees.WeekRange'))->toBeString();
})->with(['en', 'vi']);
