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
        <a href="{{ route('admin.categories.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
            <i class="fa-solid fa-plus"></i> Add Category
        </a>
    </div>

    @php
        $headers = [
            ['label' => '', 'key' => 'drag', 'sortable' => false],
            ['label' => 'Category Name', 'key' => 'name', 'sortable' => true],
            ['label' => 'Parent Category', 'key' => 'parent_id', 'sortable' => false],
            ['label' => 'Status', 'key' => 'is_active', 'sortable' => false],
            ['label' => 'Sort Order', 'key' => 'sort_order', 'sortable' => true],
            ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
        ];
    @endphp

    <x-nested-table title="All Categories" :totalCount="$categories->total()" searchPlaceholder="Search categories…"
        action="{{ route('admin.categories.index') }}" tableId="categoriesTableWrapper" searchInputId="categorySearchInput"
        totalCountId="categoriesTotalCount" :items="$categories" :headers="$headers" reorderRoute="admin.categories.reorder" editRoute="admin.categories.edit" />

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