@props([
    'liveSearch' => true,
    'size' => 8,
    'container' => 'body',
])

<select {{ $attributes->merge([
    'class' => 'form-control select-picker',
]) }} @if ($liveSearch) data-live-search="true" @endif data-size="{{ $size }}" @if ($container) data-container="{{ $container }}" @endif>
    {{ $slot }}
</select>
