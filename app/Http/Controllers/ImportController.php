<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    /**
     * Get import progress percentage
     *
     * @param  mixed  $id
     * @return mixed
     */
    public function getImportProgress($name, $id)
    {
        // Đảm bảo request đủ lâu để xử lý nhiều job (web server thường dùng php.ini khác CLI)
        set_time_limit(300);

        // Xử lý nhiều job mỗi lần poll để import 50–100 dòng không bị chậm (tránh phải poll 10–20 lần)
        $execution_jobs = 50;

        Artisan::call('queue:work database --max-jobs=' . $execution_jobs . ' --queue=' . $name . ' --stop-when-empty');

        $batch = Bus::findBatch($id);
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

        return Reply::dataOnly(['progress' => $progress, 'failedJobs' => $failedJobs, 'processedJobs' => $processedJobs, 'pendingJobs' => $pendingJobs, 'totalJobs' => $totalJobs]);
    }

    public function getQueueException($name)
    {
        $batchId = request()->query('batch_id');

        if ($batchId) {
            $batchRecord = DB::table('job_batches')->where('id', $batchId)->first();
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

        foreach ($exceptions as $exception) {
            $exception->exception = '[' . $exception->queue . '] ' . $this->parseExceptionMessage($exception->exception);
        }

        $view = view('import.import_exception', $this->data)->with(['exceptions' => $exceptions])->render();

        return Reply::dataOnly(['view' => $view, 'count' => count($exceptions)]);
    }

    /**
     * Parse exception message. Returns full message with newlines preserved (max 50 lines).
     */
    private function parseExceptionMessage($exceptionTrace)
    {
        $lines = explode("\n", (string) $exceptionTrace);

        return implode("\n", array_slice($lines, 0, 50));
    }
}
