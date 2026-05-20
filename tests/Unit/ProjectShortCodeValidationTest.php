<?php

use Illuminate\Support\Facades\Validator;

it('rejects project short code containing sql metacharacters', function () {
    $validator = Validator::make(
        ['project_code' => "'; DROP TABLE tasks;--"],
        ['project_code' => 'max:50|regex:/^[A-Za-z0-9_\-]+$/']
    );

    expect($validator->fails())->toBeTrue();
});

it('accepts safe alphanumeric project short code', function () {
    $validator = Validator::make(
        ['project_code' => 'PRJ-2026_A1'],
        ['project_code' => 'max:50|regex:/^[A-Za-z0-9_\-]+$/']
    );

    expect($validator->fails())->toBeFalse();
});
