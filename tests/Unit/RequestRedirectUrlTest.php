<?php

declare(strict_types=1);

use App\Support\RequestRedirectUrl;

it('decodes double-encoded absolute redirect urls', function (): void {
    $target = 'https://craveva-staging.test/account/production/boms';
    $encoded = urlencode(urlencode($target));

    expect(RequestRedirectUrl::resolve($encoded, '/fallback'))->toBe($target);
});

it('decodes single-encoded absolute redirect urls', function (): void {
    $target = 'https://craveva-staging.test/account/production/boms';
    $encoded = urlencode($target);

    expect(RequestRedirectUrl::resolve($encoded, '/fallback'))->toBe($target);
});

it('passes through relative paths', function (): void {
    expect(RequestRedirectUrl::resolve('/account/production/boms', '/fallback'))
        ->toBe('/account/production/boms');
});

it('uses fallback when redirect is empty', function (): void {
    expect(RequestRedirectUrl::resolve(null, '/account/production/boms'))
        ->toBe('/account/production/boms');
});
