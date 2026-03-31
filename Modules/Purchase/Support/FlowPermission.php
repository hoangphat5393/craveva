<?php

namespace Modules\Purchase\Support;

class FlowPermission
{
    public static function allowsAlias(string $aliasKey): bool
    {
        $alias = (array) config('purchase.permission_aliases.' . $aliasKey, []);
        $newPermission = (string) ($alias['new'] ?? '');
        $legacyPermission = (string) ($alias['legacy'] ?? '');

        $newAllowed = self::allowsPermission($newPermission);
        if (self::isCutoverEnabled() || $legacyPermission === '') {
            return $newAllowed;
        }

        return $newAllowed || self::allowsPermission($legacyPermission);
    }

    public static function isCutoverEnabled(): bool
    {
        return (bool) config('purchase.do_grn_cutover_enabled', false);
    }

    private static function allowsPermission(string $permissionName): bool
    {
        if ($permissionName === '' || ! function_exists('user') || ! user()) {
            return false;
        }

        $value = user()->permission($permissionName);

        return ! in_array($value, ['none', 5, '5', null, ''], true);
    }
}
