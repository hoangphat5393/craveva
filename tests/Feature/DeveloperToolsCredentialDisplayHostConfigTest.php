<?php

it('merges developertools credential_display_host config', function () {
    expect(config()->has('developertools.credential_display_host'))->toBeTrue();
});
