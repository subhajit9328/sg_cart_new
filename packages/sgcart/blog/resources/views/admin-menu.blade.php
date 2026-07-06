@can('manage blog')
    @if(!Gate::allows('manage coupons'))
        <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Marketing</p>
    @endif

    <div class="nav-item-dropdown group-[.icon-only]:bg-blue-100/10 rounded-lg {{ (Request::is('admin/blog-posts*') || Request::is('admin/blog-categories*')) ? 'open' : '' }}">
        <button type="button" class="nav-link w-full text-left justify-between flex items-center dropdown-toggle" data-tooltip="Blog" style="background: transparent; border: none;">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-blog"></i>
                <span class="sidebar-text">Blog</span>
            </span>
            <i class="fa-solid fa-chevron-down group-[.icon-only]:hidden! text-[10px] arrow-icon transition-transform duration-200 {{ (Request::is('admin/blog-posts*') || Request::is('admin/blog-categories*')) ? 'rotate-180' : '' }}"></i>
        </button>
        <div class="dropdown-menu-items group-[.icon-only]:p-0! pl-8 pr-1 py-1 space-y-1 {{ (Request::is('admin/blog-posts*') || Request::is('admin/blog-categories*')) ? '' : 'hidden' }}">
            <a href="{{ route('admin.blog-posts.index') }}" class="nav-link py-2 text-[0.82rem] {{ Request::is('admin/blog-posts*') ? 'active' : '' }}" data-tooltip="Blog Posts">
                <i class="fa-solid fa-file-lines text-[11px]"></i>
                <span class="sidebar-text">Blog Posts</span>
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="nav-link py-2 text-[0.82rem] {{ Request::is('admin/blog-categories*') ? 'active' : '' }}" data-tooltip="Blog Categories">
                <i class="fa-solid fa-folder-open text-[11px]"></i>
                <span class="sidebar-text">Blog Categories</span>
            </a>
        </div>
    </div>
@endcan

@pushOnce('styles')
<style>
    .nav-item-dropdown .dropdown-toggle {
        cursor: pointer;
    }
    .nav-item-dropdown .arrow-icon {
        color: #94a3b8;
    }
    .nav-item-dropdown .dropdown-menu-items .nav-link {
        color: #94a3b8;
    }
    .nav-item-dropdown .dropdown-menu-items .nav-link:hover {
        color: #ffffff;
    }
    .nav-item-dropdown .dropdown-menu-items .nav-link.active {
        background: rgba(59, 130, 246, 0.15);
        color: #ffffff;
    }

    /* Collapsed sidebar (icon-only) styles */
    #sidebar.icon-only .nav-item-dropdown .arrow-icon {
        display: none !important;
    }
    #sidebar.icon-only .nav-item-dropdown.open {
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.02);
        border-radius: 0.5rem;
        margin: 4px 6px;
        padding: 4px 0;
    }
    #sidebar.icon-only .nav-item-dropdown .dropdown-menu-items {
        padding-left: 0 !important;
    }
</style>
@endpushOnce

@pushOnce('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();
            const parent = $(this).closest('.nav-item-dropdown');
            const menu = parent.find('.dropdown-menu-items');
            const arrow = $(this).find('.arrow-icon');

            parent.toggleClass('open');
            menu.toggleClass('hidden');
            arrow.toggleClass('rotate-180');
        });
    });
</script>
@endpushOnce
