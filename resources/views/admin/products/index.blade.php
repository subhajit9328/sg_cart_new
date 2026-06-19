@extends('layouts.admin')
@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-bold">Products</h1>
    <div class="flex gap-2">
        <button onclick="document.getElementById('bulkModal').classList.remove('hidden')"
            class="bg-green-600 text-white px-4 py-2 rounded">⬆ Bulk Upload</button>
        <a href="{{ route('admin.products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Product</a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search name / SKU…" class="border rounded px-3 py-1.5 text-sm">
    <select name="category_id" class="border rounded px-3 py-1.5 text-sm">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="status" class="border rounded px-3 py-1.5 text-sm">
        <option value="">All Status</option>
        @foreach(['draft','active','inactive'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-gray-700 text-white px-4 py-1.5 rounded text-sm">Filter</button>
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 self-center">Clear</a>
</form>

@if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<table class="w-full border text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left w-16">Image</th>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">SKU</th>
            <th class="p-2 text-left">Category</th>
            <th class="p-2 text-left">Price</th>
            <th class="p-2 text-left">Stock</th>
            <th class="p-2 text-left">Status</th>
            <th class="p-2 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr class="border-t">
            <td class="p-2">
                @if($product->primaryImage)
                    <img src="{{ Storage::url($product->primaryImage->path) }}" class="w-10 h-10 object-cover rounded">
                @else
                    <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400">No img</div>
                @endif
            </td>
            <td class="p-2 font-medium">{{ $product->name }}</td>
            <td class="p-2 font-mono text-xs">{{ $product->sku }}</td>
            <td class="p-2">{{ $product->category?->name ?? '—' }}</td>
            <td class="p-2">
                ₹{{ number_format($product->price, 2) }}
                @if($product->sale_price)
                    <span class="text-green-600 text-xs block">Sale: ₹{{ number_format($product->sale_price, 2) }}</span>
                @endif
            </td>
            <td class="p-2">{{ $product->stock }}</td>
            <td class="p-2">
                <span class="px-2 py-0.5 rounded text-xs
                    {{ $product->status === 'active' ? 'bg-green-100 text-green-700' :
                      ($product->status === 'draft'  ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($product->status) }}
                </span>
            </td>
            <td class="p-2 flex gap-3">
                <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600">Edit</a>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete product?')">
                    @csrf @method('DELETE')
                    <button class="text-red-600">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="p-4 text-center text-gray-400">No products found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">{{ $products->links() }}</div>

{{-- Bulk Upload Modal --}}
<div id="bulkModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Bulk Upload Products</h2>
        <p class="text-sm text-gray-500 mb-3">
            CSV/Excel columns: <code class="bg-gray-100 px-1 rounded">name, sku, category, manufacturer, price, sale_price, stock, status, weight, dimensions, short_description, description</code>
        </p>
        <form method="POST" action="{{ route('admin.products.bulk-upload') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".csv,.xlsx,.xls" class="w-full border rounded px-3 py-2 mb-4">
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('bulkModal').classList.add('hidden')"
                    class="px-4 py-2 border rounded text-sm">Cancel</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection