<?php

test('homepage root redirects guests', function () {
    $response = $this->get('/');

    $response->assertOk();
});
