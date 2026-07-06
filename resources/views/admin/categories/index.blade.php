@extends('layouts.admin')

@section('title', 'Category Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Categories</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Categories']
        ]" />
    </div>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-plus"></i> Add Category
    </a>
</div>

@php
    $headers = [
        ['label' => 'Category Name', 'key' => 'name', 'sortable' => true],
        ['label' => 'Parent Category', 'key' => 'parent_id', 'sortable' => false],
        ['label' => 'Status', 'key' => 'is_active', 'sortable' => true],
        ['label' => 'Sort Order', 'key' => 'sort_order', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="All Categories"
    :totalCount="$categories->total()"
    searchPlaceholder="Search categories…"
    action="{{ route('admin.categories.index') }}"
    tableId="categoriesTableWrapper"
    searchInputId="categorySearchInput"
    totalCountId="categoriesTotalCount"
    :items="$categories"
    :headers="$headers"
>
    @forelse($categories as $cat)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm">
                <div class="flex items-center gap-3">
                    @if($cat->image)
                        <img src="{{ Storage::url($cat->image) }}" class="w-8 h-8 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                    @else
                        <div class="w-8 h-8 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center text-xs font-bold border border-blue-100 dark:border-blue-900/50 select-none">
                            {{ \App\Helpers\AvatarHelper::getInitials($cat->name) }}
                        </div>
                    @endif
                    <span>{{ $cat->name }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                {{ $cat->parent?->name ?? '—' }}
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                    {{ $cat->is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20' }}">
                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                {{ $cat->sort_order }}
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    <a href="{{ route('admin.categories.edit', $cat->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Category">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <button onclick="openDeleteModal('{{ $cat->ulid }}', '{{ addslashes($cat->name) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer animate-fadeIn" title="Delete Category">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20 block"></i>
                No categories found matching the criteria.
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
            `Are you sure you want to delete category "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/categories/${categoryId}`;
                form.submit();
            },
            'Delete Category?'
        );
    }
</script>
@endsection