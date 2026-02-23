<?php

namespace Modules\Pricing\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\User;
use App\Models\ClientDetails;
use Modules\Pricing\Entities\PricingTier;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class ClientTiersDataTable extends BaseDataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a class="dropdown-item openRightModal" href="' . route('pricing.client_tiers.edit', $row->id) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('client_name', function ($row) {
                $client = view('components.client', ['user' => $row]);
                
                $details = '<div class="d-flex flex-column ml-2">';
                if ($row->company_name) {
                    $details .= '<span class="f-12 text-dark-grey">' . $row->company_name . '</span>';
                }
                if ($row->client_code) {
                    $details .= '<span class="f-11 text-light-grey">' . __('pricing::app.customerCode') . ': ' . $row->client_code . '</span>';
                }
                $details .= '</div>';

                return '<div class="d-flex align-items-center">' . $client . $details . '</div>';
            })
            ->addColumn('tier_name', function ($row) {
                if ($row->pricing_tier_name) {
                    return '<span class="badge badge-success" style="background-color: #28a745 !important;">' . $row->pricing_tier_name . '</span>';
                }
                return '--';
            })
            ->rawColumns(['action', 'check', 'client_name', 'tier_name'])
            ->addIndexColumn();
    }

    public function query(User $model)
    {
        $request = $this->request();
        $users = $model->with('clientDetails')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('client_details', 'users.id', '=', 'client_details.user_id')
            ->leftJoin('pricing_tiers', 'client_details.pricing_tier_id', '=', 'pricing_tiers.id')
            ->select('users.id', 'users.name', 'users.email', 'users.image', 'client_details.company_name', 'pricing_tiers.name as pricing_tier_name', 'client_details.pricing_tier_id', 'client_details.client_code')
            ->where('roles.name', 'client');

        if ($request->searchText != '') {
            $users->where(function ($query) use ($request) {
                $query->where('users.name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('users.email', 'like', '%' . $request->searchText . '%')
                    ->orWhere('client_details.company_name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('client_details.client_code', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->tier_id != 'all' && $request->tier_id != '') {
            if ($request->tier_id == 'none') {
                 $users->whereNull('client_details.pricing_tier_id');
            } else {
                $users->where('client_details.pricing_tier_id', $request->tier_id);
            }
        }

        return $users;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('client-tiers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2)
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->stateSave(true)
            ->processing(true)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["client-tiers-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: "[data-toggle=\"tooltip\"]"
                    })
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'width' => '5%'
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('app.client') => ['data' => 'client_name', 'name' => 'users.name', 'title' => __('app.client')],
            __('pricing::app.pricingTier') => ['data' => 'tier_name', 'name' => 'pricing_tiers.name', 'title' => __('pricing::app.pricingTier')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
