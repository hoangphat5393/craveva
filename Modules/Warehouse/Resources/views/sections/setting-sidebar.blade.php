@if (user()->permission('manage_company_setting') == 'all' && in_array('warehouse', user_modules() ?? [], true))
    <x-setting-menu-item :active="$activeMenu" menu="warehouse_flow_settings" :href="route('warehouse.company-flow-settings.index')" :text="__('warehouse::app.warehouseFlowSettingsMenu')" />
@endif
