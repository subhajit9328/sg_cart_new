@can('manage hero section')
<a href="{{ route('admin.hero.settings') }}" class="nav-link {{ Request::is('admin/hero-settings*') ? 'active' : '' }}" data-tooltip="Hero Section">
    <i class="fa-solid fa-images"></i>
    <span class="sidebar-text">Hero Section</span>
</a>
@endcan
