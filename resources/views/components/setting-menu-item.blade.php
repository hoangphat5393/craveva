@php
    $hrefStr = is_string($href ?? null) ? $href : '#';
    $textStr = is_string($text ?? null) ? $text : (is_array($text ?? null) ? implode(', ', $text) : (string) ($text ?? ''));
@endphp
<li {{ $isActive($menu) ? 'class=active' : '' }} {{ $attributes }}>
    <a class="d-block f-15 text-dark-grey border-bottom-grey" href="{{ $hrefStr }}">{{ $textStr }}</a>
</li>
