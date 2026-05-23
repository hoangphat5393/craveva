@if (user()->permission('manage_company_setting') == 'all' && \Modules\Production\Support\ProductionTenantAccess::tenantMayUseProduction())
    <x-setting-menu-item :active="$activeMenu" menu="production_fg_quantity_policy" :href="route('production.fg-quantity-policy.index')" :text="__('production::app.productionSettingsMenu')" />
@endif
