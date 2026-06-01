@props([
    'title',
    'open' => false,
])

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
