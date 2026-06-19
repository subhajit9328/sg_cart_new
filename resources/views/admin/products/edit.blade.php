@extends('layouts.admin')

@section('title', 'Edit Product — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.products.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Product</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Products', 'url' => route('admin.products.index')],
            ['label' => 'Edit'],
            ['label' => $product->name, 'mono' => true]
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <h2 class="font-semibold text-sm">Update Product Information</h2>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product->ulid) }}" enctype="multipart/form-data" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        {{-- ── Basic Info Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="iPhone 15 Pro">
                    @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">SKU *</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 font-mono @error('sku') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="IPH15P-128GB">
                    @error('sku') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Category</label>
                    <select name="category_id" id="category_id"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->parent ? $cat->parent->name . ' › ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manufacturer_id" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Manufacturer / Vendor</label>
                    <select name="manufacturer_id" id="manufacturer_id"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                        <option value="">— Select Vendor —</option>
                        @foreach($manufacturers as $m)
                            <option value="{{ $m->id }}" {{ old('manufacturer_id', $product->manufacturer_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="short_description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Short Description</label>
                <textarea name="short_description" id="short_description" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Brief description of the product highlights...">{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div>
                <label for="description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Full Description</label>
                <textarea name="description" id="description" rows="5"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Provide full description of the product...">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        {{-- ── Pricing & Inventory Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Pricing & Inventory</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Price (₹) *</label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label for="sale_price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Sale Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label for="stock" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Stock Inventory *</label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Publish Status</label>
                    <select name="status" id="status"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                        @foreach(['draft','active','inactive'] as $s)
                            <option value="{{ $s }}" {{ old('status', $product->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="weight" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Weight</label>
                    <input type="text" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" placeholder="e.g. 500g"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label for="dimensions" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Dimensions (L×W×H)</label>
                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="e.g. 10x5x3 cm"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
            </div>

            <label class="flex items-center gap-3 cursor-pointer select-none py-1.5 w-fit">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Featured Product</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Show this product in storefront featured carousel or landing cards.</span>
                </div>
            </label>
        </div>

        {{-- ── Product Variants Section ── --}}
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-1 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Variants (Size / Colour / Weight)</h3>
                <button type="button" id="addVariant" class="text-blue-600 dark:text-blue-400 text-xs font-semibold hover:underline bg-transparent border-none outline-none cursor-pointer">+ Add Variant</button>
            </div>
            <div id="variantsContainer" class="space-y-4">
                @foreach($product->variants as $index => $variant)
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 rounded-lg p-4 variant-row relative">
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SKU *</label>
                        <input type="text" name="variants[{{ $index }}][sku]" value="{{ old("variants.$index.sku", $variant->sku) }}" required
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1 font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Size</label>
                        <input type="text" name="variants[{{ $index }}][size]" value="{{ old("variants.$index.size", $variant->size) }}"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Colour</label>
                        <input type="text" name="variants[{{ $index }}][colour]" value="{{ old("variants.$index.colour", $variant->colour) }}"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Weight</label>
                        <input type="text" name="variants[{{ $index }}][weight]" value="{{ old("variants.$index.weight", $variant->weight) }}"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Price (₹)</label>
                        <input type="number" step="0.01" name="variants[{{ $index }}][price]" value="{{ old("variants.$index.price", $variant->price) }}"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Stock</label>
                        <input type="number" name="variants[{{ $index }}][stock]" value="{{ old("variants.$index.stock", $variant->stock) }}"
                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
                    </div>
                    <div class="col-span-1 md:col-span-6 text-right">
                        <button type="button" onclick="this.closest('.variant-row').remove()" 
                            class="text-rose-500 hover:text-rose-600 hover:underline text-xs bg-transparent border-none outline-none cursor-pointer"><i class="fa-solid fa-xmark mr-1"></i>Remove Variant</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Specifications Section ── --}}
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-1 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Specifications</h3>
                <button type="button" id="addSpec" class="text-blue-600 dark:text-blue-400 text-xs font-semibold hover:underline bg-transparent border-none outline-none cursor-pointer">+ Add Row</button>
            </div>
            <div id="specsContainer" class="space-y-3">
                @foreach($product->specifications as $index => $spec)
                <div class="flex gap-3 items-center spec-row">
                    <input type="text" name="specs[{{ $index }}][label]" value="{{ $spec->label }}" placeholder="Label (e.g. Battery)" required
                        class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100">
                    <input type="text" name="specs[{{ $index }}][value]" value="{{ $spec->value }}" placeholder="Value (e.g. 5000 mAh)" required
                        class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100">
                    <button type="button" onclick="this.closest('.spec-row').remove()" 
                        class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors text-rose-500 bg-transparent cursor-pointer"><i class="fa-solid fa-trash-can text-xs"></i></button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Image Gallery Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Image Gallery</h3>
            
            @if($product->images->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 p-4 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 rounded-xl">
                    @foreach($product->images as $img)
                        <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm group w-full aspect-square bg-slate-950 flex items-center justify-center">
                            <img src="{{ Storage::url($img->path) }}" class="w-full h-full object-cover">
                            
                            <!-- Badges -->
                            <div class="absolute top-2 left-2 flex gap-1.5 flex-col">
                                <span class="bg-black/60 backdrop-blur-sm text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full w-fit">#{{ $img->sort_order }}</span>
                                @if($img->is_primary)
                                    <span class="bg-blue-600/90 text-white text-[8px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded-full w-fit">Primary</span>
                                @endif
                            </div>

                            <!-- Actions Overlay -->
                            <div class="absolute inset-0 bg-slate-950/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2 text-center">
                                <label class="flex items-center gap-1.5 text-[10px] text-white font-semibold cursor-pointer">
                                    <input type="radio" name="primary_image_id" value="{{ $img->id }}" {{ $img->is_primary ? 'checked' : '' }}
                                        class="accent-blue-500 w-3 h-3 cursor-pointer">
                                    <span>Set Primary</span>
                                </label>
                                
                                <button type="button" onclick="confirmDeleteImage('{{ $img->ulid }}')" 
                                    class="px-2 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold transition-all border-none cursor-pointer flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-trash-can text-[9px]"></i> Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-lg space-y-3">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Upload New Images</label>
                    <input type="file" name="images[]" multiple accept="image/*" id="imageInput"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-800 dark:text-slate-200 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-500/10 dark:file:text-blue-400 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <div id="imagePreview" class="flex flex-wrap gap-4 mt-2"></div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Update Product
            </button>
        </div>

    </form>
</div>

{{-- Hidden Form for Image Deletion --}}
<form id="deleteImageForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
// ── Variants ──
let variantIdx = {{ $product->variants->count() }};
document.getElementById('addVariant').addEventListener('click', () => {
    const i = variantIdx++;
    const html = `
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 rounded-lg p-4 variant-row relative">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SKU *</label>
            <input type="text" name="variants[${i}][sku]" placeholder="VAR-SKU-001" required
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1 font-mono">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Size</label>
            <input type="text" name="variants[${i}][size]" placeholder="M / XL"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Colour</label>
            <input type="text" name="variants[${i}][colour]" placeholder="Red"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Weight</label>
            <input type="text" name="variants[${i}][weight]" placeholder="200g"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Price (₹)</label>
            <input type="number" step="0.01" name="variants[${i}][price]" placeholder="12.99"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Stock</label>
            <input type="number" name="variants[${i}][stock]" value="0"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100 mt-1">
        </div>
        <div class="col-span-1 md:col-span-6 text-right">
            <button type="button" onclick="this.closest('.variant-row').remove()" 
                class="text-rose-500 hover:text-rose-600 hover:underline text-xs bg-transparent border-none outline-none cursor-pointer"><i class="fa-solid fa-xmark mr-1"></i>Remove Variant</button>
        </div>
    </div>`;
    document.getElementById('variantsContainer').insertAdjacentHTML('beforeend', html);
});

// ── Specs ──
let specIdx = {{ $product->specifications->count() }};
document.getElementById('addSpec').addEventListener('click', () => {
    const i = specIdx++;
    const html = `
    <div class="flex gap-3 items-center spec-row">
        <input type="text" name="specs[${i}][label]" placeholder="Label (e.g. Battery)" required
            class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100">
        <input type="text" name="specs[${i}][value]" placeholder="Value (e.g. 5000 mAh)" required
            class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100">
        <button type="button" onclick="this.closest('.spec-row').remove()" 
            class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors text-rose-500 bg-transparent cursor-pointer"><i class="fa-solid fa-trash-can text-xs"></i></button>
    </div>`;
    document.getElementById('specsContainer').insertAdjacentHTML('beforeend', html);
});

// ── Image Preview ──
document.getElementById('imageInput').addEventListener('change', function () {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    [...this.files].forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            preview.insertAdjacentHTML('beforeend', `
                <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm w-24 h-24 group">
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <span class="absolute top-1.5 left-1.5 bg-black/60 backdrop-blur-sm text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">#${i}</span>
                </div>`);
        };
        reader.readAsDataURL(file);
    });
});

// ── Image Delete ──
function confirmDeleteImage(imageId) {
    showConfirm(
        'Are you sure you want to remove this image from the gallery? This action is permanent.',
        () => {
            const form = document.getElementById('deleteImageForm');
            form.action = `/admin/products/images/${imageId}`;
            form.submit();
        },
        'Remove Image?'
    );
}
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#category_id').select2({
            placeholder: "— Select Category —",
            allowClear: true,
            width: '100%'
        });
        $('#manufacturer_id').select2({
            placeholder: "— Select Vendor —",
            allowClear: true,
            width: '100%'
        });
        $('#status').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
    });
</script>
@endpush
@endsection
