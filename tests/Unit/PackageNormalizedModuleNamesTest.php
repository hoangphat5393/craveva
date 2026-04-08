<?php

use App\Models\SuperAdmin\Package;

it('extracts module names from package json keyed by module id', function () {
    $json = json_encode(['53' => 'clients', '54' => 'Products']);

    expect(Package::normalizedModuleNamesFromPackageJson($json))->toBe(['clients', 'products']);
});

it('returns empty for invalid or empty json', function () {
    expect(Package::normalizedModuleNamesFromPackageJson(null))->toBe([]);
    expect(Package::normalizedModuleNamesFromPackageJson(''))->toBe([]);
    expect(Package::normalizedModuleNamesFromPackageJson('not-json'))->toBe([]);
});
