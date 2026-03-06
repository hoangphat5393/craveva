<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Modules\Purchase\DataTables\PurchaseOrderReportDataTable;
use Modules\Purchase\Entities\PurchaseSetting;

class PurchaseOrderReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'purchase::app.menu.purchaseOrderReport';
        $this->middleware(function ($request, $next) {
            abort_403(! in_array(PurchaseSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(PurchaseOrderReportDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_order_report');
        abort_403(! in_array($viewPermission, ['all']));

        return $dataTable->render('purchase::reports.index', $this->data);
    }
}
