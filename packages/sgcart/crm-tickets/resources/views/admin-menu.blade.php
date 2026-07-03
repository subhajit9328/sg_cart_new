@can('view tickets')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Support</p>
<a href="{{ route('admin.tickets.index') }}" class="nav-link {{ Request::is('admin/tickets*') ? 'active' : '' }}" data-tooltip="Support Tickets">
    <i class="fa-solid fa-headset"></i>
    <span class="sidebar-text">Support Tickets</span>
</a>
@endcan
