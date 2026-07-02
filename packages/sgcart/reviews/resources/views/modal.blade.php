<!-- Reviews Package Modal Component -->
<div id="review-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden" style="display: none;">
    <!-- Modal Card -->
    <div id="review-modal-card" class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col overflow-hidden max-h-[90vh] transition-transform duration-150 ease-out scale-95">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-display font-extrabold text-slate-800 dark:text-white text-sm uppercase tracking-wider" id="review-modal-title">Write a Review</h3>
            <button type="button" onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 cursor-pointer border-none bg-transparent outline-none">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="review-form" action="{{ route('store.reviews.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 px-5 py-4 space-y-4 overflow-y-auto">
            @csrf
            <input type="hidden" name="product_id" id="review-product-id">
            <div id="keep-images-container"></div>

            <!-- Product Info Badge -->
            <div class="flex items-center gap-3 bg-slate-50/70 dark:bg-slate-850 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                <img id="review-product-image" src="" alt="Product Image" class="w-12 h-12 object-cover rounded-lg border border-slate-200/50 dark:border-slate-700 shrink-0">
                <div class="min-w-0 flex-1">
                    <h4 id="review-product-name" class="text-xs font-bold text-slate-800 dark:text-white truncate">Product Name</h4>
                </div>
            </div>

            <!-- Stars Rating Picker -->
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Overall Rating <span class="text-rose-500 required-asterisk">*</span>
                </label>
                <div class="flex items-center gap-1.5" id="review-stars-container">
                    <input type="hidden" name="rating" id="review-rating-input" value="0">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button"
                                onclick="setReviewRating({{ $i }})"
                                onmouseover="hoverReviewRating({{ $i }})"
                                onmouseout="resetReviewRating()"
                                class="text-2xl text-slate-300 dark:text-slate-750 hover:scale-110 transition-transform cursor-pointer border-none bg-transparent p-0 outline-none">
                            <i class="fa-regular fa-star transition-colors duration-150"></i>
                        </button>
                    @endfor
                </div>
                <p id="rating-error-msg" class="text-rose-500 text-[10px] font-bold hidden uppercase tracking-wide mt-1">
                    <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Please select a rating
                </p>
            </div>

            <!-- Comment Box -->
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Review Details</label>
                <textarea name="comment" id="review-comment-input" rows="4"
                          class="w-full text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-850 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent resize-none"
                          placeholder="What did you like or dislike? How was the quality?"></textarea>
            </div>

            <!-- Photo Attachment -->
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Add Photos (Max 5)</label>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Custom Upload Button -->
                    <label for="review-photo-upload" class="flex flex-col items-center justify-center border border-dashed border-slate-200 dark:border-slate-700 hover:border-accent dark:hover:border-accent rounded-xl p-3 cursor-pointer transition-colors bg-slate-50/50 dark:bg-slate-850/50 hover:bg-slate-50 dark:hover:bg-slate-800 w-20 h-20 shrink-0">
                        <i class="fa-solid fa-camera text-slate-400 dark:text-slate-650 text-base mb-1"></i>
                        <span class="text-[8px] font-bold text-slate-500 dark:text-slate-450 text-center">Upload Images</span>
                        <input id="review-photo-upload" type="file" name="images[]" accept="image/*" class="hidden" multiple onchange="previewReviewImages(this)">
                    </label>

                    <!-- Previews Container -->
                    <div id="review-images-preview-container" class="flex flex-wrap gap-2">
                        <!-- Previews will be dynamically populated here -->
                    </div>
                </div>
                <p id="review-image-error" class="text-rose-500 text-[10px] font-bold hidden uppercase tracking-wide mt-1">
                    <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Max 5 images allowed. Discarded excess files.
                </p>
            </div>

            <!-- Submit and Actions -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeReviewModal()" class="btn btn-outline btn-sm px-4 py-2 rounded-xl text-xs font-semibold cursor-pointer">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm px-5 py-2 rounded-xl text-xs font-semibold cursor-pointer flex items-center gap-1.5">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedRating = 0;
    let selectedNewFiles = []; // Holds local File objects
    let existingImagesList = []; // Holds {path, url} objects from the server

    function openReviewModal(productId, productName, productImage, existingRating = 0, existingComment = '', existingImages = []) {
        selectedRating = parseInt(existingRating) || 0;
        selectedNewFiles = [];
        existingImagesList = existingImages;

        // Fill inputs
        document.getElementById('review-product-id').value = productId;
        document.getElementById('review-rating-input').value = selectedRating;
        document.getElementById('review-comment-input').value = existingComment;

        // Fill texts and images
        document.getElementById('review-product-name').textContent = productName;
        document.getElementById('review-product-image').src = productImage;

        // Update Title
        const modalTitle = document.getElementById('review-modal-title');
        modalTitle.textContent = selectedRating > 0 ? 'Edit Your Review' : 'Write a Review';

        // Set Stars
        setReviewRating(selectedRating);

        // Reset file input and preview container
        document.getElementById('review-photo-upload').value = '';
        hideImageError();
        renderPreviewContainers();

        // Show Modal
        const modal = document.getElementById('review-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        // Fade in animation trigger
        setTimeout(() => {
            document.getElementById('review-modal-card').classList.remove('scale-95');
            document.getElementById('review-modal-card').classList.add('scale-100');
        }, 10);
    }

    function closeReviewModal() {
        const card = document.getElementById('review-modal-card');
        card.classList.remove('scale-100');
        card.classList.add('scale-95');

        setTimeout(() => {
            const modal = document.getElementById('review-modal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }, 150);
    }

    function setReviewRating(rating) {
        selectedRating = rating;
        document.getElementById('review-rating-input').value = rating;

        const stars = document.querySelectorAll('#review-stars-container i');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('fa-regular', 'text-slate-300', 'dark:text-slate-700');
                star.classList.add('fa-solid', 'text-amber-400');
            } else {
                star.classList.remove('fa-solid', 'text-amber-400');
                star.classList.add('fa-regular', 'text-slate-300', 'dark:text-slate-700');
            }
        });

        if (rating > 0) {
            document.getElementById('rating-error-msg').classList.add('hidden');
        }
    }

    function hoverReviewRating(rating) {
        const stars = document.querySelectorAll('#review-stars-container i');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-slate-300', 'dark:text-slate-700');
                star.classList.add('text-amber-400');
            } else {
                star.classList.remove('text-amber-400');
            }
        });
    }

    function resetReviewRating() {
        setReviewRating(selectedRating);
    }

    function renderPreviewContainers() {
        const container = document.getElementById('review-images-preview-container');
        container.innerHTML = '';

        const keepContainer = document.getElementById('keep-images-container');
        keepContainer.innerHTML = '';

        // 1. Render existing images
        existingImagesList.forEach((img, index) => {
            // Append hidden input for keep_images
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'keep_images[]';
            hiddenInput.value = img.path;
            keepContainer.appendChild(hiddenInput);

            // Render thumbnail
            const div = document.createElement('div');
            div.className = 'relative w-20 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-850 bg-slate-50 dark:bg-slate-800 shrink-0';
            div.innerHTML = `
                <img src="${img.url}" class="w-full h-full object-cover">
                <button type="button" onclick="removeExistingImage(${index})" class="absolute top-1 right-1 bg-black/75 hover:bg-black/90 text-white w-4.5 h-4.5 rounded-full flex items-center justify-center cursor-pointer transition-colors border-none outline-none">
                    <i class="fa-solid fa-xmark text-[8px]"></i>
                </button>
            `;
            container.appendChild(div);
        });

        // 2. Render new selected images
        selectedNewFiles.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'relative w-20 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-850 bg-slate-50 dark:bg-slate-800 shrink-0';
            div.innerHTML = `
                <img id="new-preview-img-${index}" class="w-full h-full object-cover bg-slate-100 dark:bg-slate-800">
                <button type="button" onclick="removeNewImage(${index})" class="absolute top-1 right-1 bg-black/75 hover:bg-black/90 text-white w-4.5 h-4.5 rounded-full flex items-center justify-center cursor-pointer transition-colors border-none outline-none">
                    <i class="fa-solid fa-xmark text-[8px]"></i>
                </button>
            `;
            container.appendChild(div);

            const reader = new FileReader();
            reader.onload = function(e) {
                const imgEl = document.getElementById(`new-preview-img-${index}`);
                if (imgEl) imgEl.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function previewReviewImages(input) {
        if (!input.files || input.files.length === 0) return;

        let newFiles = Array.from(input.files);
        let totalSoFar = existingImagesList.length + selectedNewFiles.length;
        let limitRemaining = 5 - totalSoFar;
        let exceeded = false;

        if (newFiles.length > limitRemaining) {
            exceeded = true;
            newFiles = newFiles.slice(0, limitRemaining);
        }

        // Append new files
        selectedNewFiles = selectedNewFiles.concat(newFiles);

        if (exceeded) {
            showImageError("Maximum 5 images allowed. Discarded excess files.");
        } else {
            hideImageError();
        }

        // Update the files list on file input
        updateFileInput();

        // Render updated view
        renderPreviewContainers();
    }

    function removeExistingImage(index) {
        existingImagesList.splice(index, 1);
        hideImageError();
        renderPreviewContainers();
    }

    function removeNewImage(index) {
        selectedNewFiles.splice(index, 1);
        updateFileInput();
        hideImageError();
        renderPreviewContainers();
    }

    function updateFileInput() {
        const fileInput = document.getElementById('review-photo-upload');
        const dt = new DataTransfer();
        selectedNewFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    function showImageError(message) {
        const errorEl = document.getElementById('review-image-error');
        errorEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-0.5"></i> ${message}`;
        errorEl.classList.remove('hidden');
    }

    function hideImageError() {
        const errorEl = document.getElementById('review-image-error');
        errorEl.classList.add('hidden');
    }

    // Form Submission Validation
    document.getElementById('review-form').addEventListener('submit', function(e) {
        const rating = document.getElementById('review-rating-input').value;
        if (!rating || parseInt(rating) === 0) {
            e.preventDefault();
            document.getElementById('rating-error-msg').classList.remove('hidden');
        }
    });
</script>
