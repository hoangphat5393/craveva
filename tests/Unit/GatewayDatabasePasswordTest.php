<?php

use Modules\DeveloperTools\Support\GatewayDatabasePassword;

it('generates passwords that satisfy strict mysql-style policies', function () {
    for ($i = 0; $i < 50; $i++) {
        $p = GatewayDatabasePassword::generate(24);
        expect(strlen($p))->toBe(24);
        expect($p)->toMatch('/[a-z]/');
        expect($p)->toMatch('/[A-Z]/');
        expect($p)->toMatch('/[0-9]/');
        expect($p)->toMatch('/[^A-Za-z0-9]/');
    }
});

it('enforces minimum length of 12', function () {
    $p = GatewayDatabasePassword::generate(4);
    expect(strlen($p))->toBe(12);
});
