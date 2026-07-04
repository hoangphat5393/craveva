<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\ClientDetails;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientsDataTable extends BaseDataTable
{
    private $viewClientPermission;

    private $editClientPermission;

    private $deleteClientPermission;

    private $viewInvoicePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewClientPermission = user()->permission('view_clients');
        $this->editClientPermission = user()->permission('edit_clients');
        $this->deleteClientPermission = user()->permission('delete_clients');
        $this->viewInvoicePermission = user()->permission('view_invoices');
    }

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {

        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('check', fn ($row) => $this->checkBox($row));
        $datatables->addColumn('action', function ($row) {

            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-'.$row->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-'.$row->id.'" tabindex="0">';

            $action .= '<a href="'.route('clients.show', [$row->id]).'" class="dropdown-item"><i class="fa fa-eye mr-2"></i>'.__('app.view').'</a>';

            if (in_array('admin', user_roles()) && ! $row->admin_approval) {
                $action .= '<a href="javascript:;" class="dropdown-item verify-user" data-user-id="'.$row->id.'"><i class="fa fa-check mr-2"></i>'.__('app.approve').'</a>';
            }

            if ($this->editClientPermission == 'all' || ($this->editClientPermission == 'added' && user()->id == $row->added_by) || ($this->editClientPermission == 'both' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item openRightModal" href="'.route('clients.edit', [$row->id]).'">
                                <i class="fa fa-edit mr-2"></i>
                                '.trans('app.edit').'
                            </a>';
            }

            if ($this->deleteClientPermission == 'all' || ($this->deleteClientPermission == 'added' && user()->id == $row->added_by) || ($this->deleteClientPermission == 'both' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="'.$row->id.'">
                                <i class="fa fa-trash mr-2"></i>
                                '.trans('app.delete').'
                            </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });
        $datatables->addColumn('client_code', fn ($row) => $row->client_code ?? '--');
        $datatables->addColumn('client_name', function ($row) {
            if (! $row->salutation) {
                return '--';
            }

            return $row->salutation->label();
        });
        $datatables->addColumn('mobile', function ($row) {
            if (trim((string) ($row->mobile ?? '')) === '') {
                return '--';
            }
            $prefix = ! is_null($row->country_phonecode) && $row->country_phonecode !== '' ? '+'.$row->country_phonecode.' ' : '';

            return $prefix.$row->mobile;
        });
        $datatables->addColumn('category_name', function ($row) {
            return ! is_null($row->clientDetails->category_id) ? $row->cat_name : '--';
        });
        $datatables->addColumn('added_by', fn ($row) => optional($row->clientDetails)->addedBy ? $row->clientDetails->addedBy->name : '--');
        $datatables->addColumn('payment_terms', fn ($row) => $row->payment_terms ?? '--');
        $datatables->addColumn('customer_grade', fn ($row) => $row->customer_grade ?? '--');
        $datatables->addColumn('pricing_tier_name', fn ($row) => $row->pricing_tier_name ?? '--');
        $datatables->addColumn('contract_pricing_active', function ($row) {
            if ((bool) $row->contract_pricing_active) {
                return '<span class="badge badge-success">'.__('app.yes').'</span>';
            }

            return '<span class="badge badge-secondary">'.__('app.no').'</span>';
        });
        $datatables->addColumn('outstanding_balance', function ($row) {
            if ($this->viewInvoicePermission === 'none') {
                return '--';
            }

            $amount = (float) ($row->outstanding_balance ?? 0);

            return $amount > 0 ? currency_format($amount, $this->company->currency_id) : '--';
        });
        $datatables->addColumn('channel_type', fn ($row) => $row->channel_type ?? '--');
        $datatables->addColumn('business_type', fn ($row) => $row->business_type ?? '--');
        $datatables->addColumn('business_closure_date', function ($row) {
            $d = $row->business_closure_date ?? null;
            if ($d === null || $d === '') {
                return '--';
            }
            try {
                return Carbon::parse($d)->translatedFormat($this->company->date_format);
            } catch (\Throwable $e) {
                return '--';
            }
        });
        $datatables->editColumn('name', fn ($row) => view('components.client', ['user' => $row]));
        $datatables->editColumn('id', fn ($row) => $row->clientDetails?->id);
        $datatables->editColumn('created_at', fn ($row) => Carbon::parse($row->created_at)->translatedFormat($this->company->date_format));
        $datatables->editColumn('status', function ($row) {
            if ($this->editClientPermission == 'all' || ($this->editClientPermission == 'added' && user()->id == $row->clientDetails?->added_by) || ($this->editClientPermission == 'both' && user()->id == $row->clientDetails?->added_by)) {
                $status = '<select class="form-control select-picker change-client-status" data-size="4" data-client-id="'.$row->id.'">';
                $status .= '<option '.($row->status == 'active' ? 'selected' : '').' value="active" data-content="<i class=\'fa fa-circle mr-2 text-light-green\'></i> '.__('app.active').'">'.__('app.active').'</option>';
                $status .= '<option '.($row->status == 'deactive' ? 'selected' : '').' value="deactive" data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> '.__('app.inactive').'">'.__('app.inactive').'</option>';
                $status .= '</select>';

                return $status;
            }

            return $this->clientStatusBadge($row->status);
        });
        $datatables->smart(false);
        $datatables->setRowId(fn ($row) => 'row-'.$row->id);
        // Order map: column index => DB column (phải trùng thứ tự với DataTable để custom field load đúng trang)
        $clientOrderMap = [
            2 => 'users.id',
            3 => 'client_details.client_code',
            4 => 'users.name',
            5 => 'pricing_tiers.name',
            6 => 'contract_pricing_active',
            7 => 'outstanding_balance',
            8 => 'users.salutation',
            9 => 'users.email',
            10 => 'client_details.added_by',
            11 => 'users.mobile',
            12 => 'client_categories.category_name',
            13 => 'users.status',
            14 => 'users.created_at',
            15 => 'client_details.payment_terms',
            16 => 'client_details.customer_grade',
            17 => 'client_details.channel_type',
            18 => 'client_details.business_type',
            19 => 'client_details.business_closure_date',
        ];
        $customFieldColumns = CustomField::customFieldData(
            $datatables,
            ClientDetails::CUSTOM_FIELD_MODEL,
            'clientDetails',
            $query,
            'client_details.id',
            $clientOrderMap
        );

        $datatables->rawColumns(array_merge(['name', 'action', 'status', 'check', 'contract_pricing_active', 'outstanding_balance'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @return User|Builder|\Illuminate\Database\Query\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();
        $company = function_exists('company') ? company() : null;
        $today = now($company && $company->timezone ? $company->timezone : config('app.timezone'))->toDateString();
        $companyId = $company ? $company->id : null;
        $users = $model->withoutGlobalScope(ActiveScope::class)
            ->with('session:id', 'clientDetails.addedBy:id,name,image', 'clientDetails.company:id,logo,company_name')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->leftJoin('client_categories', 'client_details.category_id', '=', 'client_categories.id')
            ->leftJoin('pricing_tiers', 'client_details.pricing_tier_id', '=', 'pricing_tiers.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select(
                'client_categories.category_name as cat_name',
                'pricing_tiers.name as pricing_tier_name',
                'users.id',
                'client_details.client_code as client_code',
                'users.salutation',
                'users.is_client_contact',
                'users.name',
                'client_details.company_name',
                'users.email',
                'users.mobile',
                'users.country_phonecode',
                'users.image',
                'users.created_at',
                'users.status',
                'client_details.added_by',
                'users.admin_approval',
                'client_details.payment_terms',
                'client_details.customer_grade',
                'client_details.channel_type',
                'client_details.business_type',
                'client_details.business_closure_date'
            )
            ->selectSub(function ($query) use ($today, $companyId) {
                $query->from('client_product_pricing')
                    ->select(DB::raw('1'))
                    ->whereColumn('client_product_pricing.client_id', 'users.id')
                    ->where('client_product_pricing.company_id', $companyId)
                    ->where('client_product_pricing.is_active', true)
                    ->whereDate('client_product_pricing.start_date', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('client_product_pricing.end_date')
                            ->orWhereDate('client_product_pricing.end_date', '>=', $today);
                    })
                    ->limit(1);
            }, 'contract_pricing_active')
            ->selectSub(function ($query) use ($companyId) {
                $payments = DB::table('payments')
                    ->select('invoice_id', DB::raw('SUM(amount) as paid_amount'))
                    ->where('status', 'complete')
                    ->groupBy('invoice_id');

                $query->from('invoices')
                    ->leftJoinSub($payments, 'invoice_payments', function ($join) {
                        $join->on('invoice_payments.invoice_id', '=', 'invoices.id');
                    })
                    ->selectRaw('COALESCE(SUM(GREATEST(invoices.total - COALESCE(invoice_payments.paid_amount, 0), 0)), 0)')
                    ->whereColumn('invoices.client_id', 'users.id')
                    ->where('invoices.company_id', $companyId)
                    ->where('invoices.credit_note', 0)
                    ->whereIn('invoices.status', ['unpaid', 'partial']);
            }, 'outstanding_balance')
            ->where('roles.name', 'client')
            ->whereNull('users.is_client_contact');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $users = $users->where('users.created_at', '>=', $startDate.' 00:00:00');
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $users = $users->where('users.created_at', '<=', $endDate.' 23:59:59');
        }

        if ($request->status != 'all' && $request->status != '') {
            $users = $users->where('users.status', $request->status);
        }

        if ($request->client != 'all' && $request->client != '') {
            $users = $users->where('users.id', $request->client);
        }

        if (! is_null($request->category_id) && $request->category_id != 'all') {
            $users = $users->where('client_details.category_id', $request->category_id);
        }

        if (! is_null($request->sub_category_id) && $request->sub_category_id != 'all') {
            $users = $users->where('client_details.sub_category_id', $request->sub_category_id);
        }

        if (! is_null($request->project_id) && $request->project_id != 'all') {
            $users->whereHas('projects', function ($query) use ($request) {
                return $query->where('id', $request->project_id);
            });
        }

        if (! is_null($request->contract_type_id) && $request->contract_type_id != 'all') {
            $users->whereHas('contracts', function ($query) use ($request) {
                return $query->where('contracts.contract_type_id', $request->contract_type_id);
            });
        }

        if (! is_null($request->country_id) && $request->country_id != 'all') {
            $users->whereHas('country', function ($query) use ($request) {
                return $query->where('id', $request->country_id);
            });
        }

        if ($request->verification != 'all') {
            if ($request->verification == 'yes') {
                $users->where('users.admin_approval', 1);
            } elseif ($request->verification == 'no') {
                $users->where('users.admin_approval', 0);
            }
        }

        if ($this->viewClientPermission == 'added' || $this->viewClientPermission == 'both') {
            $users = $users->where('client_details.added_by', user()->id);
        }

        if ($request->searchText != '') {
            $users = $users->where(function ($query) {
                $safeTerm = Common::safeString(request('searchText'));
                $query->where('users.name', 'like', '%'.$safeTerm.'%')
                    ->orWhere('users.email', 'like', '%'.$safeTerm.'%')
                    ->orWhere('client_details.company_name', 'like', '%'.$safeTerm.'%')
                    ->orWhere('client_details.client_code', 'like', '%'.$safeTerm.'%');
            });
        }

        return $users;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('clients-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    var dt = window.LaravelDataTables["clients-table"];
                    if (dt && dt.buttons) {
                        dt.buttons().container().appendTo("#client-dt-buttons");
                    }
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".change-client-status").selectpicker();
                }',
                'stateSaveParams' => 'function (settings, data) {
                    data.clientListColumnsVersion = "2026-06-14-pm-defaults-v1";
                }',
                'stateLoadParams' => 'function (settings, data) {
                    if (data.clientListColumnsVersion !== "2026-06-14-pm-defaults-v1") {
                        return false;
                    }
                }',
            ]);

        $buttons = [Button::make([
            'extend' => 'colvis',
            'text' => '<i class="fa fa-columns"></i> '.trans('app.columns'),
            'columns' => ':not(:first):not(:last)',
        ])];
        if (canDataTableExport()) {
            array_unshift($buttons, Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> '.trans('app.exportExcel')]));
        }
        $dataTable->buttons($buttons);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => ! showId(), 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],
            __('app.clientCode') => ['data' => 'client_code', 'name' => 'client_details.client_code', 'title' => __('app.clientCode')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false, 'title' => __('app.name')],
            __('pricing::app.pricingTier') => ['data' => 'pricing_tier_name', 'name' => 'pricing_tiers.name', 'title' => __('pricing::app.pricingTier')],
            __('pricing::app.menu.contractPricing') => ['data' => 'contract_pricing_active', 'name' => 'contract_pricing_active', 'orderable' => false, 'searchable' => false, 'title' => __('pricing::app.menu.contractPricing')],
            __('modules.invoices.amountDue') => ['data' => 'outstanding_balance', 'name' => 'outstanding_balance', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => __('modules.invoices.amountDue')],
            __('modules.client.salutation') => ['data' => 'client_name', 'name' => 'users.salutation', 'visible' => false, 'title' => __('modules.client.salutation')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'visible' => false, 'title' => __('app.email')],
            __('app.addedBy') => ['data' => 'added_by', 'name' => 'added_by', 'visible' => false, 'title' => __('app.addedBy')],
            __('app.mobile') => ['data' => 'mobile', 'name' => 'mobile', 'visible' => false, 'title' => __('app.mobile')],
            __('app.category') => ['data' => 'category_name', 'name' => 'category_name', 'visible' => false, 'title' => __('app.category')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            __('app.createdAt') => ['data' => 'created_at', 'name' => 'created_at', 'visible' => false, 'title' => __('app.createdAt')],
            __('modules.client.paymentTerms') => ['data' => 'payment_terms', 'name' => 'payment_terms', 'visible' => false, 'title' => __('modules.client.paymentTerms')],
            __('modules.client.customerGrade') => ['data' => 'customer_grade', 'name' => 'customer_grade', 'visible' => false, 'title' => __('modules.client.customerGrade')],
            __('modules.client.channelType') => ['data' => 'channel_type', 'name' => 'channel_type', 'visible' => false, 'title' => __('modules.client.channelType')],
            __('modules.client.businessType') => ['data' => 'business_type', 'name' => 'business_type', 'visible' => false, 'title' => __('modules.client.businessType')],
            __('modules.client.businessClosureDate') => ['data' => 'business_closure_date', 'name' => 'business_closure_date', 'visible' => false, 'title' => __('modules.client.businessClosureDate')],
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];

        return array_merge($data, $this->clientCustomFieldColumns(), $action);
    }

    private function clientCustomFieldColumns(): array
    {
        $columns = CustomFieldGroup::customFieldsDataMerge(new ClientDetails);

        $visibilityPolicy = $this->clientCustomFieldVisibilityPolicy();

        foreach ($columns as $fieldName => $column) {
            $policyKey = str_replace('-', '_', $fieldName);
            $dataPolicyKey = str_replace('-', '_', $column['data'] ?? '');

            if (array_key_exists($policyKey, $visibilityPolicy)) {
                $columns[$fieldName]['visible'] = $visibilityPolicy[$policyKey];
            } elseif (array_key_exists($dataPolicyKey, $visibilityPolicy)) {
                $columns[$fieldName]['visible'] = $visibilityPolicy[$dataPolicyKey];
            }
        }

        return $columns;
    }

    private function clientCustomFieldVisibilityPolicy(): array
    {
        return [
            'salesperson' => true,
            'last_transaction_at' => true,
            'sales_assistant_name' => false,
            'department' => false,
            'channel_type' => false,
            'business_type' => false,
            'geographical_distinction' => false,
            'payment_terms' => false,
            'customer_grade' => false,
        ];
    }

    private function clientStatusBadge(?string $status): string
    {
        if ($status === 'active') {
            return '<span class="badge badge-success"><i class="fa fa-circle mr-1 f-10"></i>'.__('app.active').'</span>';
        }

        return '<span class="badge badge-secondary"><i class="fa fa-circle mr-1 f-10"></i>'.__('app.inactive').'</span>';
    }
}
