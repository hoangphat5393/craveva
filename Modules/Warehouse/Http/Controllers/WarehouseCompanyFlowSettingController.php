<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Warehouse\Entities\WarehouseCompanyFlowSetting;
use Modules\Warehouse\Http\Requests\UpdateWarehouseCompanyFlowSettingRequest;
use Modules\Warehouse\Services\WarehouseFlowConfigService;

class WarehouseCompanyFlowSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'warehouse::app.warehouseFlowSettingsHeading';
        $this->activeSettingMenu = 'warehouse_flow_settings';

        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_company_setting') !== 'all');
            abort_403(! in_array('warehouse', user_modules() ?? [], true));

            return $next($request);
        });
    }

    public function index(): Renderable
    {
        $companyId = (int) company()->id;
        $this->flowSettings = app(WarehouseFlowConfigService::class)->forCompany($companyId);

        return view('warehouse::company-flow-settings.index', $this->data);
    }

    public function update(UpdateWarehouseCompanyFlowSettingRequest $request)
    {
        $companyId = (int) company()->id;

        WarehouseCompanyFlowSetting::query()->updateOrCreate(
            ['company_id' => $companyId],
            $request->validated()
        );

        return Reply::success(__('messages.updateSuccess'));
    }
}
