@extends('marketplace::layouts.seller')

@section('title', 'Edit Product — Seller Portal')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('seller.products.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-55 dark:hover:bg-slate-800 transition-colors no-underline">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="font-display text-xl sm:text-2xl font-bold">Edit Product</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Seller Portal', 'url' => route('seller.dashboard')],
                ['label' => 'Catalogue'],
                ['label' => 'My Products', 'url' => route('seller.products.index')],
                ['label' => 'Edit'],
                ['label' => $product->name, 'mono' => true]
            ]" />
        </div>
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
        @if(Route::has('seller.products.variants.grid'))
        <button type="button" onclick="switchTab('variants')" id="tabBtn_variants" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-tags text-xs"></i>
            <span>Product Variants</span>
        </button>
        @endif
    </div>
</div>

<div id="mainProductCard" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <h2 class="font-semibold text-sm font-display" id="cardTitle">Update Product Information</h2>
    </div>

    <form method="POST" action="{{ route('seller.products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div id="detailsTabContent" class="tab-content p-4 sm:p-6 space-y-6 sm:space-y-8">

        {{-- ── Basic Info Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Product Name <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="iPhone 15 Pro">
                    @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="slug" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">SEO Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" placeholder="auto-generated-from-name"
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
                    <label for="manufacturer_id" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Brand / Manufacturer</label>
                    <select name="manufacturer_id" id="manufacturer_id"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                        <option value="">— Select Brand —</option>
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
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Price (₹) <span class="text-rose-600">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('price') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sale_price" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Sale Price (₹)</label>
                    <input type="number" step="0.01" min="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('sale_price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('sale_price') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stock" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Stock Inventory <span class="text-rose-600">*</span></label>
                    @php
                        $hasActiveVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class) && $product->variants()->where('is_active', true)->exists();
                    @endphp
                    @if($hasActiveVariants)
                        <input type="number" id="stock" value="{{ $product->stock }}" readonly
                            class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm outline-none text-slate-500 cursor-not-allowed font-mono" style="pointer-events: none;">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1.5 font-medium">
                            <i class="fa-solid fa-circle-info"></i> Stock is automatically calculated from active product variants.
                        </p>
                        <input type="hidden" name="stock" value="{{ $product->stock }}">
                    @else
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" required min="0"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @endif
                    @error('stock') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="min_stock" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Minimum Stock <span class="text-rose-600">*</span></label>
                    <input type="number" name="min_stock" id="min_stock" value="{{ old('min_stock', $product->min_stock ?? 5) }}" required min="0"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('min_stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('min_stock') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
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
        </div>

        {{-- ── Product Image Section ── --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Product Images</h3>
            
            <div class="p-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-xl space-y-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Manage product images below. Choose a default image using the radio button on the image card.</p>
                
                <div id="imageGallery" class="flex flex-wrap gap-4">
                    <!-- Existing Images -->
                    @if($product->images && $product->images->count() > 0)
                        @foreach($product->images as $img)
                            <div class="relative border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm w-32 h-32 bg-slate-950 flex flex-col justify-between group">
                                <img src="{{ Storage::url($img->image_path) }}" class="w-full h-full object-cover absolute inset-0">
                                
                                <!-- Delete Button -->
                                <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" onclick="if(confirm('Are you sure you want to delete this image?')) document.getElementById('deleteImageForm_{{ $img->id }}').submit();"
                                        class="text-rose-500 hover:text-rose-700 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full p-1.5 transition-colors outline-none cursor-pointer flex items-center justify-center w-7 h-7 shadow-sm" 
                                        title="Delete Image">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                                
                                <!-- Default Selector -->
                                <div class="absolute bottom-2 left-2 right-2 bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 flex items-center gap-1.5 shadow-sm z-10">
                                    <input type="radio" name="default_image" value="existing_{{ $img->id }}" id="radio_existing_{{ $img->id }}" {{ $img->is_default ? 'checked' : '' }} class="accent-blue-600 cursor-pointer">
                                    <label for="radio_existing_{{ $img->id }}" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider cursor-pointer select-none">Default</label>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    
                    <!-- Add Image Button Card -->
                    <div id="addImageCard" class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl w-32 h-32 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-slate-100/50 dark:hover:bg-slate-800/30 gap-1.5 group select-none" onclick="triggerAddImage()">
                        <i class="fa-solid fa-circle-plus text-2xl text-slate-400 dark:text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Add Image</span>
                    </div>
                </div>
            </div>
            
            <div id="hiddenInputsContainer" class="hidden"></div>
        </div>
        </div> {{-- Close detailsTabContent --}}

        <div id="seoTabContent" class="tab-content hidden p-4 sm:p-6 space-y-6 sm:space-y-8">
            {{-- ── SEO Metadata Section ── --}}
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">SEO Metadata</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_title') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            placeholder="Leave empty to use Product Name">
                        @error('meta_title') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_keywords') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            placeholder="keyword1, keyword2, keyword3">
                        @error('meta_keywords') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="meta_description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('meta_description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="Leave empty to fallback to Product Short Description/Description...">{{ old('meta_description', $product->meta_description) }}</textarea>
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
        </div> {{-- Close seoTabContent --}}

        <!-- Form Actions -->
        <div id="formActions" class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 p-4 sm:p-6 border-t border-slate-100 dark:border-slate-800 w-full">
            <a href="{{ route('seller.products.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-55 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer flex items-center">
                Update Product
            </button>
        </div>
    </form>
</div>

@if(Route::has('seller.products.variants.grid'))
<!-- Variants Tab Content -->
<div id="variantsTabContent" class="tab-content hidden p-4 sm:p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
    <!-- Loader Skeleton -->
    <div id="variantsLoader" class="space-y-6 animate-pulse">
        <!-- Header Skeleton -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-36 mb-2"></div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800/60 rounded w-96"></div>
            </div>
            <div class="flex gap-2">
                <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded-lg w-32"></div>
                <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded-lg w-28"></div>
                <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded-lg w-36"></div>
            </div>
        </div>

        <!-- Spreadsheet Grid Skeleton -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-44">Color</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-44">Size</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">SKU Override</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Price (₹)</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Sale Price (₹)</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-32">Stock Qty</th>
                            <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-24">Active</th>
                            <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Gallery</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @for($i = 0; $i < 3; $i++)
                        <tr class="transition-colors">
                            <td class="px-4 py-3.5 w-44"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5 w-44"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5 w-36"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5 w-36"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5 w-32"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div></td>
                            <td class="px-4 py-3.5 w-24 text-center"><div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto"></div></td>
                            <td class="px-4 py-3.5 w-36 text-center"><div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-24 mx-auto"></div></td>
                            <td class="px-4 py-3.5 w-16 text-right"><div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg ml-auto"></div></td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Grid Container -->
    <div id="variantsGridContainer"></div>
</div>
@endif

@if($product->images && $product->images->count() > 0)
    @foreach($product->images as $img)
        <form id="deleteImageForm_{{ $img->id }}" action="{{ route('seller.products.delete-image', $img->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

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
                            <button type="button" class="text-rose-500 hover:text-rose-700 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-full p-1.5 transition-colors outline-none cursor-pointer flex items-center justify-center w-7 h-7 shadow-sm" onclick="removeNewImage('${id}')">
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
                
                // Automatically check if it's the first one added and no default is selected
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
    // Tab toggler logic
    window.switchTab = function(tab) {
        const tabs = ['details', 'seo'];
        @if(Route::has('seller.products.variants.grid'))
        tabs.push('variants');
        @endif
        const cardTitle = document.getElementById('cardTitle');
        const formActions = document.getElementById('formActions');
        const mainCard = document.getElementById('mainProductCard');

        if (mainCard) {
            if (tab === 'variants') {
                mainCard.classList.add('hidden');
            } else {
                mainCard.classList.remove('hidden');
            }
        }

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
                cardTitle.textContent = "Update Product Information";
            } else if (tab === 'seo') {
                let metaTitle = $('#meta_title').val();
                let metaDescription = $('#meta_description').val();
                let metaKeywords = $('#meta_keywords').val();
                if (metaTitle || metaDescription || metaKeywords) {
                    cardTitle.textContent = "Configure SEO Metadata & Preview";
                } else {
                    cardTitle.textContent = "Add SEO Metadata";
                }
            } else if (tab === 'variants') {
                cardTitle.textContent = "Manage Product Variants & Inventory";
            }
        }

        if (formActions) {
            if (tab === 'variants') {
                formActions.classList.add('hidden');
            } else {
                formActions.classList.remove('hidden');
            }
        }

        // Store tab in URL query parameter history
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);

        // Load grid if switching to variants
        if (tab === 'variants') {
            @if(Route::has('seller.products.variants.grid'))
            loadVariantsGrid();
            @endif
        }
    };

    @if(Route::has('seller.products.variants.grid'))
    window.loadVariantsGrid = function() {
        const $gridContainer = $('#variantsGridContainer');
        const loader = document.getElementById('variantsLoader');
        
        loader.classList.remove('hidden');
        $gridContainer.empty();

        $.ajax({
            url: '{{ route("seller.products.variants.grid", $product->id) }}',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html) {
                loader.classList.add('hidden');
                $gridContainer.html(html);
            },
            error: function(err) {
                loader.classList.add('hidden');
                $gridContainer.html('<p class="text-rose-500 text-sm">Failed to load variants grid.</p>');
                console.error(err);
            }
        });
    };
    @endif

    $(document).ready(function() {
        $('#category_id').select2({
            placeholder: "— Select Category —",
            allowClear: true,
            width: '100%'
        });
        $('#manufacturer_id').select2({
            placeholder: "— Select Brand —",
            allowClear: true,
            width: '100%'
        });
        $('#status').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });

        // Auto-populate slug from product name on edit in real-time
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
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'details';
            if (activeTab === 'seo') {
                const cardTitle = document.getElementById('cardTitle');
                if (cardTitle) {
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
        @if(Route::has('seller.products.variants.grid'))
        allowedTabs.push('variants');
        @endif
        if (allowedTabs.includes(activeTab)) {
            switchTab(activeTab);
        } else {
            switchTab('details');
        }
    });
</script>
@endpush
@endsection
