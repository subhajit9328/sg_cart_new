@extends('layouts.admin')

@section('title', 'Product Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Products</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Products']
        ]" />
    </div>
    <div class="flex gap-3">
        {{-- <button onclick="showToast('Coming soon', 'warning')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-medium shadow-lg shadow-emerald-600/10 transition-colors cursor-pointer border-none">
            <i class="fa-solid fa-file-import"></i> Bulk Upload
        </button> --}}
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
    </div>
</div>

@php
    $headers = [
        ['label' => 'Image', 'key' => 'image', 'sortable' => false, 'width' => '20'],
        ['label' => 'Product Name', 'key' => 'name', 'sortable' => true],
        ['label' => 'SKU', 'key' => 'sku', 'sortable' => true],
        ['label' => 'Seller', 'key' => 'seller_id', 'sortable' => false],
        ['label' => 'Category', 'key' => 'category_id', 'sortable' => false],
        ['label' => 'Price', 'key' => 'price', 'sortable' => true],
        ['label' => 'Stock', 'key' => 'stock', 'sortable' => true],
        ['label' => 'Status', 'key' => 'status', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="All Products"
    :totalCount="$products->total()"
    searchPlaceholder="Search products…"
    action="{{ route('admin.products.index') }}"
    tableId="productsTableWrapper"
    searchInputId="productSearchInput"
    totalCountId="productsTotalCount"
    clearBtnId="productsClearBtn"
    clearBtnWrapperId="productsClearBtnWrapper"
    :items="$products"
    :headers="$headers"
    :filterKeys="['category_id', 'status']"
>
    <x-slot name="filters">
        <!-- Category Filter Dropdown -->
        <div class="min-w-[180px]">
            <x-select2 
                name="category_id" 
                id="category_id_filter"
                placeholder="All Categories"
                :options="$categories"
                optionValue="id"
                optionLabel="name"
                :selected="request('category_id')"
                :compact="true"
                :allowClear="false"
            />
        </div>

        @if(!empty($sellers) && count($sellers) > 0)
        <!-- Seller Filter Dropdown -->
        <div class="min-w-[160px]">
            <x-select2 
                name="seller_id" 
                id="seller_id_filter"
                placeholder="All Sellers"
                :selected="request('seller_id')"
                :compact="true"
                :allowClear="false"
                :searchable="true"
            >
                <option value="">All Sellers</option>
                <option value="admin" {{ request('seller_id') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                @foreach($sellers as $sel)
                    <option value="{{ $sel->id }}" {{ request('seller_id') == $sel->id ? 'selected' : '' }}>{{ $sel->shop_name }}</option>
                @endforeach
            </x-select2>
        </div>
        @endif

        <!-- Status Filter Dropdown -->
        <div class="min-w-[130px]">
            <x-select2 
                name="status" 
                id="status_filter"
                placeholder="All Status"
                :selected="request('status')"
                :compact="true"
                :allowClear="false"
                :searchable="false"
            >
                @foreach(['draft','active','inactive'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </x-select2>
        </div>
    </x-slot>

    @forelse($products as $product)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="w-9 h-9 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                @else
                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 dark:border-slate-700/50">
                        NO IMG
                    </div>
                @endif
            </td>
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 text-sm" title="{{ $product->name }}">
                {{ \Illuminate\Support\Str::limit($product->name, 30) }}
            </td>
            <td class="px-5 py-3.5 text-slate-550 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                {{ $product->sku }}
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-650 dark:text-slate-350 whitespace-nowrap">
                @if($product->seller)
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/20" title="Seller Product: {{ $product->seller->shop_name }}">
                        <i class="fa-solid fa-store text-[9px] opacity-75"></i>
                        {{ \Illuminate\Support\Str::limit($product->seller->shop_name, 18) }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        <i class="fa-solid fa-shield-halved text-[9px] opacity-75"></i>
                        SGCart
                    </span>
                @endif
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                {{ $product->category?->name ?? '—' }}
            </td>
            <td class="px-5 py-3.5 text-slate-800 dark:text-slate-200 font-semibold whitespace-nowrap text-xs">
                ₹{{ number_format($product->price, 2) }}
                @if($product->sale_price)
                    <span class="text-emerald-600 dark:text-emerald-400 text-[9px] block font-semibold mt-0.5">Sale: ₹{{ number_format($product->sale_price, 2) }}</span>
                @endif
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($product->stock > 10)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30">
                        {{ $product->stock }} In Stock
                    </span>
                @elseif($product->stock > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/30">
                        {{ $product->stock }} Low Stock
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/30">
                        Out of Stock
                    </span>
                @endif
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold 
                    {{ $product->status->value === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' :
                      ($product->status->value === 'draft'  ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/20' : 
                      ($product->status->value === 'rejected' ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20' :
                      'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-350 border border-slate-200/50')) }}">
                    {{ ucfirst($product->status->value) }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    @if($product->status->value === 'active')
                        <a href="{{ route('store.product', $product->slug) }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Preview Product">
                            <i class="fa-solid fa-arrow-up-right-from-square text-slate-500 dark:text-slate-400 text-xs"></i>
                        </a>
                    @else
                        <span class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-center transition-colors cursor-not-allowed" title="Product must be active to preview">
                            <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 dark:text-slate-650 text-xs"></i>
                        </span>
                    @endif
                    <a href="{{ route('admin.products.show', $product->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="View Product details">
                        <i class="fa-solid fa-eye text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <a href="{{ route('admin.products.edit', $product->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Product">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <button onclick="openDeleteModal('{{ $product->ulid }}', '{{ addslashes($product->name) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer animate-fadeIn" title="Delete Product">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20 block"></i>
                No products found matching the criteria.
            </td>
        </tr>
    @endforelse
</x-data-table>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(productId, name) {
        showConfirm(
            `Are you sure you want to delete product "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/products/${productId}`;
                form.submit();
            },
            'Delete Product?'
        );
    }
</script>
@endsection