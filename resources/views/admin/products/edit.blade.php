@extends('layouts.admin')

@section('title', 'Edit Product — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="font-display text-xl sm:text-2xl font-bold">Edit Product</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'Catalogue'],
                ['label' => 'Products', 'url' => route('admin.products.index')],
                ['label' => 'Edit'],
                ['label' => $product->name, 'mono' => true]
            ]" />
        </div>
    </div>
    <div class="w-full sm:w-auto">
        @if($product->status === 'active')
            <a href="{{ route('store.product', $product->slug) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                <i class="fa-solid fa-eye text-slate-500 dark:text-slate-400"></i>
                <span>Preview Product</span>
            </a>
        @else
            <span class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-slate-400 dark:text-slate-600 rounded-lg text-sm font-semibold cursor-not-allowed shadow-sm" title="Product must be active to view on storefront">
                <i class="fa-solid fa-eye text-slate-400 dark:text-slate-600"></i>
                <span>Preview Product</span>
            </span>
        @endif
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
        <button type="button" onclick="switchTab('variants')" id="tabBtn_variants" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-tags text-xs"></i>
            <span>Product Variants</span>
        </button>
        @endif
        <button type="button" onclick="switchTab('search-tags')" id="tabBtn_search-tags" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
            <span>Search Tags</span>
        </button>
        @if(class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class))
        <button type="button" onclick="switchTab('related')" id="tabBtn_related" class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-link text-xs"></i>
            <span>Related Products</span>
        </button>
        @endif
    </div>
</div>

<div id="mainProductCard" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <h2 class="font-semibold text-sm font-display" id="cardTitle">Update Product Information</h2>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product->ulid) }}" enctype="multipart/form-data">
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
                    @if(class_exists(\SGCart\Inventory\Models\InventoryLog::class))
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" readonly
                            class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm outline-none text-slate-500 cursor-not-allowed font-mono" style="pointer-events: none;">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1.5 font-medium">
                            <i class="fa-solid fa-circle-info"></i> Stock is managed via the <a href="{{ route('admin.inventory.index') }}" class="underline hover:text-blue-700">Inventory System</a>.
                        </p>
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
                    <p class="text-[11px] text-slate-400 mt-1.5 font-medium">
                        <i class="fa-solid fa-circle-info"></i> If not provided, the default minimum stock is 5.
                    </p>
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
                            <option value="{{ $s }}" {{ old('status', $product->status->value) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="weight" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Weight</label>
                    <input type="text" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" placeholder="e.g. 500g"
                        pattern="\d+(\.\d+)?\s*[a-zA-Z]+"
                        title="Weight must be a number followed by a unit (e.g., 500g, 1.5kg)"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('weight') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    <p id="weight-error" class="text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                    @error('weight') <p id="weight-server-error" class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="dimensions" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Dimensions (L×W×H)</label>
                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="e.g. 10x5x3 cm"
                        pattern="\d+(\.\d+)?\s*[xX×]\s*\d+(\.\d+)?\s*[xX×]\s*\d+(\.\d+)?(\s*[a-zA-Z]+)?"
                        title="Dimensions must be in the format LxWxH, optionally followed by a unit (e.g., 10x5x3 cm)"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('dimensions') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    <p id="dimensions-error" class="text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                    @error('dimensions') <p id="dimensions-server-error" class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
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
                                    <button type="button" onclick="showConfirm('Are you sure you want to delete this product image?', () => document.getElementById('deleteImageForm_{{ $img->id }}').submit(), 'Delete Image')"
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

        @if(class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class))
        {{-- ── Related Products Tab Content ── --}}
        <div id="relatedTabContent" class="tab-content hidden p-4 sm:p-6 space-y-6">
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider pb-1 border-b border-slate-100 dark:border-slate-800">Related Products</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Search and select products below to link them as related products. They will appear as cards in the grid below.</p>

                <div>
                    <label for="related_product_search" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Search Products</label>
                    <select id="related_product_search" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none">
                        <option value="">— Search & Select Product —</option>
                        @foreach(\App\Models\Product::where('status', 'active')->where('id', '!=', $product->id)->get() as $p)
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
                        @foreach($product->relatedProducts as $rel)
                            <div class="relative group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300" id="related_card_{{ $rel->id }}">
                                <div class="aspect-square bg-slate-50 dark:bg-slate-900/50 relative overflow-hidden flex items-center justify-center">
                                    <img src="{{ $rel->image ? Storage::url($rel->image) : asset('images/no-image.svg') }}" alt="{{ $rel->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <button type="button" onclick="removeRelatedProduct({{ $rel->id }})" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 hover:bg-rose-500 hover:text-white dark:bg-slate-800/90 dark:hover:bg-rose-600 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm cursor-pointer transition-colors z-10 border-none" title="Remove Related Product">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                                <div class="p-3.5 space-y-1">
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ $rel->category ? $rel->category->name : 'Uncategorized' }}</p>
                                    <h3 class="font-display font-semibold text-sm text-slate-800 dark:text-slate-100 line-clamp-1">{{ $rel->name }}</h3>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="font-bold text-sm text-slate-900 dark:text-slate-100">₹{{ number_format($rel->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Actions -->
        <div id="formActions" class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 p-4 sm:p-6 border-t border-slate-100 dark:border-slate-800 w-full">
            <a href="{{ route('admin.products.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer flex items-center">
                Update Product
            </button>
        </div>
    </form>
    </div>

    @if(Route::has('admin.products.variants.grid'))
    <!-- Variants Tab Content -->
    <div id="variantsTabContent" class="tab-content hidden p-4 sm:p-6">
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
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">
                                    Price(₹)
                                    <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 ml-1 cursor-help" data-tooltip="Set a custom price for this variant. If left empty, it will fallback to the base product's price."></i>
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">
                                    Sale Price(₹)
                                    <i class="fa-solid fa-circle-question text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 ml-1 cursor-help" data-tooltip="Set a custom sale price for this variant. If left empty, it will fallback to the base product's sale price."></i>
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-32">Stock Qty</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-24">Active</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Gallery</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @for($i = 0; $i < 3; $i++)
                            <tr class="transition-colors">
                                <td class="px-4 py-3.5 w-44">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5 w-44">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5 w-36">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5 w-36">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5 w-32">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-full"></div>
                                </td>
                                <td class="px-4 py-3.5 w-24 text-center">
                                    <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto"></div>
                                </td>
                                <td class="px-4 py-3.5 w-36 text-center">
                                    <div class="h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg w-24 mx-auto"></div>
                                </td>
                                <td class="px-4 py-3.5 w-16 text-right">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/80 rounded-lg ml-auto"></div>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Actions Skeleton -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <div class="h-10 bg-slate-100 dark:bg-slate-800 rounded-lg w-20"></div>
                <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-lg w-44"></div>
            </div>
        </div>
        <!-- Grid Container -->
        <div id="variantsGridContainer"></div>
    </div>
    @endif

    <!-- Search Tags Tab Content -->
    <div id="search-tagsTabContent" class="tab-content hidden p-4 sm:p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center -mx-4 sm:-mx-6 -mt-4 sm:-mt-6 mb-6">
            <h2 class="font-semibold text-sm font-display text-slate-800 dark:text-slate-200">Search Tags Management</h2>
        </div>
        <div class="space-y-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Search tags are used to match this product on the storefront. The search order checks:
                    <strong class="text-slate-800 dark:text-slate-200">Product Name &rarr; SKU &rarr; Category &rarr; Search Tags</strong>.
                </p>
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-350">Auto-Generate Tags</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Automatically extract keywords from name, description, and categories.</p>
                    </div>
                    <button type="button" id="generateTagsBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition-all shadow-md shadow-blue-500/10 cursor-pointer border-none">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Search Tags
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-350">Add Tag Manually</h4>
                <form id="addTagForm" class="flex gap-2">
                    <input type="text" id="newTagInput" placeholder="e.g. navy blue shirt" required
                        class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-750 rounded-lg py-2 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"/>
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-750 dark:hover:bg-slate-700 text-white rounded-lg font-semibold text-sm transition-all cursor-pointer border-none">
                        <i class="fa-solid fa-plus"></i> Add Tag
                    </button>
                </form>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-350">Active Search Tags</h4>
                <div id="tagsListContainer" class="min-h-[100px] border border-slate-100 dark:border-slate-800 rounded-lg p-4 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex justify-center py-6">
                        <i class="fa-solid fa-circle-notch fa-spin text-slate-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($product->images && $product->images->count() > 0)
    @foreach($product->images as $img)
        <form id="deleteImageForm_{{ $img->id }}" action="{{ route('admin.products.delete-image', $img->id) }}" method="POST" class="hidden">
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
        const tabs = ['details', 'seo', 'search-tags'];
        @if(Route::has('admin.products.variants.grid'))
        tabs.push('variants');
        @endif
        if (document.getElementById('tabBtn_related')) {
            tabs.push('related');
        }
        const cardTitle = document.getElementById('cardTitle');
        const formActions = document.getElementById('formActions');
        const mainCard = document.getElementById('mainProductCard');

        if (mainCard) {
            if (tab === 'variants' || tab === 'search-tags') {
                mainCard.classList.add('hidden');
            } else {
                mainCard.classList.remove('hidden');
            }
        }

        tabs.forEach(t => {
            const btn = document.getElementById('tabBtn_' + t);
            const content = document.getElementById(t + 'TabContent');
            if (t === tab) {
                if (btn) btn.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2";
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) btn.className = "px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 outline-none select-none bg-transparent cursor-pointer flex items-center gap-2";
                if (content) content.classList.add('hidden');
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
            } else if (tab === 'search-tags') {
                cardTitle.textContent = "Search Tags Management";
            } else if (tab === 'related') {
                cardTitle.textContent = "Configure Related Products";
            }
        }

        if (formActions) {
            if (tab === 'variants' || tab === 'search-tags') {
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
            @if(Route::has('admin.products.variants.grid'))
            loadVariantsGrid();
            @endif
        } else if (tab === 'search-tags') {
            loadSearchTags();
        }
    };

    @if(Route::has('admin.products.variants.grid'))
    window.loadVariantsGrid = function() {
        const $gridContainer = $('#variantsGridContainer');
        const loader = document.getElementById('variantsLoader');

        loader.classList.remove('hidden');
        $gridContainer.empty();

        $.ajax({
            url: '{{ route("admin.products.variants.grid", $product->id) }}',
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
            placeholder: "— Select Vendor —",
            allowClear: true,
            width: '100%'
        });
        $('#status').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        const weightInput = document.getElementById('weight');
        const weightError = document.getElementById('weight-error');
        const weightServerError = document.getElementById('weight-server-error');

        if (weightInput && weightError) {
            const validateWeight = () => {
                if (weightServerError) weightServerError.classList.add('hidden');
                const val = weightInput.value.trim();
                if (val === '') {
                    weightError.classList.add('hidden');
                    weightInput.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                    return;
                }
                const regex = /^\d+(?:\.\d+)?\s*[a-zA-Z]+$/;
                if (!regex.test(val)) {
                    weightError.textContent = 'Weight must be a number followed by a unit (e.g., 500g, 1.5kg).';
                    weightError.classList.remove('hidden');
                    weightInput.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                } else {
                    weightError.classList.add('hidden');
                    weightInput.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                }
            };
            weightInput.addEventListener('input', debounce(validateWeight, 300));
        }

        const dimensionsInput = document.getElementById('dimensions');
        const dimensionsError = document.getElementById('dimensions-error');
        const dimensionsServerError = document.getElementById('dimensions-server-error');

        if (dimensionsInput && dimensionsError) {
            const validateDimensions = () => {
                if (dimensionsServerError) dimensionsServerError.classList.add('hidden');
                const val = dimensionsInput.value.trim();
                if (val === '') {
                    dimensionsError.classList.add('hidden');
                    dimensionsInput.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                    return;
                }
                const regex = /^\d+(?:\.\d+)?\s*[xX×]\s*\d+(?:\.\d+)?\s*[xX×]\s*\d+(?:\.\d+)?(?:\s*[a-zA-Z]+)?$/;
                if (!regex.test(val)) {
                    dimensionsError.textContent = 'Dimensions must be in the format LxWxH, optionally followed by a unit (e.g., 10x5x3 cm).';
                    dimensionsError.classList.remove('hidden');
                    dimensionsInput.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                } else {
                    dimensionsError.classList.add('hidden');
                    dimensionsInput.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                }
            };
            dimensionsInput.addEventListener('input', debounce(validateDimensions, 300));
        }

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

            // Populate existing values
            @if(class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class))
                @foreach($product->relatedProducts as $rel)
                    selectedRelatedIds.add({{ $rel->id }});
                @endforeach
                updateHiddenRelatedSelect();
            @endif

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

        // Search tags AJAX logic
        $('#addTagForm').on('submit', function(e) {
            e.preventDefault();
            const term = $('#newTagInput').val().trim();
            if (!term) return;

            $.ajax({
                url: "{{ route('admin.products.search-tags.add', $product->ulid) }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    term: term
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        $('#newTagInput').val('');
                        loadSearchTags();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                    const msg = errors && errors.term ? errors.term[0] : 'Failed to add search tag.';
                    showToast(msg, 'error');
                }
            });
        });

        $(document).on('click', '.delete-tag-btn', function() {
            const tagId = $(this).data('id');
            const pill = $(this).closest('.tag-pill');

            $.ajax({
                url: `/admin/products/{{ $product->ulid }}/search-tags/${tagId}`,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        pill.remove();
                        if ($('.tag-pill').length === 0) {
                            showSearchTagsEmptyState();
                        }
                    }
                },
                error: function() {
                    showToast('Failed to delete search tag.', 'error');
                }
            });
        });

        $(document).on('click', '#generateTagsBtn', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Generating...');

            $.ajax({
                url: "{{ route('admin.products.search-tags.generate', $product->ulid) }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadSearchTags();
                    }
                },
                error: function() {
                    showToast('Failed to generate search tags.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Generate Search Tags');
                }
            });
        });

        // Check for active tab query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'details';
        const allowedTabs = ['details', 'seo', 'search-tags'];
        @if(Route::has('admin.products.variants.grid'))
        allowedTabs.push('variants');
        @endif
        if (document.getElementById('tabBtn_related')) {
            allowedTabs.push('related');
        }
        if (allowedTabs.includes(activeTab)) {
            switchTab(activeTab);
        } else {
            switchTab('details');
        }
    });

    window.loadSearchTags = function() {
        $('#tagsListContainer').html(`
            <div class="flex justify-center py-6">
                <i class="fa-solid fa-circle-notch fa-spin text-slate-400 text-xl"></i>
            </div>
        `);

        $.ajax({
            url: "{{ route('admin.products.search-tags.json', $product->ulid) }}",
            type: "GET",
            success: function(response) {
                if (response.tags && response.tags.length > 0) {
                    let html = '<div class="flex flex-wrap gap-2">';
                    response.tags.forEach(tag => {
                        html += `
                            <span class="tag-pill inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-350 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-all hover:bg-slate-200 dark:hover:bg-slate-700">
                                <span>${escapeHtml(tag.term)}</span>
                                <button type="button" class="delete-tag-btn text-rose-500 hover:text-rose-700 font-bold focus:outline-none ml-1 cursor-pointer border-none bg-transparent" data-id="${tag.id}" title="Remove Tag">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </span>
                        `;
                    });
                    html += '</div>';
                    $('#tagsListContainer').html(html);
                } else {
                    showSearchTagsEmptyState();
                }
            },
            error: function() {
                $('#tagsListContainer').html('<p class="text-rose-500 text-sm">Failed to load search tags.</p>');
            }
        });
    }

    window.showSearchTagsEmptyState = function() {
        $('#tagsListContainer').html(`
            <div class="flex flex-col items-center justify-center text-center p-6 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-lg">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-3">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                <h5 class="text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1">No search tags found</h5>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Generate automatically or add manually above.</p>
                <button type="button" id="generateTagsBtnEmpty" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition-all shadow-md shadow-blue-500/10 cursor-pointer border-none">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Search Tags
                </button>
            </div>
        `);

        $('#generateTagsBtnEmpty').on('click', function() {
            $('#generateTagsBtn').click();
        });
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
@endsection
