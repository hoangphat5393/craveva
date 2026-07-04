<?php

it('keeps language code uniqueness in the consolidated baseline and repair command', function () {
    $path = database_path('migrations/2000_01_01_000171_create_language_settings_baseline.php');

    expect(file_exists($path))->toBeTrue();
    $contents = file_get_contents($path);
    expect($contents)->toBeString();
    expect($contents)->toContain('language_settings_language_code_unique');

    $command = file_get_contents(app_path('Console/Commands/LanguageSettingsDedupeDuplicateCodesCommand.php'));
    expect($command)->toBeString();
    expect($command)->toContain('LanguageSettingsDuplicateMergeService');
    expect($command)->toContain('ensureUniqueIndexOnLanguageCode');
});
