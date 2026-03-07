<?php

namespace Modules\Webhooks\DataTables;

use App\DataTables\BaseDataTable;
use Carbon\Carbon;
use Modules\Webhooks\Entities\WebhooksLog;
use Yajra\DataTables\Html\Column;

class WebhookLogsDataTable extends BaseDataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', fn ($row) => $this->checkBox($row));

        $datatables->addColumn('name', function ($row) {
            return $row->webhookSettings ? '<a href="'.route('webhooks-log.show', [$row->id]).'" class="text-darkest-grey openRightModal">'.$row->webhookSettings->name.'</a>' : '--';
        });

        $datatables->addColumn('webhook_url', function ($row) {
            $url = $row->webhookSettings?->url ?? '--';
            if ($url === '--') {
                return $url;
            }

            return '<a href="'.e($url).'" target="_blank" rel="noopener" class="text-primary" title="'.e($url).'">'.e($url).'</a>';
        });

        $datatables->addColumn('response_code', function ($row) {
            $responseCodes = [
                200 => ['class' => 'badge-success', 'label' => 'Success'],
                404 => ['class' => 'badge-warning', 'label' => 'Not Found'],
                500 => ['class' => 'badge-danger', 'label' => 'Internal Server Error'],
                // Add more response codes and their corresponding labels and classes as needed
            ];

            if (isset($responseCodes[$row->response_code])) {
                $badge = $responseCodes[$row->response_code];

                return '<span class="badge '.$badge['class'].'">'.$row->response_code.'</span>';
            }

            return '<span class="badge badge-secondary">'.$row->response_code.'</span>'; // Default badge for unknown response codes
        });

        $datatables->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
        });

        $datatables->addColumn('action', function ($row) {
            if (! $row->webhookSettings) {
                return '--';
            }
            $action = '<div class="task_view"><div class="dropdown">
                <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                    id="dropdownMenuLink-'.$row->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-options-vertical icons"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-'.$row->id.'" tabindex="0">
                    <a href="'.route('webhooks-log.show', [$row->id]).'" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>'.__('app.view').'</a>
                    <a class="dropdown-item delete-table-row" href="javascript:;" data-log-id="'.$row->id.'"><i class="fa fa-trash mr-2"></i>'.__('app.delete').'</a>
                </div>
            </div></div>';

            return $action;
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);

        $datatables->setRowId(fn ($row) => 'row-'.$row->id);

        $datatables->rawColumns(['check', 'name', 'webhook_url', 'action', 'response_code', 'created_at']);

        return $datatables;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(WebhooksLog $model)
    {
        $request = $this->request();

        $model = $model->with('webhookSettings')
            ->select('webhooks_logs.*', 'webhooks_settings.name as ws_name', 'webhooks_settings.url as ws_url')
            ->join('webhooks_settings', 'webhooks_settings.id', '=', 'webhooks_logs.webhooks_setting_id')
            ->orderBy('webhooks_logs.created_at', 'desc');

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('webhooks_settings.name', 'like', '%'.request('searchText').'%')
                    ->orWhere('webhooks_logs.response_code', 'like', '%'.request('searchText').'%')
                    ->orWhere('webhooks_settings.url', 'like', '%'.request('searchText').'%');
            });
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('webhooks-log-table', 3)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["webhooks-log-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                }',
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => true, 'title' => '#'],

            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],

            'check' => ['data' => 'check', 'name' => 'check', 'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">', 'orderable' => false, 'searchable' => false, 'exportable' => false],

            __('webhooks::app.webhookName') => ['data' => 'name', 'name' => 'name', 'title' => __('webhooks::app.webhookName'), 'exportable' => false],

            __('webhooks::app.requestUrl') => ['data' => 'webhook_url', 'name' => 'webhook_url', 'title' => __('webhooks::app.requestUrl'), 'orderable' => false, 'searchable' => false, 'className' => 'webhook-url-cell'],

            __('webhooks::app.responseCode') => ['data' => 'response_code', 'name' => 'response_code', 'title' => __('webhooks::app.responseCode'), 'className' => 'response-code-cell'],

            __('webhooks::app.createdAt') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('webhooks::app.createdAt'), 'visible' => true, 'className' => 'recorded-on-cell'],
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->addClass('text-right pr-20'),
        ];

        return array_merge($data, $action);
    }
}
