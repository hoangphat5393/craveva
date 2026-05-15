<?php

use App\Helper\Reply;

it('reply redirect payload includes fields used by package axios success handler', function () {
    $payload = Reply::redirect('https://example.test/account/packages', 'messages.updateSuccess');

    expect($payload['status'])->toBe('success')
        ->and($payload['action'])->toBe('redirect')
        ->and($payload['url'])->toBe('https://example.test/account/packages')
        ->and($payload['message'])->not->toBeEmpty();
});
