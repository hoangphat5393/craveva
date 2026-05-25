<?php

namespace Modules\Purchase\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\Purchase\Entities\PurchaseSetting;

class DeliveryOrderSettingsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'purchase::app.menu.deliveryOrderSettings';
        $this->activeSettingMenu = 'delivery_order_settings';

        $this->middleware(function ($request, $next) {
            abort_403(! in_array(PurchaseSetting::MODULE_NAME, user_modules()));
            abort_403(user()->permission('view_purchase_setting') !== 'all');

            return $next($request);
        });
    }

    public function index(): View
    {
        $this->purchaseSetting = PurchaseSetting::first();

        return view('purchase::delivery-order-settings.index', $this->data);
    }

    public function update(Request $request, int $id): array
    {
        $request->validate([
            'delivery_order_terms' => 'nullable|string',
        ]);

        $purchaseSetting = PurchaseSetting::findOrFail($id);

        abort_403((int) $purchaseSetting->company_id !== (int) company()->id);

        $purchaseSetting->delivery_order_terms = $request->delivery_order_terms;
        $purchaseSetting->save();

        cache()->forget('purchase_setting_'.$purchaseSetting->company_id);
        session()->forget('purchase_setting');

        return Reply::success(__('messages.updateSuccess'));
    }
}
