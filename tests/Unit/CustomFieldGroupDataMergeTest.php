<?php

declare(strict_types=1);

it('customFieldsDataMerge defines defaultContent for each custom field column', function () {
    $src = (string) file_get_contents(app_path('Models/CustomFieldGroup.php'));

    expect($src)->toContain("'defaultContent'");
});
