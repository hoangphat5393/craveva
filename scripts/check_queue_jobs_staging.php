<?php

/**
 * Ops: job counts by queue + unfinished batches.
 * Run on server from project root: php scripts/check_queue_jobs_staging.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'queue.default=' . config('queue.default') . "\n";
echo 'batching.table=' . config('queue.batching.table') . "\n\n";

$rows = DB::table('jobs')
    ->selectRaw('queue, COUNT(*) as pending, SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as reserved')
    ->groupBy('queue')
    ->orderByDesc('pending')
    ->get();

echo "jobs table (by queue):\n";
foreach ($rows as $r) {
    echo sprintf("%s\tpending=%d\treserved=%d\n", $r->queue, $r->pending, $r->reserved);
}
echo "\nTotal jobs rows: " . DB::table('jobs')->count() . "\n";

$batches = DB::table('job_batches')
    ->whereNull('finished_at')
    ->orderByDesc('created_at')
    ->limit(5)
    ->get(['id', 'name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'created_at']);

echo "\nRecent unfinished job_batches (max 5):\n";
foreach ($batches as $b) {
    echo sprintf(
        "%s name=%s total=%d pending=%d failed=%d\n",
        $b->id,
        $b->name,
        $b->total_jobs,
        $b->pending_jobs,
        $b->failed_jobs
    );
}
