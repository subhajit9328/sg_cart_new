@extends('layouts.admin')

@section('title', 'Blog Posts — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Blog Posts</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Blog'],
            ['label' => 'Posts']
        ]" />
    </div>
    <a href="{{ route('admin.blog-posts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-plus"></i> Add Post
    </a>
</div>

@php
    $headers = [
        ['label' => 'Image', 'key' => 'image', 'sortable' => false],
        ['label' => 'Title', 'key' => 'title', 'sortable' => false],
        ['label' => 'Category', 'key' => 'blog_category_id', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
        ['label' => 'Published At', 'key' => 'published_at', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Blog Posts"
    :totalCount="$posts->total()"
    searchPlaceholder="Search posts..."
    action="{{ route('admin.blog-posts.index') }}"
    tableId="blogPostsTableWrapper"
    searchInputId="postSearchInput"
    totalCountId="postsTotalCount"
    :items="$posts"
    :headers="$headers"
>
    @forelse($posts as $post)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-4 whitespace-nowrap">
                @if($post->featured_image)
                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-12 h-12 object-cover rounded-lg border border-slate-200 dark:border-slate-800">
                @else
                    <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-800">
                        <i class="fa-solid fa-image text-slate-400"></i>
                    </div>
                @endif
            </td>
            <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-100">
                <div class="max-w-[300px] truncate" title="{{ $post->title }}">
                    {{ $post->title }}
                </div>
            </td>
            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                {{ $post->category?->name ?? 'Uncategorized' }}
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
                @if($post->status === 'published')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        Published
                    </span>
                @elseif($post->status === 'draft')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/20">
                        Draft
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-50 dark:bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-200/20">
                        Archived
                    </span>
                @endif
            </td>
            <td class="px-5 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                {{ $post->published_at ? $post->published_at->format('d M Y H:i') : 'N/A' }}
            </td>
            <td class="px-5 py-4 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5">
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="View Post">
                        <i class="fa-solid fa-eye text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <a href="{{ route('admin.blog-posts.edit', $post->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Post">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    
                    <button onclick="openDeleteModal('{{ $post->id }}', '{{ addslashes($post->title) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Post">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-file-lines text-4xl mb-3 opacity-20 block"></i>
                No blog posts found. Click "Add Post" to create one.
            </td>
        </tr>
    @endforelse
</x-data-table>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(postId, title) {
        showConfirm(
            `Are you sure you want to delete blog post "${title}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/blog-posts/${postId}`;
                form.submit();
            },
            'Delete Blog Post?'
        );
    }
</script>
@endsection
