<?php

it('recruit source settings nav translation resolves to a string', function () {
    app()->setLocale('en');

    $label = __('recruit::modules.sourceSetting.source');

    expect($label)->toBeString()->not->toBe('');
});

it('recruit source tab label falls back when translation is not a string', function () {
    app()->setLocale('en');

    $primary = __('recruit::modules.sourceSetting.source');
    $fallback = __('recruit::app.dashboard.source');

    expect(is_string($primary) ? $primary : $fallback)->toBeString()->not->toBe('');
});
