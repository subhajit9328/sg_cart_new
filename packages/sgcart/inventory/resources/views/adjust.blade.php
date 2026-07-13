@extends('layouts.admin')

@section('title', 'Bulk Stock Adjustment — SGCart Admin')

@section('content')
<style>
    /* Premium Compact Select2 Overrides */
    .select2-container .select2-selection--single {
        height: 28px !important;
        padding: 0 !important;
        font-size: 11px !important;
        border-radius: 6px !important;
        border-color: #e2e8f0 !important;
        background-color: #f8fafc !important;
        display: flex;
        align-items: center;
    }
    .dark .select2-container .select2-selection--single {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px !important;
        right: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 8px !important;
        padding-right: 20px !important;
        color: #0f172a !important;
        font-weight: 600 !important;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important;
    }
    .select2-dropdown {
        border-radius: 6px !important;
        border-color: #e2e8f0 !important;
        font-size: 11px !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
    }
    .dark .select2-dropdown {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    .dark .select2-results__option--selectable {
        color: #cbd5e1 !important;
    }
    .dark .select2-results__option--highlighted[aria-selected] {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
    .select2-search--dropdown {
        padding: 4px !important;
    }
    .select2-search--dropdown .select2-search__field {
        padding: 4px 8px !important;
        border-radius: 4px !important;
        border-color: #e2e8f0 !important;
        font-size: 11px !important;
        outline: none !important;
    }
    .dark .select2-search--dropdown .select2-search__field {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
</style>
<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('admin.inventory.index') }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors no-underline">
            <i class="fa-solid fa-arrow-left text-slate-500 dark:text-slate-400" style="font-size:13px;"></i>
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:0 0 2px;">Bulk Stock Adjustment</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin',    'url' => route('admin.dashboard')],
                ['label' => 'Inventory', 'url' => route('admin.inventory.index')],
                ['label' => 'Bulk Adjust']
            ]" />
        </div>
    </div>
    
    <!-- Right Side Header Action Buttons (Centered and perfectly aligned) -->
    <div class="flex items-center gap-2 h-9">
        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold text-xs transition-all no-underline h-9 flex items-center justify-center">
            Cancel
        </a>
        <button type="submit" form="bulkForm" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs transition-all shadow-md shadow-blue-600/10 cursor-pointer border-none h-9 flex items-center justify-center" id="submitBtn" disabled>
            Save Adjustments
        </button>
    </div>
</div>

<!-- Errors/Success Alert -->
@if(session('error'))
    <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 mb-6">
        <div class="flex gap-3 text-rose-800 dark:text-rose-400 text-sm">
            <i class="fa-solid fa-circle-exclamation text-base mt-0.5"></i>
            <div>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<!-- Bulk Adjust Form (SGCart Show Page Layout Structure) -->
<form action="{{ route('admin.inventory.adjust') }}" method="POST" id="bulkForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <!-- Left Column: Search Box & Compact Product Table (Takes 3 cols) -->
        <div class="lg:col-span-3 space-y-4">
            
            <!-- Autocomplete Search Input Above Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm relative">
                <label class="block text-slate-700 dark:text-slate-300 text-[10px] font-bold uppercase tracking-wider mb-2">Search Catalog to Add Items</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="productSearchInput" autocomplete="off" placeholder="Type product name or SKU..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 pl-10 pr-10 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-850 dark:text-slate-100 transition-all">
                    
                    <!-- Search Spinner loader (Initially Hidden) -->
                    <i class="fa-solid fa-spinner animate-spin absolute right-3.5 top-3 text-slate-400 text-xs" id="searchSpinner" style="display: none;"></i>

                    <!-- Search Results Dropdown -->
                    <div id="searchResultsDropdown" class="absolute right-0 left-0 mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-60 overflow-y-auto z-50 hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                        <!-- Populated via Ajax -->
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="adjustTable">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800">
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-[380px]">Product / Item Details</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Stock</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-28">Change Qty</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Reason / Details (Optional)</th>
                                <th class="px-4 py-3 text-right w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="adjustTableBody">
                            <!-- Empty State Placeholder -->
                            <tr id="emptyStateRow">
                                <td colspan="5" class="px-5 py-20 text-center text-slate-400 dark:text-slate-500 bg-slate-50/20 dark:bg-slate-900/50">
                                    <i class="fa-solid fa-cart-plus text-3xl mb-2 opacity-20 block"></i>
                                    <span class="font-medium text-xs block mb-0.5">Adjustment list is empty</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500">Search and select items using the search bar above.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Action Summary Panel (Takes 1 col) -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Global Reason Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm" id="sidebarActionCard">
                <h4 class="text-xs font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-3 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span>Summary</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400" id="itemsCountBadge">0 Items</span>
                </h4>

                <div>
                    <label for="reasonInput" class="block text-slate-500 dark:text-slate-400 text-[10px] font-bold uppercase mb-1.5">
                        Global Reason
                    </label>
                    <textarea name="reason" id="reasonInput" rows="4" placeholder="Optional notes (fallback if product reason is left blank)..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 resize-none"></textarea>
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="bg-amber-500/5 dark:bg-amber-500/5 border border-amber-500/10 dark:border-amber-500/15 rounded-xl p-4 shadow-sm">
                <h4 class="text-xs font-bold text-slate-750 dark:text-slate-350 mb-2.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-amber-500"></i> Adjustment Guide
                </h4>
                <ul class="text-[11px] text-slate-500 dark:text-slate-400 space-y-2 leading-relaxed list-none pl-0">
                    <li class="flex gap-2">
                        <span class="text-amber-500 font-bold">•</span>
                        <span>Use the search bar above to look up and add items to the adjustment table.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-amber-500 font-bold">•</span>
                        <span>For items with variants, select the desired variant to activate change input.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-amber-500 font-bold">•</span>
                        <span>Input positive quantities (e.g. <code class="text-emerald-600 dark:text-emerald-450 bg-emerald-500/10 dark:bg-emerald-500/15 px-1 py-0.2 rounded font-bold font-mono">+20</code>) to restock or negative quantities (e.g. <code class="text-rose-600 dark:text-rose-450 bg-rose-500/10 dark:bg-rose-500/15 px-1 py-0.2 rounded font-bold font-mono">-5</code>) to deduct.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-amber-500 font-bold">•</span>
                        <span>Ensure that final stock counts do not fall below zero (indicated by wavy red underlines).</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('productSearchInput');
        const dropdown = document.getElementById('searchResultsDropdown');
        const searchSpinner = document.getElementById('searchSpinner');
        const tableBody = document.getElementById('adjustTableBody');
        const emptyStateRow = document.getElementById('emptyStateRow');
        const submitBtn = document.getElementById('submitBtn');
        const itemsCountBadge = document.getElementById('itemsCountBadge');

        let addedItemIds = new Set();
        let rowIndex = 0;

        // 1. Search Query Handling via AJAX
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                searchSpinner.style.display = 'none';
                return;
            }

            searchSpinner.style.display = 'block';

            debounceTimer = setTimeout(() => {
                fetch(`/admin/inventory/search-products?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        renderDropdown(data);
                    })
                    .catch(err => {
                        console.error('Error searching products:', err);
                    })
                    .finally(() => {
                        searchSpinner.style.display = 'none';
                    });
            }, 300);
        });

        // 2. Render Search Results Dropdown
        function renderDropdown(items) {
            dropdown.innerHTML = '';
            if (items.length === 0) {
                dropdown.innerHTML = `<div class="px-4 py-3.5 text-xs text-slate-400 italic text-center">No matching products or variants found.</div>`;
                dropdown.classList.remove('hidden');
                return;
            }

            items.forEach(item => {
                const imgHTML = item.image 
                    ? `<img src="${item.image}" class="w-9 h-9 object-cover rounded-lg border border-slate-100 bg-white">`
                    : `<div class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200">NO IMG</div>`;
                               const itemDiv = document.createElement('div');
                itemDiv.className = 'px-4 py-2.5 flex items-center gap-3 hover:bg-blue-500/5 dark:hover:bg-blue-500/10 cursor-pointer transition-colors';
                itemDiv.innerHTML = `
                    ${imgHTML}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate">${item.name}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="font-mono text-[9px] text-slate-400 bg-slate-50 dark:bg-slate-800 px-1 py-0.2 rounded">SKU: ${item.sku}</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-[9px] font-bold bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-slate-650 dark:text-slate-350">Stock: ${item.stock}</span>
                    </div>
                `;

                itemDiv.addEventListener('click', () => {
                    addItemRow(item);
                    dropdown.classList.add('hidden');
                    searchInput.value = '';
                });

                dropdown.appendChild(itemDiv);
            });

            dropdown.classList.remove('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // 3. Add Item Row to Table
        function addItemRow(item) {
            if (addedItemIds.has(item.id)) {
                // Flash existing row
                const existingRow = document.getElementById(`row_${item.id}`);
                if (existingRow) {
                    existingRow.classList.add('bg-blue-500/5', 'dark:bg-blue-500/10');
                    const selectEl = existingRow.querySelector('.variant-select');
                    const qtyInput = existingRow.querySelector('.qty-input');
                    if (selectEl) {
                        $(selectEl).select2('open');
                    } else {
                        qtyInput.focus();
                    }
                    setTimeout(() => {
                        existingRow.classList.remove('bg-blue-500/5', 'dark:bg-blue-500/10');
                    }, 1000);
                }
                return;
            }

            // Hide empty state row
            if (emptyStateRow) {
                emptyStateRow.style.display = 'none';
            }

            const tr = document.createElement('tr');
            tr.id = `row_${item.id}`;
            tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors animate-fadeIn';

            const imgHTML = item.image 
                ? `<img src="${item.image}" class="w-9 h-9 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">`
                : `<div class="w-9 h-9 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 dark:border-slate-700/50">NO IMG</div>`;

            let detailsHTML = '';
            let stockDisplay = '';
            let qtyInputDisabled = '';
            let qtyInputPlaceholder = 'e.g. +10 or -5';
            let initialStock = item.stock;
            const curRowIndex = rowIndex;
            const truncatedName = item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name;

            if (item.has_variants) {
                qtyInputDisabled = 'disabled';
                qtyInputPlaceholder = 'Select variant';
                initialStock = 0;

                // Create options dropdown
                let optionsHTML = '<option value="">-- Choose Variant --</option>';
                item.variants.forEach(v => {
                    optionsHTML += `<option value="${v.id}" data-sku="${v.sku}" data-stock="${v.stock}">${v.name} (Stock: ${v.stock})</option>`;
                });

                detailsHTML = `
                    <div class="font-bold text-slate-850 dark:text-slate-100 text-xs" title="${item.name}">${truncatedName}</div>
                    <div class="mt-1.5 w-full max-w-[340px]">
                        <select id="variant_select_${rowIndex}" class="variant-select w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1 px-2.5 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                            ${optionsHTML}
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span id="sku_display_${rowIndex}" data-default-sku="${item.sku || '—'}" class="font-mono text-[9px] text-slate-450 dark:text-slate-400 bg-slate-150 dark:bg-slate-800 px-1.5 py-0.2 rounded">SKU: ${item.sku || '—'}</span>
                    </div>
                `;
                stockDisplay = `<span id="stock_display_${rowIndex}">—</span>`;
            } else {
                detailsHTML = `
                    <div class="font-bold text-slate-850 dark:text-slate-100 text-xs" title="${item.name}">${truncatedName}</div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="font-mono text-[9px] text-slate-450 dark:text-slate-400 bg-slate-150 dark:bg-slate-800 px-1.5 py-0.2 rounded">SKU: ${item.sku}</span>
                    </div>
                `;
                stockDisplay = `<span id="stock_display_${rowIndex}">${item.stock}</span>`;
            }

            tr.innerHTML = `
                <input type="hidden" name="adjustments[${rowIndex}][product_id]" value="${item.id}">
                <input type="hidden" name="adjustments[${rowIndex}][variant_id]" id="variant_id_${rowIndex}" value="">
                
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        ${imgHTML}
                        <div class="min-w-0 flex-1">
                            ${detailsHTML}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300 text-xs">
                    ${stockDisplay}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <input type="number" name="adjustments[${rowIndex}][qty]" value="0" required ${qtyInputDisabled}
                        id="qty_input_${rowIndex}" data-current="${initialStock}"
                        placeholder="${qtyInputPlaceholder}"
                        class="w-20 text-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1 px-1.5 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 font-mono font-bold qty-input">
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <input type="text" name="adjustments[${rowIndex}][reason]" placeholder="Fallback to global..." 
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-750 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-850 dark:text-slate-100">
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer remove-btn" title="Remove">
                        <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                    </button>
                </td>
            `;

            // Bind events for row calculations
            const qtyInput = tr.querySelector('.qty-input');
            qtyInput.addEventListener('input', function() {
                calculateRowNewStock(this, curRowIndex);
            });

            // Bind event for removing row
            const removeBtn = tr.querySelector('.remove-btn');
            removeBtn.addEventListener('click', () => {
                const selectEl = tr.querySelector('.variant-select');
                if (selectEl) {
                    $(selectEl).select2('destroy');
                }
                tr.remove();
                addedItemIds.delete(item.id);
                updateSummaryBadge();
            });

            tableBody.appendChild(tr);
            addedItemIds.add(item.id);
            rowIndex++;

            updateSummaryBadge();

            // Auto-focus and initialize Select2 on variant select elements
            const selectEl = tr.querySelector('.variant-select');
            if (selectEl) {
                const $select = $(selectEl).select2({
                    placeholder: '-- Choose Variant --',
                    allowClear: false,
                    width: '100%'
                });
                
                $select.on('change', function() {
                    updateRowVariant(this, curRowIndex);
                });
                
                $select.select2('open');
            } else {
                qtyInput.focus();
                qtyInput.select();
            }
        }

        // 4. Update Summary details and Submit button
        function updateSummaryBadge() {
            if (addedItemIds.size === 0) {
                if (emptyStateRow) {
                    emptyStateRow.style.display = '';
                }
                submitBtn.disabled = true;
                itemsCountBadge.innerText = '0 Items';
            } else {
                if (emptyStateRow) {
                    emptyStateRow.style.display = 'none';
                }
                submitBtn.removeAttribute('disabled');
                itemsCountBadge.innerText = `${addedItemIds.size} Item${addedItemIds.size > 1 ? 's' : ''}`;
            }
        }

        // 5. Submit Form Validation and Spinner Loading Indicator
        const bulkForm = document.getElementById('bulkForm');
        bulkForm.addEventListener('submit', function(e) {
            const globalReason = document.getElementById('reasonInput').value.trim();
            const rows = document.querySelectorAll('#adjustTableBody tr:not(#emptyStateRow)');
            
            let missingReason = false;
            let invalidStockValue = false;
            let missingVariantSelection = false;

            rows.forEach(row => {
                const variantSelect = row.querySelector('.variant-select');
                if (variantSelect && !variantSelect.value) {
                    missingVariantSelection = true;
                    variantSelect.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
                } else if (variantSelect) {
                    variantSelect.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
                }

                const qtyInput = row.querySelector('.qty-input');
                if (qtyInput && !qtyInput.disabled) {
                    const qtyVal = parseInt(qtyInput.value || 0);
                    
                    // Check if item has non-zero adjustment change
                    if (qtyVal !== 0) {
                        const rowReasonInput = row.querySelector('input[name*="[reason]"]');
                        const rowReason = rowReasonInput.value.trim();

                        // If neither row reason nor global reason is provided
                        if (!rowReason && !globalReason) {
                            missingReason = true;
                            rowReasonInput.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
                        } else {
                            rowReasonInput.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
                        }

                        // Check if adjustment makes stock negative
                        const currentStock = parseInt(qtyInput.getAttribute('data-current') || 0);
                        if (currentStock + qtyVal < 0) {
                            invalidStockValue = true;
                            qtyInput.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
                        } else {
                            qtyInput.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
                        }
                    }
                }
            });

            if (missingVariantSelection) {
                e.preventDefault();
                if (window.showToast) {
                    showToast('Please select a variant for all variant-based products in the list.', 'warning');
                } else {
                    alert('Please select a variant for all variant-based products in the list.');
                }
                return false;
            }

            if (invalidStockValue) {
                e.preventDefault();
                if (window.showToast) {
                    showToast('Some products have adjustments resulting in negative stock values. Please correct them.', 'error');
                } else {
                    alert('Some products have adjustments resulting in negative stock values. Please correct them.');
                }
                return false;
            }

            if (missingReason) {
                e.preventDefault();
                if (window.showToast) {
                    showToast('Please provide a reason for all adjusted products (either on each product row or globally).', 'warning');
                } else {
                    alert('Please provide a reason for all adjusted products (either on each product row or globally).');
                }
                return false;
            }

            // Check native HTML5 constraints
            if (!this.checkValidity()) {
                return;
            }

            // Defer disabling the button until after all other submit handlers have run
            setTimeout(() => {
                if (!e.defaultPrevented) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<i class="fa-solid fa-spinner animate-spin mr-1.5"></i> Saving...`;
                    
                    const cancelBtn = document.querySelector('a[href="{{ route("admin.inventory.index") }}"]');
                    if (cancelBtn) {
                        cancelBtn.classList.add('pointer-events-none', 'opacity-50');
                    }
                }
            }, 0);
        });
    });

    function calculateRowNewStock(inputEl, rIndex) {
        const currentStock = parseInt(inputEl.getAttribute('data-current') || 0);
        const stockDisplaySpan = document.getElementById(`stock_display_${rIndex}`);
        const val = inputEl.value.trim();
        const isVariantSelectSelected = !document.getElementById(`variant_select_${rIndex}`) || document.getElementById(`variant_select_${rIndex}`).value !== '';

        if (!stockDisplaySpan) return;

        if (val === '' || isNaN(val) || parseInt(val) === 0) {
            stockDisplaySpan.innerHTML = isVariantSelectSelected ? currentStock : '—';
            inputEl.className = inputEl.className.replace(/\b(border-emerald-500|border-rose-500)\b/g, '').trim();
            return;
        }

        const change = parseInt(val);
        const calculated = currentStock + change;

        if (calculated < 0) {
            stockDisplaySpan.innerHTML = `<span class="text-slate-400 dark:text-slate-500 font-normal">${currentStock}</span> <i class="fa-solid fa-arrow-right text-[10px] text-rose-500 mx-1"></i><span class="text-rose-600 dark:text-rose-400 font-extrabold underline decoration-wavy decoration-rose-500">${calculated}</span>`;
            inputEl.className = inputEl.className.replace(/\b(border-emerald-500)\b/g, '').trim() + ' border-rose-500';
        } else {
            const colorClass = change > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
            const arrowClass = change > 0 ? 'text-emerald-500' : 'text-rose-500';
            stockDisplaySpan.innerHTML = `<span class="text-slate-400 dark:text-slate-500 font-normal">${currentStock}</span> <i class="fa-solid fa-arrow-right text-[10px] ${arrowClass} mx-1"></i><span class="${colorClass} font-extrabold">${calculated}</span>`;
            
            const borderClass = change > 0 ? 'border-emerald-500' : 'border-rose-500';
            inputEl.className = inputEl.className.replace(/\b(border-emerald-500|border-rose-500)\b/g, '').trim() + ' ' + borderClass;
        }
    }

    function updateRowVariant(selectEl, rIndex) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const qtyInput = document.getElementById(`qty_input_${rIndex}`);
        const variantIdInput = document.getElementById(`variant_id_${rIndex}`);
        const skuSpan = document.getElementById(`sku_display_${rIndex}`);
        const oldStockSpan = document.getElementById(`stock_display_${rIndex}`);

        if (selectEl.value) {
            const stock = parseInt(selectedOpt.getAttribute('data-stock') || 0);
            const sku = selectedOpt.getAttribute('data-sku') || '';
            const defaultSku = skuSpan.getAttribute('data-default-sku') || '—';
            
            variantIdInput.value = selectEl.value;
            skuSpan.innerText = 'SKU: ' + (sku && sku.trim() !== '' && sku !== 'null' ? sku : defaultSku);
            oldStockSpan.innerText = stock;
            
            qtyInput.disabled = false;
            qtyInput.placeholder = "e.g. +10 or -5";
            qtyInput.setAttribute('data-current', stock);
            qtyInput.focus();
            
            calculateRowNewStock(qtyInput, rIndex);
        } else {
            variantIdInput.value = '';
            const defaultSku = skuSpan.getAttribute('data-default-sku') || '—';
            skuSpan.innerText = 'SKU: ' + defaultSku;
            oldStockSpan.innerText = '—';
            
            qtyInput.value = '0';
            qtyInput.disabled = true;
            qtyInput.placeholder = "Select variant";
            qtyInput.setAttribute('data-current', 0);
            calculateRowNewStock(qtyInput, rIndex);
        }
    }
</script>
@endsection
