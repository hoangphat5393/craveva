<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\Request;
use Modules\Pricing\Entities\VolumeDiscountRule;

class VolumeRuleController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('pricing', array_map('strtolower', $this->user->modules)));
            // Ensure strict company context
            if (!company()) {
                abort(403, 'Company context is required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $viewPermission = user()->permission('view_pricing_tiers');
        abort_403($viewPermission == 'none');

        $this->rules = VolumeDiscountRule::query()
            ->orderByDesc('id')
            ->get();

        return view('pricing::volume_rules.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_pricing_tiers');
        abort_403($addPermission == 'none');

        $this->products = Product::all();
        $this->view = 'pricing::volume_rules.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::volume_rules.create', $this->data);
    }

    public function store(Request $request)
    {
        $addPermission = user()->permission('add_pricing_tiers');
        abort_403($addPermission == 'none');

        $request->validate([
            'name' => 'required|string|max:191',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'minimum_quantity' => 'required|integer|min:1',
            'maximum_quantity' => 'nullable|integer|min:1',
            'discount_value' => 'required|numeric|min:0',
            'applies_to_type' => 'required|in:all,products',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $rule = new VolumeDiscountRule();
        $rule->company_id = user()->company_id;
        
        if (is_null($rule->company_id)) {
            abort(403, 'Company context is required to create volume discount rules.');
        }

        $rule->name = $request->name;
        $rule->discount_type = $request->discount_type;
        $rule->minimum_quantity = $request->minimum_quantity;
        $rule->maximum_quantity = $request->maximum_quantity;
        $rule->discount_value = $request->discount_value;
        $rule->applies_to_type = $request->applies_to_type;

        if ($request->applies_to_type === 'products') {
            $rule->applies_to_product_id = $request->product_id;
        }

        $rule->is_active = true;
        $rule->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('pricing.volume_rules.index')]);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $this->rule = VolumeDiscountRule::findOrFail($id);
        $this->products = Product::all();
        $this->view = 'pricing::volume_rules.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::volume_rules.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $request->validate([
            'name' => 'required|string|max:191',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'minimum_quantity' => 'required|integer|min:1',
            'maximum_quantity' => 'nullable|integer|min:1',
            'discount_value' => 'required|numeric|min:0',
            'applies_to_type' => 'required|in:all,products',
            'product_id' => 'nullable|exists:products,id',
            'is_active' => 'nullable|boolean',
        ]);

        $rule = VolumeDiscountRule::findOrFail($id);
        $rule->name = $request->name;
        $rule->discount_type = $request->discount_type;
        $rule->minimum_quantity = $request->minimum_quantity;
        $rule->maximum_quantity = $request->maximum_quantity;
        $rule->discount_value = $request->discount_value;
        $rule->applies_to_type = $request->applies_to_type;
        $rule->applies_to_product_id = null;

        if ($request->applies_to_type === 'products') {
            $rule->applies_to_product_id = $request->product_id;
        }

        $rule->is_active = $request->has('is_active') ? true : false;
        $rule->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('pricing.volume_rules.index')]);
    }

    public function changeStatus(Request $request)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $rule = VolumeDiscountRule::find($request->id);

        if (!$rule) {
            return Reply::error('Record not found for ID: ' . $request->id);
        }

        $rule->is_active = $request->status == 'active';
        $rule->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_pricing_tiers');
        abort_403($deletePermission == 'none');

        VolumeDiscountRule::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('pricing.volume_rules.index')]);
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
        $deletePermission = user()->permission('delete_pricing_tiers');
        abort_403($deletePermission == 'none');

        VolumeDiscountRule::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeStatusBulk(Request $request)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        VolumeDiscountRule::whereIn('id', explode(',', $request->row_ids))->update(['is_active' => $request->status == 'active']);
    }
}
