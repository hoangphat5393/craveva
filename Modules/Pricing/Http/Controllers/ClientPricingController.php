<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Pricing\Entities\ClientProductPricing;

class ClientPricingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            // Ensure strict company context
            if (! company()) {
                abort(403, 'Company context is required.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $viewPermission = user()->permission('view_client_pricing');
        abort_403($viewPermission == 'none');

        $this->pricings = ClientProductPricing::with(['client', 'client.clientDetails', 'product'])
            ->where('company_id', user()->company_id)
            ->orderBy('id', 'desc')
            ->get();

        return view('pricing::client_pricing.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_client_pricing');
        abort_403($addPermission == 'none');

        $this->clients = User::allClients();
        $this->products = Product::orderBy('name')->get();
        $this->view = 'pricing::client_pricing.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::client_pricing.create', $this->data);
    }

    public function store(Request $request)
    {
        $addPermission = user()->permission('add_client_pricing');
        abort_403($addPermission == 'none');

        $request->validate([
            'client_id' => 'required|integer',
            'product_id' => 'required|integer',
            'custom_price' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric',
            'start_date' => 'required|date_format:"'.company()->date_format.'"|after_or_equal:today',
            'end_date' => 'nullable|date_format:"'.company()->date_format.'"|after_or_equal:start_date',
        ], [
            'product_id.required' => __('pricing::app.productRequired'),
            'start_date.required' => __('pricing::app.startDateRequired'),
            'start_date.after_or_equal' => __('pricing::app.startDateRequired'),
            'end_date.after_or_equal' => __('pricing::app.endDateAfterStartDate'),
        ]);

        $startDate = Carbon::createFromFormat(company()->date_format, $request->start_date)->format('Y-m-d');
        $endDate = $request->end_date
            ? Carbon::createFromFormat(company()->date_format, $request->end_date)->format('Y-m-d')
            : '2099-12-31';

        $overlap = ClientProductPricing::where('client_id', $request->client_id)
            ->where('product_id', $request->product_id)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($overlap) {
            return Reply::error(__('pricing::app.overlapError'));
        }

        $pricing = new ClientProductPricing;
        $pricing->client_id = $request->client_id;
        $pricing->product_id = $request->product_id;
        $pricing->company_id = user()->company_id ?? null;
        $pricing->custom_price = $request->custom_price;
        $pricing->discount_type = $request->discount_type;
        $pricing->discount_value = $request->discount_value;
        $pricing->start_date = $startDate;
        $pricing->end_date = $endDate;
        $pricing->is_active = true;
        $pricing->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('pricing.client_pricing.index')]);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $this->pricing = ClientProductPricing::findOrFail($id);
        $this->clients = User::allClients();
        $this->products = Product::orderBy('name')->get();
        $this->view = 'pricing::client_pricing.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::client_pricing.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $request->validate([
            'client_id' => 'required|integer',
            'product_id' => 'required|integer',
            'custom_price' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric',
            'start_date' => 'required|date_format:"'.company()->date_format.'"',
            'end_date' => 'nullable|date_format:"'.company()->date_format.'"|after_or_equal:start_date',
        ], [
            'product_id.required' => __('pricing::app.productRequired'),
            'start_date.required' => __('pricing::app.startDateRequired'),
            'end_date.after_or_equal' => __('pricing::app.endDateAfterStartDate'),
        ]);

        $startDate = Carbon::createFromFormat(company()->date_format, $request->start_date)->format('Y-m-d');
        $endDate = $request->end_date
            ? Carbon::createFromFormat(company()->date_format, $request->end_date)->format('Y-m-d')
            : '2099-12-31';

        $overlap = ClientProductPricing::where('client_id', $request->client_id)
            ->where('product_id', $request->product_id)
            ->where('id', '!=', $id)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($overlap) {
            return Reply::error(__('pricing::app.overlapError'));
        }

        $pricing = ClientProductPricing::findOrFail($id);
        $pricing->client_id = $request->client_id;
        $pricing->product_id = $request->product_id;
        $pricing->custom_price = $request->custom_price;
        $pricing->discount_type = $request->discount_type;
        $pricing->discount_value = $request->discount_value;
        $pricing->start_date = $startDate;
        $pricing->end_date = $endDate;
        $pricing->is_active = $request->has('is_active') ? true : false;
        $pricing->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('pricing.client_pricing.index')]);
    }

    public function changeStatus(Request $request)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $pricing = ClientProductPricing::find($request->id);

        if (! $pricing) {
            return Reply::error('Record not found for ID: '.($request->id ?? 'NULL'));
        }

        $pricing->is_active = ($request->status == 'active');
        $pricing->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $pricing = ClientProductPricing::where('company_id', user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $pricing->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('pricing.client_pricing.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);

                return Reply::success(__('messages.deleteSuccess'));
            case 'change-status':
                $this->changeStatusBulk($request);

                return Reply::success(__('messages.updateSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords(Request $request)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        ClientProductPricing::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeStatusBulk(Request $request)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        ClientProductPricing::whereIn('id', explode(',', $request->row_ids))->update(['is_active' => $request->status == 'active']);
    }
}
