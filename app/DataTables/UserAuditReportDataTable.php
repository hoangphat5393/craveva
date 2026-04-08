<?php

namespace App\DataTables;

use App\Models\UserActivity;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;

class UserAuditReportDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('user_name', function ($row) {
                return $row->user ? $row->user->name : '—';
            })
            ->editColumn('activity', function ($row) {
                return e(__($row->activity));
            })
            ->editColumn('created_at', function ($row) {
                if (! $row->created_at) {
                    return '—';
                }

                return Carbon::parse($row->created_at)->timezone($this->company->timezone)->format($this->company->date_format . ' ' . $this->company->time_format);
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->make(true);
    }

    public function query(UserActivity $model)
    {
        $request = $this->request();

        $model = $model->newQuery()->with(['user'])->select('user_activities.*');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            if (! is_null($startDate)) {
                $model->whereDate('user_activities.created_at', '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            if (! is_null($endDate)) {
                $model->whereDate('user_activities.created_at', '<=', $endDate);
            }
        }

        if ($request->employee != 'all' && ! is_null($request->employee) && $request->employee != '') {
            $model->where('user_activities.user_id', (int) $request->employee);
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('audit-report-table', 3)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["audit-report-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getColumns(): array
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('app.date') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.date')],
            __('app.employee') => ['data' => 'user_name', 'name' => 'user.name', 'orderable' => false, 'title' => __('app.employee')],
            __('app.activity') => ['data' => 'activity', 'name' => 'activity', 'orderable' => false, 'title' => __('app.activity')],
        ];
    }
}
