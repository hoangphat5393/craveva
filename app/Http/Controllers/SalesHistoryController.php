<?php

namespace App\Http\Controllers;

use App\DataTables\SalesHistoryDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Imports\SalesHistoryImport;
use App\Jobs\ImportSalesHistoryStreamJob;
use App\Models\SalesHistory;
use App\Models\User;
use App\Traits\ImportExcel;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class SalesHistoryController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.salesHistory';
    }

    public function index(SalesHistoryDataTable $dataTable)
    {
        abort_403(user()->permission('view_sales_history') != 'all');

        if (! request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);
        }

        $this->clients = User::allClients();

        return $dataTable->render('sales-history.index', $this->data);
    }

    public function importHistory()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.salesHistory');
        abort_403(user()->permission('add_sales_history_import') != 'all');

        $this->view = 'sales-history.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('sales-history.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        abort_403(user()->permission('add_sales_history_import') != 'all');

        $rvalue = $this->importFileProcess($request, SalesHistoryImport::class);

        if ($rvalue == 'abort') {
            return Reply::error(__('messages.abortAction'));
        }

        $this->data['originalImportFilename'] = $request->import_file->getClientOriginalName();
        $view = view('sales-history.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        abort_403(user()->permission('add_sales_history_import') != 'all');

        $salesHistory = SalesHistory::create([
            'company_id' => company()->id,
            'source_filename' => $request->input('original_filename', ''),
            'imported_by' => user()->id,
            'imported_at' => now(),
        ]);

        $columns = array_filter((array) $request->columns, static fn($value) => $value !== null);

        $batch = Bus::batch([
            new ImportSalesHistoryStreamJob(
                (string) $request->file,
                (int) company()->id,
                (int) $salesHistory->id,
                $columns,
                (bool) $request->boolean('has_heading'),
                (bool) $request->boolean('has_skip_footer')
            ),
        ])->onConnection('database')->onQueue('SalesHistoryImport')->name('SalesHistoryImport-streamed')->dispatch();

        $batchId = data_get($batch, 'id');
        if ($batchId) {
            Cache::put('import_metrics_' . $batchId, [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'skipped_missing_required' => 0,
                'invalid_status' => 0,
            ], now()->addHours(12));
        }

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }
}
