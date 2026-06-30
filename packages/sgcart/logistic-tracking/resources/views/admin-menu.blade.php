@can('manage products')
@if(!class_exists(\SGCart\Shipping\Providers\ShippingServiceProvider::class))
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Logistics</p>
@endif
<a href="{{ route('admin.couriers.index') }}" class="nav-link {{ Request::is('admin/couriers*') ? 'active' : '' }}" data-tooltip="Shipping Couriers">
    <i class="fa-solid fa-truck-fast"></i>
    <span class="sidebar-text">Shipping Carriers</span>
</a>
@endcan
