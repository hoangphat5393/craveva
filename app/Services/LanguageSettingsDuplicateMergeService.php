<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LanguageSettingsDuplicateMergeService
{
    /**
     * Tables where at most one row per language is expected; if the canonical row
     * already has children, rows for the duplicate language id are dropped.
     *
     * @var list<string>
     */
    private const TABLES_SINGLE_CHILD_PER_LANGUAGE = [
        'tr_front_details',
        'sign_up_settings',
        'front_menu_buttons',
    ];

    /**
     * Tables where many rows may share one language_setting_id; FKs are always repointed.
     *
     * @var list<string>
     */
    private const TABLES_MULTI_CHILD_PER_LANGUAGE = [
        'front_features',
        'features',
        'footer_menu',
        'seo_details',
        'front_clients',
        'testimonials',
        'front_faqs',
    ];

    public function hasDuplicateLanguageRows(): bool
    {
        if (! Schema::hasTable('language_settings')) {
            return false;
        }

        return DB::table('language_settings')
            ->select('language_code')
            ->groupBy('language_code')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    /**
     * @return list<array{language_code: string, ids: list<int>}>
     */
    public function duplicateGroups(): array
    {
        if (! Schema::hasTable('language_settings')) {
            return [];
        }

        $codes = DB::table('language_settings')
            ->select('language_code')
            ->groupBy('language_code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('language_code');

        $out = [];
        foreach ($codes as $languageCode) {
            $ids = DB::table('language_settings')
                ->where('language_code', $languageCode)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (count($ids) > 1) {
                $out[] = ['language_code' => (string) $languageCode, 'ids' => $ids];
            }
        }

        return $out;
    }

    /**
     * Merge duplicate language_settings rows (keep lowest id per code), then ensure unique index.
     * Destructive: may delete child rows when both canonical and duplicate have rows in single-child tables.
     */
    public function mergeAllDuplicatesAndEnsureUniqueIndex(): void
    {
        DB::transaction(function (): void {
            $duplicateCodes = DB::table('language_settings')
                ->select('language_code')
                ->groupBy('language_code')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('language_code');

            foreach ($duplicateCodes as $languageCode) {
                $ids = DB::table('language_settings')
                    ->where('language_code', $languageCode)
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                if (count($ids) < 2) {
                    continue;
                }

                $keepId = (int) array_shift($ids);

                foreach ($ids as $dupId) {
                    $this->mergeDuplicateLanguageSetting($keepId, (int) $dupId);
                }
            }
        });

        $this->ensureUniqueIndexOnLanguageCode();
    }

    public function ensureUniqueIndexOnLanguageCode(): void
    {
        if (! Schema::hasTable('language_settings')) {
            return;
        }

        if ($this->languageCodeUniqueIndexExists()) {
            return;
        }

        try {
            Schema::table('language_settings', function (Blueprint $table) {
                $table->unique('language_code');
            });
        } catch (\Throwable $e) {
            if ($this->isDuplicateKeyOrIndexExistsMessage($e->getMessage())) {
                return;
            }

            throw $e;
        }
    }

    private function isDuplicateKeyOrIndexExistsMessage(string $message): bool
    {
        return str_contains($message, 'Duplicate key name')
            || str_contains($message, 'already exists')
            || str_contains($message, 'duplicate key');
    }

    public function languageCodeUniqueIndexExists(): bool
    {
        if (! Schema::hasTable('language_settings')) {
            return false;
        }

        $schemaBuilder = Schema::getConnection()->getSchemaBuilder();
        if (! method_exists($schemaBuilder, 'getIndexes')) {
            return false;
        }

        return collect($schemaBuilder->getIndexes('language_settings'))
            ->contains(fn (array $index): bool => $index['unique'] && in_array('language_code', $index['columns'], true));
    }

    private function mergeDuplicateLanguageSetting(int $keepId, int $dupId): void
    {
        foreach (self::TABLES_SINGLE_CHILD_PER_LANGUAGE as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'language_setting_id')) {
                continue;
            }

            $dupCount = DB::table($table)->where('language_setting_id', $dupId)->count();
            if ($dupCount === 0) {
                continue;
            }

            $keepCount = DB::table($table)->where('language_setting_id', $keepId)->count();
            if ($keepCount > 0) {
                DB::table($table)->where('language_setting_id', $dupId)->delete();
            } else {
                DB::table($table)->where('language_setting_id', $dupId)->update(['language_setting_id' => $keepId]);
            }
        }

        foreach (self::TABLES_MULTI_CHILD_PER_LANGUAGE as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'language_setting_id')) {
                continue;
            }

            DB::table($table)->where('language_setting_id', $dupId)->update(['language_setting_id' => $keepId]);
        }

        DB::table('language_settings')->where('id', $dupId)->delete();
    }

    public function logWarningIfDuplicatesSkipMigration(): void
    {
        Log::warning('language_settings has duplicate language_code rows; migration skipped destructive dedupe. After backup, run: php artisan language-settings:dedupe-duplicate-codes --force');
    }
}
