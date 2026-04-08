<?php

use Illuminate\Support\Facades\Schema;

it('runs developertools:audit when database is reachable', function () {
    try {
        Schema::getConnection()->getPdo();
    } catch (Throwable $e) {
        expect(true)->toBeTrue();

        return;
    }

    $this->artisan('developertools:audit')->assertSuccessful();
});
