<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Purchase\Entities\PurchaseSetting;

class DeliveryOrderSettingsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            abort_403(! in_array(PurchaseSetting::MODULE_NAME, user_modules()));
            abort_403(user()->permission('view_purchase_setting') !== 'all');

            return $next($request);
        });
    }

    public function index(): RedirectResponse
    {
        return redirect()
            ->route('purchase-settings.index', ['tab' => 'general'])
            ->withFragment('document-terms');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        return redirect()
            ->route('purchase-settings.index', ['tab' => 'general'])
            ->withFragment('document-terms');
    }
}
