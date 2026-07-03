@can('view reports')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Reporting</p>
<a href="{{ route('admin.reports.orders') }}" class="nav-link {{ Request::is('admin/reports*') ? 'active' : '' }}" data-tooltip="Orders Report">
    <i class="fa-solid fa-chart-line"></i>
    <span class="sidebar-text">Orders</span>
</a>
@endcan
