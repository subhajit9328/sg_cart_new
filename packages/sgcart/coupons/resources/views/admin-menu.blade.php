@can('manage coupons')
<a href="{{ route('admin.coupons.index') }}" class="nav-link {{ Request::is('admin/coupons*') ? 'active' : '' }}" data-tooltip="Coupons">
    <i class="fa-solid fa-ticket"></i>
    <span class="sidebar-text">Coupons</span>
</a>
@endcan
