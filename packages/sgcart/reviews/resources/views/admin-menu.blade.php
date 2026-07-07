@can('manage reviews')
<a href="{{ route('admin.reviews.index') }}" class="nav-link {{ Request::is('admin/reviews*') ? 'active' : '' }}" data-tooltip="Product Reviews">
    <i class="fa-solid fa-comments"></i>
    <span class="sidebar-text">Product Reviews</span>
</a>
@endcan
