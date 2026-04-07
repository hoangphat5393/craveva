<?php

it('recruit source settings nav translation resolves to a string', function () {
    app()->setLocale('en');

    $label = __('recruit::modules.sourceSetting.source');

    expect($label)->toBeString()->not->toBe('');
});
