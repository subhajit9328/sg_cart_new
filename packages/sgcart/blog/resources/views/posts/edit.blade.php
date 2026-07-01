@extends('layouts.admin')

@section('title', 'Edit Blog Post — SGCart Admin')

@section('content')
<form method="POST" action="{{ route('admin.blog-posts.update', $post->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog-posts.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-display text-2xl font-bold">Edit Blog Post</h1>
                <x-breadcrumbs :items="[
                    ['label' => 'Admin', 'url' => route('admin.dashboard')],
                    ['label' => 'Blog'],
                    ['label' => 'Posts', 'url' => route('admin.blog-posts.index')],
                    ['label' => 'Edit']
                ]" />
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog-posts.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-350 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Update Post
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main Content Panel -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Post Information Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="font-semibold text-sm">Post Information</h2>
            </div>
            <div class="p-6 space-y-5">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Title <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('title') border-rose-500 @enderror"
                        placeholder="E.g., 5 Web Design Trends for 2026">
                    @error('title') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Slug (URL identifier)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('slug') border-rose-500 @enderror"
                        placeholder="Leave blank to auto-generate">
                    @error('slug') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Category selection -->
                <div>
                    <x-select2 
                        name="blog_category_id" 
                        id="blog_category_id" 
                        label="Category"
                        placeholder="— Uncategorized —"
                        :options="$categories"
                        optionValue="id"
                        optionLabel="name"
                        :selected="old('blog_category_id', $post->blog_category_id)"
                    />
                </div>

                <!-- Summary / Excerpt -->
                <div>
                    <label for="summary" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Summary / Excerpt</label>
                    <textarea name="summary" id="summary" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="Provide a brief summary of the post...">{{ old('summary', $post->summary) }}</textarea>
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Content <span class="text-rose-600">*</span></label>
                    <textarea name="content" id="content" rows="12" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('content') border-rose-500 @enderror"
                        placeholder="Write your blog post content here...">{{ old('content', $post->content) }}</textarea>
                    @error('content') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- SEO Metadata Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="font-semibold text-sm">SEO Metadata</h2>
            </div>
            <div class="p-6 space-y-5">
                <!-- Meta Title -->
                <div>
                    <label for="meta_title" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $post->meta_title) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="Meta title for SEO optimization">
                </div>

                <!-- Meta Description -->
                <div>
                    <label for="meta_description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="Meta description for SEO optimization">{{ old('meta_description', $post->meta_description) }}</textarea>
                </div>

                <!-- Meta Keywords -->
                <div>
                    <label for="meta_keywords" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="E.g., design, trends, ecommerce (comma separated)">
                </div>
            </div>
        </div>
    </div>

    <!-- Side Panel -->
    <div class="space-y-6">
        <!-- Publishing Options Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="font-semibold text-sm">Publishing Options</h2>
            </div>
            <div class="p-6 space-y-5">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Status <span class="text-rose-600">*</span></label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                        <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status', $post->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <!-- Published At Date picker -->
                <div>
                    <label for="published_at" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Publish Date / Time</label>
                    <input type="datetime-local" name="published_at" id="published_at" 
                        value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                    <p class="text-xs text-slate-400 mt-1">Leave blank to publish immediately on saving as 'published'.</p>
                </div>
            </div>
        </div>

        <!-- Featured Image Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="font-semibold text-sm">Featured Image</h2>
            </div>
            <div class="p-6 space-y-4">
                @if($post->featured_image)
                    <div class="relative rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-2">
                        <img src="{{ $post->featured_image_url }}" alt="Preview" class="max-h-40 mx-auto object-contain rounded">
                    </div>
                @endif
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Upload Image</label>
                    <input type="file" name="featured_image" accept="image/*"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-800 dark:text-slate-200 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-500/10 dark:file:text-blue-400 hover:file:bg-blue-100 cursor-pointer">
                    @error('featured_image') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Featured Store Products Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm relative z-30">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 rounded-t-xl">
                <h2 class="font-semibold text-sm">Featured Store Products</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="relative">
                    <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Search Store Products</label>
                    <input type="text" id="adminProductSearchInput" placeholder="Type product name to link..." 
                        class="w-full bg-slate-55 dark:bg-slate-800 border border-slate-200 dark:border-slate-750 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                    
                    <!-- Suggestions Dropdown -->
                    <div id="adminProductSuggestions" class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-250 dark:border-slate-800 rounded-xl shadow-lg z-[999] hidden max-h-56 overflow-y-auto pr-1"></div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-850 pt-4">
                    <label class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-2.5">Linked Products</label>
                    <div id="selectedProductsList" class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @foreach($post->products as $prod)
                            <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 group" data-product-id="{{ $prod->id }}">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->image ? Storage::url($prod->image) : asset('images/no-image.svg') }}" class="w-9 h-9 rounded object-cover">
                                    <div class="text-xs">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $prod->name }}</span>
                                        <span class="text-slate-400 mt-0.5 block">₹{{ number_format($prod->price, 2) }}</span>
                                    </div>
                                </div>
                                <button type="button" onclick="removeLinkedProduct('{{ $prod->id }}')" class="text-slate-450 hover:text-rose-500 p-1 border-none bg-transparent cursor-pointer transition-colors">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                                <input type="hidden" name="products[]" value="{{ $prod->id }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    (function() {
        const textarea = document.querySelector('#content');
        if (textarea) {
            ClassicEditor
                .create(textarea, {
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
                })
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        textarea.value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        }
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const productSearch = document.getElementById('adminProductSearchInput');
        const suggestions = document.getElementById('adminProductSuggestions');
        const selectedList = document.getElementById('selectedProductsList');
        let searchDebounce;

        if (productSearch) {
            productSearch.addEventListener('input', () => {
                const query = productSearch.value.trim();
                clearTimeout(searchDebounce);

                if (query.length < 2) {
                    suggestions.innerHTML = '';
                    suggestions.classList.add('hidden');
                    return;
                }

                suggestions.classList.remove('hidden');
                suggestions.innerHTML = `
                    <div class="p-3 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-notch fa-spin text-blue-500 mr-1.5"></i> Searching...
                    </div>
                `;

                searchDebounce = setTimeout(async () => {
                    try {
                        const response = await fetch(`/search-live?q=${encodeURIComponent(query)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        
                        if (Array.isArray(data) && data.length > 0) {
                            let html = '<div class="flex flex-col p-1">';
                            data.forEach(prod => {
                                const exists = selectedList.querySelector(`[data-product-id="${prod.id}"]`);
                                if (exists) return;

                                html += `
                                    <button type="button" onclick="addLinkedProduct('${prod.id}', '${escapeHtml(prod.name)}', '${prod.price}', '${prod.img}')" class="flex items-center gap-3 w-full p-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-left border-none bg-transparent cursor-pointer rounded-lg transition-colors">
                                        <img src="${prod.img}" class="w-8 h-8 rounded object-cover border border-slate-100 dark:border-slate-800">
                                        <div class="text-xs">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block">${prod.name}</span>
                                            <span class="text-slate-400 mt-0.5 block">₹${prod.price.toFixed(2)}</span>
                                        </div>
                                    </button>
                                `;
                            });
                            html += '</div>';
                            suggestions.innerHTML = html;
                        } else {
                            suggestions.innerHTML = `
                                <div class="p-3 text-center text-slate-400 text-xs">
                                    No products found for "${query}"
                                </div>
                            `;
                        }
                    } catch (err) {
                        console.error(err);
                        suggestions.classList.add('hidden');
                    }
                }, 250);
            });
        }

        document.addEventListener('click', (e) => {
            if (productSearch && !productSearch.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.add('hidden');
            }
        });
    });

    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function addLinkedProduct(id, name, price, img) {
        const selectedList = document.getElementById('selectedProductsList');
        if (!selectedList) return;

        if (selectedList.querySelector(`[data-product-id="${id}"]`)) return;

        const container = document.createElement('div');
        container.className = "flex items-center justify-between p-2.5 rounded-lg border border-slate-250 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 group";
        container.setAttribute('data-product-id', id);
        
        container.innerHTML = `
            <div class="flex items-center gap-3">
                <img src="${img}" class="w-9 h-9 rounded object-cover">
                <div class="text-xs">
                    <span class="font-bold text-slate-800 dark:text-slate-200 block">${name}</span>
                    <span class="text-slate-400 mt-0.5 block">₹${parseFloat(price).toFixed(2)}</span>
                </div>
            </div>
            <button type="button" onclick="removeLinkedProduct('${id}')" class="text-slate-455 hover:text-rose-500 p-1 border-none bg-transparent cursor-pointer transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
            <input type="hidden" name="products[]" value="${id}">
        `;

        selectedList.appendChild(container);
        
        const productSearch = document.getElementById('adminProductSearchInput');
        const suggestions = document.getElementById('adminProductSuggestions');
        if (productSearch) productSearch.value = '';
        if (suggestions) {
            suggestions.innerHTML = '';
            suggestions.classList.add('hidden');
        }
    }

    function removeLinkedProduct(id) {
        const selectedList = document.getElementById('selectedProductsList');
        const item = selectedList.querySelector(`[data-product-id="${id}"]`);
        if (item) {
            item.remove();
        }
    }
</script>

<script>
<style>
    .ck-editor__editable_current {
        min-height: 300px;
    }
    /* CKEditor theme overrides for admin panel dark mode */
    .dark .ck.ck-editor__main>.ck-editor__editable {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }
    .dark .ck.ck-toolbar {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    .dark .ck.ck-toolbar .ck-button {
        color: #cbd5e1 !important;
    }
    .dark .ck.ck-toolbar .ck-button:hover {
        background-color: #1e293b !important;
    }
    .dark .ck.ck-toolbar .ck-button.ck-on {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
    .ck.ck-editor {
        color: #000000;
    }
</style>
@endpush
