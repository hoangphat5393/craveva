@aware(['inAccordion' => false])

@php
    $hrefStr = is_string($href ?? null) ? $href : '#';
    $textStr = is_string($text ?? null) ? $text : (is_array($text ?? null) ? implode(', ', $text) : (string) ($text ?? ''));
    $isLinkActive = $isActive($menu);
    $linkClass = 'f-14 text-dark-grey settings-menu-link'.($isLinkActive ? ' active' : '');
@endphp

@if ($inAccordion)
    <a {{ $attributes->merge([
        'class' => $linkClass,
        'href' => $hrefStr,
        'title' => $textStr,
    ]) }}>{{ $textStr }}</a>
@else
    <li {{ $isLinkActive ? 'class=active' : '' }} {{ $attributes->except('inAccordion') }}>
        <a class="{{ $linkClass }}" href="{{ $hrefStr }}" title="{{ $textStr }}">{{ $textStr }}</a>
    </li>
@endif
