<?php

declare(strict_types=1);

use App\Helper\Reply;

it('redirect reply uses url and action for ajax consumers', function (): void {
    $payload = Reply::redirect('https://example.test/account/estimates', 'Updated successfully.');

    expect($payload['status'])->toBe('success')
        ->and($payload['action'])->toBe('redirect')
        ->and($payload['url'])->toBe('https://example.test/account/estimates')
        ->and($payload['message'] ?? '')->not->toBe('');
});

it('redirect reply without message omits message key', function (): void {
    $payload = Reply::redirect('https://example.test/list');

    expect($payload)->not->toHaveKey('message')
        ->and($payload['url'])->toBe('https://example.test/list')
        ->and($payload['action'])->toBe('redirect');
});
