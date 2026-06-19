@extends('layouts.admin')

@section('title', 'Add Category — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.categories.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Add Category</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Categories', 'url' => route('admin.categories.index')],
            ['label' => 'Add']
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Enter Category Information</h2>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf

        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Category Name <span class="text-rose-600">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Electronics">
            @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <!-- Parent Category -->
        <div>
            <label for="parent_id" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Parent Category</label>
            <select name="parent_id" id="parent_id"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                <option value="">— None (Top-level) —</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                placeholder="Description of the category..."></textarea>
        </div>

        <!-- Image Upload -->
        <div>
            <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Category Image</label>
            <input type="file" name="image" accept="image/*"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-800 dark:text-slate-200 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-500/10 dark:file:text-blue-400 hover:file:bg-blue-100 cursor-pointer">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
            </div>

            <!-- Status Checkbox -->
            <div class="flex items-end pb-3">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Active Status</span>
                        <span class="text-xs text-slate-400 block mt-0.5">Determine if the category is visible in the shop catalogue.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Save Category
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#parent_id').select2({
            placeholder: "— Select Parent Category —",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush