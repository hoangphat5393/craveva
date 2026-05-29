@php
    $purchaseViewVendorPermission = user()->permission('view_vendor');
    $purchaseViewOrderPermission = user()->permission('view_purchase_order');
    $purchaseViewBillPermission = user()->permission('view_bill');
    $purchaseViewCreditPermission = user()->permission('view_vendor_credit');
    $purchaseViewInventoryPermission = user()->permission('view_inventory');
    $purchaseViewOrderReportPermission = user()->permission('view_order_report');
    $purchaseViewPaymentPermission = user()->permission('view_vendor_payment');
    $canViewGrn = \Modules\Purchase\Support\FlowPermission::allowsAlias('grn.view');
    $canViewSalesDo = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.view');
    $flowNamingMode = config('purchase.flow_naming_mode', 'compat_v2');
    $grnLabel = $flowNamingMode === 'legacy' ? __('purchase::app.menu.deliveryOrders') : __('purchase::app.menu.goodsReceivedNote');
    $salesDoLabel = $flowNamingMode === 'legacy' ? __('purchase::app.menu.salesShipments') : __('purchase::app.menu.saleDeliveryOrder');
    $grnRouteName = $flowNamingMode === 'legacy' ? 'delivery-orders.index' : 'grn.index';
    $salesDoRouteName = $flowNamingMode === 'legacy' ? 'sales-shipments.index' : 'sales-do.index';
    $viewWarehouses = user()->permission('view_warehouses');
    $viewWarehouseStock = user()->permission('view_warehouse_stock');
    $manageWarehouseTransfer = user()->permission('manage_warehouse_transfer');
    $canSeeWarehouseMaster = ($viewWarehouses && $viewWarehouses != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canSeeWarehouseStockUi = ($viewWarehouseStock && $viewWarehouseStock != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canViewOperationsProducts = in_array('client', user_roles())
        ? in_array('orders', user_modules()) && user()->permission('add_order') == 'all' && isset($sidebarUserPermissions['view_product']) && !in_array($sidebarUserPermissions['view_product'], [5, 'none'])
        : in_array('products', user_modules()) && isset($sidebarUserPermissions['view_product']) && !in_array($sidebarUserPermissions['view_product'], [5, 'none']);
    $viewProductionOrders = user()->permission('view_production_orders');
    $canViewProductionOrders = \Modules\Production\Support\ProductionTenantAccess::tenantMayUseProduction() && isset($viewProductionOrders) && $viewProductionOrders != 'none' && $viewProductionOrders != '';

    $showProcurementMenu =
        ($purchaseViewVendorPermission != 'none' && $purchaseViewVendorPermission != '') ||
        ($purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != '') ||
        ($purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != '') ||
        ($purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != '') ||
        ($purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != '') ||
        $canViewGrn;

    $showSalesFulfillmentMenu = $canViewSalesDo || (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none') || (in_array('orders', user_modules()) && isset($sidebarUserPermissions['view_sales_history']) && $sidebarUserPermissions['view_sales_history'] != 'none' && user()->permission('view_sales_history') === 'all');

    $showInventoryWarehouseMenu = $canViewOperationsProducts || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '') || (in_array('warehouse', user_modules()) && ($canSeeWarehouseMaster || $canSeeWarehouseStockUi));

    $showProductionMenu = $canViewProductionOrders;

    $procurementMenuActive = request()->routeIs('vendors.*', 'purchase-order.*', 'delivery-orders.*', 'grn.*', 'bills.*', 'vendor-payments.*', 'vendor-credits.*');
    $salesFulfillmentMenuActive = request()->routeIs('orders.*', 'sales-shipments.*', 'sales-do.*', 'sales-history.*');
    $inventoryWarehouseMenuActive = request()->routeIs('purchase-products.*', 'purchase_products.*', 'purchase-inventory.*', 'warehouse.*', 'warehouse.stock.*', 'warehouse.transfer.*', 'warehouse.movements.*');
    $productionMenuActive = request()->routeIs('production.*');

    $walletIconPath =
        '<path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.6.6 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.6.6 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.6.6 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535z" /><path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.6.6 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0z" />';
@endphp
@if (in_array(\Modules\Purchase\Entities\PurchaseManagementSetting::MODULE_NAME, user_modules()) && ($showProcurementMenu || $showSalesFulfillmentMenu || $showInventoryWarehouseMenu || $showProductionMenu))

    @if ($showProcurementMenu)
        <x-menu-item icon="wallet" :text="__('app.menu.procurement')" :addon="App::environment('demo')" :active="$procurementMenuActive">
            <x-slot name="iconPath">{!! $walletIconPath !!}</x-slot>
            <div class="accordionItemContent pb-2">
                <x-sub-menu-item :link="route('vendors.index')" :text="__('purchase::app.menu.vendor')" :permission="$purchaseViewVendorPermission != 'none' && $purchaseViewVendorPermission != ''" />
                <x-sub-menu-item :link="route('purchase-order.index')" :text="__('purchase::app.menu.purchaseOrder')" :permission="$purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != ''" />
                <x-sub-menu-item :link="route($grnRouteName)" :text="$grnLabel" :permission="$canViewGrn" />
                <x-sub-menu-item :link="route('bills.index')" :text="__('purchase::app.menu.bills')" :permission="$purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != ''" />
                <x-sub-menu-item :link="route('vendor-payments.index')" :text="__('purchase::app.purchaseOrder.vendorPayments')" :permission="$purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != ''" />
                <x-sub-menu-item :link="route('vendor-credits.index')" :text="__('purchase::app.menu.vendorCredits')" :permission="$purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != ''" />
            </div>
        </x-menu-item>
    @endif

    @if ($showSalesFulfillmentMenu)
        <x-menu-item icon="cart3" :text="__('app.menu.salesFulfillment')" :active="$salesFulfillmentMenuActive">
            <x-slot name="iconPath">
                <path
                    d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </x-slot>
            <div class="accordionItemContent pb-2">
                @if (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none')
                    <x-sub-menu-item :link="route('orders.index')" :text="__('app.menu.saleOrders')" />
                @endif
                <x-sub-menu-item :link="route($salesDoRouteName)" :text="$salesDoLabel" :permission="$canViewSalesDo" />
                @if (in_array('orders', user_modules()) && user()->permission('view_sales_history') === 'all')
                    <x-sub-menu-item :link="route('sales-history.index')" :text="__('app.menu.salesHistory')" :active="request()->routeIs('sales-history.*')" />
                @endif
            </div>
        </x-menu-item>
    @endif

    @if ($showInventoryWarehouseMenu)
        <x-menu-item icon="basket" :text="__('app.menu.inventoryWarehouse')" :active="$inventoryWarehouseMenuActive">
            <x-slot name="iconPath">
                <path
                    d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z" />
            </x-slot>
            <div class="accordionItemContent pb-2">
                @if ($canViewOperationsProducts)
                    <x-sub-menu-item :link="route('purchase-products.index')" :text="__('purchase::app.menu.products')" :active="request()->routeIs('purchase-products.*', 'purchase_products.*')" />
                @endif
                <x-sub-menu-item :link="route('purchase-inventory.index')" :text="__('purchase::app.menu.inventory')" :permission="$purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != ''" />
                @if (in_array('warehouse', user_modules()) && $canSeeWarehouseMaster)
                    <x-sub-menu-item :link="route('warehouse.index')" :text="__('warehouse::app.warehouses')" :permission="true" :active="request()->routeIs('warehouse.index', 'warehouse.show', 'warehouse.edit', 'warehouse.create')" />
                @endif
                @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                    <x-sub-menu-item :link="route('warehouse.stock.index')" :text="__('warehouse::app.adjustStock')" :permission="true" :active="request()->routeIs('warehouse.stock.*')" />
                @endif
                @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                    <x-sub-menu-item :link="route('warehouse.movements.index')" :text="__('warehouse::app.stockMovements')" :permission="true" :active="request()->routeIs('warehouse.movements.*')" />
                @endif
            </div>
        </x-menu-item>
    @endif

    @if ($showProductionMenu)
        <x-menu-item icon="gear" :text="__('app.menu.productionHub')" :active="$productionMenuActive">
            <x-slot name="iconPath">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z" />
                <path
                    d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z" />
            </x-slot>
            <div class="accordionItemContent pb-2">
                <x-sub-menu-item :link="route('production.orders.index')" :text="__('production::app.menuProductionOrders')" :permission="true" :active="request()->routeIs('production.orders.*', 'production.batches.*', 'production.outputs.post-fg-receipt')" />
                <x-sub-menu-item :link="route('production.boms.index')" :text="__('production::app.menuBillOfMaterials')" :permission="true" :active="request()->routeIs('production.boms.*')" />
            </div>
        </x-menu-item>
    @endif

@endif
