<?php

use Illuminate\Contracts\Console\Kernel;

/**
 * Kiểm tra an toàn trước khi chạy queue worker (chỉ đọc DB / config, không ghi, không xóa queue).
 * php scripts/queue-worker-preflight.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$ok = true;
$default = config('queue.default');
$importBatch = config('queue.import_batch_connection', 'database');
echo "queue.default = {$default}\n";
echo "queue.import_batch_connection = {$importBatch}\n";
if ($importBatch !== 'database') {
    echo "NOTE: Import batches use connection '{$importBatch}'. Run workers accordingly, e.g. php artisan queue:work {$importBatch} --queue=ClientImport,...\n";
} elseif ($default !== 'database') {
    echo "NOTE: Default queue connection is not 'database'. Check config for import batch + general jobs.\n";
}

try {
    DB::connection()->getPdo();
    echo 'DB: connection OK (' . config('database.default') . ")\n";
} catch (Throwable $e) {
    echo 'DB: FAIL — ' . $e->getMessage() . "\n";
    $ok = false;
}

try {
    $n = DB::table('jobs')->count();
    echo "jobs table: readable, rows={$n}\n";
} catch (Throwable $e) {
    echo 'jobs table: FAIL — ' . $e->getMessage() . "\n";
    $ok = false;
}

try {
    $b = DB::table('job_batches')->count();
    echo "job_batches table: readable, rows={$b}\n";
} catch (Throwable $e) {
    echo 'job_batches: FAIL — ' . $e->getMessage() . "\n";
    $ok = false;
}

exit($ok ? 0 : 1);
