@extends('layouts.admin')

@section('title', 'Edit Blog Category — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.blog-categories.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Blog Category</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Blog'],
            ['label' => 'Categories', 'url' => route('admin.blog-categories.index')],
            ['label' => 'Edit']
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Update Category Information</h2>
    </div>

    <form method="POST" action="{{ route('admin.blog-categories.update', $category->id) }}" class="p-6 space-y-5">
        @csrf
        @method('PUT')

        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Category Name <span class="text-rose-600">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="E.g., Trends, News, Tutorial">
            @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <!-- Slug Field -->
        <div>
            <label for="slug" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Slug (URL identifier)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Leave blank to auto-generate">
            @error('slug') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                placeholder="Description of the category...">{{ old('description', $category->description) }}</textarea>
        </div>

        <!-- Status Checkbox -->
        <div class="flex items-end pb-3">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Active Status</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Determine if the category is active and shown on storefront.</span>
                </div>
            </label>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.blog-categories.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Update Category
            </button>
        </div>

    </form>
</div>
@endsection
