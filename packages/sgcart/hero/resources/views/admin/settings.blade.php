@extends('layouts.admin')

@section('title', 'Hero Section Settings — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-800 dark:text-slate-100">Hero Section Settings</h1>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Manage the Hero Section slides, carousel settings, and package associations.</p>
    </div>
</div>

<div class="mx-auto flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/30 rounded-xl text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.hero.settings.update') }}" method="POST" enctype="multipart/form-data" id="hero-settings-form" class="flex flex-col gap-6">
        @csrf

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <!-- Card Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-800 shrink-0 shadow-2xs bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400">
                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Upload Slides</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Add slide images for the storefront Hero section.</p>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 flex flex-col gap-4">
                <!-- Upload Images -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Upload New Slides</label>
                    <div class="relative w-full border border-dashed border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors p-6 flex flex-col items-center justify-center gap-2.5 text-center">
                        <i class="fa-solid fa-images text-2xl text-slate-400 dark:text-slate-500"></i>
                        <div class="text-xs text-slate-600 dark:text-slate-400">
                            <span class="font-semibold text-blue-600 dark:text-blue-400">Click to upload</span> or drag and drop multiple images
                        </div>
                        <p class="text-[10px] text-slate-400">PNG, JPG, JPEG, GIF or WEBP (Max 4MB per file)</p>
                        <input type="file" name="images[]" id="images-input" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                    </div>
                    @error('images')
                        <p class="text-rose-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-rose-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                    @enderror

                    <!-- Preview Grid for newly selected files -->
                    <div id="new-images-preview" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        <!-- Dynamically inserted previews -->
                    </div>

                    <!-- Upload Action Button inside Section -->
                    <div id="upload-action-container" class="mt-4 flex justify-end hidden">
                        <button type="button" id="btn-upload-images" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Upload Images
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slides Management List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <!-- Card Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Slide Management</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Manage uploaded slides, set sort order, or delete images.</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    {{ $images->count() }} Slide(s)
                </span>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                @if($images->isEmpty())
                    <div class="flex flex-col items-center justify-center p-12 text-center text-slate-400 dark:text-slate-500">
                        <i class="fa-regular fa-image text-4xl mb-3 opacity-40"></i>
                        <p class="text-xs font-semibold">No slides uploaded yet</p>
                        <p class="text-[10px] mt-0.5">Upload multiple images above to build your hero slide deck.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($images as $img)
                            <div class="relative bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl p-3 flex flex-col justify-between transition-all duration-300 hover:shadow-sm">
                                <!-- Thumbnail -->
                                <div class="w-full aspect-[16/9] rounded-lg overflow-hidden border border-slate-100 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ Storage::url($img->image_path) }}" class="w-full h-full object-cover">
                                </div>

                                <!-- Image Meta & Actions -->
                                <div class="mt-3.5 flex items-center justify-between gap-3">
                                    <div class="flex-1 min-w-0 flex items-center gap-1.5">
                                        <label class="shrink-0 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sort</label>
                                        <input type="number" name="sort_order[{{ $img->id }}]" value="{{ old('sort_order.'.$img->id, $img->sort_order) }}" class="w-16 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100" required>
                                    </div>

                                    <!-- Delete Button Form -->
                                    <button type="button" onclick="confirmDeleteSlide('{{ route('admin.hero.settings.delete-image', $img->id) }}')" class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 cursor-pointer transition-colors" title="Delete Slide">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Action Footer -->
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" @disabled($images->isEmpty()) class="{{$images->isEmpty() ? 'bg-slate-200' : 'bg-blue-600 hover:bg-blue-700'}} text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-circle-check text-xs"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Dummy Hidden Deletion Form -->
<form id="delete-slide-form" action="" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function confirmDeleteSlide(url) {
        showConfirm('Are you sure you want to delete this hero image slide?', () => {
            const form = document.getElementById('delete-slide-form');
            form.action = url;
            form.submit();
        }, 'Delete Hero Slide');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('images-input');
        const previewContainer = document.getElementById('new-images-preview');
        const uploadContainer = document.getElementById('upload-action-container');
        const uploadBtn = document.getElementById('btn-upload-images');

        let selectedFiles = [];

        window.removeSelectedFile = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
        };

        if (input && previewContainer) {
            input.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        const hasError = file.size > 4 * 1024 * 1024; // 4MB
                        file.hasError = hasError;
                        selectedFiles.push(file);
                    });
                    this.value = '';
                    renderPreviews();
                }
            });
        }

        function renderPreviews() {
            previewContainer.innerHTML = '';

            if (selectedFiles.length > 0) {
                previewContainer.classList.remove('hidden');
                uploadContainer.classList.remove('hidden');

                const header = document.createElement('div');
                header.className = 'col-span-full border-b border-slate-100 dark:border-slate-800 pb-2 mb-2';
                header.innerHTML = '<h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Selected Images to Upload:</h4>';
                previewContainer.appendChild(header);

                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();

                    const card = document.createElement('div');
                    const cardBorderClass = file.hasError
                        ? 'border-rose-500 ring-2 ring-rose-500/20'
                        : 'border-slate-200 dark:border-slate-800';
                    card.className = `relative bg-slate-50/50 dark:bg-slate-800/20 border ${cardBorderClass} rounded-xl p-3 flex flex-col gap-2`;

                    const thumbWrap = document.createElement('div');
                    thumbWrap.className = 'w-full aspect-[16/9] rounded-lg overflow-hidden border border-slate-100 dark:border-slate-700 bg-slate-100 dark:bg-slate-800';

                    const img = document.createElement('img');
                    img.className = 'w-full h-full object-cover';
                    thumbWrap.appendChild(img);
                    card.appendChild(thumbWrap);

                    const crossBtn = document.createElement('button');
                    crossBtn.type = 'button';
                    crossBtn.className = 'absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-slate-700 hover:bg-rose-700 text-white flex items-center justify-center border-none shadow-md cursor-pointer transition-colors z-20';
                    crossBtn.innerHTML = '<i class="fa-solid fa-xmark text-[10px]"></i>';
                    crossBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        window.removeSelectedFile(index);
                    });
                    card.appendChild(crossBtn);

                    const details = document.createElement('div');
                    details.className = 'text-[9px] font-semibold text-slate-500 dark:text-slate-400 truncate';
                    details.innerText = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    card.appendChild(details);

                    if (file.hasError) {
                        const errorText = document.createElement('div');
                        errorText.className = 'text-[9px] font-bold text-rose-500';
                        errorText.innerText = 'Size exceeds 4MB limit';
                        card.appendChild(errorText);
                    }

                    previewContainer.appendChild(card);

                    reader.onload = function (e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                previewContainer.classList.add('hidden');
                uploadContainer.classList.add('hidden');
            }
        }

        if (uploadBtn) {
            uploadBtn.addEventListener('click', function (e) {
                e.preventDefault();

                if (selectedFiles.length === 0) return;

                const hasErrors = selectedFiles.some(file => file.hasError);
                if (hasErrors) {
                    showToast('Please remove files that exceed the 4MB limit before uploading.', 'error');
                    return;
                }

                uploadBtn.disabled = true;
                uploadBtn.classList.add('opacity-75', 'cursor-not-allowed');
                const icon = uploadBtn.querySelector('i');
                let originalIconClass = '';
                if (icon) {
                    originalIconClass = icon.className;
                    icon.className = 'fa-solid fa-spinner animate-spin text-xs';
                }

                const formData = new FormData();
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append('_token', csrfToken);

                selectedFiles.forEach(file => {
                    formData.append('images[]', file);
                });

                fetch("{{ route('admin.hero.settings.update') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Images uploaded successfully.', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        showToast(data.message || 'Error occurred during upload.', 'error');
                        resetUploadBtn();
                    }
                })
                .catch(error => {
                    console.error('Error uploading:', error);
                    showToast('Network error during upload.', 'error');
                    resetUploadBtn();
                });

                function resetUploadBtn() {
                    uploadBtn.disabled = false;
                    uploadBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    if (icon) {
                        icon.className = originalIconClass;
                    }
                }
            });
        }
    });
</script>
@endpush
