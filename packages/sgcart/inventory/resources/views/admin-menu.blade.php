@can('manage inventory')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Inventory</p>
<a href="{{ route('admin.inventory.index') }}" class="nav-link {{ Request::is('admin/inventory*') ? 'active' : '' }}" data-tooltip="Inventory Management">
    <i class="fa-solid fa-boxes-stacked"></i>
    <span class="sidebar-text">Inventory Management</span>
</a>
@endcan
