@extends('layouts.admin')

@section('content')
<div class="mx-auto flex flex-col gap-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-1.5">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Hero Section Settings</h1>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-slate-600 dark:text-slate-400">Settings</span>
        </div>

    </div>

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 px-4 py-3.5 rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-check text-sm shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <form action="{{ route('admin.hero.settings.update') }}" method="POST" enctype="multipart/form-data" id="hero-settings-form" class="flex flex-col gap-6">
        @csrf

        <!-- Slides Management List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <!-- Card Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Slide Management</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Drag to sort slides, configure link URLs, and click Save Changes to persist order.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    @if($images->isNotEmpty())
                        <div class="flex items-center gap-2.5">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                <input type="checkbox" id="select-all-slides" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                <span>Select All</span>
                            </label>
                            <button type="button" id="btn-bulk-delete" class="hidden bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i> Delete Selected (<span id="selected-count">0</span>)
                            </button>
                        </div>
                    @endif
                    <!-- Upload Images Button (Shown when files are queued) -->
                    <button type="button" id="btn-upload-images" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Upload Images <span class="upload-btn-count"></span>
                    </button>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        {{ $images->count() }} Slide(s)
                    </span>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6" id="slides-container">
                    @foreach($images as $img)
                        <div class="relative bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl p-3.5 flex flex-col justify-between transition-all duration-300 hover:shadow-sm slide-card cursor-grab active:cursor-grabbing" draggable="true" data-id="{{ $img->id }}" id="slide-card-{{ $img->id }}">
                            <!-- Bulk Selection Checkbox -->
                            <div class="absolute top-4 left-4 z-10" draggable="false" ondragstart="return false;">
                                <input type="checkbox" name="bulk_ids[]" value="{{ $img->id }}" class="slide-checkbox w-4 h-4 rounded border-slate-350 dark:border-slate-700 bg-white/90 backdrop-blur-xs text-blue-600 focus:ring-blue-500 shadow-sm cursor-pointer transition-all hover:scale-105" draggable="false">
                            </div>

                            <!-- Thumbnail -->
                            <div class="w-full aspect-video rounded-lg overflow-hidden border border-slate-150 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 relative cursor-default" id="thumb-wrap-{{ $img->id }}" onclick="handleThumbClick({{ $img->id }})">
                                <img src="{{ Storage::url($img->image_path) }}" class="w-full h-full object-cover pointer-events-none" id="img-{{ $img->id }}">
                                <!-- Pencil overlay on image (edit mode) -->
                                <div class="absolute inset-0 bg-black/20 flex items-center justify-center cursor-pointer hidden edit-overlay" id="overlay-{{ $img->id }}">
                                    <i class="fa-solid fa-pencil text-white text-lg"></i>
                                </div>
                            </div>

                            <!-- URL Input Field -->
                            <div class="mt-3.5">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Link URL (Optional)</label>
                                <input type="text" name="urls[{{ $img->id }}]" id="url-input-{{ $img->id }}" value="{{ old('urls.'.$img->id, $img->url) }}" placeholder="https://example.com/collection" class="url-field w-full bg-slate-100/60 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-[11px] outline-none text-slate-500 dark:text-slate-400 transition-all shadow-2xs cursor-not-allowed" readonly>
                            </div>

                            <!-- Image Meta & Actions -->
                            <div class="mt-4 flex items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex-1 min-w-0 flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-grip-lines text-xs"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Drag to reorder</span>
                                    <input type="hidden" name="sort_order[{{ $img->id }}]" class="slide-sort-order" value="{{ $img->sort_order }}">
                                    <!-- Hidden Replacement File Input -->
                                    <input type="file" name="replace_images[{{ $img->id }}]" id="replace-image-input-{{ $img->id }}" class="hidden replace-image-input" accept="image/*" onchange="previewReplacementImage(this, {{ $img->id }})">
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- Toggle Edit / Save Button -->
                                    <button type="button" id="btn-edit-toggle-{{ $img->id }}" onclick="toggleEditSlide({{ $img->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-600 dark:bg-slate-800 dark:hover:bg-slate-700/80 dark:text-slate-350 border border-slate-200 dark:border-slate-700 cursor-pointer transition-colors" title="Edit Slide">
                                        <i class="fa-solid fa-pencil text-xs" id="btn-icon-{{ $img->id }}"></i>
                                    </button>

                                    <!-- Delete Button Form -->
                                    <button type="button" onclick="confirmDeleteSlide('{{ route('admin.hero.settings.delete-image', $img->id) }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 cursor-pointer transition-colors" title="Delete Slide">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Dynamic Plus Add Slide Card -->
                    <div id="btn-add-slide" class="relative border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500 rounded-2xl flex flex-col items-center justify-center gap-2 cursor-pointer transition-all hover:bg-slate-50/50 dark:hover:bg-slate-800/10 min-h-[280px] shadow-2xs">
                        <i class="fa-solid fa-plus text-xl text-slate-400 dark:text-slate-600"></i>
                        <span class="add-slide-text text-xs font-bold text-slate-400 dark:text-slate-650">Add Slide</span>
                        <input type="file" name="images[]" id="images-input" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- Action Footer (Save changes for existing) -->
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
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

<!-- Dummy Hidden Bulk Deletion Form -->
<form id="bulk-delete-form" action="{{ route('admin.hero.settings.bulk-delete') }}" method="POST" class="hidden">
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

    function toggleEditSlide(id) {
        const card = document.getElementById(`slide-card-${id}`);
        const urlInput = document.getElementById(`url-input-${id}`);
        const overlay = document.getElementById(`overlay-${id}`);
        const thumbWrap = document.getElementById(`thumb-wrap-${id}`);
        const btnToggle = document.getElementById(`btn-edit-toggle-${id}`);
        const btnIcon = document.getElementById(`btn-icon-${id}`);
        const fileInput = document.getElementById(`replace-image-input-${id}`);

        if (!card) return;

        const isEditing = card.classList.contains('in-edit-mode');

        if (!isEditing) {
            // Enter Edit Mode
            card.classList.add('in-edit-mode');

            // Make URL editable
            urlInput.removeAttribute('readonly');
            urlInput.classList.remove('bg-slate-100/60', 'dark:bg-slate-900', 'text-slate-500', 'dark:text-slate-400', 'cursor-not-allowed');
            urlInput.classList.add('bg-white', 'dark:bg-slate-850', 'text-slate-800', 'dark:text-slate-100');
            urlInput.focus();

            // Show overlay on image
            overlay.classList.remove('hidden');
            thumbWrap.classList.remove('cursor-default');
            thumbWrap.classList.add('cursor-pointer');

            // Change button icon to check icon
            btnIcon.className = 'fa-solid fa-circle-check text-xs';
            btnToggle.classList.remove('text-slate-650', 'dark:text-slate-350');
            btnToggle.classList.add('text-emerald-600', 'dark:text-emerald-400', 'bg-emerald-50', 'dark:bg-emerald-950/20', 'border-emerald-250');
            btnToggle.title = 'Save Slide';
        } else {
            // Save Slide changes
            if (fileInput.files && fileInput.files[0] && fileInput.files[0].size > 4 * 1024 * 1024) {
                showToast('Selected image exceeds the 4MB limit.', 'error');
                return;
            }

            btnToggle.disabled = true;
            btnIcon.className = 'fa-solid fa-spinner animate-spin text-xs';

            const formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            formData.append('_token', csrfToken);

            formData.append(`urls[${id}]`, urlInput.value);

            const sortOrderInput = card.querySelector('.slide-sort-order');
            if (sortOrderInput) {
                formData.append(`sort_order[${id}]`, sortOrderInput.value);
            }

            if (fileInput.files && fileInput.files[0]) {
                formData.append(`replace_images[${id}]`, fileInput.files[0]);
            }

            fetch("{{ route('admin.hero.settings.update') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Slide updated successfully.', 'success');

                    card.classList.remove('in-edit-mode');
                    card.classList.remove('border-amber-400', 'ring-2', 'ring-amber-500/10');
                    fileInput.value = '';

                    urlInput.setAttribute('readonly', 'readonly');
                    urlInput.classList.remove('bg-white', 'dark:bg-slate-850', 'text-slate-800', 'dark:text-slate-100');
                    urlInput.classList.add('bg-slate-100/60', 'dark:bg-slate-900', 'text-slate-500', 'dark:text-slate-400', 'cursor-not-allowed');

                    overlay.classList.add('hidden');
                    thumbWrap.classList.remove('cursor-pointer');
                    thumbWrap.classList.add('cursor-default');

                    btnIcon.className = 'fa-solid fa-pencil text-xs';
                    btnToggle.classList.remove('text-emerald-600', 'dark:text-emerald-400', 'bg-emerald-50', 'dark:bg-emerald-950/20', 'border-emerald-250');
                    btnToggle.classList.add('text-slate-600', 'dark:text-slate-350');
                    btnToggle.title = 'Edit Slide';
                } else {
                    showToast(data.message || 'Error updating slide.', 'error');
                    restoreBtn();
                }
            })
            .catch(error => {
                console.error('Error saving slide:', error);
                showToast('Failed to save slide changes.', 'error');
                restoreBtn();
            })
            .finally(() => {
                btnToggle.disabled = false;
            });
        }

        function restoreBtn() {
            btnIcon.className = 'fa-solid fa-circle-check text-xs';
        }
    }

    function handleThumbClick(id) {
        const card = document.getElementById(`slide-card-${id}`);
        if (card && card.classList.contains('in-edit-mode')) {
            const fileInput = document.getElementById(`replace-image-input-${id}`);
            if (fileInput) {
                fileInput.click();
            }
        }
    }

    function previewReplacementImage(input, id) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 4 * 1024 * 1024) {
                showToast('Replacement image exceeds 4MB limit.', 'error');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = input.closest('.slide-card');
                const img = card ? card.querySelector('img') : null;
                if (img) {
                    img.src = e.target.result;
                    card.classList.add('border-amber-400', 'ring-2', 'ring-amber-500/10');
                }
            };
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('images-input');
        const previewContainer = document.getElementById('slides-container');
        const btnAddSlide = document.getElementById('btn-add-slide');
        const uploadBtn = document.getElementById('btn-upload-images');

        const MAX_IMAGES = {{ config('hero.max_images', 5) }};
        const CURRENT_IMAGES_COUNT = {{ $images->count() }};

        let selectedFiles = [];

        function syncInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }

        window.removeSelectedFile = function(index) {
            selectedFiles.splice(index, 1);
            syncInputFiles();
            renderPreviews();
        };

        if (input && previewContainer) {
            input.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    const incomingCount = this.files.length;
                    if (CURRENT_IMAGES_COUNT + selectedFiles.length + incomingCount > MAX_IMAGES) {
                        showToast(`You cannot upload more than ${MAX_IMAGES} slide images in total. (Current: ${CURRENT_IMAGES_COUNT}, Selected: ${selectedFiles.length}, Trying to add: ${incomingCount})`, 'error');
                        this.value = '';
                        return;
                    }

                    Array.from(this.files).forEach(file => {
                        const hasError = file.size > 4 * 1024 * 1024; // 4MB
                        file.hasError = hasError;
                        selectedFiles.push(file);
                    });

                    syncInputFiles();
                    renderPreviews();
                }
            });
        }

        // Standard Form Submit Guardian
        const form = document.getElementById('hero-settings-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                const hasErrors = selectedFiles.some(file => file.hasError);
                if (hasErrors) {
                    e.preventDefault();
                    showToast('Please remove files that exceed the 4MB limit before saving.', 'error');
                    return false;
                }
            });
        }

        function renderPreviews() {
            // Remove previous new slide preview elements
            previewContainer.querySelectorAll('.new-slide-preview').forEach(c => c.remove());

            const totalCount = CURRENT_IMAGES_COUNT + selectedFiles.length;

            if (selectedFiles.length > 0) {
                uploadBtn.classList.remove('hidden');
                uploadBtn.querySelector('.upload-btn-count').innerText = `(${selectedFiles.length})`;
            } else {
                uploadBtn.classList.add('hidden');
            }

            if (totalCount >= MAX_IMAGES) {
                btnAddSlide.classList.add('hidden');
            } else {
                btnAddSlide.classList.remove('hidden');

                if (totalCount === 0) {
                    btnAddSlide.className = 'relative col-span-full border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500 rounded-2xl aspect-[21/9] min-h-[250px] flex flex-col items-center justify-center gap-3.5 cursor-pointer transition-all hover:bg-slate-50/50 dark:hover:bg-slate-800/10 shadow-2xs';
                    btnAddSlide.querySelector('.add-slide-text').innerText = 'Add Your First Slide';
                    btnAddSlide.querySelector('i').className = 'fa-solid fa-plus text-4xl text-slate-400 dark:text-slate-650';
                } else {
                    btnAddSlide.className = 'relative border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl flex flex-col items-center justify-center gap-2 cursor-pointer transition-all hover:bg-slate-50/50 dark:hover:bg-slate-800/10 min-h-[280px] shadow-2xs';
                    btnAddSlide.querySelector('.add-slide-text').innerText = 'Add Slide';
                    btnAddSlide.querySelector('i').className = 'fa-solid fa-plus text-xl text-slate-400 dark:text-slate-600';
                }
            }

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();

                const card = document.createElement('div');
                const cardBorderClass = file.hasError
                    ? 'border-rose-500 ring-2 ring-rose-500/20'
                    : 'border-slate-200 dark:border-slate-800';
                card.className = `new-slide-preview relative bg-slate-50/50 dark:bg-slate-800/20 border ${cardBorderClass} rounded-xl p-3.5 flex flex-col justify-between min-h-[280px]`;

                const thumbWrap = document.createElement('div');
                thumbWrap.className = 'w-full aspect-[16/9] rounded-lg overflow-hidden border border-slate-150 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 relative';

                const img = document.createElement('img');
                img.className = 'w-full h-full object-cover';
                thumbWrap.appendChild(img);

                const crossBtn = document.createElement('button');
                crossBtn.type = 'button';
                crossBtn.className = 'absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center border-none shadow-md cursor-pointer transition-colors z-20';
                crossBtn.innerHTML = '<i class="fa-solid fa-xmark text-[10px]"></i>';
                crossBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    window.removeSelectedFile(index);
                });
                card.appendChild(crossBtn);
                card.appendChild(thumbWrap);

                const urlDiv = document.createElement('div');
                urlDiv.className = 'mt-3.5';
                urlDiv.innerHTML = `
                    <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Link URL (Optional)</label>
                    <input type="text" name="new_urls[]" data-index="${index}" class="new-url-input w-full bg-white dark:bg-slate-855 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-[11px] outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs" placeholder="https://example.com/collection">
                `;
                card.appendChild(urlDiv);

                const footerDiv = document.createElement('div');
                footerDiv.className = 'mt-4 flex items-center justify-between text-[9px] text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800';
                footerDiv.innerHTML = `<span class="truncate pr-2 font-semibold">${file.name}</span>`;

                if (file.hasError) {
                    const errSpan = document.createElement('span');
                    errSpan.className = 'font-bold text-rose-500 shrink-0';
                    errSpan.innerText = 'Exceeds 4MB';
                    footerDiv.appendChild(errSpan);
                } else {
                    const sizeSpan = document.createElement('span');
                    sizeSpan.className = 'shrink-0';
                    sizeSpan.innerText = `${(file.size / 1024 / 1024).toFixed(1)}MB`;
                    footerDiv.appendChild(sizeSpan);
                }
                card.appendChild(footerDiv);

                previewContainer.insertBefore(card, btnAddSlide);

                reader.onload = function (e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });

            // Prevent drag events when clicking inputs or buttons in cards
            const allCards = previewContainer.querySelectorAll('.slide-card, .new-slide-preview');
            allCards.forEach(c => {
                c.querySelectorAll('input, button').forEach(el => {
                    el.addEventListener('dragstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
            });
        }

        // Initialize empty state size configurations on boot
        renderPreviews();

        // AJAX uploader trigger
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

                selectedFiles.forEach((file, index) => {
                    formData.append('images[]', file);
                    const urlInput = document.querySelector(`.new-url-input[data-index="${index}"]`);
                    const urlValue = urlInput ? urlInput.value : '';
                    formData.append('new_urls[]', urlValue);
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

        // Draggable Slide Management
        const slidesContainer = document.getElementById('slides-container');
        if (slidesContainer) {
            let draggingCard = null;

            const cards = slidesContainer.querySelectorAll('.slide-card');
            cards.forEach(card => {
                registerDragEvents(card);
            });

            function registerDragEvents(card) {
                card.addEventListener('dragstart', function (e) {
                    draggingCard = this;
                    this.classList.add('opacity-40', 'border-blue-500', 'ring-2', 'ring-blue-500/10');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.innerHTML);
                });

                card.addEventListener('dragend', function () {
                    draggingCard = null;
                    this.classList.remove('opacity-40', 'border-blue-500', 'ring-2', 'ring-blue-500/10');

                    slidesContainer.querySelectorAll('.slide-card').forEach(c => {
                        c.classList.remove('border-blue-400', 'ring-2', 'ring-blue-500/20');
                    });

                    updateSortOrders();
                });

                card.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    if (draggingCard && draggingCard !== this) {
                        const rect = this.getBoundingClientRect();
                        const midX = rect.left + rect.width / 2;
                        const midY = rect.top + rect.height / 2;

                        const isAfter = e.clientX > midX || e.clientY > midY;

                        if (isAfter) {
                            slidesContainer.insertBefore(draggingCard, this.nextSibling);
                        } else {
                            slidesContainer.insertBefore(draggingCard, this);
                        }
                    }
                    return false;
                });

                card.addEventListener('dragenter', function (e) {
                    if (draggingCard && draggingCard !== this) {
                        this.classList.add('border-blue-400', 'ring-2', 'ring-blue-500/20');
                    }
                });

                card.addEventListener('dragleave', function () {
                    if (draggingCard !== this) {
                        this.classList.remove('border-blue-400', 'ring-2', 'ring-blue-500/20');
                    }
                });
            }

            function updateSortOrders() {
                const sortedCards = slidesContainer.querySelectorAll('.slide-card');
                sortedCards.forEach((card, index) => {
                    const hiddenInput = card.querySelector('.slide-sort-order');
                    if (hiddenInput) {
                        hiddenInput.value = index + 1;
                    }
                });
            }

            // Prevent drag events when clicking inputs or buttons
            cards.forEach(card => {
                card.querySelectorAll('input, button').forEach(el => {
                    el.addEventListener('dragstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
            });
        }

        // Bulk Delete Actions
        const selectAllCheckbox = document.getElementById('select-all-slides');
        const slideCheckboxes = document.querySelectorAll('.slide-checkbox');
        const bulkDeleteBtn = document.getElementById('btn-bulk-delete');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');

        if (selectAllCheckbox && bulkDeleteBtn) {
            selectAllCheckbox.addEventListener('change', function () {
                slideCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBulkDeleteButton();
            });

            slideCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    const allChecked = Array.from(slideCheckboxes).every(c => c.checked);
                    selectAllCheckbox.checked = allChecked;
                    updateBulkDeleteButton();
                });
            });

            function updateBulkDeleteButton() {
                const checkedCount = Array.from(slideCheckboxes).filter(c => c.checked).length;
                selectedCountSpan.innerText = checkedCount;
                if (checkedCount > 0) {
                    bulkDeleteBtn.classList.remove('hidden');
                } else {
                    bulkDeleteBtn.classList.add('hidden');
                }
            }

            bulkDeleteBtn.addEventListener('click', function () {
                const checkedIds = Array.from(slideCheckboxes)
                    .filter(c => c.checked)
                    .map(c => c.value);

                if (checkedIds.length === 0) return;

                showConfirm(`Are you sure you want to delete the selected ${checkedIds.length} slides?`, () => {
                    bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

                    checkedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkDeleteForm.appendChild(input);
                    });

                    bulkDeleteForm.submit();
                }, 'Bulk Delete Slides');
            });
        }
    });
</script>
@endpush
