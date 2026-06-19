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
        <button onclick="showToast('Coming soon', 'warning')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-medium shadow-lg shadow-emerald-600/10 transition-colors cursor-pointer border-none">
            <i class="fa-solid fa-file-import"></i> Bulk Upload
        </button>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
    </div>
</div>

<!-- Products Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Search & Dropdowns -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Products</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $products->total() }}
            </span>
        </div>
        
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-56">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Search products…">
            </div>

            <!-- Category Filter Dropdown -->
            <div class="min-w-[140px]">
                <select name="category_id" onchange="this.form.submit()"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-400 outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter Dropdown -->
            <div class="min-w-[110px]">
                <select name="status" onchange="this.form.submit()"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-400 outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">All Status</option>
                    @foreach(['draft','active','inactive'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request()->filled('search') || request()->filled('category_id') || request()->filled('status'))
                <a href="{{ route('admin.products.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center animate-fadeIn">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-20">Image</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Product Name</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">SKU</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Category</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Price</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Stock</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Status</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
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
                    <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm">
                        {{ $product->name }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                        {{ $product->sku }}
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
                            {{ $product->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' :
                              ($product->status === 'draft'  ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/20' : 
                              'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50') }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5 justify-end">
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
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($products->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $products->links() }}
        </div>
    @endif
</div>



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