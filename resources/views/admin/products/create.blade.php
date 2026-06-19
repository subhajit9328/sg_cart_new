@extends('layouts.admin')
@section('content')
<h1 class="text-2xl font-bold mb-6">Add Product</h1>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
@csrf

{{-- ── Basic Info ──────────────────────────────────────────── --}}
<div class="bg-white border rounded-lg p-5 mb-5 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Basic Information</h2>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Product Name *</label>
            <input name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 mt-1">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">SKU *</label>
            <input name="sku" value="{{ old('sku') }}" class="w-full border rounded px-3 py-2 mt-1 font-mono">
            @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Category</label>
            <select name="category_id" class="w-full border rounded px-3 py-2 mt-1">
                <option value="">— Select —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->parent ? $cat->parent->name . ' › ' : '' }}{{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Manufacturer / Vendor</label>
            <select name="manufacturer_id" class="w-full border rounded px-3 py-2 mt-1">
                <option value="">— Select —</option>
                @foreach($manufacturers as $m)
                    <option value="{{ $m->id }}" {{ old('manufacturer_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Short Description</label>
        <textarea name="short_description" rows="2" class="w-full border rounded px-3 py-2 mt-1">{{ old('short_description') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium">Full Description</label>
        <textarea name="description" id="description" rows="6" class="w-full border rounded px-3 py-2 mt-1">{{ old('description') }}</textarea>
    </div>
</div>

{{-- ── Pricing & Inventory ─────────────────────────────────── --}}
<div class="bg-white border rounded-lg p-5 mb-5 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Pricing & Inventory</h2>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium">Price (₹) *</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', 0) }}" class="w-full border rounded px-3 py-2 mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium">Sale Price (₹)</label>
            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" class="w-full border rounded px-3 py-2 mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium">Stock *</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" class="w-full border rounded px-3 py-2 mt-1">
        </div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 mt-1">
                @foreach(['draft','active','inactive'] as $s)
                    <option value="{{ $s }}" {{ old('status','draft') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Weight</label>
            <input name="weight" value="{{ old('weight') }}" placeholder="e.g. 500g" class="w-full border rounded px-3 py-2 mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium">Dimensions (L×W×H)</label>
            <input name="dimensions" value="{{ old('dimensions') }}" placeholder="e.g. 10x5x3 cm" class="w-full border rounded px-3 py-2 mt-1">
        </div>
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
        Featured Product
    </label>
</div>

{{-- ── Product Variants ────────────────────────────────────── --}}
<div class="bg-white border rounded-lg p-5 mb-5">
    <div class="flex justify-between items-center border-b pb-2 mb-4">
        <h2 class="font-semibold text-gray-700">Variants (Size / Colour / Weight)</h2>
        <button type="button" id="addVariant" class="text-blue-600 text-sm">+ Add Variant</button>
    </div>
    <div id="variantsContainer" class="space-y-3">
        {{-- injected by JS --}}
    </div>
</div>

{{-- ── Specifications ──────────────────────────────────────── --}}
<div class="bg-white border rounded-lg p-5 mb-5">
    <div class="flex justify-between items-center border-b pb-2 mb-4">
        <h2 class="font-semibold text-gray-700">Specifications</h2>
        <button type="button" id="addSpec" class="text-blue-600 text-sm">+ Add Row</button>
    </div>
    <div id="specsContainer" class="space-y-2">
        {{-- injected by JS --}}
    </div>
</div>

{{-- ── Image Gallery ───────────────────────────────────────── --}}
<div class="bg-white border rounded-lg p-5 mb-5">
    <h2 class="font-semibold text-gray-700 border-b pb-2 mb-4">Image Gallery</h2>
    <input type="file" name="images[]" multiple accept="image/*" id="imageInput" class="mb-3">
    <p class="text-xs text-gray-400 mb-3">Select primary image index (0 = first uploaded):</p>
    <input type="number" name="primary_image_index" value="0" min="0" class="w-24 border rounded px-3 py-1.5 text-sm">
    <div id="imagePreview" class="flex flex-wrap gap-3 mt-4"></div>
</div>

<button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-medium">Create Product</button>
</form>

<script>
// ── Variants ──────────────────────────────────────────────────
let variantIdx = 0;
document.getElementById('addVariant').addEventListener('click', () => {
    const i = variantIdx++;
    const html = `
    <div class="grid grid-cols-6 gap-2 items-end border rounded p-3 variant-row">
        <div><label class="text-xs font-medium">SKU *</label>
            <input name="variants[${i}][sku]" placeholder="VAR-SKU-001" class="w-full border rounded px-2 py-1.5 text-sm mt-1 font-mono"></div>
        <div><label class="text-xs font-medium">Size</label>
            <input name="variants[${i}][size]" placeholder="M / XL…" class="w-full border rounded px-2 py-1.5 text-sm mt-1"></div>
        <div><label class="text-xs font-medium">Colour</label>
            <input name="variants[${i}][colour]" placeholder="Red…" class="w-full border rounded px-2 py-1.5 text-sm mt-1"></div>
        <div><label class="text-xs font-medium">Weight</label>
            <input name="variants[${i}][weight]" placeholder="200g" class="w-full border rounded px-2 py-1.5 text-sm mt-1"></div>
        <div><label class="text-xs font-medium">Price (₹)</label>
            <input type="number" step="0.01" name="variants[${i}][price]" class="w-full border rounded px-2 py-1.5 text-sm mt-1"></div>
        <div><label class="text-xs font-medium">Stock</label>
            <input type="number" name="variants[${i}][stock]" value="0" class="w-full border rounded px-2 py-1.5 text-sm mt-1"></div>
        <div class="col-span-6 text-right">
            <button type="button" onclick="this.closest('.variant-row').remove()" class="text-red-500 text-xs">✕ Remove</button>
        </div>
    </div>`;
    document.getElementById('variantsContainer').insertAdjacentHTML('beforeend', html);
});

// ── Specs ─────────────────────────────────────────────────────
let specIdx = 0;
document.getElementById('addSpec').addEventListener('click', () => {
    const i = specIdx++;
    const html = `
    <div class="flex gap-2 items-center spec-row">
        <input name="specs[${i}][label]" placeholder="Label (e.g. Battery)" class="flex-1 border rounded px-3 py-1.5 text-sm">
        <input name="specs[${i}][value]" placeholder="Value (e.g. 5000 mAh)" class="flex-1 border rounded px-3 py-1.5 text-sm">
        <button type="button" onclick="this.closest('.spec-row').remove()" class="text-red-500 text-sm">✕</button>
    </div>`;
    document.getElementById('specsContainer').insertAdjacentHTML('beforeend', html);
});

// ── Image Preview ─────────────────────────────────────────────
document.getElementById('imageInput').addEventListener('change', function () {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    [...this.files].forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            preview.insertAdjacentHTML('beforeend', `
                <div class="relative">
                    <img src="${e.target.result}" class="w-24 h-24 object-cover rounded border">
                    <span class="absolute top-1 left-1 bg-black/60 text-white text-xs px-1 rounded">#${i}</span>
                </div>`);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection