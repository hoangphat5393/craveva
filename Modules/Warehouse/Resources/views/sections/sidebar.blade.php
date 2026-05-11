{{-- Menu chính dùng purchase::sections.sidebar (Operations). File này giữ để tái sử dụng / include tùy chỉnh. --}}
@php
    $viewWarehouses = user()->permission('view_warehouses');
    $addWarehouses = user()->permission('add_warehouses');
    $viewWarehouseStock = user()->permission('view_warehouse_stock');
    $addWarehouseStock = user()->permission('add_warehouse_stock');
    $manageWarehouseTransfer = user()->permission('manage_warehouse_transfer');
    $canSeeWarehouseTransferUi = $manageWarehouseTransfer && $manageWarehouseTransfer != 'none';
    $hasWarehouseMenu = ($viewWarehouses && $viewWarehouses != 'none') || ($addWarehouses && $addWarehouses != 'none') || ($viewWarehouseStock && $viewWarehouseStock != 'none') || ($addWarehouseStock && $addWarehouseStock != 'none') || ($manageWarehouseTransfer && $manageWarehouseTransfer != 'none');
@endphp

@if (in_array('warehouse', user_modules()) && $hasWarehouseMenu)

    <x-menu-item icon="box-seam" :text="__('warehouse::app.menu')" :addon="App::environment('demo')">
        <x-slot name="iconPath">
            <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.571v7.923l6.5-2.241v-8.253zm-7.5 10.514L1 11.732V4.464l6.5 2.571v7.923zM15 4.464v7.253l-6.5 2.571V7.03l6.5-2.566z" />
        </x-slot>

        <div class="accordionItemContent pb-2">
            @if ($viewWarehouses && $viewWarehouses != 'none')
                <x-sub-menu-item :link="route('warehouse.index')" :text="__('warehouse::app.warehouses')" :permission="true" :active="request()->routeIs('warehouse.index', 'warehouse.show', 'warehouse.edit', 'warehouse.create')" />
            @endif

            @if ($viewWarehouseStock && $viewWarehouseStock != 'none')
                <x-sub-menu-item :link="route('warehouse.stock.index')" :text="__('warehouse::app.adjustStock')" :permission="true" :active="request()->routeIs('warehouse.stock.*')" />
                <x-sub-menu-item :link="route('warehouse.product-batches.index')" :text="__('warehouse::app.warehouseBatchInventory')" :permission="true" :active="request()->routeIs('warehouse.product-batches.*')" />
            @endif

            @if ($canSeeWarehouseTransferUi)
                <x-sub-menu-item :link="route('warehouse.transfer.create')" :text="__('warehouse::app.transferStock')" :permission="true" :active="request()->routeIs('warehouse.transfer.*')" />
            @endif

            @if ($viewWarehouseStock && $viewWarehouseStock != 'none')
                <x-sub-menu-item :link="route('warehouse.movements.index')" :text="__('warehouse::app.stockMovements')" :permission="true" :active="request()->routeIs('warehouse.movements.*')" />
            @endif

        </div>
    </x-menu-item>

@endif
