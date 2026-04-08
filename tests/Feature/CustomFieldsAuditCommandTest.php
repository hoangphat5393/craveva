<?php

it('runs custom-fields:audit without exception', function () {
    $this->artisan('custom-fields:audit')->assertSuccessful();
});
