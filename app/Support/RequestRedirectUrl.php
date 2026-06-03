<?php

declare(strict_types=1);

namespace App\Support;

final class RequestRedirectUrl
{
    /**
     * Resolve redirect targets from modal/query/form input (may be URL-encoded once or twice).
     */
    public static function resolve(?string $value, string $fallback): string
    {
        $candidate = trim((string) ($value ?? ''));
        if ($candidate === '') {
            return $fallback;
        }

        $decoded = self::fullyUrldecode($candidate);
        if ($decoded === '') {
            return $fallback;
        }

        if (str_starts_with($decoded, '/')) {
            return $decoded;
        }

        if (preg_match('#^https?://#i', $decoded)) {
            return $decoded;
        }

        if (str_contains($decoded, '%2F') || str_contains($decoded, '%3A')) {
            return $fallback;
        }

        return $decoded;
    }

    private static function fullyUrldecode(string $value): string
    {
        $decoded = $value;
        for ($i = 0; $i < 3; $i++) {
            $next = urldecode($decoded);
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return trim($decoded);
    }
}
