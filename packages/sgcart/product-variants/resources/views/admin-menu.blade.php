@can('manage variants')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Attributes</p>
<a href="{{ route('admin.colors.index') }}" class="nav-link {{ Request::is('admin/colors*') ? 'active' : '' }}" data-tooltip="Color Swatches">
    <i class="fa-solid fa-palette"></i>
    <span class="sidebar-text">Color Swatches</span>
</a>
<a href="{{ route('admin.sizes.index') }}" class="nav-link {{ Request::is('admin/sizes*') ? 'active' : '' }}" data-tooltip="Size Labels">
    <i class="fa-solid fa-ruler-horizontal"></i>
    <span class="sidebar-text">Size Labels</span>
</a>
@endcan
