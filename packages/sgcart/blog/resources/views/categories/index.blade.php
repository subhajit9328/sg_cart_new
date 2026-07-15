@extends('layouts.admin')

@section('title', 'Blog Categories — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Blog Categories</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Blog'],
            ['label' => 'Categories']
        ]" />
    </div>
    <a href="{{ route('admin.blog-categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-plus"></i> Add Category
    </a>
</div>

@php
    $headers = [
        ['label' => 'Name', 'key' => 'name', 'sortable' => false],
        ['label' => 'Slug', 'key' => 'slug', 'sortable' => false],
        ['label' => 'Status', 'key' => 'is_active', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Blog Categories"
    :totalCount="$categories->total()"
    searchPlaceholder="Search categories..."
    action="{{ route('admin.blog-categories.index') }}"
    tableId="blogCategoriesTableWrapper"
    searchInputId="categorySearchInput"
    totalCountId="categoriesTotalCount"
    :items="$categories"
    :headers="$headers"
>
    @forelse($categories as $category)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-4 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                {{ $category->name }}
            </td>
            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                {{ $category->slug }}
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
                @if($category->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20">
                        Disabled
                    </span>
                @endif
            </td>
            <td class="px-5 py-4 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5">
                    <a href="{{ route('admin.blog-categories.edit', $category->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Category">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    
                    <button onclick="openDeleteModal('{{ $category->id }}', '{{ addslashes($category->name) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Category">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20 block"></i>
                No blog categories found. Click "Add Category" to create one.
            </td>
        </tr>
    @endforelse
</x-data-table>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(categoryId, name) {
        showConfirm(
            `Are you sure you want to delete blog category "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/blog-categories/${categoryId}`;
                form.submit();
            },
            'Delete Blog Category?'
        );
    }
</script>
@endsection
