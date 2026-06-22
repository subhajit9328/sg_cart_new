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
    <div>
        @if($product->status === 'active')
            <a href="{{ route('store.product', $product->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                <i class="fa-solid fa-eye text-slate-500 dark:text-slate-400"></i>
                <span>View Product</span>
            </a>
        @else
            <span class="inline-flex items-center gap-2 px-4 py-2 border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 text-slate-400 dark:text-slate-600 rounded-lg text-sm font-semibold cursor-not-allowed shadow-sm" title="Product must be active to view on storefront">
                <i class="fa-solid fa-eye text-slate-400 dark:text-slate-600"></i>
                <span>View Product</span>
            </span>
        @endif
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
                    <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Product Name <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        placeholder="iPhone 15 Pro">
                    @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">SKU <span class="text-rose-600">*</span></label>
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
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" required min="0"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('stock') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
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
