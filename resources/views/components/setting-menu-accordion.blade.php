@if ($href)
    <li @class([
        'accordionItem',
        'settings-menu-accordion',
        'settings-menu-single-link',
        'closeIt',
    ])>
        <a @class([
            'nav-item',
            'accordionItemHeading',
            'f-15',
            'text-dark-grey',
            'settings-sidebar-heading',
            'active' => $isHeadingActive(),
        ]) href="{{ $href }}" title="{{ $title }}">
            <span>{{ $title }}</span>
        </a>
    </li>
@else
    <li @class([
        'accordionItem',
        'settings-menu-accordion',
        'openIt' => $open,
        'closeIt' => ! $open,
    ])>
        <a class="nav-item accordionItemHeading f-15 text-dark-grey settings-sidebar-heading" href="javascript:;" title="{{ $title }}">
            <span>{{ $title }}</span>
        </a>
        <div class="accordionItemContent pb-2">
            {{ $slot }}
        </div>
    </li>
@endif
