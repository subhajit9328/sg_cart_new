@can('manage coupons')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Marketing</p>
<a href="{{ route('admin.coupons.index') }}" class="nav-link {{ Request::is('admin/coupons*') ? 'active' : '' }}" data-tooltip="Coupons">
    <i class="fa-solid fa-ticket"></i>
    <span class="sidebar-text">Coupons</span>
</a>
@endcan
