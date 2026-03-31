<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrnMigrateRollbackCommand extends Command
{
    protected $signature = 'purchase:grn-migrate-rollback
        {--manifest= : Manifest JSON path from purchase:grn-migrate-data execute run}
        {--execute : Execute rollback (default is dry-run)}
        {--force : Required with --execute to avoid accidental run}';

    protected $description = 'Rollback for grn migration by manifest (dry-run by default)';

    public function handle(): int
    {
        if (! Schema::hasTable('grns') || ! Schema::hasTable('grn_items')) {
            $this->error('Required tables not found: grns and/or grn_items.');

            return self::FAILURE;
        }

        $manifestOption = (string) $this->option('manifest');
        if ($manifestOption === '') {
            $this->error('Missing required option: --manifest=<path-to-manifest-json>.');

            return self::FAILURE;
        }

        $manifestPath = $this->resolvePath($manifestOption);
        if (! is_file($manifestPath)) {
            $this->error('Manifest file not found: ' . $manifestPath);

            return self::FAILURE;
        }

        $decoded = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($decoded)) {
            $this->error('Manifest is not valid JSON: ' . $manifestPath);

            return self::FAILURE;
        }

        $headerIds = array_values(array_map('intval', (array) ($decoded['created_header_ids'] ?? [])));
        $itemIds = array_values(array_map('intval', (array) ($decoded['created_item_ids'] ?? [])));

        $summary = [
            'command' => 'purchase:grn-migrate-rollback',
            'mode' => (bool) $this->option('execute') ? 'execute' : 'dry-run',
            'generated_at' => now()->toIso8601String(),
            'manifest_path' => $manifestPath,
            'input' => [
                'header_ids_count' => count($headerIds),
                'item_ids_count' => count($itemIds),
            ],
            'exists_now' => [
                'headers_count' => (int) DB::table('grns')->whereIn('id', $headerIds ?: [0])->count(),
                'items_count' => (int) DB::table('grn_items')->whereIn('id', $itemIds ?: [0])->count(),
            ],
        ];

        if (! $this->option('execute')) {
            $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Dry-run rollback completed. No data was modified.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Execute rollback requires --force.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($headerIds, $itemIds): void {
                if ($itemIds !== []) {
                    DB::table('grn_items')->whereIn('id', $itemIds)->delete();
                }

                if ($headerIds !== []) {
                    DB::table('grns')->whereIn('id', $headerIds)->delete();
                }
            });
        } catch (Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $summary['deleted'] = [
            'headers_count' => count($headerIds),
            'items_count' => count($itemIds),
        ];

        $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Execute rollback completed.');

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
