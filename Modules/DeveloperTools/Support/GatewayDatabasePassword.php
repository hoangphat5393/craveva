<?php

namespace Modules\DeveloperTools\Support;

/**
 * Builds random passwords that satisfy strict MySQL / cloud password policies
 * (mixed case, digit, special character) — Str::random() is alphanumeric only.
 */
final class GatewayDatabasePassword
{
    /**
     * @param  int  $length  Minimum 12; default 24
     */
    public static function generate(int $length = 24): string
    {
        $length = max(12, $length);

        $lowers = 'abcdefghijklmnopqrstuvwxyz';
        $uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specials = '!@#$%&*-_=+';

        $chars = [
            $lowers[random_int(0, strlen($lowers) - 1)],
            $uppers[random_int(0, strlen($uppers) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $pool = $lowers . $uppers . $digits . $specials;
        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($chars);

        return implode('', $chars);
    }
}
