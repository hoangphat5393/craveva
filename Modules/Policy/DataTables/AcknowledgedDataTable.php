<?php

namespace Modules\Policy\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Policy\Entities\PolicyEmployeeAcknowledged;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AcknowledgedDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    private $policyId;

    private $viewPermission;

    public function __construct($id)
    {
        parent::__construct();
        $this->policyId = $id;
        $this->viewPermission = user()->permission('view_acknowledged');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-'.$row->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-'.$row->id.'" tabindex="0">';
                $action .= '<a href="'.route('policy.download', [$this->policyId, $row->user_id]).'" class="dropdown-item"><i class="mr-2 fa fa-download"></i>'.__('app.download').'</a>';
                $action .= '</div>
                        </div>
                        </div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                return view('components.employee', ['user' => $row->users])->render();
            })
            ->rawColumns(array_merge(['employee_name', 'action']))
            ->editColumn('acknowledged_on', function ($row) {
                return $row->acknowledged_on ? $row->acknowledged_on->timezone(company()->timezone)->format(company()->date_format.' '.company()->time_format) : '--';
            })
            ->setRowId('id')
            ->addIndexColumn();
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PolicyEmployeeAcknowledged $model)
    {
        $model = $model->with('users', 'policy')->where('policy_id', $this->policyId);

        if ($this->viewPermission == 'owned') {
            $model = $model->where('user_id', user()->id);
        }

        return $model;

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setBuilder('acknowledged-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["acknowledged-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();
                }',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns()
    {
        $data = [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('modules.employees.employeeName') => ['data' => 'employee_name', 'name' => 'user_id', 'exportable' => false, 'title' => __('modules.employees.employeeName')],
            __('policy::app.acknowledgedOn') => ['data' => 'acknowledged_on', 'name' => 'acknowledged_on', 'exportable' => false, 'title' => __('policy::app.acknowledgedOn')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];

        return $data;
    }
}
