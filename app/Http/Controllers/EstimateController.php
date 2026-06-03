<?php

namespace App\Http\Controllers;

use App\DataTables\EstimatesDataTable;
use App\Events\NewEstimateEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Helper\UserService;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\EstimateAcceptRequest;
use App\Http\Requests\StoreEstimate;
use App\Imports\EstimateImport;
use App\Jobs\ImportEstimateChunkJob;
use App\Models\AcceptEstimate;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateItemImage;
use App\Models\EstimateRequest;
use App\Models\EstimateTemplate;
use App\Models\EstimateTemplateItem;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Tax;
use App\Models\UnitType;
use App\Models\User;
use App\Services\Estimates\EstimateApprovalEventLogger;
use App\Services\Estimates\EstimateBomLineSync;
use App\Services\Estimates\EstimatePhase1Notifier;
use App\Services\Estimates\EstimateProductionBomCopier;
use App\Services\Estimates\EstimateSimilarRecipeSearch;
use App\Services\Estimates\EstimateTotalsCalculator;
use App\Services\Estimates\EstimateVpMarginPolicy;
use App\Support\DocumentLineUnitPricing;
use App\Support\EstimateReviewAuthorization;
use App\Support\OrderProductUnitPrice;
use App\Traits\ImportExcel;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Production\Entities\ProductionBom;

class EstimateController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.quotation_ui.page_title';
        $this->pageIcon = 'ti-file';
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('estimates', $this->user->modules));

            return $next($request);
        });
    }

    public function index(EstimatesDataTable $dataTable)
    {
        abort_403(! in_array(user()->permission('view_estimates'), ['all', 'added', 'owned', 'both']));

        return $dataTable->render('estimates.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_estimates');

        abort_403(! in_array($this->addPermission, ['all', 'added']));

        if (request('estimate') != '') {
            $this->estimateId = request('estimate');
            $this->type = 'estimate';
            $this->estimate = Estimate::with('items', 'items.estimateItemImage', 'bomLines.unit', 'bomLines.product', 'client', 'unit', 'client.projects')->findOrFail($this->estimateId);
        }

        $this->pageTitle = __('app.quotation_ui.create');
        $this->clients = User::allClients();
        $this->currencies = Currency::all();
        $this->lastEstimate = Estimate::lastEstimateNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastEstimate) < $this->invoiceSetting->estimate_digit) {
            $condition = $this->invoiceSetting->estimate_digit - strlen($this->lastEstimate);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }

        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->template = EstimateTemplate::all();
        $this->units = UnitType::all();
        $this->bomComponentProducts = $this->bomComponentProductsForEstimateForm();
        $this->productionBoms = $this->productionBomsForEstimateForm();

        $this->estimateTemplate = null;
        $this->estimateTemplateItem = null;

        if (request()->has('template')) {
            $this->estimateTemplate = EstimateTemplate::findOrFail(request('template'));

            $this->estimateTemplateItem = EstimateTemplateItem::with('estimateTemplateItemImage')->where('estimate_template_id', request('template'))->get();
        }

        if (request()->has('estimate-request')) {
            $this->estimateTemplate = EstimateRequest::findOrFail(request('estimate-request'));
            $this->estimateRequestId = $this->estimateTemplate->id;
        }

        // this data is sent from project and client invoices
        $this->project = request('project_id') ? Project::findOrFail(request('project_id')) : null;

        $estimate = new Estimate;
        $getCustomFieldGroupsWithFields = $estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->client = isset(request()->default_client) ? User::findOrFail(request()->default_client) : null;

        $userId = UserService::getUserId();
        $this->isClient = User::isClient($userId);

        if ($this->isClient) {
            $this->client = User::with('projects')->findOrFail($userId);
        }

        if (request()->has('estimate-request')) {
            $this->client = EstimateRequest::findOrFail(request('estimate-request'))->client;
        }

        $this->view = 'estimates.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);
    }

    public function store(StoreEstimate $request)
    {
        $buildResult = $this->buildLineItemsFromEstimateRequest($request);
        if ($buildResult['error'] !== null) {
            return Reply::error($buildResult['error']);
        }

        $bomBuildResult = ['lines' => [], 'error' => null];
        if ($this->isPhase1ReviewGateEnabled()) {
            $bomBuildResult = app(EstimateBomLineSync::class)->parseFromRequest($request);
            if ($bomBuildResult['error'] !== null) {
                return Reply::error($bomBuildResult['error']);
            }
        }

        $lineItems = $buildResult['items'];
        $calculationLines = $buildResult['calculation_lines'];

        $estimate = new Estimate;
        $estimate->client_id = $request->client_id;
        $estimate->project_id = $request->project_id ?? null;
        $estimate->valid_till = companyToYmd($request->valid_till);
        $this->applyCalculatedTotalsToEstimate($estimate, $calculationLines, $request);
        $estimate->currency_id = $request->currency_id;
        $estimate->note = trim_editor($request->note);
        $estimate->discount = round($request->discount_value, 2);
        $estimate->discount_type = $request->discount_type;
        if ($this->isPhase1ReviewGateEnabled()) {
            $estimate->president_review_status = Estimate::INTERNAL_REVIEW_PENDING;
            $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
        }
        $estimate->description = trim_editor($request->description);
        $estimate->estimate_number = $request->estimate_number;
        $estimate->estimate_request_id = $request->estimate_request_id ?? null;
        $estimate->status = request('type') === 'draft' ? 'draft' : 'waiting';
        $this->applyQuotationSourceFields($request, $estimate);
        if ($this->isPhase1ReviewGateEnabled()) {
            $this->applyRecipeHeaderFields($request, $estimate);
        }
        $estimate->save();

        foreach ($lineItems as $index => $lineItem) {
            EstimateItem::query()->create([
                'estimate_id' => $estimate->id,
                'item_name' => $lineItem['item_name'],
                'item_summary' => $lineItem['item_summary'],
                'type' => 'item',
                'unit_id' => $lineItem['unit_id'],
                'product_id' => $lineItem['product_id'],
                'hsn_sac_code' => $lineItem['hsn_sac_code'],
                'quantity' => $lineItem['quantity'],
                'unit_price' => $lineItem['unit_price'],
                'amount' => $lineItem['amount'],
                'taxes' => $lineItem['taxes'],
                'field_order' => $index + 1,
                'free_quantity' => $lineItem['free_quantity'],
                'line_effective_date' => $lineItem['line_effective_date'],
                'line_expiry_date' => $lineItem['line_expiry_date'],
            ]);
        }

        if ($this->isPhase1ReviewGateEnabled()) {
            app(EstimateBomLineSync::class)->sync($estimate, $bomBuildResult['lines']);
        }

        if (request()->has('estimate_request_id')) {
            $estimateRequest = EstimateRequest::findOrFail(request('estimate_request_id'));
            $estimateRequest->update(['status' => 'accepted', 'estimate_id' => $estimate->id]);
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $estimate->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logSearchEntry($estimate->id, $estimate->estimate_number, 'estimates.show', 'estimate');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('estimates.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['estimateId' => $estimate->id, 'redirectUrl' => $redirectUrl]);
    }

    public function show(int $id)
    {
        $this->invoice = Estimate::with('sign', 'client', 'unit', 'clientdetails', 'bomLines.unit', 'bomLines.product', 'presidentReviewer', 'vpPricingReviewer', 'approvalEvents.actor')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_estimates');
        $userId = UserService::getUserId();

        abort_403(! (
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->invoice->added_by == $userId)
            || ($this->viewPermission == 'owned' && $this->invoice->client_id == $userId)
            || ($this->viewPermission == 'both' && ($this->invoice->client_id == $userId || $this->invoice->added_by == $userId))
        ));

        if ($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount . '%';
        } else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->invoice->estimate_number;

        $this->discount = 0;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            } else {
                $this->discount = $this->invoice->discount;
            }
        }

        $taxList = [];

        $this->firstEstimate = Estimate::orderBy('id', 'desc')->first();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (! isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }
                    } else {

                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        if (in_array('client', user_roles())) {
            $lastViewed = now();
            $ipAddress = request()->ip();
            $this->invoice->last_viewed = $lastViewed;
            $this->invoice->ip_address = $ipAddress;
            $this->invoice->save();
        }

        $this->productionBoms = $this->productionBomsForEstimateForm();
        $this->similarRecipes = $this->isPhase1ReviewGateEnabled()
            ? app(EstimateSimilarRecipeSearch::class)->findForEstimate($this->invoice)
            : [];

        return view('estimates.show', $this->data);
    }

    public function edit(int $id)
    {
        $this->estimate = Estimate::with(
            'items.estimateItemImage',
            'bomLines.unit',
            'bomLines.product',
            'presidentReviewer',
            'vpPricingReviewer',
        )->findOrFail($id)->withCustomFields();
        $userId = UserService::getUserId();
        $this->editPermission = user()->permission('edit_estimates');

        abort_403(! (
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && ($this->estimate->added_by == user()->id || $this->estimate->added_by == $userId))
            || ($this->editPermission == 'owned' && $this->estimate->client_id == $userId)
            || ($this->editPermission == 'both' && ($this->estimate->client_id == $userId || $this->estimate->added_by == user()->id || $this->estimate->added_by == $userId))
        ));

        $this->pageTitle = $this->estimate->estimate_number;

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->isClient = User::isClient($userId);

        $this->units = UnitType::all();
        $this->clients = User::allClients();
        $this->currencies = Currency::all();
        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();
        $this->bomComponentProducts = $this->bomComponentProductsForEstimateForm();
        $this->productionBoms = $this->productionBomsForEstimateForm();
        $this->similarRecipes = $this->isPhase1ReviewGateEnabled()
            ? app(EstimateSimilarRecipeSearch::class)->findForEstimate($this->estimate)
            : [];

        $currency = Currency::find($this->estimate->currency_id) ?? Currency::find(company()->currency_id);
        $this->productSellableUnitsMap = DocumentLineUnitPricing::sellableUnitsMapForOrderItems(
            $this->estimate->items,
            $currency
        );

        $this->view = 'estimates.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);
    }

    public function update(StoreEstimate $request, int $id)
    {
        $buildResult = $this->buildLineItemsFromEstimateRequest($request);
        if ($buildResult['error'] !== null) {
            return Reply::error($buildResult['error']);
        }

        $bomBuildResult = ['lines' => [], 'error' => null];
        if ($this->isPhase1ReviewGateEnabled()) {
            $bomBuildResult = app(EstimateBomLineSync::class)->parseFromRequest($request);
            if ($bomBuildResult['error'] !== null) {
                return Reply::error($bomBuildResult['error']);
            }
        }

        $lineItems = $buildResult['items'];
        $calculationLines = $buildResult['calculation_lines'];

        $invoiceItemImage = $request->invoice_item_image;
        $invoiceItemImageUrl = $request->invoice_item_image_url;
        $itemIds = (array) ($request->item_ids ?? []);

        $estimate = Estimate::findOrFail($id);
        $estimate->client_id = $request->client_id;
        $estimate->project_id = $request->project_id ?? null;
        $estimate->valid_till = companyToYmd($request->valid_till);
        $estimate->discount = round($request->discount_value, 2);
        $estimate->discount_type = $request->discount_type;
        $this->applyCalculatedTotalsToEstimate($estimate, $calculationLines, $request);
        $estimate->currency_id = $request->currency_id;
        $estimate->status = $request->status;
        $estimate->note = trim_editor($request->note);
        $estimate->description = trim_editor($request->description);
        $estimate->estimate_number = $request->estimate_number;
        $this->applyQuotationSourceFields($request, $estimate);
        if ($this->isPhase1ReviewGateEnabled()) {
            $this->applyRecipeHeaderFields($request, $estimate);
        }
        $estimate->save();

        $persistedItemIds = [];

        foreach ($lineItems as $order => $lineItem) {
            $formIndex = $lineItem['form_index'];
            $invoiceItemId = isset($itemIds[$formIndex]) ? (int) $itemIds[$formIndex] : 0;

            try {
                $estimateItem = EstimateItem::findOrFail($invoiceItemId);
            } catch (Exception) {
                $estimateItem = new EstimateItem;
            }

            $estimateItem->estimate_id = $estimate->id;
            $estimateItem->item_name = $lineItem['item_name'];
            $estimateItem->item_summary = $lineItem['item_summary'];
            $estimateItem->type = 'item';
            $estimateItem->unit_id = $lineItem['unit_id'];
            $estimateItem->product_id = $lineItem['product_id'];
            $estimateItem->hsn_sac_code = $lineItem['hsn_sac_code'];
            $estimateItem->quantity = $lineItem['quantity'];
            $estimateItem->unit_price = $lineItem['unit_price'];
            $estimateItem->amount = $lineItem['amount'];
            $estimateItem->taxes = $lineItem['taxes'];
            $estimateItem->field_order = $order + 1;
            $estimateItem->free_quantity = $lineItem['free_quantity'];
            $estimateItem->line_effective_date = $lineItem['line_effective_date'];
            $estimateItem->line_expiry_date = $lineItem['line_expiry_date'];
            $estimateItem->save();

            $persistedItemIds[] = $estimateItem->id;

            if ((isset($invoiceItemImage[$formIndex]) && $request->hasFile('invoice_item_image.' . $formIndex)) || isset($invoiceItemImageUrl[$formIndex])) {
                if (! isset($invoiceItemImageUrl[$formIndex]) && $estimateItem->estimateItemImage) {
                    Files::deleteFile($estimateItem->estimateItemImage->hashname, EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                }

                $filename = '';

                if (isset($invoiceItemImage[$formIndex])) {
                    $filename = Files::uploadLocalOrS3($invoiceItemImage[$formIndex], EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                }

                EstimateItemImage::updateOrCreate(
                    [
                        'estimate_item_id' => $estimateItem->id,
                    ],
                    [
                        'filename' => isset($invoiceItemImage[$formIndex]) ? $invoiceItemImage[$formIndex]->getClientOriginalName() : null,
                        'hashname' => isset($invoiceItemImage[$formIndex]) ? $filename : null,
                        'size' => isset($invoiceItemImage[$formIndex]) ? $invoiceItemImage[$formIndex]->getSize() : null,
                        'external_link' => isset($invoiceItemImage[$formIndex]) ? null : ($invoiceItemImageUrl[$formIndex] ?? null),
                    ]
                );
            }
        }

        if ($persistedItemIds !== []) {
            EstimateItem::query()
                ->where('estimate_id', $estimate->id)
                ->whereNotIn('id', $persistedItemIds)
                ->delete();
        } else {
            EstimateItem::query()->where('estimate_id', $estimate->id)->delete();
        }

        if ($this->isPhase1ReviewGateEnabled()) {
            app(EstimateBomLineSync::class)->sync($estimate, $bomBuildResult['lines']);
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $estimate->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::redirect(route('estimates.index'), __('messages.updateSuccess'));
    }

    public function destroy(int $id)
    {
        $estimate = Estimate::findOrFail($id);

        $this->deletePermission = user()->permission('delete_estimates');

        abort_403(! (
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $estimate->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $estimate->client_id == user()->id)
            || ($this->deletePermission == 'both' && ($estimate->client_id == user()->id || $estimate->added_by == user()->id))
        ));

        Estimate::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function download(int $id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->estimate = Estimate::with('unit')->findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->viewPermission = user()->permission('view_estimates');

        abort_403(! (
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->estimate->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->estimate->client_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->estimate->client_id == user()->id || $this->estimate->added_by == user()->id))
        ));

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload(int $id)
    {
        $this->invoiceSetting = invoice_setting();
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $this->estimate = Estimate::with(['bomLines.product', 'bomLines.unit'])
            ->findOrFail($id)
            ->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->estimate->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->discount = 0;

        if ($this->estimate->discount > 0) {

            if ($this->estimate->discount_type == 'percent') {
                $this->discount = (($this->estimate->discount / 100) * $this->estimate->sub_total);
            } else {
                $this->discount = $this->estimate->discount;
            }
        }

        $taxList = [];

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->estimate->id)
            ->get();
        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (! isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }
                    } else {
                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = $this->estimate->company;

        $this->invoiceSetting = invoice_setting();

        $pdf = app('dompdf.wrapper');

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        // $pdf->loadView('estimates.pdf.' . $this->invoiceSetting->template, $this->data);
        $customCss = '<style>
                * { text-transform: none !important; }
            </style>';

        $pdf->loadHTML($customCss . view('estimates.pdf.' . $this->invoiceSetting->template, $this->data)->render());

        $filename = $this->estimate->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename,
        ];
    }

    public function sendEstimate(int $id)
    {
        $estimate = Estimate::findOrFail($id);

        $estimate->send_status = 1;

        if ($estimate->status == 'draft') {
            $estimate->status = 'waiting';
        }

        if ($this->isPhase1ReviewGateEnabled() && $estimate->president_review_status === null) {
            $estimate->president_review_status = Estimate::INTERNAL_REVIEW_PENDING;
        }

        if ($this->isPhase1ReviewGateEnabled() && $estimate->vp_pricing_review_status === null) {
            $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
        }

        $estimate->save();
        event(new NewEstimateEvent($estimate));

        return Reply::success(__('messages.updateSuccess'));
    }

    public function changeStatus(Request $request, int $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'canceled';
        $estimate->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function acceptModal(Request $request, int $id)
    {
        return view('estimates.ajax.accept-estimate', ['id' => $id]);
    }

    public function accept(EstimateAcceptRequest $request, int $id)
    {
        DB::beginTransaction();

        $estimate = Estimate::with('sign')->findOrFail($id);

        if ($this->isPhase1ReviewGateEnabled() && ! $estimate->isReadyForCommercialConversion()) {
            return Reply::error(__('modules.estimates.convertSoBlocked'));
        }

        /** @phpstan-ignore-next-line */
        if ($estimate && $estimate->sign) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $accept = new AcceptEstimate;
        $accept->full_name = $request->first_name . ' ' . $request->last_name;
        $accept->estimate_id = $estimate->id;
        $accept->email = $request->email;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('estimate/accept');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/estimate/accept/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'estimate/accept', $estimate->company_id);
        } else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'estimate/accept/', 300);
            }
        }

        $accept->signature = $imageName;
        $accept->save();

        $estimate->status = 'accepted';
        $estimate->save();

        $invoiceExist = Invoice::where('estimate_id', $estimate->id)->first();

        if (is_null($invoiceExist)) {

            $invoice = new Invoice;

            $invoice->client_id = $estimate->client_id;
            $invoice->issue_date = now($this->company->timezone)->format('Y-m-d');
            $invoice->due_date = now($this->company->timezone)->addDays(invoice_setting()->due_after)->format('Y-m-d');
            $invoice->sub_total = round($estimate->sub_total, 2);
            $invoice->discount = round($estimate->discount, 2);
            $invoice->discount_type = $estimate->discount_type;
            $invoice->total = round($estimate->total, 2);
            $invoice->currency_id = $estimate->currency_id;
            $invoice->note = trim_editor($estimate->note);
            $invoice->status = 'unpaid';
            $invoice->estimate_id = $estimate->id;
            $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
            $invoice->save();

            /** @phpstan-ignore-next-line */
            foreach ($estimate->items as $item) {
                if (! is_null($item)) {
                    InvoiceItems::create(
                        [
                            'invoice_id' => $invoice->id,
                            'item_name' => $item->item_name,
                            'item_summary' => $item->item_summary ?: '',
                            'type' => 'item',
                            'quantity' => $item->quantity,
                            'unit_price' => round($item->unit_price, 2),
                            'amount' => round($item->amount, 2),
                            'taxes' => $item->taxes,
                        ]
                    );
                }
            }

            // Log search
            $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');
        }

        DB::commit();

        return Reply::redirect(route('estimates.index'), __('messages.estimateSigned'));
    }

    public function decline(Request $request, int $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'declined';
        $estimate->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function convertToSalesOrder(int $id)
    {
        $this->addOrderPermission = user()->permission('add_order');
        abort_403(! in_array($this->addOrderPermission, ['all', 'added', 'both']));

        $estimate = Estimate::with('items')->findOrFail($id);

        if ($this->isPhase1ReviewGateEnabled() && ! $estimate->isReadyForCommercialConversion()) {
            return Reply::error(__('modules.estimates.convertSoBlocked'));
        }

        $existingOrder = Order::query()
            ->where('estimate_id', $estimate->id)
            ->orderByDesc('id')
            ->first();

        if ($existingOrder) {
            return Reply::redirect(route('orders.show', $existingOrder->id), __('messages.updateSuccess'));
        }

        $order = null;

        DB::transaction(function () use ($estimate, &$order): void {
            $order = new Order;
            $order->client_id = $estimate->client_id;
            $order->project_id = $estimate->project_id;
            $order->estimate_id = $estimate->id;
            $order->order_date = now()->format('Y-m-d');
            $order->sub_total = round((float) $estimate->sub_total, 2);
            $order->total = round((float) $estimate->total, 2);
            $order->discount = round((float) $estimate->discount, 2);
            $order->discount_type = $estimate->discount_type;
            $order->status = 'pending';
            $order->currency_id = $estimate->currency_id;
            $order->note = $estimate->note;
            $order->show_shipping_address = 'yes';
            $order->order_number = Order::lastOrderNumber() + 1;
            $order->save();

            foreach ($estimate->items as $item) {
                OrderItems::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'item_name' => $item->item_name,
                    'item_summary' => $item->item_summary,
                    'type' => $item->type ?? 'item',
                    'quantity' => $item->quantity,
                    'unit_price' => round((float) $item->unit_price, 2),
                    'amount' => round((float) $item->amount, 2),
                    'hsn_sac_code' => $item->hsn_sac_code,
                    'taxes' => $item->taxes,
                    'unit_id' => $item->unit_id,
                    'sku' => $item->sku,
                    'field_order' => $item->field_order,
                ]);
            }

            $this->logSearchEntry($order->id, $order->id, 'orders.show', 'order');
        });

        if (! $order instanceof Order) {
            return Reply::error(__('messages.invalidRequest'));
        }

        return Reply::redirect(route('orders.show', $order->id), __('messages.recordSaved'));
    }

    public function submitForReview(int $id)
    {
        abort_403(! $this->isPhase1ReviewGateEnabled());

        $this->editPermission = user()->permission('edit_estimates');
        abort_403(! in_array($this->editPermission, ['all', 'added'], true));

        $estimate = Estimate::findOrFail($id);

        if (! EstimateReviewAuthorization::canSubmitForReview($estimate)) {
            return Reply::error(__('modules.estimates.submitReviewNotAllowed'));
        }

        if ($estimate->isRevisionRequired()) {
            $estimate->resetInternalReviewForResubmission();
        } elseif ($estimate->hasLegacyInternalReviewState()) {
            $estimate->president_review_status = Estimate::INTERNAL_REVIEW_PENDING;
            $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
        } else {
            if (! $estimate->hasPresidentApproved()) {
                $estimate->president_review_status = Estimate::INTERNAL_REVIEW_PENDING;
                $estimate->president_reviewed_by = null;
                $estimate->president_reviewed_at = null;
                $estimate->president_review_note = null;
            }

            if (! $estimate->hasVpPricingApproved()) {
                $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
                $estimate->vp_pricing_reviewed_by = null;
                $estimate->vp_pricing_reviewed_at = null;
                $estimate->vp_pricing_review_note = null;
            }
        }

        if (in_array($estimate->status, ['draft', Estimate::STATUS_REVISION_REQUIRED], true)) {
            $estimate->status = 'waiting';
        }

        $estimate->save();

        app(EstimateApprovalEventLogger::class)->log(
            $estimate,
            EstimateApprovalEventLogger::EVENT_SUBMITTED,
        );

        app(EstimatePhase1Notifier::class)->notifySubmitted($estimate);

        return Reply::success(__('modules.estimates.submitReviewSuccess'));
    }

    public function productionBomLines(int $bom): JsonResponse
    {
        abort_403(! $this->isPhase1ReviewGateEnabled());

        $result = app(EstimateProductionBomCopier::class)->linesForEstimateForm(
            $bom,
            (int) company()->id,
        );

        if ($result['error'] !== null) {
            return response()->json(Reply::error($result['error']));
        }

        return response()->json([
            'status' => 'success',
            'lines' => $result['lines'],
        ]);
    }

    public function presidentReview(Request $request, int $id)
    {
        abort_403(! $this->isPhase1ReviewGateEnabled());
        abort_403(! user_can_approve_estimate_president());

        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'note' => 'nullable|string|max:5000',
        ]);

        $estimate = Estimate::findOrFail($id);
        $decision = (string) $request->input('decision');

        $estimate->president_review_status = $decision;
        $estimate->president_reviewed_by = user()->id;
        $estimate->president_reviewed_at = now();
        $estimate->president_review_note = $request->input('note');

        if ($decision === Estimate::INTERNAL_REVIEW_REJECTED) {
            $estimate->status = Estimate::STATUS_REVISION_REQUIRED;
            $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
            $estimate->vp_pricing_reviewed_by = null;
            $estimate->vp_pricing_reviewed_at = null;
            $estimate->vp_pricing_review_note = null;
        } elseif ($estimate->vp_pricing_review_status === null) {
            $estimate->vp_pricing_review_status = Estimate::INTERNAL_REVIEW_PENDING;
        }

        $estimate->save();

        app(EstimateApprovalEventLogger::class)->log(
            $estimate,
            $decision === Estimate::INTERNAL_REVIEW_APPROVED
                ? EstimateApprovalEventLogger::EVENT_PRESIDENT_APPROVED
                : EstimateApprovalEventLogger::EVENT_PRESIDENT_REJECTED,
            $request->input('note'),
        );

        app(EstimatePhase1Notifier::class)->notifyPresidentDecision($estimate, $decision);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function vpPricingReview(Request $request, int $id)
    {
        abort_403(! $this->isPhase1ReviewGateEnabled());
        abort_403(! user_can_approve_estimate_vp_pricing());

        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'note' => 'nullable|string|max:5000',
        ]);

        $estimate = Estimate::findOrFail($id);

        if (! $estimate->hasPresidentApproved()) {
            return Reply::error(__('modules.estimates.vpReviewRequiresPresident'));
        }

        $decision = (string) $request->input('decision');

        if ($decision === Estimate::INTERNAL_REVIEW_APPROVED) {
            $marginEvaluation = app(EstimateVpMarginPolicy::class)->evaluateForVpApproval($estimate);

            if (! $marginEvaluation['allowed']) {
                return Reply::error(__('modules.estimates.vpMarginBelowMinimum', [
                    'margin' => number_format((float) $marginEvaluation['margin_percent'], 2),
                    'minimum' => number_format((float) $marginEvaluation['minimum_percent'], 2),
                ]));
            }
        }

        $estimate->vp_pricing_review_status = $decision;
        $estimate->vp_pricing_reviewed_by = user()->id;
        $estimate->vp_pricing_reviewed_at = now();
        $estimate->vp_pricing_review_note = $request->input('note');

        if ($decision === Estimate::INTERNAL_REVIEW_REJECTED) {
            $estimate->status = Estimate::STATUS_REVISION_REQUIRED;
        }

        $estimate->save();

        app(EstimateApprovalEventLogger::class)->log(
            $estimate,
            $decision === Estimate::INTERNAL_REVIEW_APPROVED
                ? EstimateApprovalEventLogger::EVENT_VP_APPROVED
                : EstimateApprovalEventLogger::EVENT_VP_REJECTED,
            $request->input('note'),
        );

        app(EstimatePhase1Notifier::class)->notifyVpDecision($estimate, $decision);

        return Reply::success(__('messages.updateSuccess'));
    }

    protected function isPhase1ReviewGateEnabled(): bool
    {
        return estimates_phase1_review_enabled();
    }

    public function deleteEstimateItemImage(Request $request)
    {
        $item = EstimateItemImage::where('estimate_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'estimate-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function getclients(int $id)
    {
        $client_data = Product::where('unit_id', $id)->get();
        $unitId = UnitType::where('id', $id)->first();

        return Reply::dataOnly(['status' => 'success', 'data' => $client_data, 'type' => $unitId]);
    }

    public function addItem(Request $request)
    {
        $this->items = Product::with('unit')->findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        $this->sellableUnits = DocumentLineUnitPricing::sellableUnitsForLine(
            $this->items,
            $exchangeRate,
            $request->exchangeRate ?? $request->exchange_rate
        );

        $defaultUnitId = (int) ($this->items->unit_id ?? 0);
        $this->items->price = OrderProductUnitPrice::formatForOrder(
            $this->items,
            $defaultUnitId > 0 ? $defaultUnitId : null,
            $exchangeRate,
            $request->exchangeRate ?? $request->exchange_rate
        );

        $this->taxes = Tax::all();
        $this->units = UnitType::all();
        $this->showEstimateLineMeta = true;
        $view = view('invoices.ajax.add_item', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function importQuotation()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.quotation_ui.singular');
        $addPermission = user()->permission('add_estimates');
        abort_403(! in_array($addPermission, ['all', 'added']));

        $this->view = 'estimates.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('estimates.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $addPermission = user()->permission('add_estimates');
        abort_403(! in_array($addPermission, ['all', 'added']));

        $rvalue = $this->importFileProcess($request, EstimateImport::class);
        if ($rvalue === 'abort') {
            return Reply::error(__('messages.abortAction'));
        }

        $this->data['originalImportFilename'] = $request->import_file->getClientOriginalName();
        $view = view('estimates.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $addPermission = user()->permission('add_estimates');
        abort_403(! in_array($addPermission, ['all', 'added']));

        $chunkSize = $request->filled('chunk_size') ? (int) $request->chunk_size : 100;
        $batch = $this->importJobProcessChunked(
            $request,
            EstimateImport::class,
            ImportEstimateChunkJob::class,
            $chunkSize,
            ['import_user_id' => user()->id]
        );
        $batchId = data_get($batch, 'id');
        if ($batchId) {
            Cache::put('import_metrics_' . $batchId, [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'skipped_missing_required' => 0,
                'invalid_status' => 0,
            ], now()->addHours(12));
        }

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    /**
     * @return array{error: string|null, items: array<int, array<string, mixed>>, calculation_lines: array<int, array{amount: float, taxes: array<int|string>}>}
     */
    protected function buildLineItemsFromEstimateRequest(StoreEstimate $request): array
    {
        $items = (array) $request->input('item_name', []);
        $costPerItem = (array) $request->input('cost_per_item', []);
        $quantities = (array) $request->input('quantity', []);
        $itemSummaries = (array) $request->input('item_summary', []);
        $hsnSacCodes = (array) $request->input('hsn_sac_code', []);
        $unitIds = (array) $request->input('unit_id', []);
        $productIds = (array) $request->input('product_id', []);
        $taxes = (array) $request->input('taxes', []);
        $freeQuantities = (array) $request->input('item_free_quantity', []);
        $lineEffectiveDates = (array) $request->input('item_line_effective_date', []);
        $lineExpiryDates = (array) $request->input('item_line_expiry_date', []);

        $lineItems = [];
        $calculationLines = [];

        foreach ($items as $index => $itemName) {
            $normalizedItemName = trim((string) $itemName);
            $normalizedCost = trim((string) ($costPerItem[$index] ?? ''));
            $normalizedQuantity = $quantities[$index] ?? null;

            if ($normalizedItemName === '') {
                continue;
            }

            if ($normalizedItemName === '' || $normalizedCost === '') {
                return ['error' => __('messages.addItem'), 'items' => [], 'calculation_lines' => []];
            }

            if (! is_numeric($normalizedQuantity) || (float) $normalizedQuantity < 1) {
                return ['error' => __('messages.quantityNumber'), 'items' => [], 'calculation_lines' => []];
            }

            if (! is_numeric($normalizedCost)) {
                return ['error' => __('messages.unitPriceNumber'), 'items' => [], 'calculation_lines' => []];
            }

            $quantity = (float) $normalizedQuantity;
            $unitPrice = round((float) $normalizedCost, 2);
            $amount = round($quantity * $unitPrice, 2);
            $taxIds = array_key_exists($index, $taxes) ? (array) $taxes[$index] : [];

            $lineItems[] = [
                'form_index' => $index,
                'item_name' => $normalizedItemName,
                'item_summary' => (string) ($itemSummaries[$index] ?? ''),
                'hsn_sac_code' => isset($hsnSacCodes[$index]) && $hsnSacCodes[$index] !== '' ? $hsnSacCodes[$index] : null,
                'unit_id' => isset($unitIds[$index]) && $unitIds[$index] !== '' ? $unitIds[$index] : null,
                'product_id' => isset($productIds[$index]) && $productIds[$index] !== '' ? $productIds[$index] : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'taxes' => $taxIds !== [] ? json_encode($taxIds) : null,
                'free_quantity' => $this->parseOptionalDecimalInput($freeQuantities[$index] ?? null),
                'line_effective_date' => $this->parseOptionalDateInput($lineEffectiveDates[$index] ?? null),
                'line_expiry_date' => $this->parseOptionalDateInput($lineExpiryDates[$index] ?? null),
            ];

            $calculationLines[] = [
                'amount' => $amount,
                'taxes' => $taxIds,
            ];
        }

        if ($lineItems === []) {
            return ['error' => __('messages.addItem'), 'items' => [], 'calculation_lines' => []];
        }

        return ['error' => null, 'items' => $lineItems, 'calculation_lines' => $calculationLines];
    }

    /**
     * @param  array<int, array{amount: float, taxes: array<int|string>}>  $calculationLines
     */
    protected function applyCalculatedTotalsToEstimate(Estimate $estimate, array $calculationLines, StoreEstimate $request): void
    {
        $totals = app(EstimateTotalsCalculator::class)->calculate(
            $calculationLines,
            round((float) $request->discount_value, 2),
            (string) $request->discount_type,
            (string) $request->input('calculate_tax', 'after_discount'),
        );

        $estimate->sub_total = $totals['sub_total'];
        $estimate->total = $totals['total'];
        $estimate->calculate_tax = (string) $request->input('calculate_tax', 'after_discount');
    }

    protected function applyQuotationSourceFields(StoreEstimate $request, Estimate $estimate): void
    {
        $estimate->quotation_date = $this->parseOptionalDateInput($request->input('quotation_date'));
        $estimate->document_date = $this->parseOptionalDateInput($request->input('document_date'));
        $estimate->exchange_rate = $this->parseOptionalDecimalInput($request->input('exchange_rate'));
        $estimate->header_quotation_amount = $this->parseOptionalDecimalInput($request->input('header_quotation_amount'));
        $estimate->header_tax_amount = $this->parseOptionalDecimalInput($request->input('header_tax_amount'));
        $estimate->header_total_quantity = $this->parseOptionalDecimalInput($request->input('header_total_quantity'));
        $estimate->delivery_note = $request->filled('delivery_note') ? trim((string) $request->delivery_note) : null;
        $estimate->salesperson_name = $request->filled('salesperson_name') ? trim((string) $request->salesperson_name) : null;
        $estimate->tax_type_label = $request->filled('tax_type_label') ? trim((string) $request->tax_type_label) : null;
        $estimate->payment_terms_code = $request->filled('payment_terms_code') ? trim((string) $request->payment_terms_code) : null;
        $estimate->payment_terms_name = $request->filled('payment_terms_name') ? trim((string) $request->payment_terms_name) : null;
        $estimate->confirm_internal = $request->filled('confirm_internal') ? trim((string) $request->confirm_internal) : null;
        $estimate->confirm_customer = $request->filled('confirm_customer') ? trim((string) $request->confirm_customer) : null;
        $estimate->price_terms = $request->filled('price_terms') ? trim((string) $request->price_terms) : null;
        $estimate->volume_unit = $request->filled('volume_unit') ? trim((string) $request->volume_unit) : null;
        $estimate->total_gross_weight_kg = $this->parseOptionalDecimalInput($request->input('total_gross_weight_kg'));
        $estimate->total_volume = $this->parseOptionalDecimalInput($request->input('total_volume'));
    }

    protected function applyRecipeHeaderFields(StoreEstimate $request, Estimate $estimate): void
    {
        $moq = $request->input('recipe_moq');
        $estimate->recipe_moq = ($moq !== null && $moq !== '') ? max(0, (int) $moq) : null;
        $estimate->recipe_packaging = $request->filled('recipe_packaging') ? trim((string) $request->recipe_packaging) : null;
        $estimate->recipe_oem_sku = $request->filled('recipe_oem_sku') ? trim((string) $request->recipe_oem_sku) : null;
        $estimate->recipe_target_unit_price = $this->parseOptionalDecimalInput($request->input('recipe_target_unit_price'));

        $productionBomId = $request->input('production_bom_id');
        $estimate->production_bom_id = ($productionBomId !== null && $productionBomId !== '')
            ? (int) $productionBomId
            : null;
    }

    protected function parseOptionalDateInput(mixed $value): ?string
    {
        $value = $value !== null ? trim((string) $value) : '';
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat(company()->date_format, $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseOptionalDecimalInput(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string) $value));

        return is_numeric($s) ? (float) $s : null;
    }

    /**
     * @return Collection<int, Product>
     */
    protected function bomComponentProductsForEstimateForm()
    {
        return Product::query()
            ->with('unit')
            ->where('company_id', company()->id)
            ->forBomComponents()
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id', 'type']);
    }

    protected function productionBomsForEstimateForm(): Collection
    {
        if (! EstimateProductionBomCopier::moduleAvailable()) {
            return new Collection;
        }

        return ProductionBom::query()
            ->with('outputProduct')
            ->where('company_id', company()->id)
            ->orderByDesc('id')
            ->get();
    }
}
