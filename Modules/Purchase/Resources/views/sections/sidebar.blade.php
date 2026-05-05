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
    $addWarehouseStock = user()->permission('add_warehouse_stock');
    $manageWarehouseTransfer = user()->permission('manage_warehouse_transfer');
    $canSeeWarehouseMaster = ($viewWarehouses && $viewWarehouses != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canSeeWarehouseStockUi = ($viewWarehouseStock && $viewWarehouseStock != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canSeeWarehouseTransferUi = ($manageWarehouseTransfer && $manageWarehouseTransfer != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canViewOperationsProducts = in_array('client', user_roles())
        ? in_array('orders', user_modules()) && user()->permission('add_order') == 'all' && isset($sidebarUserPermissions['view_product']) && !in_array($sidebarUserPermissions['view_product'], [5, 'none'])
        : in_array('products', user_modules()) && isset($sidebarUserPermissions['view_product']) && !in_array($sidebarUserPermissions['view_product'], [5, 'none']);
    $viewProductionOrders = user()->permission('view_production_orders');
    $canViewProductionOrders = \Modules\Production\Support\ProductionTenantAccess::tenantMayUseProduction() && isset($viewProductionOrders) && $viewProductionOrders != 'none' && $viewProductionOrders != '';
    $operationsMenuActive = request()->routeIs('orders.*', 'vendors.*', 'purchase-products.*', 'purchase_products.*', 'purchase-order.*', 'delivery-orders.*', 'sales-shipments.*', 'bills.*', 'vendor-payments.*', 'vendor-credits.*', 'purchase-inventory.*', 'warehouse.*', 'warehouse.stock.*', 'warehouse.transfer.*', 'warehouse.movements.*', 'grn.*', 'sales-do.*', 'sales-history.*', 'production.*');
@endphp
@if (in_array(\Modules\Purchase\Entities\PurchaseManagementSetting::MODULE_NAME, user_modules()) &&
        ($purchaseViewVendorPermission != 'none' ||
            $purchaseViewOrderPermission != 'none' ||
            $purchaseViewBillPermission != 'none' ||
            $purchaseViewCreditPermission != 'none' ||
            $purchaseViewInventoryPermission != 'none' ||
            $purchaseViewOrderReportPermission != 'none' ||
            $purchaseViewPaymentPermission != 'none' ||
            $canViewSalesDo ||
            $canViewGrn ||
            $canViewOperationsProducts ||
            (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 'none') ||
            (in_array('orders', user_modules()) && isset($sidebarUserPermissions['view_sales_history']) && $sidebarUserPermissions['view_sales_history'] != 'none') ||
            $canViewProductionOrders))

    <x-menu-item icon="wallet" :text="__('app.menu.operations')" :addon="App::environment('demo')" :active="$operationsMenuActive">
        <x-slot name="iconPath">
            <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.6.6 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.6.6 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.6.6 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535z" />
            <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.6.6 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0z" />
        </x-slot>

        <div class="accordionItemContent pb-2">

            {{-- Thứ tự Operations: Mua hàng → Bán hàng → Sản phẩm → Tồn kho / kho vật lý (đồng bộ LanguagePack vi) --}}

            {{-- 1–6: Procurement --}}
            <x-sub-menu-item :link="route('vendors.index')" :text="__('purchase::app.menu.vendor')" :permission="$purchaseViewVendorPermission != 'none' && $purchaseViewVendorPermission != ''" />

            <x-sub-menu-item :link="route('purchase-order.index')" :text="__('purchase::app.menu.purchaseOrder')" :permission="$purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != ''" />

            <x-sub-menu-item :link="route($grnRouteName)" :text="$grnLabel" :permission="$canViewGrn" />

            <x-sub-menu-item :link="route('bills.index')" :text="__('purchase::app.menu.bills')" :permission="$purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != ''" />

            <x-sub-menu-item :link="route('vendor-payments.index')" :text="__('purchase::app.purchaseOrder.vendorPayments')" :permission="$purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != ''" />

            <x-sub-menu-item :link="route('vendor-credits.index')" :text="__('purchase::app.menu.vendorCredits')" :permission="$purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != ''" />

            {{-- 7–9: Sales --}}
            @if (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none')
                <x-sub-menu-item :link="route('orders.index')" :text="__('app.menu.saleOrders')" />
            @endif

            <x-sub-menu-item :link="route($salesDoRouteName)" :text="$salesDoLabel" :permission="$canViewSalesDo" />

            @if (in_array('orders', user_modules()) && user()->permission('view_sales_history') === 'all')
                <x-sub-menu-item :link="route('sales-history.index')" :text="__('app.menu.salesHistory')" :active="request()->routeIs('sales-history.*')" />
            @endif

            {{-- 10: Products (đầu nhóm master data / danh mục) --}}
            @if ($canViewOperationsProducts)
                <x-sub-menu-item :link="route('purchase-products.index')" :text="__('purchase::app.menu.products')" :active="request()->routeIs('purchase-products.*', 'purchase_products.*')" />
            @endif

            {{-- 11–15: Inventory & warehouse --}}
            <x-sub-menu-item :link="route('purchase-inventory.index')" :text="__('purchase::app.menu.inventory')" :permission="$purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != ''" />

            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseMaster)
                <x-sub-menu-item :link="route('warehouse.index')" :text="__('warehouse::app.warehouses')" :permission="true" :active="request()->routeIs('warehouse.index', 'warehouse.show', 'warehouse.edit', 'warehouse.create')" />
            @endif
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseTransferUi)
                <x-sub-menu-item :link="route('warehouse.transfer.create')" :text="__('warehouse::app.transferStock')" :permission="true" :active="request()->routeIs('warehouse.transfer.*')" />
            @endif
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                <x-sub-menu-item :link="route('warehouse.stock.index')" :text="__('warehouse::app.adjustStock')" :permission="true" :active="request()->routeIs('warehouse.stock.*')" />
            @endif
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                <x-sub-menu-item :link="route('warehouse.movements.index')" :text="__('warehouse::app.stockMovements')" :permission="true" :active="request()->routeIs('warehouse.movements.*')" />
            @endif

            @if ($canViewProductionOrders)
                <x-sub-menu-item :link="route('production.orders.index')" :text="__('production::app.menuProductionOrders')" :permission="true" :active="request()->routeIs('production.*')" />
            @endif

            <x-sub-menu-item :link="route('reports.index')" :text="__('purchase::app.menu.reports')" :permission="$purchaseViewOrderReportPermission != 'none' && $purchaseViewOrderReportPermission != '' && false" />

        </div>

    </x-menu-item>

@endif
