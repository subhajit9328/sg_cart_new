@can('view reports')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Reporting</p>
<a href="{{ route('admin.reports.orders') }}" class="nav-link {{ Request::is('admin/reports/orders*') ? 'active' : '' }}" data-tooltip="Orders Report">
    <i class="fa-solid fa-chart-line"></i>
    <span class="sidebar-text">Orders</span>
</a>
<a href="{{ route('admin.reports.customers') }}" class="nav-link {{ Request::is('admin/reports/customers*') ? 'active' : '' }}" data-tooltip="Customers Report">
    <i class="fa-solid fa-users"></i>
    <span class="sidebar-text">Customers</span>
</a>
<a href="{{ route('admin.reports.revenue') }}" class="nav-link {{ Request::is('admin/reports/revenue*') ? 'active' : '' }}" data-tooltip="Revenue breakups">
    <i class="fa-solid fa-indian-rupee-sign"></i>
    <span class="sidebar-text">Revenue</span>
</a>
<a href="{{ route('admin.reports.products') }}" class="nav-link {{ Request::is('admin/reports/products*') ? 'active' : '' }}" data-tooltip="Product Performance">
    <i class="fa-solid fa-boxes-stacked"></i>
    <span class="sidebar-text">Product Performance</span>
</a>
<a href="{{ route('admin.reports.behavior') }}" class="nav-link {{ Request::is('admin/reports/behavior*') ? 'active' : '' }}" data-tooltip="Customer Behavior">
    <i class="fa-solid fa-user-gear"></i>
    <span class="sidebar-text">Customer Behavior</span>
</a>
<a href="{{ route('admin.reports.conversion') }}" class="nav-link {{ Request::is('admin/reports/conversion*') ? 'active' : '' }}" data-tooltip="Conversion Funnel">
    <i class="fa-solid fa-filter"></i>
    <span class="sidebar-text">Conversion</span>
</a>
<a href="{{ route('admin.reports.traffic') }}" class="nav-link {{ Request::is('admin/reports/traffic*') ? 'active' : '' }}" data-tooltip="Traffic Analysis">
    <i class="fa-solid fa-globe"></i>
    <span class="sidebar-text">Traffic Analysis</span>
</a>
@endcan
