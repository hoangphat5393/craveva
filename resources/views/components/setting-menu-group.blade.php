@props(['text'])
<li {{ $attributes->merge(['class' => 'settings-menu-group']) }}>
    <span class="settings-menu-group-label">{{ $text }}</span>
</li>
