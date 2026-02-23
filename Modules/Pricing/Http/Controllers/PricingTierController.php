<?php

namespace Modules\Pricing\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use App\Helper\Reply;
use Modules\Pricing\Entities\PricingTier;
use Modules\Pricing\Entities\PricingTierItem;
use App\Models\Product;

class PricingTierController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('pricing', array_map('strtolower', $this->user->modules)));
            return $next($request);
        });
    }

    public function index()
    {
        $viewPermission = user()->permission('view_pricing_tiers');
        abort_403($viewPermission == 'none');

        $this->tiers = PricingTier::orderBy('id', 'desc')->get();
        return view('pricing::tiers.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_pricing_tiers');
        abort_403($addPermission == 'none');

        $this->view = 'pricing::tiers.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::tiers.create', $this->data);
    }

    public function store(Request $request)
    {
        $addPermission = user()->permission('add_pricing_tiers');
        abort_403($addPermission == 'none');

        $request->validate([
            'name' => 'required|string|max:100',
            'priority' => 'nullable|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $tier = new PricingTier();
        $tier->name = $request->name;
        $tier->description = $request->description;
        $tier->priority = $request->priority;
        $tier->valid_from = $request->valid_from ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->valid_from)->format('Y-m-d') : null;
        $tier->valid_to = $request->valid_to ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->valid_to)->format('Y-m-d') : null;
        $tier->discount_type = $request->discount_type;
        $tier->discount_value = $request->discount_value;
        $tier->is_active = true;
        $tier->company_id = user()->company_id ?? null;
        $tier->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('pricing.tiers.index')]);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $this->pricingTier = PricingTier::findOrFail($id);
        $this->view = 'pricing::tiers.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::tiers.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $request->validate([
            'name' => 'required|string|max:100',
            'priority' => 'nullable|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $tier = PricingTier::findOrFail($id);
        $tier->name = $request->name;
        $tier->description = $request->description;
        $tier->priority = $request->priority;
        $tier->valid_from = $request->valid_from ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->valid_from)->format('Y-m-d') : null;
        $tier->valid_to = $request->valid_to ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->valid_to)->format('Y-m-d') : null;
        $tier->discount_type = $request->discount_type;
        $tier->discount_value = $request->discount_value;
        $tier->is_active = $request->has('is_active') ? true : false;
        $tier->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('pricing.tiers.index')]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_pricing_tiers');
        abort_403($deletePermission == 'none');

        PricingTier::destroy($id);
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('pricing.tiers.index')]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_pricing_tiers');
        abort_403($viewPermission == 'none');

        $this->pricingTier = PricingTier::with('items.product')->findOrFail($id);
        $this->products = Product::all();
        $this->view = 'pricing::tiers.ajax.show';

        if (request()->ajax()) {
            return view($this->view, $this->data);
        }

        return view('pricing::tiers.show', $this->data);
    }

    public function storeItem(Request $request, $id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'discount_type' => 'required|in:percentage,fixed,specific_price',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $item = new PricingTierItem();
        $item->pricing_tier_id = $id;
        $item->product_id = $request->product_id;
        $item->discount_type = $request->discount_type;
        $item->discount_value = $request->discount_value;
        $item->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $deletePermission = user()->permission('delete_pricing_tiers');
                abort_403($deletePermission == 'none');
                $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
            case 'change-status':
                $editPermission = user()->permission('edit_pricing_tiers');
                abort_403($editPermission == 'none');
                $this->changeBulkStatus($request);
                return Reply::success(__('messages.updateSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    public function applyItemsQuickAction(Request $request)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        if ($request->action_type === 'delete') {
            $this->deleteItemRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    protected function deleteRecords($request)
    {
        PricingTier::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function deleteItemRecords($request)
    {
        PricingTierItem::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        PricingTier::whereIn('id', explode(',', $request->row_ids))->update(['is_active' => $request->status == 'active']);
    }

    public function changeStatus(Request $request)
    {
        $tier = PricingTier::findOrFail($request->tierId);
        $tier->is_active = $request->status == 'active';
        $tier->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroyItem($itemId)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        PricingTierItem::destroy($itemId);
        return Reply::success(__('messages.deleteSuccess'));
    }
}
