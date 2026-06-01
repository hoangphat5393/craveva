@aware(['inAccordion' => false])

@php
    $hrefStr = is_string($href ?? null) ? $href : '#';
    $textStr = is_string($text ?? null) ? $text : (is_array($text ?? null) ? implode(', ', $text) : (string) ($text ?? ''));
    $isLinkActive = $isActive($menu);
    $linkClass = ($heading ?? false)
        ? 'nav-item f-15 text-dark-grey settings-sidebar-heading settings-menu-link settings-menu-heading-link'.($isLinkActive ? ' active' : '')
        : 'f-14 text-dark-grey settings-menu-link'.($isLinkActive ? ' active' : '');
    $listItemClass = ($heading ?? false) ? 'settings-menu-heading-item' : '';
    if (! ($heading ?? false) && $isLinkActive) {
        $listItemClass = trim($listItemClass.' active');
    }
@endphp

@if ($inAccordion)
    <a {{ $attributes->merge([
        'class' => $linkClass,
        'href' => $hrefStr,
        'title' => $textStr,
    ]) }}>{{ $textStr }}</a>
@else
    <li @class([$listItemClass => $listItemClass !== '']) {{ $attributes->except('inAccordion', 'heading') }}>
        <a class="{{ $linkClass }}" href="{{ $hrefStr }}" title="{{ $textStr }}">
            @if ($heading ?? false)
                <span>{{ $textStr }}</span>
            @else
                {{ $textStr }}
            @endif
        </a>
    </li>
@endif
