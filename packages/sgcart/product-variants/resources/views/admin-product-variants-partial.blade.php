<form id="variantSaveForm" method="POST" action="{{ route(auth('seller')->check() ? 'seller.products.variants.save' : 'admin.products.variants.save', $product->id) }}" enctype="multipart/form-data" onsubmit="return validateVariants(event)">
    @csrf

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Product Variants</h3>
            <p class="text-xs text-slate-400 mt-1">Define combinations of colors and sizes. Override SKU, price, and track individual inventory.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(config('product-variants.features.color', true))
            <button type="button" onclick="openQuickAttributeModal('color')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all shadow-sm cursor-pointer">
                <i class="fa-solid fa-palette text-blue-500"></i> Quick Add Color
            </button>
            @endif
            @if(config('product-variants.features.size', true))
            <button type="button" onclick="openQuickAttributeModal('size')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all shadow-sm cursor-pointer">
                <i class="fa-solid fa-ruler-horizontal text-emerald-500"></i> Quick Add Size
            </button>
            @endif
            <button type="button" onclick="addVariantRow()" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-all shadow-sm shadow-blue-600/10 border-none cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Variant Row
            </button>
        </div>
    </div>

    <!-- Variants Spreadsheet Grid -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mb-6">
        @if(!auth('seller')->check() && class_exists(\SGCart\Inventory\Models\InventoryLog::class))
            <div class="px-4 py-2.5 bg-blue-50/50 dark:bg-blue-950/20 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 text-xs text-blue-650 dark:text-blue-400 font-bold">
                <i class="fa-solid fa-circle-info text-blue-500"></i> Variant stock levels are read-only. They are managed via the <a href="{{ route('admin.inventory.index') }}" class="underline hover:text-blue-700">Inventory Management System</a>.
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                        @if(config('product-variants.features.color', true))
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-44">Color</th>
                        @endif
                        @if(config('product-variants.features.size', true))
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-44">Size</th>
                        @endif
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">SKU Override</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">
                            Price(₹)
                            <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 ml-1 cursor-help" data-tooltip="Set a custom price for this variant. If left empty, it will fallback to the base product's price."></i>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">
                            Sale Price(₹)
                            <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 ml-1 cursor-help" data-tooltip="Set a custom sale price for this variant. If left empty, it will fallback to the base product's sale price."></i>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-32">Stock Qty</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-24">Min Stock</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-24">Active</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Gallery</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-16"></th>
                    </tr>
                </thead>
                <tbody id="variantsTableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($variants as $index => $v)
                    <tr data-row-index="{{ $index }}" class="hover:bg-slate-50/30 dark:hover:bg-slate-800/10 transition-colors">
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $v->id }}">
                        
                        @if(config('product-variants.features.color', true))
                        <td class="px-4 py-3.5">
                            <select name="variants[{{ $index }}][color_id]" class="color-select w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                                <option value="">— No Color —</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}" {{ $v->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                @endforeach
                            </select>
                            <div class="variant-error-msg text-[9.5px] text-rose-500 mt-1 hidden font-medium whitespace-nowrap"></div>
                        </td>
                        @endif

                        @if(config('product-variants.features.size', true))
                        <td class="px-4 py-3.5">
                            <select name="variants[{{ $index }}][size_id]" class="size-select w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                                <option value="">— No Size —</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}" {{ $v->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }} ({{ $size->code }})</option>
                                @endforeach
                            </select>
                            <div class="variant-error-msg text-[9.5px] text-rose-500 mt-1 hidden font-medium whitespace-nowrap"></div>
                        </td>
                        @endif

                        <td class="px-4 py-3.5">
                            <input type="text" name="variants[{{ $index }}][sku]" value="{{ $v->sku }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Leave empty for default">
                        </td>

                        <td class="px-4 py-3.5">
                            <input type="number" step="0.01" min="0" name="variants[{{ $index }}][price]" value="{{ $v->price }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Default">
                        </td>

                        <td class="px-4 py-3.5">
                            <input type="number" step="0.01" min="0" name="variants[{{ $index }}][sale_price]" value="{{ $v->sale_price }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Default">
                        </td>

                        <td class="px-4 py-3.5">
                            @if(!auth('seller')->check() && class_exists(\SGCart\Inventory\Models\InventoryLog::class))
                                <input type="number" name="variants[{{ $index }}][stock]" value="{{ $v->stock ?? 0 }}" readonly
                                    class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-slate-500 cursor-not-allowed font-mono" style="pointer-events: none;">
                            @else
                                <input type="number" min="0" name="variants[{{ $index }}][stock]" value="{{ $v->stock ?? 0 }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono">
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            <input type="number" min="0" name="variants[{{ $index }}][min_stock]" value="{{ $v->min_stock ?? 5 }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono">
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="variants[{{ $index }}][is_active]" value="1" {{ $v->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:height-4 after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <button type="button" onclick="openVariantGalleryModal({{ $index }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer shadow-sm mx-auto">
                                <i class="fa-solid fa-images text-blue-500"></i>
                                <span>Photos (<span id="variant_img_count_{{ $index }}">{{ $v->images->count() }}</span>)</span>
                            </button>
                        </td>

                        <td class="px-4 py-3.5 text-right">
                            <button type="button" onclick="removeVariantRow(this)" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer">
                                <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyVariantsRow">
                        <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-table-list text-4xl mb-3 opacity-20 block"></i>
                            No variants defined for this product yet. Click "Add Variant Row" to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 w-full">
        <a href="{{ route(auth('seller')->check() ? 'seller.products.index' : 'admin.products.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
            Cancel
        </a>
        <button type="submit" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer flex items-center">
            Save Variant Settings
        </button>
    </div>

    {{-- Hidden container for file inputs: must stay INSIDE the form so files are submitted --}}
    <div id="variantFileInputsContainer" class="hidden"></div>
</form>

<!-- Modal Wrapper Container (for modals rendered inline) -->
<div id="variantModalsContainer">
    @foreach($variants as $index => $v)
    <div id="variantGalleryModal_{{ $index }}" class="variant-modal fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-2xl w-full mx-4 border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="font-semibold text-sm font-display">Manage Variant Images</h3>
                <button type="button" onclick="closeVariantGalleryModal({{ $index }})" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-xs text-slate-400">
                    Upload images specific to this variant. Shoppers will see these photos automatically when they select this color and size combination. Select a radio button to make an image default for this variant.
                </p>
                
                <div class="flex flex-wrap gap-4" id="variant_image_gallery_{{ $index }}">
                    <!-- Existing images -->
                    @foreach($v->images as $img)
                        <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm w-32 h-32 bg-slate-950 flex flex-col justify-between group" id="variant_img_card_{{ $img->id }}">
                            <img src="{{ Storage::url($img->image_path) }}" class="w-full h-full object-cover absolute inset-0">
                            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="text-rose-500 hover:text-rose-700 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full p-1.5 transition-colors outline-none cursor-pointer flex items-center justify-center w-7 h-7 shadow-sm border-none" onclick="removeExistingVariantImage({{ $index }}, {{ $img->id }})">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            <div class="absolute bottom-2 left-2 right-2 bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 flex items-center gap-1.5 shadow-sm z-10">
                                <input type="radio" name="variants[{{ $index }}][default_image]" value="existing_{{ $img->id }}" id="radio_variant_existing_{{ $img->id }}" {{ $img->is_default ? 'checked' : '' }} class="accent-blue-600 cursor-pointer">
                                <label for="radio_variant_existing_{{ $img->id }}" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider cursor-pointer select-none">Default</label>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Add Image Card -->
                    <div id="addVariantImageCard_{{ $index }}" class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl w-32 h-32 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-slate-100/50 dark:hover:bg-slate-800/30 gap-1.5 group select-none" onclick="triggerAddVariantImage({{ $index }})">
                        <i class="fa-solid fa-circle-plus text-2xl text-slate-400 dark:text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Add Photo</span>
                    </div>
                </div>

                <div id="variant_hidden_inputs_{{ $index }}" class="hidden"></div>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end">
                <button type="button" onclick="closeVariantGalleryModal({{ $index }})" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition-colors cursor-pointer border-none shadow-sm shadow-blue-600/10">
                    Done
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Quick Attribute Add Modal -->
<div id="quickAttributeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full mx-4 border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-semibold text-sm font-display" id="quickAttrTitle">Quick Add Attribute</h3>
            <button type="button" onclick="closeQuickAttributeModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="quickAttributeForm" onsubmit="saveQuickAttribute(event)" class="p-6 space-y-4">
            <input type="hidden" id="quickAttrType" name="type">
            
            <div>
                <label for="quickAttrName" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Name <span class="text-rose-600">*</span></label>
                <input type="text" id="quickAttrName" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
            </div>

            <!-- Contextual Field (Hex Code for Color, Code/Tag for Size) -->
            <div id="quickAttrExtraFieldWrapper">
                <label id="quickAttrExtraLabel" for="quickAttrExtra" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Hex Code <span class="text-rose-600">*</span></label>
                <div class="flex gap-2">
                    <input type="color" id="quickAttrColorPicker" oninput="document.getElementById('quickAttrExtra').value = this.value" class="w-10 h-10 border border-slate-200 dark:border-slate-700 rounded cursor-pointer p-0 bg-transparent">
                    <input type="text" id="quickAttrExtra" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm outline-none focus:border-blue-500 transition-all font-mono" placeholder="#ffffff">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeQuickAttributeModal()" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline bg-transparent cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                    Add Swatch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Templates for dynamically created Rows & Modals -->
<template id="variantRowTemplate">
    <tr data-row-index="__INDEX__" class="hover:bg-slate-50/30 dark:hover:bg-slate-800/10 transition-colors">
        @if(config('product-variants.features.color', true))
        <td class="px-4 py-3.5">
            <select name="variants[__INDEX__][color_id]" class="color-select w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                <option value="">— No Color —</option>
                @foreach($colors as $color)
                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                @endforeach
            </select>
            <div class="variant-error-msg text-[9.5px] text-rose-500 mt-1 hidden font-medium whitespace-nowrap"></div>
        </td>
        @endif

        @if(config('product-variants.features.size', true))
        <td class="px-4 py-3.5">
            <select name="variants[__INDEX__][size_id]" class="size-select w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                <option value="">— No Size —</option>
                @foreach($sizes as $size)
                    <option value="{{ $size->id }}">{{ $size->name }} ({{ $size->code }})</option>
                @endforeach
            </select>
            <div class="variant-error-msg text-[9.5px] text-rose-500 mt-1 hidden font-medium whitespace-nowrap"></div>
        </td>
        @endif

        <td class="px-4 py-3.5">
            <input type="text" name="variants[__INDEX__][sku]" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Leave empty for default">
        </td>

        <td class="px-4 py-3.5">
            <input type="number" step="0.01" min="0" name="variants[__INDEX__][price]" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Default">
        </td>

        <td class="px-4 py-3.5">
            <input type="number" step="0.01" min="0" name="variants[__INDEX__][sale_price]" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono" placeholder="Default">
        </td>

        <td class="px-4 py-3.5">
            @if(!auth('seller')->check() && class_exists(\SGCart\Inventory\Models\InventoryLog::class))
                <input type="number" name="variants[__INDEX__][stock]" value="0" readonly
                    class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-slate-500 cursor-not-allowed font-mono" style="pointer-events: none;">
            @else
                <input type="number" min="0" name="variants[__INDEX__][stock]" value="0" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono">
            @endif
        </td>

        <td class="px-4 py-3.5">
            <input type="number" min="0" name="variants[__INDEX__][min_stock]" value="5" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono">
        </td>

        <td class="px-4 py-3.5 text-center">
            <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" name="variants[__INDEX__][is_active]" value="1" checked class="sr-only peer">
                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:height-4 after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </td>

        <td class="px-4 py-3.5 text-center">
            <button type="button" onclick="openVariantGalleryModal(__INDEX__)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer shadow-sm mx-auto">
                <i class="fa-solid fa-images text-blue-500"></i>
                <span>Photos (<span id="variant_img_count___INDEX__">0</span>)</span>
            </button>
        </td>

        <td class="px-4 py-3.5 text-right">
            <button type="button" onclick="removeVariantRow(this)" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer">
                <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
            </button>
        </td>
    </tr>
</template>

<template id="variantModalTemplate">
    <div id="variantGalleryModal___INDEX__" class="variant-modal fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-2xl w-full mx-4 border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="font-semibold text-sm font-display">Manage Variant Images</h3>
                <button type="button" onclick="closeVariantGalleryModal(__INDEX__)" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-xs text-slate-400">
                    Upload images specific to this variant. Shoppers will see these photos automatically when they select this color and size combination. Select a radio button to make an image default for this variant.
                </p>
                
                <div class="flex flex-wrap gap-4" id="variant_image_gallery___INDEX__">
                    <!-- Add Image Card -->
                    <div id="addVariantImageCard___INDEX__" class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl w-32 h-32 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-slate-100/50 dark:hover:bg-slate-800/30 gap-1.5 group select-none" onclick="triggerAddVariantImage(__INDEX__)">
                        <i class="fa-solid fa-circle-plus text-2xl text-slate-400 dark:text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Add Photo</span>
                    </div>
                </div>

                <div id="variant_hidden_inputs___INDEX__" class="hidden"></div>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end">
                <button type="button" onclick="closeVariantGalleryModal(__INDEX__)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition-colors cursor-pointer border-none shadow-sm shadow-blue-600/10">
                    Done
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    // Define unique id counter for uploads
    window.vUniqueIdCounter = window.vUniqueIdCounter || 0;
    
    // We get the initial row count to set the starting index counter
    window.variantRowIndexCounter = {{ count($variants) }};

    function openVariantGalleryModal(index) {
        const modal = document.getElementById('variantGalleryModal_' + index);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeVariantGalleryModal(index) {
        const modal = document.getElementById('variantGalleryModal_' + index);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function removeVariantRow(button) {
        showConfirm('Are you sure you want to remove this variant?', () => {
            const row = button.closest('tr');
            const index = row.getAttribute('data-row-index');
            
            // Remove matching modal
            const modal = document.getElementById('variantGalleryModal_' + index);
            if (modal) modal.remove();
            
            // Remove row
            row.remove();
            
            // Re-validate to update inline errors
            validateVariants();
            
            // If table is empty, show empty message
            const body = document.getElementById('variantsTableBody');
            if (body.querySelectorAll('tr[data-row-index]').length === 0) {
                body.innerHTML = `
                    <tr id="emptyVariantsRow">
                        <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-table-list text-4xl mb-3 opacity-20 block"></i>
                            No variants defined for this product yet. Click "Add Variant Row" to get started.
                        </td>
                    </tr>
                `;
            }
        }, 'Remove Variant?');
    }

    function addVariantRow() {
        const body = document.getElementById('variantsTableBody');
        const emptyRow = document.getElementById('emptyVariantsRow');
        if (emptyRow) emptyRow.remove();

        const index = window.variantRowIndexCounter;
        
        // 1. Append Row
        const rowTemplate = document.getElementById('variantRowTemplate').innerHTML;
        const rowHtml = rowTemplate.replace(/__INDEX__/g, index);
        body.insertAdjacentHTML('beforeend', rowHtml);
        
        // 2. Append Modal
        const modalTemplate = document.getElementById('variantModalTemplate').innerHTML;
        const modalHtml = modalTemplate.replace(/__INDEX__/g, index);
        document.getElementById('variantModalsContainer').insertAdjacentHTML('beforeend', modalHtml);
        
        // 3. Increment Counter
        window.variantRowIndexCounter++;
    }

    function triggerAddVariantImage(index) {
        window.vUniqueIdCounter++;
        const uniqueId = 'vimg_' + window.vUniqueIdCounter;
        
        const input = document.createElement('input');
        input.type = 'file';
        input.name = `variants[${index}][images][${uniqueId}]`;
        input.accept = 'image/*';
        input.id = `input_variant_file_${index}_${uniqueId}`;
        input.className = 'hidden';
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const gallery = document.getElementById(`variant_image_gallery_${index}`);
                    const addCard = document.getElementById(`addVariantImageCard_${index}`);
                    
                    const cardHtml = `
                        <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm w-32 h-32 bg-slate-950 flex flex-col justify-between group" id="variant_new_card_${index}_${uniqueId}">
                            <img src="${e.target.result}" class="w-full h-full object-cover absolute inset-0">
                            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="text-rose-500 hover:text-rose-700 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full p-1.5 transition-colors outline-none cursor-pointer flex items-center justify-center w-7 h-7 shadow-sm border-none" onclick="removeNewVariantImage(${index}, '${uniqueId}')">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            <div class="absolute bottom-2 left-2 right-2 bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 flex items-center gap-1.5 shadow-sm z-10">
                                <input type="radio" name="variants[${index}][default_image]" value="new_${uniqueId}" id="radio_variant_new_${index}_${uniqueId}" class="accent-blue-600 cursor-pointer">
                                <label for="radio_variant_new_${index}_${uniqueId}" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider cursor-pointer select-none">Default</label>
                            </div>
                        </div>
                    `;
                    addCard.insertAdjacentHTML('beforebegin', cardHtml);
                    
                    // Update photo count badge
                    updatePhotoCountBadge(index);
                    
                    // Automatically check if it's the first one and no default is selected
                    const checkedRadio = document.querySelector(`input[name="variants[${index}][default_image]"]:checked`);
                    if (!checkedRadio) {
                        const newRadio = document.getElementById(`radio_variant_new_${index}_${uniqueId}`);
                        if (newRadio) newRadio.checked = true;
                    }
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                input.remove();
            }
        });
        
        // IMPORTANT: append to the in-form container, NOT the modal div,
        // so the file is included in the form submission.
        document.getElementById('variantFileInputsContainer').appendChild(input);
        input.click();
    }

    function removeNewVariantImage(index, uniqueId) {
        const card = document.getElementById(`variant_new_card_${index}_${uniqueId}`);
        if (card) card.remove();
        
        // Input now lives inside the form container, not the modal
        const input = document.getElementById(`input_variant_file_${index}_${uniqueId}`);
        if (input) input.remove();
        
        updatePhotoCountBadge(index);
        
        const checkedRadio = document.querySelector(`input[name="variants[${index}][default_image]"]:checked`);
        if (!checkedRadio) {
            const firstRadio = document.querySelector(`input[name="variants[${index}][default_image]"]`);
            if (firstRadio) firstRadio.checked = true;
        }
    }

    function removeExistingVariantImage(index, imageId) {
        showConfirm('Are you sure you want to delete this image?', () => {
            const card = document.getElementById(`variant_img_card_${imageId}`);
            if (card) card.remove();
            
            // Add to deletion list
            const hiddenInputs = document.getElementById(`variant_hidden_inputs_${index}`);
            hiddenInputs.insertAdjacentHTML('beforeend', `<input type="hidden" name="variants[${index}][deleted_images][]" value="${imageId}">`);
            
            updatePhotoCountBadge(index);
            
            const checkedRadio = document.querySelector(`input[name="variants[${index}][default_image]"]:checked`);
            if (!checkedRadio) {
                const firstRadio = document.querySelector(`input[name="variants[${index}][default_image]"]`);
                if (firstRadio) firstRadio.checked = true;
            }
        }, 'Delete Variant Image?');
    }

    function updatePhotoCountBadge(index) {
        const gallery = document.getElementById(`variant_image_gallery_${index}`);
        if (!gallery) return;
        const cards = gallery.querySelectorAll('div[id^="variant_img_card_"], div[id^="variant_new_card_"]');
        const badge = document.getElementById(`variant_img_count_${index}`);
        if (badge) {
            badge.textContent = cards.length;
        }
    }

    // Quick Attributes
    function openQuickAttributeModal(type) {
        const modal = document.getElementById('quickAttributeModal');
        const title = document.getElementById('quickAttrTitle');
        const formType = document.getElementById('quickAttrType');
        const extraLabel = document.getElementById('quickAttrExtraLabel');
        const extraInput = document.getElementById('quickAttrExtra');
        const colorPickerWrapper = document.getElementById('quickAttrExtraFieldWrapper');

        formType.value = type;
        document.getElementById('quickAttrName').value = '';
        extraInput.value = '';

        if (type === 'color') {
            title.textContent = 'Quick Add Color';
            extraLabel.textContent = 'Hex Code';
            extraInput.placeholder = '#800020';
            document.getElementById('quickAttrColorPicker').style.display = 'block';
        } else {
            title.textContent = 'Quick Add Size';
            extraLabel.textContent = 'Size Code / Abbreviation';
            extraInput.placeholder = 'XXL';
            document.getElementById('quickAttrColorPicker').style.display = 'none';
        }

        modal.classList.remove('hidden');
    }

    function closeQuickAttributeModal() {
        const modal = document.getElementById('quickAttributeModal');
        modal.classList.add('hidden');
    }

    function saveQuickAttribute(event) {
        event.preventDefault();
        const type = document.getElementById('quickAttrType').value;
        const name = document.getElementById('quickAttrName').value;
        const extra = document.getElementById('quickAttrExtra').value;

        const data = {
            _token: '{{ csrf_token() }}',
            type: type,
            name: name,
        };

        if (type === 'color') {
            data.hex_code = extra;
        } else {
            data.code = extra;
        }

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        fetch('{{ route(auth("seller")->check() ? "seller.products.variants.quick-add-attribute" : "admin.products.variants.quick-add-attribute", $product->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (res.success) {
                // Append option to all dropdowns
                const selectClass = type === 'color' ? '.color-select' : '.size-select';
                document.querySelectorAll(selectClass).forEach(select => {
                    const opt = document.createElement('option');
                    opt.value = res.id;
                    opt.textContent = type === 'color' ? res.name : `${res.name} (${res.extra})`;
                    select.appendChild(opt);
                });
                
                // Also update the templates!
                const templateId = 'variantRowTemplate';
                const temp = document.getElementById(templateId);
                if (temp) {
                    // Parse as DOM to append option
                    const container = document.createElement('div');
                    container.innerHTML = temp.innerHTML;
                    const selectEl = container.querySelector(selectClass);
                    if (selectEl) {
                        const opt = document.createElement('option');
                        opt.value = res.id;
                        opt.textContent = type === 'color' ? res.name : `${res.name} (${res.extra})`;
                        selectEl.appendChild(opt);
                        temp.innerHTML = container.innerHTML;
                    }
                }

                closeQuickAttributeModal();
                showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} "${name}" added successfully!`);
            } else {
                showToast(res.message || 'Error occurred.', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            console.error(err);
            showToast('Error connecting to the server.', 'error');
        });
    }

    function validateVariants(event) {
        const rows = document.querySelectorAll('#variantsTableBody tr[data-row-index]');
        
        // Reset validation state
        rows.forEach(row => {
            row.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20');
            const selects = row.querySelectorAll('.color-select, .size-select');
            selects.forEach(select => {
                select.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                select.classList.add('border-slate-200', 'dark:border-slate-700');
            });
            const errorMsgs = row.querySelectorAll('.variant-error-msg');
            errorMsgs.forEach(msg => {
                msg.classList.add('hidden');
                msg.textContent = '';
            });
        });

        const combinations = {};
        const emptyRows = [];
        let hasDuplicate = false;
        let hasEmpty = false;

        rows.forEach(row => {
            const colorSelect = row.querySelector('.color-select');
            const sizeSelect = row.querySelector('.size-select');
            const colorId = colorSelect ? colorSelect.value : '';
            const sizeId = sizeSelect ? sizeSelect.value : '';

            const hasColorSelect = !!colorSelect;
            const hasSizeSelect = !!sizeSelect;

            let isEmpty = false;
            if (hasColorSelect && hasSizeSelect) {
                isEmpty = !colorId && !sizeId;
            } else if (hasColorSelect) {
                isEmpty = !colorId;
            } else if (hasSizeSelect) {
                isEmpty = !sizeId;
            }

            if (isEmpty) {
                emptyRows.push(row);
                hasEmpty = true;
            } else {
                const key = `${colorId}_${sizeId}`;
                if (!combinations[key]) {
                    combinations[key] = [];
                }
                combinations[key].push(row);
            }
        });

        // Highlight and show inline error for empty rows
        emptyRows.forEach(row => {
            row.classList.add('bg-rose-50/50', 'dark:bg-rose-950/20');
            const colorSelect = row.querySelector('.color-select');
            const sizeSelect = row.querySelector('.size-select');
            const selects = [];
            if (colorSelect) selects.push(colorSelect);
            if (sizeSelect) selects.push(sizeSelect);
            
            selects.forEach(select => {
                select.classList.remove('border-slate-200', 'dark:border-slate-700');
                select.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
            });
            const errorMsgs = row.querySelectorAll('.variant-error-msg');
            errorMsgs.forEach(msg => {
                msg.textContent = 'Required selection';
                msg.classList.remove('hidden');
            });
        });

        // Highlight and show inline error for duplicate rows
        Object.keys(combinations).forEach(key => {
            const group = combinations[key];
            if (group.length > 1) {
                hasDuplicate = true;
                group.forEach(row => {
                    row.classList.add('bg-rose-50/50', 'dark:bg-rose-950/20');
                    const colorSelect = row.querySelector('.color-select');
                    const sizeSelect = row.querySelector('.size-select');
                    const selects = [];
                    if (colorSelect) selects.push(colorSelect);
                    if (sizeSelect) selects.push(sizeSelect);
                    
                    selects.forEach(select => {
                        select.classList.remove('border-slate-200', 'dark:border-slate-700');
                        select.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                    });
                    const errorMsgs = row.querySelectorAll('.variant-error-msg');
                    errorMsgs.forEach(msg => {
                        msg.textContent = 'Duplicate combination';
                        msg.classList.remove('hidden');
                    });
                });
            }
        });

        if (hasEmpty) {
            const hasColorSelect = !!document.querySelector('.color-select');
            const hasSizeSelect = !!document.querySelector('.size-select');
            let msg = 'Each variant must have a valid configuration.';
            if (hasColorSelect && hasSizeSelect) {
                msg = 'Each variant must have at least a Color or a Size selected.';
            } else if (hasColorSelect) {
                msg = 'Each variant must have a Color selected.';
            } else if (hasSizeSelect) {
                msg = 'Each variant must have a Size selected.';
            }
            showToast(msg, 'error');
            if (event) {
                event.preventDefault();
            }
            return false;
        }

        if (hasDuplicate) {
            showToast('Duplicate variant combinations are not allowed.', 'error');
            if (event) {
                event.preventDefault();
            }
            return false;
        }

        return true;
    }

    // Dynamic real-time validation on select changes
    document.addEventListener('DOMContentLoaded', () => {
        const tableBody = document.getElementById('variantsTableBody');
        if (tableBody) {
            tableBody.addEventListener('change', (e) => {
                if (e.target.classList.contains('color-select') || e.target.classList.contains('size-select')) {
                    validateVariants();
                }
            });
        }
    });
</script>
