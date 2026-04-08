<?php

use App\Services\LanguageSettingsDuplicateMergeService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production-safe: does not delete or merge rows.
     * If duplicate language_code rows exist, log a warning and skip — run
     * `php artisan language-settings:dedupe-duplicate-codes --force` after backup.
     * If no duplicates, add UNIQUE(language_code) to prevent recurrence.
     */
    public function up(): void
    {
        if (! Schema::hasTable('language_settings')) {
            return;
        }

        $service = app(LanguageSettingsDuplicateMergeService::class);

        if ($service->hasDuplicateLanguageRows()) {
            $service->logWarningIfDuplicatesSkipMigration();

            return;
        }

        $service->ensureUniqueIndexOnLanguageCode();
    }

    public function down(): void
    {
        if (! Schema::hasTable('language_settings')) {
            return;
        }

        $service = app(LanguageSettingsDuplicateMergeService::class);

        if (! $service->languageCodeUniqueIndexExists()) {
            return;
        }

        Schema::table('language_settings', function (Blueprint $table) {
            $table->dropUnique(['language_code']);
        });
    }
};
