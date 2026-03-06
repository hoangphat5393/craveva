<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Traits\ImportExcel;
use Modules\Pricing\Imports\ClientProductPricingImport;
use Modules\Pricing\Imports\PricingTierItemsImport;
use Modules\Pricing\Jobs\ImportClientProductPricingJob;
use Modules\Pricing\Jobs\ImportPricingTierItemsJob;

class PricingImportController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
    }

    public function import()
    {
        $addClientPricing = user()->permission('add_client_pricing');
        $addPricingTiers = user()->permission('add_pricing_tiers');

        abort_403($addClientPricing == 'none' && $addPricingTiers == 'none');

        $this->view = 'pricing::import.index';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('pricing::import.index', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $type = $request->get('import_type');

        if ($type === 'client_product_pricing') {
            $addPermission = user()->permission('add_client_pricing');
            abort_403($addPermission != 'all' && $addPermission != 'added');
            $rvalue = $this->importFileProcess($request, ClientProductPricingImport::class);
            $headingTitle = __('app.importExcel').' '.__('pricing::app.contractProductPricing');
        } else {
            $addPermission = user()->permission('add_pricing_tiers');
            abort_403($addPermission != 'all' && $addPermission != 'added');
            $rvalue = $this->importFileProcess($request, PricingTierItemsImport::class);
            $headingTitle = __('app.importExcel').' Pricing Tier Items';
        }

        if ($rvalue === 'abort') {
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('pricing::import.import_progress', [
            'headingTitle' => $headingTitle,
            'processRoute' => route('pricing.import.process', ['type' => $type]),
            'backRoute' => route('pricing.client_pricing.index'),
            'backButtonText' => __('app.backToClient'),
        ] + $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $type = request()->get('type');

        if ($type === 'client_product_pricing') {
            $batch = $this->importJobProcess($request, ClientProductPricingImport::class, ImportClientProductPricingJob::class);
        } else {
            $batch = $this->importJobProcess($request, PricingTierItemsImport::class, ImportPricingTierItemsJob::class);
        }

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }
}
