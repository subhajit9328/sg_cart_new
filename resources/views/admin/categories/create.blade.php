@extends('layouts.admin')
@section('content')
<h1 class="text-2xl font-bold mb-4">Add Category</h1>

<form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-lg space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium">Name *</label>
        <input name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 mt-1">
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Parent Category</label>
        <select name="parent_id" class="w-full border rounded px-3 py-2 mt-1">
            <option value="">— None (Top-level) —</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 mt-1">{{ old('description') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium">Image</label>
        <input type="file" name="image" accept="image/*" class="mt-1">
    </div>

    <div class="flex gap-6">
        <div>
            <label class="block text-sm font-medium">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-28 border rounded px-3 py-2 mt-1">
        </div>
        <div class="flex items-end gap-2 pb-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="text-sm font-medium">Active</label>
        </div>
    </div>

    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded">Save Category</button>
</form>
@endsection