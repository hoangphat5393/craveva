<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Pricing\Entities\CompanyCustomerPricing;
use Modules\Pricing\Entities\PricingTier;

class CompanyPricingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('pricing', array_map('strtolower', $this->user->modules)));
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

        $this->pricings = CompanyCustomerPricing::with(['client', 'client.clientDetails', 'tier'])
            ->where('company_id', user()->company_id)
            ->orderBy('id', 'desc')
            ->get();

        return view('pricing::company_pricing.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_client_pricing');
        abort_403($addPermission == 'none');

        // Get all clients
        $this->clients = User::allClients();

        // Get active pricing tiers
        $this->tiers = PricingTier::where('company_id', user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->view = 'pricing::company_pricing.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::company_pricing.create', $this->data);
    }

    public function store(Request $request)
    {
        $addPermission = user()->permission('add_client_pricing');
        abort_403($addPermission == 'none');

        $request->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('company_id', user()->company_id),
            ],
            'pricing_tier_id' => [
                'nullable',
                'integer',
                Rule::exists('pricing_tiers', 'id')->where(function ($query) {
                    $query->where('company_id', company()->id)
                        ->orWhereNull('company_id');
                }),
            ],
            'custom_discount_type' => 'nullable|in:percentage,fixed_amount',
            'custom_discount_value' => 'nullable|numeric',
        ]);

        $pricing = CompanyCustomerPricing::firstOrNew([
            'company_id' => user()->company_id,
            'client_id' => $request->client_id,
        ]);

        $pricing->pricing_tier_id = $request->pricing_tier_id;
        $pricing->custom_discount_type = $request->custom_discount_type;
        $pricing->custom_discount_value = $request->custom_discount_value;
        $pricing->is_active = true;
        $pricing->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('pricing.company_pricing.index')]);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $this->pricing = CompanyCustomerPricing::findOrFail($id);

        $this->clients = User::allClients();

        $this->tiers = PricingTier::where('company_id', user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->view = 'pricing::company_pricing.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::company_pricing.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $request->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('company_id', user()->company_id),
            ],
            'pricing_tier_id' => [
                'nullable',
                'integer',
                Rule::exists('pricing_tiers', 'id')->where(function ($query) {
                    $query->where('company_id', company()->id)
                        ->orWhereNull('company_id');
                }),
            ],
            'custom_discount_type' => 'nullable|in:percentage,fixed_amount',
            'custom_discount_value' => 'nullable|numeric',
        ]);

        $pricing = CompanyCustomerPricing::where('company_id', user()->company_id)->findOrFail($id);
        $pricing->client_id = $request->client_id;
        $pricing->pricing_tier_id = $request->pricing_tier_id;
        $pricing->custom_discount_type = $request->custom_discount_type;
        $pricing->custom_discount_value = $request->custom_discount_value;
        $pricing->is_active = $request->has('is_active') ? true : false;
        $pricing->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('pricing.company_pricing.index')]);
    }

    public function changeStatus(Request $request)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $pricing = CompanyCustomerPricing::where('company_id', user()->company_id)
            ->where('id', $request->id)
            ->first();

        if (! $pricing) {
            return Reply::error('Record not found for ID: ' . $request->id);
        }

        $pricing->is_active = ($request->status == 'active');
        $pricing->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $pricing = CompanyCustomerPricing::where('company_id', user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $pricing->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('pricing.company_pricing.index')]);
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

        $ids = array_filter(array_map('intval', explode(',', (string) $request->row_ids)));
        if (empty($ids)) {
            return;
        }

        CompanyCustomerPricing::where('company_id', user()->company_id)->whereIn('id', $ids)->delete();
    }

    protected function changeStatusBulk(Request $request)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $ids = array_filter(array_map('intval', explode(',', (string) $request->row_ids)));
        if (empty($ids)) {
            return;
        }

        CompanyCustomerPricing::where('company_id', user()->company_id)->whereIn('id', $ids)->update(['is_active' => $request->status == 'active']);
    }
}
