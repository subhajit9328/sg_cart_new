@extends('layouts.admin')

@section('title', 'Inventory Stock Levels — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Inventory Levels</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Inventory', 'url' => route('admin.inventory.index')],
            ['label' => 'Stock Levels']
        ]" />
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.inventory.logs') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-colors shadow-xs no-underline">
            <i class="fa-solid fa-clock-rotate-left text-blue-500"></i> Stock History Logs
        </a>
        <a href="{{ route('admin.inventory.adjust.form') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
            <i class="fa-solid fa-plus-minus"></i> Bulk Adjust Stock
        </a>
    </div>
</div>

<!-- Analytics Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <!-- Total Items -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Catalog Items</span>
            <span class="text-2xl font-bold text-slate-800 dark:text-slate-100 block mt-1">{{ $totalItems }}</span>
        </div>
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
            <i class="fa-solid fa-boxes-stacked text-lg"></i>
        </div>
    </div>

    <!-- In Stock -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Adequate Stock</span>
            <span class="text-2xl font-bold text-slate-800 dark:text-slate-100 block mt-1">{{ $inStockItems }}</span>
        </div>
        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check text-lg"></i>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Low Stock Alerts</span>
            <span class="text-2xl font-bold text-slate-800 dark:text-slate-100 block mt-1">{{ $lowStockItems }}</span>
        </div>
        <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
            <i class="fa-solid fa-triangle-exclamation text-lg animate-pulse"></i>
        </div>
    </div>

    <!-- Out of Stock -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Out of Stock</span>
            <span class="text-2xl font-bold text-slate-800 dark:text-slate-100 block mt-1 text-rose-600 dark:text-rose-400">{{ $outOfStockItems }}</span>
        </div>
        <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
            <i class="fa-solid fa-circle-xmark text-lg"></i>
        </div>
    </div>
</div>@php
    $headers = [
        ['label' => 'Image', 'key' => 'image', 'sortable' => false, 'width' => '20'],
        ['label' => 'Product / Item Name', 'key' => 'name', 'sortable' => true],
        ['label' => 'SKU', 'key' => 'sku', 'sortable' => true],
        ['label' => 'Price', 'key' => 'price', 'sortable' => true],
        ['label' => 'Current Stock', 'key' => 'stock', 'sortable' => true],
        ['label' => 'Status', 'key' => 'status', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right', 'width' => '36'],
    ];
@endphp

<x-data-table
    title="Stock Catalog"
    :totalCount="$products->total()"
    searchPlaceholder="Search products…"
    action="{{ route('admin.inventory.index') }}"
    tableId="inventoryTableWrapper"
    searchInputId="inventorySearchInput"
    totalCountId="inventoryTotalCount"
    :items="$products"
    :headers="$headers"
>
    @forelse($products as $product)
        @php
            $activeVariantsCount = $hasVariants ? $product->variants->where('is_active', true)->count() : 0;
            $hasActiveVariants = $activeVariantsCount > 0;
        @endphp
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <!-- Image -->
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="w-9 h-9 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                @else
                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 dark:border-slate-700/50">
                        NO IMG
                    </div>
                @endif
            </td>
            <!-- Name -->
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm" title="{{ $product->name }}">
                {{ \Illuminate\Support\Str::limit($product->name, 25, '...') }}
                @if($hasActiveVariants)
                    <span class="inline-flex items-center pl-1.5 pr-0.5 py-0.5 rounded-full text-[9px] font-bold bg-violet-50 dark:bg-violet-950/20 text-violet-650 dark:text-violet-400 border border-violet-200/20 ml-1.5">
                        <i class="fa-solid fa-tags text-[8px] mr-1"></i> Has Variants
                        <span class="rounded-full px-1 ml-1 border border-violet-200">
                            {{ $activeVariantsCount }}
                        </span>
                    </span>
                @endif
            </td>
            <!-- SKU -->
            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                {{ $product->sku ?: '—' }}
            </td>
            <!-- Price -->
            <td class="px-5 py-3.5 text-slate-800 dark:text-slate-200 font-semibold whitespace-nowrap text-xs">
                ₹{{ number_format($product->price, 2) }}
                @if($product->sale_price)
                    <span class="text-emerald-600 dark:text-emerald-400 text-[9px] block font-semibold mt-0.5">Sale: ₹{{ number_format($product->sale_price, 2) }}</span>
                @endif
            </td>
            <!-- Stock -->
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($product->stock > ($product->min_stock ?? 5))
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
            <!-- Status -->
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                    {{ $product->status->value === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20' :
                      ($product->status->value === 'draft'  ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/20' :
                      ($product->status->value === 'rejected' ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20' :
                      'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-350 border border-slate-200/50')) }}">
                    {{ ucfirst($product->status->value) }}
                </span>
            </td>
            <!-- Actions -->
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                @if($hasActiveVariants)
                    <button type="button"
                        onclick="openQuickAdjustModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->stock }}', {{ json_encode($product->variants->where('is_active', true)->map(function($v) use ($product) {
                            $attrs = [];
                            if($v->color) $attrs[] = $v->color->name;
                            if($v->size) $attrs[] = $v->size->code;
                            return [
                                'id' => $v->id,
                                'name' => implode(' / ', $attrs) ?: 'Default Variant',
                                'sku' => $v->sku ?: ($product->sku ?: '—'),
                                'stock' => $v->stock
                            ];
                        })->values()->toArray()) }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 text-blue-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-xs cursor-pointer border-none">
                        <i class="fa-solid fa-plus-minus text-[10px]"></i> Adjust
                    </button>
                @else
                    <button type="button"
                        onclick="openQuickAdjustModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->stock }}', null)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 text-blue-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-xs cursor-pointer border-none">
                        <i class="fa-solid fa-plus-minus text-[10px]"></i> Adjust
                    </button>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20 block"></i>
                No products found in the catalog.
            </td>
        </tr>
    @endforelse
</x-data-table>

<!-- Quick Adjustment Modal (Premium design with smooth transitions) -->
<div id="quickAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 ease-out" id="modalContainer">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-plus-minus text-blue-500"></i> Adjust Stock Level
            </h3>
            <button type="button" onclick="closeQuickAdjustModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.inventory.adjust') }}" method="POST" id="modalAdjustForm" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="variant_id" id="modalVariantId">

            <!-- Item Name (Display) -->
            <div class="bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl p-4">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Target Item</span>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-100 block mt-1" id="modalItemName">iPhone 15 Pro</span>
            </div>

            <!-- Variant Dropdown Selector (Initially Hidden) -->
            <div id="modalVariantSelectorGroup" class="hidden">
                <label for="modalVariantSelect" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Select Variant <span class="text-rose-600">*</span></label>
                <select id="modalVariantSelect" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-850 dark:text-slate-100">
                    <!-- Populated dynamically via JS -->
                </select>
            </div>

            <!-- Stock Calculations -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Old Stock</label>
                    <div class="bg-slate-100 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700 text-center" id="modalOldStock">
                        0
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">New Stock</label>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-400 dark:text-slate-500 font-bold border border-slate-200 dark:border-slate-700 text-center transition-all duration-300" id="modalNewStock">
                        —
                    </div>
                </div>
            </div>

            <!-- Input fields -->
            <div>
                <label for="modalQty" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Adjustment Quantity <span class="text-rose-600">*</span></label>
                <input type="number" name="qty" id="modalQty" required placeholder="e.g. +15 or -5" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 font-mono">
                <span class="text-[11px] text-rose-500 dark:text-rose-450 font-bold mt-1 block" id="modalQtyError" style="display: none;"></span>
            </div>

            <div>
                <label for="modalReason" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Reason / Description <span class="text-rose-600">*</span></label>
                <input type="text" name="reason" id="modalReason" required placeholder="e.g. Returned / physical count..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100">
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" onclick="closeQuickAdjustModal()" class="px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer bg-transparent">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn" class="px-4 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg transition-all shadow-md shadow-blue-600/10 cursor-pointer border-none">
                    Apply Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentOldStockVal = 0;

    function showInlineError(message) {
        const errorSpan = document.getElementById('modalQtyError');
        const qtyInput = document.getElementById('modalQty');

        if (message) {
            errorSpan.innerText = message;
            errorSpan.style.display = 'block';
            qtyInput.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
        } else {
            errorSpan.innerText = '';
            errorSpan.style.display = 'none';
            qtyInput.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
        }
    }

    function openQuickAdjustModal(productId, itemName, currentStock, variantsJson) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalItemName').innerText = itemName;

        const variantSelectorGroup = document.getElementById('modalVariantSelectorGroup');
        const variantSelect = document.getElementById('modalVariantSelect');
        const modalVariantId = document.getElementById('modalVariantId');
        const qtyInput = document.getElementById('modalQty');
        const submitBtn = document.getElementById('modalSubmitBtn');
        const oldStockDiv = document.getElementById('modalOldStock');
        const newStockDiv = document.getElementById('modalNewStock');

        qtyInput.value = '';
        newStockDiv.innerText = '—';
        newStockDiv.className = "bg-slate-50 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-400 dark:text-slate-500 font-bold border border-slate-200 dark:border-slate-700 text-center";
        document.getElementById('modalReason').value = '';
        showInlineError(null);

        // Check if product has variants
        if (variantsJson && Array.isArray(variantsJson) && variantsJson.length > 0) {
            variantSelectorGroup.classList.remove('hidden');
            qtyInput.disabled = true;
            qtyInput.placeholder = "Select a variant first";
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'pointer-events-none');
            oldStockDiv.innerText = '—';
            modalVariantId.value = '';
            currentOldStockVal = 0;

            // Populate variant choices
            variantSelect.innerHTML = '<option value="">-- Choose Variant --</option>';
            variantsJson.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.setAttribute('data-stock', v.stock);
                opt.innerText = `${v.name} (SKU: ${v.sku}) — Stock: ${v.stock}`;
                variantSelect.appendChild(opt);
            });

            // Handle choice event
            variantSelect.onchange = function() {
                const selectedOpt = this.options[this.selectedIndex];
                if (this.value) {
                    modalVariantId.value = this.value;
                    const stock = parseInt(selectedOpt.getAttribute('data-stock') || 0);
                    currentOldStockVal = stock;
                    oldStockDiv.innerText = stock;

                    qtyInput.disabled = false;
                    qtyInput.placeholder = "e.g. +15 or -5";
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'pointer-events-none');
                    qtyInput.focus();

                    // Re-calculate stock if qty is filled
                    triggerQtyCalculation();
                } else {
                    modalVariantId.value = '';
                    currentOldStockVal = 0;
                    oldStockDiv.innerText = '—';
                    qtyInput.value = '';
                    qtyInput.disabled = true;
                    qtyInput.placeholder = "Select a variant first";
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'pointer-events-none');
                    newStockDiv.innerText = '—';
                }
            };
        } else {
            variantSelectorGroup.classList.add('hidden');
            modalVariantId.value = '';
            currentOldStockVal = parseInt(currentStock || 0);
            oldStockDiv.innerText = currentOldStockVal;

            qtyInput.disabled = false;
            qtyInput.placeholder = "e.g. +15 or -5";
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'pointer-events-none');

            setTimeout(() => {
                qtyInput.focus();
                qtyInput.select();
            }, 100);
        }

        const modal = document.getElementById('quickAdjustModal');
        const container = document.getElementById('modalContainer');

        modal.classList.remove('hidden');
        void modal.offsetWidth; // Force layout recalculation

        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
    }

    function closeQuickAdjustModal() {
        const modal = document.getElementById('quickAdjustModal');
        const container = document.getElementById('modalContainer');

        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function triggerQtyCalculation() {
        const qtyInput = document.getElementById('modalQty');
        const val = qtyInput.value.trim();
        const newStockDiv = document.getElementById('modalNewStock');

        if (val === '') {
            newStockDiv.innerText = '—';
            newStockDiv.className = "bg-slate-50 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-400 dark:text-slate-500 font-bold border border-slate-200 dark:border-slate-700 text-center";
            showInlineError(null);
            return;
        }

        if (!/^[+-]?\d+$/.test(val)) {
            newStockDiv.innerText = '—';
            newStockDiv.className = "bg-slate-50 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-400 dark:text-slate-500 font-bold border border-slate-200 dark:border-slate-700 text-center";
            showInlineError('Quantity must be an integer.');
            return;
        }

        const change = parseInt(val);

        if (change === 0) {
            newStockDiv.innerText = '—';
            newStockDiv.className = "bg-slate-50 dark:bg-slate-800 rounded-xl py-2 px-3.5 font-mono text-sm text-slate-400 dark:text-slate-500 font-bold border border-slate-200 dark:border-slate-700 text-center";
            showInlineError('Adjustment quantity cannot be zero.');
            return;
        }

        const calculated = currentOldStockVal + change;

        if (calculated < 0) {
            newStockDiv.innerText = `${calculated} (Invalid)`;
            newStockDiv.className = "bg-rose-50 dark:bg-rose-955/20 border border-rose-200 dark:border-rose-800 text-rose-500 rounded-xl py-2 px-3.5 font-mono text-sm font-bold text-center";
            showInlineError('Adjustment results in negative stock level.');
        } else {
            newStockDiv.innerText = calculated;
            if (change > 0) {
                newStockDiv.className = "bg-emerald-50 dark:bg-emerald-955/20 border border-emerald-200/30 text-emerald-600 dark:text-emerald-400 rounded-xl py-2 px-3.5 font-mono text-sm font-bold text-center";
            } else {
                newStockDiv.className = "bg-rose-50 dark:bg-rose-955/20 border border-rose-200 dark:border-rose-800 text-rose-500 rounded-xl py-2 px-3.5 font-mono text-sm font-bold text-center";
            }
            showInlineError(null);
        }
    }

    document.getElementById('modalQty').addEventListener('input', triggerQtyCalculation);

    // Modal Form Client-side Validation
    document.getElementById('modalAdjustForm').addEventListener('submit', function(e) {
        const qtyInput = document.getElementById('modalQty');
        const val = qtyInput.value.trim();

        if (val !== '' && !/^[+-]?\d+$/.test(val)) {
            e.preventDefault();
            showInlineError('Quantity must be an integer.');
            qtyInput.focus();
            return false;
        }

        const qtyVal = parseInt(qtyInput.value || 0);

        if (qtyVal === 0) {
            e.preventDefault();
            showInlineError('Adjustment quantity cannot be zero.');
            qtyInput.focus();
            return false;
        }

        if (currentOldStockVal + qtyVal < 0) {
            e.preventDefault();
            showInlineError('Adjustment results in negative stock level.');
            qtyInput.focus();
            return false;
        }

        // Check native HTML5 constraints
        if (!this.checkValidity()) {
            return;
        }

        // Defer disabling the button until after all other submit handlers have run
        const submitBtn = this.querySelector('button[type="submit"]');
        setTimeout(() => {
            if (!e.defaultPrevented) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="fa-solid fa-spinner animate-spin mr-1.5"></i> Applying...`;
            }
        }, 0);
    });
</script>

<x-table-ajax-handler
    tableId="inventoryTableWrapper"
    searchInputId="inventorySearchInput"
    totalCountId="inventoryTotalCount"
/>
@endsection
