<?php

it('language_settings dedupe migration is non-destructive and delegates to service', function () {
    $path = database_path('migrations/2026_04_08_120000_deduplicate_language_settings_by_language_code.php');

    expect(file_exists($path))->toBeTrue();
    $contents = file_get_contents($path);
    expect($contents)->toBeString();
    expect($contents)->toContain('LanguageSettingsDuplicateMergeService');
    expect($contents)->toContain('ensureUniqueIndexOnLanguageCode');
    expect($contents)->not->toContain('mergeDuplicateLanguageSetting');
});
