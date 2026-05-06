<?php

namespace Modules\Production\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Production\Entities\ProductionCompanyFgPolicy;
use Modules\Production\Http\Requests\UpdateProductionFgQuantityPolicyRequest;
use Modules\Production\Services\ProductionFgQuantityPolicyService;
use Modules\Production\Support\ProductionTenantAccess;

class ProductionFgQuantityPolicySettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'production::app.fgQuantityPolicySettingsHeading';
        $this->activeSettingMenu = 'production_fg_quantity_policy';

        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_company_setting') !== 'all');
            abort_if(! ProductionTenantAccess::tenantMayUseProduction(), 403);

            return $next($request);
        });
    }

    public function index(): Renderable
    {
        $companyId = (int) company()->id;

        $row = ProductionCompanyFgPolicy::query()->where('company_id', $companyId)->first();

        /** @var array<string, mixed> $defaults */
        $defaults = ProductionFgQuantityPolicyService::mergedDefaultsFromConfig();

        /** @var array<string, mixed> $settings */
        $settings = array_merge($defaults, $row instanceof ProductionCompanyFgPolicy ? $row->only([
            'policy_mode',
            'tolerance_percent',
            'tolerance_absolute',
            'controlled_require_reason_beyond_tolerance',
            'controlled_block_beyond_tolerance',
        ]) : []);

        $this->fgPolicySettings = $settings;

        return view('production::fg-quantity-policy.index', $this->data);
    }

    public function update(UpdateProductionFgQuantityPolicyRequest $request)
    {
        $companyId = (int) company()->id;

        ProductionCompanyFgPolicy::query()->updateOrCreate(
            ['company_id' => $companyId],
            $request->validated()
        );

        return Reply::success(__('messages.updateSuccess'));
    }
}
