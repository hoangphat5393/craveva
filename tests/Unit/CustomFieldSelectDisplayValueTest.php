<?php

use App\Models\CustomField;

it('resolves numeric index for json array options', function () {
    $json = json_encode(['In Stock', 'Out of Stock']);

    expect(CustomField::resolveSelectFieldDisplayValue($json, 0))->toBe('In Stock');
    expect(CustomField::resolveSelectFieldDisplayValue($json, '0'))->toBe('In Stock');
});

it('resolves case-insensitive label when stored value is a legacy label string', function () {
    $json = json_encode(['In Stock', 'Out of Stock']);

    expect(CustomField::resolveSelectFieldDisplayValue($json, 'in stock'))->toBe('In Stock');
});

it('resolves associative option keys', function () {
    $json = json_encode(['in stock' => 'In Stock', 'out' => 'Out']);

    expect(CustomField::resolveSelectFieldDisplayValue($json, 'in stock'))->toBe('In Stock');
});

it('returns dash for empty stored value', function () {
    expect(CustomField::resolveSelectFieldDisplayValue('["A"]', null))->toBe('--');
    expect(CustomField::resolveSelectFieldDisplayValue('["A"]', ''))->toBe('--');
});

it('returns stored value when options json is empty', function () {
    expect(CustomField::resolveSelectFieldDisplayValue(null, 'legacy'))->toBe('legacy');
});
