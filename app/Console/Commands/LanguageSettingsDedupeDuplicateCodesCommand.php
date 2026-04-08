<?php

namespace App\Console\Commands;

use App\Services\LanguageSettingsDuplicateMergeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class LanguageSettingsDedupeDuplicateCodesCommand extends Command
{
    protected $signature = 'language-settings:dedupe-duplicate-codes {--force : Merge duplicates and add UNIQUE(language_code). Requires backup first.}';

    protected $description = 'Report or fix duplicate language_settings rows (same language_code). Safe default: list only; use --force after backup.';

    public function handle(LanguageSettingsDuplicateMergeService $service): int
    {
        if (! Schema::hasTable('language_settings')) {
            $this->warn('Table language_settings missing.');

            return self::SUCCESS;
        }

        $groups = $service->duplicateGroups();
        if ($groups === []) {
            $this->info('No duplicate language_code rows.');
            $service->ensureUniqueIndexOnLanguageCode();
            $this->info('UNIQUE(language_code) ensured if it was missing.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($groups as $g) {
            $rows[] = [$g['language_code'], implode(', ', array_map('strval', $g['ids']))];
        }
        $this->table(['language_code', 'language_setting_ids'], $rows);

        if (! $this->option('force')) {
            $this->warn('Dry run only. Back up the database, then run: php artisan language-settings:dedupe-duplicate-codes --force');

            return self::SUCCESS;
        }

        $service->mergeAllDuplicatesAndEnsureUniqueIndex();
        $this->info('Merged duplicate language_settings rows and ensured UNIQUE(language_code).');

        return self::SUCCESS;
    }
}
