@extends('marketplace::layouts.seller')

@section('title', 'My Products — Seller Portal')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">My Products</h1>
        <p class="text-sm text-slate-400 mt-0.5">Manage your shop product list, inventory levels, and details.</p>
    </div>
    <a href="{{ route('seller.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-plus"></i> Add Product
    </a>
</div>

@php
    $headers = [
        ['label' => 'Product details', 'key' => 'name', 'sortable' => false],
        ['label' => 'Category', 'key' => 'category', 'sortable' => false],
        ['label' => 'Price', 'key' => 'price', 'sortable' => false],
        ['label' => 'Stock', 'key' => 'stock', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Shop Products Catalog"
    :totalCount="$products->total()"
    searchPlaceholder="Search products…"
    action="{{ route('seller.products.index') }}"
    tableId="productsTableWrapper"
    searchInputId="productSearchInput"
    totalCountId="productsTotalCount"
    clearBtnId="productsClearBtn"
    clearBtnWrapperId="productsClearBtnWrapper"
    :items="$products"
    :headers="$headers"
>
    @forelse($products as $p)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-medium whitespace-nowrap">
                <div class="flex items-center gap-3">
                    @if($p->image)
                        <img src="{{ Storage::url($p->image) }}" class="w-8 h-8 object-cover rounded border border-slate-200 dark:border-slate-800 bg-white">
                    @elseif($p->images && $p->images->isNotEmpty())
                        <img src="{{ Storage::url($p->images->sortByDesc('is_default')->first()->image_path) }}" class="w-8 h-8 object-cover rounded border border-slate-200 dark:border-slate-800 bg-white">
                    @else
                        <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800 rounded flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 dark:border-slate-700/50">
                            NO IMG
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ $p->name }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-mono">SKU: {{ $p->sku }}</p>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $p->category->name ?? 'N/A' }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-semibold">${{ number_format($p->price, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->stock }} units</td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                @php
                    $statusClass = 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                    if ($p->status->value === 'active') {
                        $statusClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400';
                    } elseif ($p->status->value === 'pending_approval') {
                        $statusClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400';
                    } elseif ($p->status->value === 'rejected') {
                        $statusClass = 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-450';
                    }
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusClass }}">
                    {{ str_replace('_', ' ', $p->status->value) }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    @if($p->status->value === 'active')
                        <a href="{{ route('store.product', $p->slug) }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Preview Product">
                            <i class="fa-solid fa-arrow-up-right-from-square text-slate-500 dark:text-slate-400 text-xs"></i>
                        </a>
                    @else
                        <span class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-center transition-colors cursor-not-allowed" title="Product must be active to preview">
                            <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 dark:text-slate-650 text-xs"></i>
                        </span>
                    @endif
                    <a href="{{ route('seller.products.show', $p) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="View Product details">
                        <i class="fa-solid fa-eye text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <a href="{{ route('seller.products.edit', $p) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Product">
                        <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    <form action="{{ route('seller.products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500" title="Delete Product">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No products found. Add your first listing to get started!</td>
        </tr>
    @endforelse
</x-data-table>
@endsection
