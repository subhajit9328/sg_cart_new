@can('manage shipping')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Logistics</p>
<a href="{{ route('admin.shipping.index') }}" class="nav-link {{ Request::is('admin/shipping*') ? 'active' : '' }}" data-tooltip="Shipping Rates">
    <i class="fa-solid fa-truck"></i>
    <span class="sidebar-text">Shipping Rates</span>
</a>
@endcan
