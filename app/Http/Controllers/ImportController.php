<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    /**
     * Queue names used by ImportExcel / module imports (short class names). Blocks arbitrary queue:work targets.
     */
    private const ALLOWED_IMPORT_QUEUE_NAMES = [
        'ClientImport',
        'ProductImport',
        'EmployeeImport',
        'ProjectImport',
        'DealImport',
        'LeadImport',
        'ExpenseImport',
        'AttendanceImport',
        'JobApplicationImport',
        'ClientProductPricingImport',
        'PricingTierItemsImport',
        'WarehouseImport',
        'InventoryImport',
    ];

    /**
     * Get import progress percentage
     *
     * @param  mixed  $id
     * @return mixed
     */
    public function getImportProgress($name, $id)
    {
        $this->assertAllowedImportQueueName($name);

        // Staging/production: do NOT run queue:work inside HTTP — nginx/php-fpm often times out (60s),
        // breaking JSON polling (empty progress). Use Supervisor: `php artisan queue:work database --queue=ClientImport`.
        // Local dev: default runs worker per poll unless IMPORT_PROGRESS_RUN_QUEUE_WORKER=false.
        if ($this->shouldRunQueueWorkerDuringImportProgressPoll()) {
            set_time_limit(300);
            $execution_jobs = 50;
            Artisan::call('queue:work database --max-jobs=' . $execution_jobs . ' --queue=' . $name . ' --stop-when-empty');
        }

        $batch = Bus::findBatch($id);
        $this->assertBatchBelongsToImportQueue($batch, $name);
        $progress = 0;
        $failedJobs = 0;
        $processedJobs = 0;
        $pendingJobs = 0;
        $totalJobs = 0;

        if ($batch) {
            $failedJobs = $batch->failedJobs;
            $pendingJobs = $batch->pendingJobs;
            $totalJobs = $batch->totalJobs;
            $processedJobs = $batch->processedJobs();

            $progress = $totalJobs > 0 ? round((($processedJobs + $failedJobs) / $totalJobs) * 100, 2) : 0;
        }

        $metrics = Cache::get('import_metrics_' . $id);

        return Reply::dataOnly([
            'progress' => $progress,
            'failedJobs' => $failedJobs,
            'processedJobs' => $processedJobs,
            'pendingJobs' => $pendingJobs,
            'totalJobs' => $totalJobs,
            'metrics' => $metrics,
        ]);
    }

    public function getQueueException($name)
    {
        $this->assertAllowedImportQueueName($name);

        $batchId = request()->query('batch_id');

        if ($batchId) {
            $batchRecord = DB::table('job_batches')->where('id', $batchId)->first();
            if ($batchRecord && ! $this->batchRecordNameMatchesQueue($batchRecord->name ?? '', $name)) {
                abort(403);
            }
            $failedIds = $batchRecord && ! empty($batchRecord->failed_job_ids)
                ? json_decode($batchRecord->failed_job_ids, true) ?? []
                : [];
            $exceptions = $failedIds !== []
                ? DB::table('failed_jobs')->whereIn('uuid', $failedIds)->orderBy('failed_at', 'desc')->get()
                : collect();
        } else {
            $exceptions = DB::table('failed_jobs')
                ->where('queue', $name)
                ->orderBy('failed_at', 'desc')
                ->limit(50)
                ->get();
        }

        $failedRows = [];
        foreach ($exceptions as $exception) {
            $raw = $this->parseExceptionMessage($exception->exception);
            $exception->exception = '[' . $exception->queue . '] ' . $raw;
            foreach ($this->parseFailedRowsFromMessage($raw) as $item) {
                $failedRows[] = $item;
            }
        }
        $failedRows = $this->sortAndDedupeFailedRows($failedRows);

        $summary = null;
        if ($batchId && $batchRecord) {
            $totalJobs = (int) ($batchRecord->total_jobs ?? 0);
            $failedJobsCount = (int) ($batchRecord->failed_jobs ?? 0);
            $processedJobs = $totalJobs - $failedJobsCount;
            $summary = [
                'total_jobs' => $totalJobs,
                'failed_jobs' => $failedJobsCount,
                'processed_jobs' => $processedJobs,
            ];
        }

        // Phương án E: persist client import log to file for later review (UX like webhook log)
        if ($batchId && $batchRecord && $name === 'ClientImport') {
            $this->writeClientImportLogFile($batchId, $batchRecord, $failedRows, $summary);
        }

        $view = view('import.import_exception', $this->data)->with([
            'exceptions' => $exceptions,
            'failedRows' => $failedRows,
            'summary' => $summary,
        ])->render();

        return Reply::dataOnly([
            'view' => $view,
            'count' => count($exceptions),
            'failed_rows' => $failedRows,
            'summary' => $summary,
        ]);
    }

    /**
     * Write client import result to JSON file (storage/app/import-logs/clients/{company_id}/{batch_id}.json).
     * Allows viewing import history in UI similar to webhook log.
     */
    private function writeClientImportLogFile(string $batchId, object $batchRecord, array $failedRows, ?array $summary): void
    {
        $companyId = company()?->id;
        $user = user();
        if (! $companyId || ! $user) {
            return;
        }
        $batchOptions = $batchRecord->options ? (json_decode($batchRecord->options, true) ?? []) : [];
        $originalFilename = $batchOptions['original_filename'] ?? null;
        $totalJobs = (int) ($batchRecord->total_jobs ?? 0);
        $failedJobsCount = (int) ($batchRecord->failed_jobs ?? 0);
        $processedJobs = $totalJobs - $failedJobsCount;
        $payload = [
            'batch_id' => $batchId,
            'company_id' => $companyId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => 'client',
            'original_filename' => $originalFilename,
            'total_jobs' => $totalJobs,
            'processed_jobs' => $processedJobs,
            'failed_jobs' => $failedJobsCount,
            'failed_rows' => $failedRows,
            'summary' => $summary ?? [
                'total_jobs' => $totalJobs,
                'failed_jobs' => $failedJobsCount,
                'processed_jobs' => $processedJobs,
            ],
            'completed_at' => now()->toIso8601String(),
        ];
        $dir = sprintf('import-logs/clients/%s', $companyId);
        $path = $dir . '/' . $batchId . '.json';
        try {
            if (! Storage::disk('local')->exists($dir)) {
                Storage::disk('local')->makeDirectory($dir);
            }
            Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Parse exception message. Returns full message with newlines preserved (max 50 lines).
     */
    private function parseExceptionMessage($exceptionTrace)
    {
        $lines = explode("\n", (string) $exceptionTrace);

        return implode("\n", array_slice($lines, 0, 50));
    }

    /**
     * Extract "Row N: message" pairs from exception text (e.g. from ImportClientChunkJob::fail()).
     *
     * @return array<int, array{row: int, message: string}>
     */
    private function parseFailedRowsFromMessage(string $text): array
    {
        $out = [];
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^Row\s+(\d+):\s*(.+)$/i', $line, $m)) {
                $out[] = ['row' => (int) $m[1], 'message' => trim($m[2])];
            }
        }

        return $out;
    }

    /**
     * Sort by row number and remove duplicate rows (keep first message).
     *
     * @param  array<int, array{row: int, message: string}>  $rows
     * @return array<int, array{row: int, message: string}>
     */
    private function sortAndDedupeFailedRows(array $rows): array
    {
        $byRow = [];
        foreach ($rows as $r) {
            if (! isset($byRow[$r['row']])) {
                $byRow[$r['row']] = $r;
            }
        }
        ksort($byRow, SORT_NUMERIC);

        return array_values($byRow);
    }

    private function assertAllowedImportQueueName(string $name): void
    {
        if (! in_array($name, self::ALLOWED_IMPORT_QUEUE_NAMES, true)) {
            abort(403);
        }
    }

    /**
     * Legacy: poll endpoint used to run queue:work so imports worked without a daemon. That breaks behind
     * reverse proxies with short timeouts. Default: only on APP_ENV=local when env is unset.
     */
    private function shouldRunQueueWorkerDuringImportProgressPoll(): bool
    {
        $configured = config('app.import_progress_run_queue_worker');

        if ($configured === null || $configured === '') {
            return app()->environment('local');
        }

        return filter_var($configured, FILTER_VALIDATE_BOOLEAN);
    }

    private function assertBatchBelongsToImportQueue($batch, string $queueName): void
    {
        if (! $batch) {
            return;
        }
        $batchName = (string) ($batch->name ?? '');
        if ($batchName !== '' && ! $this->batchRecordNameMatchesQueue($batchName, $queueName)) {
            abort(403);
        }
    }

    /**
     * Batches are named like "ClientImport" or "ClientImport-chunked".
     */
    private function batchRecordNameMatchesQueue(string $batchName, string $queueName): bool
    {
        return $batchName === $queueName
            || str_starts_with($batchName, $queueName . '-')
            || str_starts_with($batchName, $queueName . '_');
    }
}
