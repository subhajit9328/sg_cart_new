@can('manage tax')
<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Finance</p>
<a href="{{ route('admin.tax.index') }}" class="nav-link {{ Request::is('admin/tax*') ? 'active' : '' }}" data-tooltip="Tax Rates">
    <i class="fa-solid fa-percent"></i>
    <span class="sidebar-text">Tax Rates</span>
</a>
@endcan
