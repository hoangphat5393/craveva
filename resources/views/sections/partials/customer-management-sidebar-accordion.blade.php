{{-- Customer Management (leads, deals, clients, proposals, quotations) --}}
@if (!in_array('client', user_roles()) && in_array('leads', user_modules()) && (($sidebarUserPermissions['view_lead'] != 5 && $sidebarUserPermissions['view_lead'] != 'none') || ($sidebarUserPermissions['view_deals'] != 5 && $sidebarUserPermissions['view_deals'] != 'none')))
    <x-menu-item icon="person-vcard" :text="__('app.menu.sales')">
        <x-slot name="iconPath">
            <path d="M8 9.05a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
            <path d="M1 1a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h.5a.5.5 0 0 0 .5-.5.5.5 0 0 1 1 0 .5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5.5.5 0 0 1 1 0 .5.5 0 0 0 .5.5h.5a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H6.707L6 1.293A1 1 0 0 0 5.293 1zm0 1h4.293L6 2.707A1 1 0 0 0 6.707 3H15v10h-.085a1.5 1.5 0 0 0-2.4-.63C11.885 11.223 10.554 10 8 10c-2.555 0-3.886 1.224-4.514 2.37a1.5 1.5 0 0 0-2.4.63H1z" />
        </x-slot>
        <div class="accordionItemContent pb-2">
            @if ($sidebarUserPermissions['view_lead'] != 5 && $sidebarUserPermissions['view_lead'] != 'none')
                <x-sub-menu-item :link="route('lead-contact.index')" :text="__('app.leadContact')" />
            @endif

            @if ($sidebarUserPermissions['view_deals'] != 5 && $sidebarUserPermissions['view_deals'] != 'none')
                <x-sub-menu-item :link="route('deals.index')" :text="__('app.deal')" />
            @endif

            @if (!in_array('client', user_roles()) && in_array('clients', user_modules()) && $sidebarUserPermissions['view_clients'] != 5 && $sidebarUserPermissions['view_clients'] != 'none')
                <x-sub-menu-item :link="route('clients.index')" :text="__('app.menu.clients')" />
            @endif

            @if (in_array('leads', user_modules()) && $sidebarUserPermissions['view_lead_proposals'] != 5 && $sidebarUserPermissions['view_lead_proposals'] != 'none')
                <x-sub-menu-item :link="route('proposals.index')" :text="__('app.menu.proposal')" />
            @endif

            @if (in_array('estimates', user_modules()) && $sidebarUserPermissions['view_estimates'] != 5 && $sidebarUserPermissions['view_estimates'] != 'none')
                <x-sub-menu-item :link="route('estimates.index')" :text="__('app.quotation_ui.menu')" />
            @endif
        </div>
    </x-menu-item>
@endif
