@php
    $resolvedHeaderColor = '#1d82f5';
    if (isset($appTheme) && $appTheme && !empty($appTheme->header_color)) {
        $resolvedHeaderColor = $appTheme->header_color;
    } elseif (isset($company) && $company && !empty($company->header_color)) {
        $resolvedHeaderColor = $company->header_color;
    } else {
        $resolvedHeaderColor = global_setting()->header_color ?? $resolvedHeaderColor;
    }
    $resolvedHeaderColor = trim((string) $resolvedHeaderColor);
    if ($resolvedHeaderColor !== '' && !str_starts_with($resolvedHeaderColor, '#')) {
        $resolvedHeaderColor = '#' . $resolvedHeaderColor;
    }
@endphp
<style>
    :root {
        /* Logged-in: ThemeSetting / company; fallback matches global default */
        --header_color: {{ $resolvedHeaderColor }};
    }

    /* Staging had custom-css overriding .sidebar-dark to white; force dark palette when dark sidebar is selected. */
    .sidebar-dark .main-sidebar,
    .sidebar-dark .sidebar-brand-box,
    .sidebar-dark .sidebar-menu,
    .sidebar-dark .sidebarTogglerBox {
        background-color: #171f29 !important;
    }

    .sidebar-dark .sidebar-menu li .nav-item,
    .sidebar-dark .sidebar-menu li .accordionItemContent a,
    .sidebar-dark .sidebar-brand-name h1,
    .sidebar-dark .sidebar-brand-logo {
        color: #f7faff !important;
    }

    /* Sidebar light: SCSS alone is near-white; tie panel to brand color (visible without re-login). */
    .sidebar-light .main-sidebar {
        background-color: #f1f5f9;
        background-color: color-mix(in srgb, var(--header_color) 9%, #f8fafc);
        box-shadow: inset 3px 0 0 0 var(--header_color);
    }

    .sidebar-light .sidebar-brand-box .sidebar-brand {
        border-bottom-color: color-mix(in srgb, var(--header_color) 22%, #e2e8f0);
    }

    /* Current route: JS adds .active to .nav-item */
    .sidebar-light .sidebar-menu a.nav-item.active {
        color: var(--header_color) !important;
        background-color: color-mix(in srgb, var(--header_color) 16%, #ffffff) !important;
        border-radius: 8px;
        margin-left: 8px;
        margin-right: 8px;
        width: calc(100% - 16px) !important;
    }

    .sidebar-dark .sidebar-menu a.nav-item.active {
        background-color: color-mix(in srgb, var(--header_color) 35%, transparent) !important;
        color: #f8fafc !important;
        border-radius: 8px;
    }

    /*
     * Submenu items use <a> inside .accordionItemContent without .nav-item — server sets .active via SubMenuItem.
     * Hover must stay subtler than .active so users can tell current page vs hover (sidebar.scss uses strong :hover).
     */
    .sidebar-light .sidebar-menu li .accordionItemContent a.active {
        color: var(--header_color) !important;
        background-color: color-mix(in srgb, var(--header_color) 18%, #ffffff) !important;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: inset 3px 0 0 0 var(--header_color);
    }

    .sidebar-light .sidebar-menu li .accordionItemContent a:hover:not(.active) {
        color: color-mix(in srgb, var(--header_color) 70%, #334155) !important;
        background-color: color-mix(in srgb, var(--header_color) 7%, #ffffff) !important;
        font-weight: 500;
    }

    .sidebar-dark .sidebar-menu li .accordionItemContent a.active {
        background-color: color-mix(in srgb, var(--header_color) 38%, rgba(255, 255, 255, 0.08)) !important;
        color: #f8fafc !important;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: inset 3px 0 0 0 var(--header_color);
    }

    .sidebar-dark .sidebar-menu li .accordionItemContent a:hover:not(.active) {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #f7faff !important;
        font-weight: 500;
    }

    .sidebar-dark .sidebar-menu li .nav-item:hover:not(.active),
    .sidebar-light .sidebar-menu li .nav-item:hover:not(.active) {
        opacity: 0.92;
    }

    .btn-primary,
    .btn-primary.disabled:hover,
    .btn-primary:disabled:hover {
        background-color: var(--header_color) !important;
        border: 1px solid var(--header_color) !important;
    }

    .text-primary {
        color: var(--header_color) !important;
    }

    .bg-primary {
        background: var(--header_color) !important;
    }

    .datepicker table tr td,
    .datepicker table tr th {
        font-size: 14px;
    }

    .project-header .project-menu .p-sub-menu.active:after,
    .project-header .project-menu .p-sub-menu::after,
    .qs-current,
    .datepicker table tr td.active.active {
        background: var(--header_color) !important;
        text-shadow: none;
        border-color: var(--header_color) !important;
    }

    .sidebar-brand-box .sidebar-brand-dropdown a.dropdown-item:hover,
    .dropdown-item.active,
    .close-task-detail {
        background-color: var(--header_color) !important;
    }

    .pagination .page-item.active .page-link,
    .custom-control-input:checked~.custom-control-label::before {
        background-color: var(--header_color) !important;
        border-color: var(--header_color) !important;
    }

    .close-task-detail span {
        border: 1px solid var(--header_color) !important;
    }

    .tabs .nav .nav-link.active,
    .tabs .nav .nav-item:hover {
        border-bottom: 3px solid var(--header_color) !important;
    }

    .sidebar-light .sidebar-menu li .nav-item:focus,
    .sidebar-light .sidebar-menu li .nav-item:hover:not(.active) {
        color: color-mix(in srgb, var(--header_color) 75%, #475569) !important;
    }

    .sidebar-light .accordionItem a.active {
        color: var(--header_color) !important;
    }

    .menu-item-count,
    .unread-notifications-count,
    .active-timer-count {
        background-color: var(--header_color) !important;
    }

    .dropdown-item.active .text-dark-grey {
        color: #ffffff;
    }
</style>
