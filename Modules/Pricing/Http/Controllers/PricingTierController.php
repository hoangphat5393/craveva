<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Pricing\Entities\PricingTier;
use Modules\Pricing\Entities\PricingTierItem;
use Modules\Pricing\Http\Controllers\Concerns\ValidatesBulkRowIds;

class PricingTierController extends AccountBaseController
{
    use ValidatesBulkRowIds;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('pricing', array_map('strtolower', $this->user->modules)));
            // Ensure strict company context - Super Admin cannot access without impersonation
            if (! company()) {
                abort(403, 'Company context is required.');
            }

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

        $tier = new PricingTier;
        $tier->name = $request->name;
        $tier->description = $request->description;
        $tier->priority = $request->priority;
        $tier->valid_from = $this->parseTierDate($request->valid_from);
        $tier->valid_to = $this->parseTierDate($request->valid_to);
        $tier->discount_type = $request->discount_type;
        $tier->discount_value = $request->discount_value;
        $tier->is_active = true;
        $tier->company_id = user()->company_id;

        if (is_null($tier->company_id)) {
            abort(403, 'Company context is required to create pricing tiers.');
        }

        $tier->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('pricing.tiers.index')]);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $this->pricingTier = PricingTier::where('company_id', user()->company_id)->findOrFail($id);
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

        $tier = PricingTier::where('company_id', user()->company_id)->findOrFail($id);
        $tier->name = $request->name;
        $tier->description = $request->description;
        $tier->priority = $request->priority;
        $tier->valid_from = $this->parseTierDate($request->valid_from);
        $tier->valid_to = $this->parseTierDate($request->valid_to);
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

        PricingTier::where('company_id', user()->company_id)->where('id', $id)->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('pricing.tiers.index')]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_pricing_tiers');
        abort_403($viewPermission == 'none');

        $this->pricingTier = PricingTier::with(['items.product.unit'])
            ->where('company_id', user()->company_id)
            ->findOrFail($id);
        $this->products = Product::query()
            ->select(['id', 'name', 'sku', 'allow_purchase', 'status', 'unit_id'])
            ->with('unit')
            ->orderBy('name')
            ->get();
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

        PricingTier::where('company_id', user()->company_id)->findOrFail($id);

        $item = new PricingTierItem;
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
        $ids = $this->validatedBulkRowIds($request);

        PricingTier::where('company_id', user()->company_id)->whereIn('id', $ids)->delete();
    }

    protected function deleteItemRecords($request)
    {
        $ids = $this->validatedBulkRowIds($request);
        $allowedTierIds = PricingTier::where('company_id', user()->company_id)->pluck('id');
        PricingTierItem::whereIn('id', $ids)->whereIn('pricing_tier_id', $allowedTierIds)->delete();
    }

    protected function changeBulkStatus($request)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $ids = $this->validatedBulkRowIds($request);

        PricingTier::where('company_id', user()->company_id)->whereIn('id', $ids)->update(['is_active' => $request->status == 'active']);
    }

    public function changeStatus(Request $request)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $request->validate([
            'tierId' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $tier = PricingTier::where('company_id', user()->company_id)->findOrFail($request->tierId);
        $tier->is_active = $request->status == 'active';
        $tier->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroyItem($itemId)
    {
        $editPermission = user()->permission('edit_pricing_tiers');
        abort_403($editPermission == 'none');

        $item = PricingTierItem::findOrFail($itemId);
        PricingTier::where('company_id', user()->company_id)->findOrFail($item->pricing_tier_id);
        $item->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Parse tier validity dates using company format, with fallback for ISO/other strings.
     */
    private function parseTierDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $format = company()->date_format;

        try {
            return Carbon::createFromFormat($format, $value)->format('Y-m-d');
        } catch (\Throwable) {
            return Carbon::parse($value)->format('Y-m-d');
        }
    }
}
