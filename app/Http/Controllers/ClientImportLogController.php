<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Helper\Reply;
use Illuminate\Support\Facades\Storage;

class ClientImportLogController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.clientImportLog');
    }

    /**
     * List client import log files for current company (storage: import-logs/clients/{company_id}/*.json).
     */
    public function index()
    {
        abort_403(! in_array('clients', user_modules()) || user()->permission('view_clients') == 'none');

        $companyId = company()->id;
        $path = "import-logs/clients/{$companyId}";
        $files = [];
        if (Storage::disk('local')->exists($path)) {
            $list = Storage::disk('local')->files($path);
            foreach ($list as $file) {
                if (str_ends_with($file, '.json')) {
                    try {
                        $content = Storage::disk('local')->get($file);
                        $data = json_decode($content, true);
                        if (is_array($data) && isset($data['batch_id'])) {
                            $files[] = [
                                'batch_id' => $data['batch_id'],
                                'completed_at' => $data['completed_at'] ?? null,
                                'total_jobs' => $data['total_jobs'] ?? 0,
                                'processed_jobs' => $data['processed_jobs'] ?? 0,
                                'failed_jobs' => $data['failed_jobs'] ?? 0,
                                'user_name' => $data['user_name'] ?? '—',
                            ];
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }
        }
        usort($files, function ($a, $b) {
            $t1 = $a['completed_at'] ?? '';
            $t2 = $b['completed_at'] ?? '';

            return strcmp($t2, $t1);
        });

        $this->logs = $files;

        return view('client-import-log.index', $this->data);
    }

    /**
     * Show a single client import log (JSON body, webhook-style UX).
     */
    public function show(string $batchId)
    {
        abort_403(! in_array('clients', user_modules()) || user()->permission('view_clients') == 'none');

        $companyId = company()->id;
        $path = "import-logs/clients/{$companyId}/{$batchId}.json";
        if (! Storage::disk('local')->exists($path)) {
            abort(404, __('app.clientImportLogNotFound'));
        }
        $content = Storage::disk('local')->get($path);
        $data = json_decode($content, true);
        if (! is_array($data) || (isset($data['company_id']) && (int) $data['company_id'] !== (int) $companyId)) {
            abort(404, __('app.clientImportLogNotFound'));
        }
        $this->log = $data;
        $this->logJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (request()->ajax()) {
            $html = view('client-import-log.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('client-import-log.show', $this->data);
    }
}
