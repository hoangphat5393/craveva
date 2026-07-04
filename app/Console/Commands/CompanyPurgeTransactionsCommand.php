<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Company\CompanyTransactionPurgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CompanyPurgeTransactionsCommand extends Command
{
    protected $signature = 'company:purge-transactions
                            {--company-id= : Company ID to purge transactional data for}
                            {--execute : Actually DELETE rows (default is dry-run only)}
                            {--confirm-token= : Required with --execute; value printed at end of dry-run}
                            {--include-boms : Also delete Production BOM master and BOM items}
                            {--include-optional : Include optional modules (not implemented yet)}';

    protected $description = 'Dry-run or purge operational ERP data for one company; BOM master is deleted only with --include-boms.';

    public function handle(CompanyTransactionPurgeService $service): int
    {
        $companyId = (int) $this->option('company-id');

        if ($companyId < 1) {
            $this->error('Required: --company-id={positive integer}');

            return self::FAILURE;
        }

        $company = Company::query()->find($companyId);

        if ($company === null) {
            $this->error("Company id={$companyId} not found.");

            return self::FAILURE;
        }

        $includeBoms = (bool) $this->option('include-boms');
        $token = $this->expectedConfirmToken($companyId, (string) $company->company_name, $includeBoms);
        $execute = (bool) $this->option('execute');

        $this->info('Company: #'.$companyId.' — '.$company->company_name);
        $this->line('Mode: '.($execute ? 'EXECUTE (destructive)' : 'DRY-RUN (count only, no DELETE)'));
        $this->line('Production BOM master: '.($includeBoms ? 'DELETE' : 'KEEP'));
        $this->line('Reference: docs/OPS_COMPANY_TRANSACTION_PURGE.md');
        $this->newLine();

        if ($execute) {
            if (! config('company-purge.allow_execute')) {
                $this->error('Blocked: set COMPANY_PURGE_ALLOW_EXECUTE=true in .env to allow --execute.');
                $this->line('Dry-run is always safe: php artisan company:purge-transactions --company-id='.$companyId);

                return self::FAILURE;
            }

            if ($this->option('confirm-token') !== $token) {
                $this->error('Blocked: --execute requires --confirm-token='.$token);

                return self::FAILURE;
            }

            if (! $this->option('no-interaction') && ! $this->confirm('This will permanently DELETE transactional rows for company #'.$companyId.'. Continue?', false)) {
                $this->warn('Aborted.');

                return self::SUCCESS;
            }
        }

        $results = $execute
            ? $service->execute($companyId, $includeBoms)
            : $service->dryRun($companyId, $includeBoms);

        $total = 0;
        $headers = ['Phase', 'Table', 'Scope', 'Rows', 'Note'];
        $tableRows = [];

        foreach ($results as $row) {
            $note = $row['skipped'] ? ($row['reason'] ?? 'skipped') : '';
            $tableRows[] = [
                $row['phase'],
                $row['table'],
                $row['scope'],
                $row['count'],
                $note,
            ];
            if (! $row['skipped']) {
                $total += $row['count'];
            }
        }

        $this->table($headers, $tableRows);
        $this->newLine();
        $this->info('Total rows '.($execute ? 'deleted' : 'matched').': '.$total);

        if (! $execute) {
            $this->warn('Dry-run only — no data was deleted.');
            $this->line('To execute later:');
            $this->line('  1) Backup DB');
            $this->line('  2) COMPANY_PURGE_ALLOW_EXECUTE=true in .env');
            $includeBomsOption = $includeBoms ? ' --include-boms' : '';
            $this->line('  3) php artisan company:purge-transactions --company-id='.$companyId.$includeBomsOption.' --execute --confirm-token='.$token);
        } else {
            $this->writeLog($companyId, $results, $total);
            $this->info('Done. Run: php artisan cache:clear');
        }

        return self::SUCCESS;
    }

    private function expectedConfirmToken(int $companyId, string $companyName, bool $includeBoms): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $companyName) ?? '');
        $slug = trim($slug, '-');

        return 'PURGE-'.$companyId.'-'.$slug.($includeBoms ? '-WITH-BOMS' : '');
    }

    /**
     * @param  list<array{phase: string, table: string, scope: string, count: int, skipped: bool, reason: ?string}>  $results
     */
    private function writeLog(int $companyId, array $results, int $total): void
    {
        $dir = storage_path('logs');
        $file = $dir.DIRECTORY_SEPARATOR.'company-purge-'.$companyId.'-'.now()->format('Ymd_His').'.log';

        $lines = [
            'company_id='.$companyId,
            'executed_at='.now()->toIso8601String(),
            'total_deleted='.$total,
            '',
        ];

        foreach ($results as $row) {
            $lines[] = sprintf(
                '[%s] %s (%s) => %d %s',
                $row['phase'],
                $row['table'],
                $row['scope'],
                $row['count'],
                $row['skipped'] ? '['.($row['reason'] ?? 'skip').']' : ''
            );
        }

        File::put($file, implode(PHP_EOL, $lines).PHP_EOL);
        $this->line('Log: '.$file);
    }
}
