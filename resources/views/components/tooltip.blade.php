@props([
    'content' => '',
    'position' => 'top',
    'theme' => 'dark',
    'width' => 'w-64'
])

<span {{ $attributes->merge(['class' => 'inline-block']) }} data-tooltip="{{ $content }}" data-tooltip-position="{{ $position }}" data-tooltip-theme="{{ $theme }}" data-tooltip-width="{{ $width }}">
    {{ $slot }}
</span>
