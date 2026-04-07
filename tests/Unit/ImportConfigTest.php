<?php

it('imports client chunk size is between 1 and 500', function () {
    $size = config('import.client_chunk_size');

    expect($size)->toBeInt()->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(500);
});
