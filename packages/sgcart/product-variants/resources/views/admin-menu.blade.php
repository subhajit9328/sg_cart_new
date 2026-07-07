@can('manage variants')
    @if(config('product-variants.features.color', true) || config('product-variants.features.size', true))
        @if(config('product-variants.features.color', true))
            <a href="{{ route('admin.colors.index') }}" class="nav-link {{ Request::is('admin/colors*') ? 'active' : '' }}" data-tooltip="Color Swatches">
                <i class="fa-solid fa-palette"></i>
                <span class="sidebar-text">Color Swatches</span>
            </a>
        @endif
        @if(config('product-variants.features.size', true))
            <a href="{{ route('admin.sizes.index') }}" class="nav-link {{ Request::is('admin/sizes*') ? 'active' : '' }}" data-tooltip="Size Labels">
                <i class="fa-solid fa-ruler-horizontal"></i>
                <span class="sidebar-text">Size Labels</span>
            </a>
        @endif
    @endif
@endcan
