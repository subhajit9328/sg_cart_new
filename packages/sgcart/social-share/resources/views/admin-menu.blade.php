@can('manage social shares')
<a href="{{ route('admin.social-shares.index') }}" class="nav-link {{ Request::is('admin/social-shares*') ? 'active' : '' }}" data-tooltip="Influencer Posts">
    <i class="fa-solid fa-share-nodes"></i>
    <span class="sidebar-text">Influencer Posts</span>
</a>
@endcan
