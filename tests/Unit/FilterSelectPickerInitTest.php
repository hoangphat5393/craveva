<?php

it('defines initFilterSelectPickers in custom.js', function () {
    $js = file_get_contents(public_path('js/custom.js'));

    expect($js)
        ->toContain('initFilterSelectPickers')
        ->toContain('data-live-search')
        ->toContain('data-container')
        ->toContain('selectpicker("destroy")');
});
