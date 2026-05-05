<?php

it('guest is redirected from purchase product create', function () {
    $response = $this->get('/account/purchase-products/create');

    expect($response->status())->toBeIn([302, 401]);
});
