@extends('layouts.admin')

@section('title', 'Add Product — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.products.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-xl sm:text-2xl font-bold">Add Product</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Products', 'url' => route('admin.products.index')],
            ['label' => 'Add']
        ]" />
    </div>
</div>

<!-- Tab Navigation (Underline Style outside the card) -->
<div class="flex items-center border-b border-slate-200 dark:border-slate-800 mb-6 overflow-x-auto scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
    <div class="flex gap-1 -mb-px min-w-max">
        <button type="button" onclick="switchTab('details')" id="tabBtn_details" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-xs"></i>
            <span>Basic Details</span>
        </button>
        <button type="button" onclick="switchTab('seo')" id="tabBtn_seo" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-search text-xs"></i>
            <span>SEO Metadata</span>
        </button>
        @if(Route::has('admin.products.variants.grid'))
        <x-tooltip content="Product must be created first before you can configure variants." position="top" theme="dark" width="w-64">
            <button type="button" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-400 dark:text-slate-500 outline-none flex items-center gap-2 bg-transparent cursor-not-allowed" disabled>
                <i class="fa-solid fa-tags text-xs text-slate-400 dark:text-slate-500"></i>
                <span>Product Variants</span>
                <i class="fa-solid fa-lock text-[10px]"></i>
            </button>
        </x-tooltip>
        @endif
        @if(class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class))
        <button type="button" onclick="switchTab('related')" id="tabBtn_related" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-link text-xs"></i>
            <span>Related Products</span>
        </button>
        @endif
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-wrap justify-between items-center gap-4">
        <h2 class="font-semibold text-sm font-display" id="cardTitle">Enter Product Specifications & Details</h2>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-6 sm:space-y-8">
        @csrf

        <div id="detailsTabContent" class="tab-content space-y-8">

        {{-- ── Basic Info Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Basic Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Product Name <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="iPhone 15 Pro">
                    @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="slug" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">SEO Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="auto-generated-from-name"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('slug') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2 flex items-center gap-1.5">
                        <span>SKU</span>
                        <span class="text-rose-600">*</span>
                        <x-tooltip content="Stock Keeping Unit: a unique identifier for this product variant." position="top" theme="info" width="w-56">
                            <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-blue-500 cursor-help text-xs"></i>
                        </x-tooltip>
                    </label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
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
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                            <option value="{{ $m->id }}" {{ old('manufacturer_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="short_description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Short Description</label>
                <textarea name="short_description" id="short_description" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Brief description of the product highlights..."></textarea>
            </div>

            <div>
                <label for="description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Full Description</label>
                <textarea name="description" id="description" rows="5"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Provide full description of the product..."></textarea>
            </div>
        </div>

        {{-- ── Pricing & Inventory Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Pricing & Inventory</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Price (₹) <span class="text-rose-600">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="price" id="price" value="{{ old('price') }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('price') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sale_price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Sale Price (₹)</label>
                    <input type="number" step="0.01" min="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('sale_price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('sale_price') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stock" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Stock Inventory <span class="text-rose-600">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 1) }}" required min="0"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('stock') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2 flex items-center gap-1.5">
                        <span>Publish Status</span>
                        <x-tooltip content="Draft: invisible to shoppers. Active: published in storefront. Inactive: hidden temporarily." position="top" theme="info" width="w-64">
                            <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-blue-500 cursor-help text-xs"></i>
                        </x-tooltip>
                    </label>
                    <select name="status" id="status"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                        @foreach(['draft','active','inactive'] as $s)
                            <option value="{{ $s }}" {{ old('status','draft') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="weight" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Weight</label>
                    <input type="text" name="weight" id="weight" value="{{ old('weight') }}" placeholder="e.g. 500g"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label for="dimensions" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Dimensions (L×W×H)</label>
                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions') }}" placeholder="e.g. 10x5x3 cm"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                </div>
            </div>


        </div>

        {{-- ── Product Image Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Product Images</h3>

            <div class="p-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-xl space-y-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Add product images below. Choose a default image using the radio button on the image card.</p>

                <div id="imageGallery" class="flex flex-wrap gap-4">
                    <!-- Dynamic Preview Cards will be inserted here -->

                    <!-- Add Image Button Card -->
                    <div id="addImageCard" class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl w-32 h-32 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-slate-100/50 dark:hover:bg-slate-800/30 gap-1.5 group select-none" onclick="triggerAddImage()">
                        <i class="fa-solid fa-circle-plus text-2xl text-slate-400 dark:text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Add Image</span>
                    </div>
                </div>
            </div>

            <div id="hiddenInputsContainer" class="hidden"></div>
        </div>

            <div id="hiddenInputsContainer" class="hidden"></div>
        </div>
        {{-- Close detailsTabContent --}}

        <div id="seoTabContent" class="tab-content hidden space-y-8">
            {{-- ── SEO Metadata Section ── --}}
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">SEO Metadata</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_title') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            placeholder="Leave empty to use Product Name">
                        @error('meta_title') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_keywords') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            placeholder="keyword1, keyword2, keyword3">
                        @error('meta_keywords') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="meta_description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="Leave empty to fallback to Product Short Description/Description...">{{ old('meta_description') }}</textarea>
                    @error('meta_description') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- Live SEO Preview Card --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-xl space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Google Search Snippet Preview</span>
                    <div class="text-sm font-display space-y-1">
                        <div id="seo-preview-url" class="text-xs text-emerald-600 dark:text-emerald-500 truncate font-mono">
                            {{ url('/products') }}/<span id="seo-preview-slug-text"></span>
                        </div>
                        <div id="seo-preview-title" class="text-blue-700 dark:text-blue-400 font-medium text-lg leading-tight hover:underline cursor-pointer truncate">
                            Product Name
                        </div>
                        <div id="seo-preview-description" class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                            Enter meta description details to preview search snippet here.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class))
        {{-- ── Related Products Tab Content ── --}}
        <div id="relatedTabContent" class="tab-content hidden">
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Related Products</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Search and select products below to link them as related products. They will appear as cards in the grid below.</p>

                <div>
                    <label for="related_product_search" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Search Products</label>
                    <select id="related_product_search" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none">
                        <option value="">— Search & Select Product —</option>
                        @foreach(\App\Models\Product::where('status', 'active')->get() as $p)
                            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-sku="{{ $p->sku }}" data-price="{{ $p->price }}" data-cat="{{ $p->category ? $p->category->name : 'Uncategorized' }}" data-img="{{ $p->image ? Storage::url($p->image) : asset('images/no-image.svg') }}">
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hidden select containing actual selected values to be submitted --}}
                <select name="related_product_ids[]" id="related_product_ids" class="hidden" multiple></select>

                {{-- Selected products grid matching storefront style --}}
                <div class="space-y-2 mt-6">
                    <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Selected Related Products</h4>
                    <p id="noRelatedPlaceholder" class="text-sm text-slate-500 dark:text-slate-400">No related products added.</p>
                    <div id="relatedProductsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 hidden">
                        {{-- Product cards will be dynamically appended here --}}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Actions -->
        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 w-full">
            <a href="{{ route('admin.products.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer flex items-center">
                Create Product
            </button>
        </div>

    </form>
</div>

<script>
let uniqueIdCounter = 0;

function triggerAddImage() {
    uniqueIdCounter++;
    const id = 'img_' + uniqueIdCounter;

    // Create dynamic file input
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'product_images[' + id + ']';
    input.accept = 'image/*';
    input.id = 'input_' + id;
    input.className = 'hidden';

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const gallery = document.getElementById('imageGallery');
                const addCard = document.getElementById('addImageCard');

                const cardHtml = `
                    <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm w-32 h-32 bg-slate-950 flex flex-col justify-between group" id="preview_card_${id}">
                        <img src="${e.target.result}" class="w-full h-full object-cover absolute inset-0">
                        <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" class="text-rose-500 hover:text-rose-700 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full p-1.5 transition-colors outline-none cursor-pointer flex items-center justify-center w-7 h-7 shadow-sm" onclick="removeNewImage('${id}')">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                        <div class="absolute bottom-2 left-2 right-2 bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 flex items-center gap-1.5 shadow-sm z-10">
                            <input type="radio" name="default_image" value="new_${id}" id="radio_new_${id}" class="accent-blue-600 cursor-pointer">
                            <label for="radio_new_${id}" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider cursor-pointer select-none">Default</label>
                        </div>
                    </div>
                `;
                addCard.insertAdjacentHTML('beforebegin', cardHtml);

                // Automatically check if it's the first one added
                const checkedRadio = document.querySelector('input[name="default_image"]:checked');
                if (!checkedRadio) {
                    document.getElementById('radio_new_' + id).checked = true;
                }
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            input.remove();
        }
    });

    document.getElementById('hiddenInputsContainer').appendChild(input);
    input.click();
}

function removeNewImage(id) {
    document.getElementById('preview_card_' + id).remove();
    document.getElementById('input_' + id).remove();

    const checkedRadio = document.querySelector('input[name="default_image"]:checked');
    if (!checkedRadio) {
        const firstRadio = document.querySelector('input[name="default_image"]');
        if (firstRadio) firstRadio.checked = true;
    }
}
</script>

@push('scripts')
<script>
    window.switchTab = function(tab) {
        const tabs = ['details', 'seo'];
        if (document.getElementById('tabBtn_related')) {
            tabs.push('related');
        }
        const cardTitle = document.getElementById('cardTitle');

        tabs.forEach(t => {
            const btn = document.getElementById('tabBtn_' + t);
            const content = document.getElementById(t + 'TabContent');
            if (t === tab) {
                btn.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2";
                content.classList.remove('hidden');
            } else {
                btn.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2";
                content.classList.add('hidden');
            }
        });

        if (cardTitle) {
            if (tab === 'details') {
                cardTitle.textContent = "Enter Product Specifications & Details";
            } else if (tab === 'seo') {
                let metaTitle = $('#meta_title').val();
                let metaDescription = $('#meta_description').val();
                let metaKeywords = $('#meta_keywords').val();
                if (metaTitle || metaDescription || metaKeywords) {
                    cardTitle.textContent = "Configure SEO Metadata & Preview";
                } else {
                    cardTitle.textContent = "Add SEO Metadata";
                }
            } else if (tab === 'related') {
                cardTitle.textContent = "Configure Related Products";
            }
        }
    };

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

        if ($('#related_product_search').length) {
            $('#related_product_search').select2({
                placeholder: "— Search & Select Product —",
                allowClear: true,
                width: '100%',
                templateResult: formatProductOption,
                templateSelection: formatProductSelection
            });

            function formatProductOption(state) {
                if (!state.id) {
                    return state.text;
                }
                const img = $(state.element).data('img');
                const sku = $(state.element).data('sku');
                const cat = $(state.element).data('cat');

                const $state = $(
                    `<div class="flex items-center gap-3">
                        <img src="${img}" class="w-8 h-8 rounded object-cover" />
                        <div>
                            <div class="font-semibold text-sm">${state.text}</div>
                            <div class="text-[10px] text-slate-400 font-mono">${sku} | ${cat}</div>
                        </div>
                    </div>`
                );
                return $state;
            }

            function formatProductSelection(state) {
                return state.text;
            }

            const selectedRelatedIds = new Set();

            $('#related_product_search').on('select2:select', function (e) {
                const data = e.params.data;
                const element = data.element;
                if (!element) return;

                const id = parseInt(data.id);
                const name = $(element).data('name');
                const sku = $(element).data('sku');
                const price = $(element).data('price');
                const cat = $(element).data('cat');
                const img = $(element).data('img');

                addRelatedProduct(id, { name, sku, price, cat, img });

                $(this).val('').trigger('change');
            });

            window.addRelatedProduct = function(id, data) {
                if (selectedRelatedIds.has(id)) return;
                selectedRelatedIds.add(id);

                updateHiddenRelatedSelect();

                const cardHtml = `
                    <div class="relative group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300" id="related_card_${id}">
                        <div class="aspect-square bg-slate-50 dark:bg-slate-900/50 relative overflow-hidden flex items-center justify-center">
                            <img src="${data.img}" alt="${data.name}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <button type="button" onclick="removeRelatedProduct(${id})" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 hover:bg-rose-500 hover:text-white dark:bg-slate-800/90 dark:hover:bg-rose-600 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm cursor-pointer transition-colors z-10 border-none" title="Remove Related Product">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                        <div class="p-3.5 space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">${data.cat}</p>
                            <h3 class="font-display font-semibold text-sm text-slate-800 dark:text-slate-100 line-clamp-1">${data.name}</h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="font-bold text-sm text-slate-900 dark:text-slate-100">₹${parseFloat(data.price).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
                $('#relatedProductsGrid').append(cardHtml);
                checkPlaceholder();
            }

            window.removeRelatedProduct = function(id) {
                selectedRelatedIds.delete(id);
                $(`#related_card_${id}`).remove();
                updateHiddenRelatedSelect();
                checkPlaceholder();
            }

            function updateHiddenRelatedSelect() {
                const $select = $('#related_product_ids');
                $select.empty();
                selectedRelatedIds.forEach(id => {
                    $select.append(`<option value="${id}" selected>${id}</option>`);
                });
            }

            function checkPlaceholder() {
                const $grid = $('#relatedProductsGrid');
                const $placeholder = $('#noRelatedPlaceholder');
                if (selectedRelatedIds.size === 0) {
                    $placeholder.removeClass('hidden');
                    $grid.addClass('hidden');
                } else {
                    $placeholder.addClass('hidden');
                    $grid.removeClass('hidden');
                }
            }

            // Init placeholder check
            checkPlaceholder();
        }

        // Auto-populate slug from product name in real-time
        $('#name').on('input', function() {
            let slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            $('#slug').val(slug);
        });

        // SEO Real-time Snippet Preview Script
        function updateSeoPreview() {
            let name = $('#name').val() || 'Product Name';
            let slug = $('#slug').val() || '';
            let metaTitle = $('#meta_title').val();
            let metaDescription = $('#meta_description').val();
            let metaKeywords = $('#meta_keywords').val();
            let shortDesc = $('#short_description').val();
            let fullDesc = $('#description').val();

            // Set Title Preview
            $('#seo-preview-title').text(metaTitle ? metaTitle : name);

            // Set Slug/URL Preview
            $('#seo-preview-slug-text').text(slug);

            // Set Description Preview
            let descPreview = 'Enter meta description details to preview search snippet here.';
            if (metaDescription) {
                descPreview = metaDescription;
            } else if (shortDesc) {
                descPreview = shortDesc;
            } else if (fullDesc) {
                descPreview = fullDesc;
            }
            $('#seo-preview-description').text(descPreview);

            // Dynamically update card title if currently on SEO tab
            const cardTitle = document.getElementById('cardTitle');
            if (cardTitle) {
                const isSeoActive = $('#tabBtn_seo').hasClass('border-blue-600') || $('#tabBtn_seo').hasClass('dark:border-blue-500');
                if (isSeoActive) {
                    if (metaTitle || metaDescription || metaKeywords) {
                        cardTitle.textContent = "Configure SEO Metadata & Preview";
                    } else {
                        cardTitle.textContent = "Add SEO Metadata";
                    }
                }
            }
        }

        $('#name, #slug, #meta_title, #meta_description, #short_description, #description').on('input change', function() {
            updateSeoPreview();
        });

        // Initialize SEO Preview
        updateSeoPreview();

        // Check for active tab query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'details';
        const allowedTabs = ['details', 'seo'];
        if (document.getElementById('tabBtn_related')) {
            allowedTabs.push('related');
        }
        if (allowedTabs.includes(activeTab)) {
            switchTab(activeTab);
        } else {
            switchTab('details');
        }
    });
</script>
@endpush
@endsection
