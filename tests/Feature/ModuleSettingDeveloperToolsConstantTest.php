<?php

use App\Models\ModuleSetting;

it('includes developertools in OTHER_MODULES so new companies get module_settings rows', function () {
    expect(ModuleSetting::OTHER_MODULES)->toContain('developertools');
});
