<!-- Support Ticket Modal Component -->
<div id="ticket-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden" style="display: none;">
    <!-- Modal Card -->
    <div id="ticket-modal-card" class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 flex flex-col overflow-hidden max-h-[90vh] transition-transform duration-150 ease-out scale-95">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-display font-extrabold text-slate-800 text-sm uppercase tracking-wider">Raise a Support Ticket</h3>
            <button type="button" onclick="closeTicketModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent outline-none">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="ticket-form" action="{{ route('store.tickets.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 px-5 py-4 space-y-4 overflow-y-auto">
            @csrf
            <input type="hidden" name="ticketable_type" value="App\Models\Order">
            <input type="hidden" name="ticketable_id" value="{{ $order->id }}">

            <!-- Order Info Badge -->
            <div class="flex items-center gap-3 bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <i class="fa-solid fa-receipt text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-slate-850 truncate">Support for Order Reference: {{ $order->order_number }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Subject Input -->
            <div class="space-y-1">
                <label for="ticket-subject" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Subject <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="subject" id="ticket-subject" required
                       class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-850 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                       placeholder="Summarize the issue (e.g. Broken item, Missing package)">
            </div>

            <!-- Priority Picker -->
            <div class="space-y-1">
                <label for="ticket-priority" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Priority <span class="text-rose-500">*</span>
                </label>
                <select name="priority" id="ticket-priority" required
                        class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-850 focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent cursor-pointer">
                    @foreach(\SGCart\CrmTickets\Enums\TicketPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" {{ $priority->value === 'Medium' ? 'selected' : '' }}>{{ $priority->value }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Description Box -->
            <div class="space-y-1">
                <label for="ticket-description" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Detailed Description <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" id="ticket-description" rows="4" required
                          class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-850 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent resize-none"
                          placeholder="Describe your query or complaint in detail. Add any relevant info..."></textarea>
            </div>

            <!-- Photo Attachment -->
            @php
                $maxImages = config('crm-tickets.max_attachments_per_comment', 3);
            @endphp
            @if($maxImages > 0)
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Attach Photos (Max {{ $maxImages }})</label>
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Custom Upload Button -->
                        <label for="ticket-photo-upload" class="flex flex-col items-center justify-center border border-dashed border-slate-200 rounded-xl p-3 cursor-pointer transition-colors bg-slate-50/50 hover:bg-slate-50 w-20 h-20 shrink-0">
                            <i class="fa-solid fa-camera text-slate-400 text-base mb-1"></i>
                            <span class="text-[8px] font-bold text-slate-500 text-center">Upload Images</span>
                            <input id="ticket-photo-upload" type="file" name="images[]" accept="image/*" class="hidden" multiple onchange="previewTicketImages(this)">
                        </label>

                        <!-- Previews Container -->
                        <div id="ticket-images-preview-container" class="flex flex-wrap gap-2"></div>
                    </div>
                    <p id="ticket-image-error" class="text-rose-500 text-[10px] font-bold hidden uppercase tracking-wide mt-1">
                        <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Max {{ $maxImages }} images allowed. Discarded excess files.
                    </p>
                </div>
            @endif

            <!-- Submit and Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeTicketModal()" class="btn btn-outline btn-sm px-4 py-2 rounded-xl text-xs font-semibold cursor-pointer">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm px-5 py-2 rounded-xl text-xs font-semibold cursor-pointer flex items-center gap-1.5">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedTicketFiles = [];

    function openTicketModal() {
        selectedTicketFiles = [];
        
        // Clear fields
        document.getElementById('ticket-subject').value = '';
        document.getElementById('ticket-description').value = '';
        document.getElementById('ticket-priority').value = 'Medium';
        document.getElementById('ticket-photo-upload').value = '';
        
        hideTicketImageError();
        renderTicketPreviews();

        // Show Modal
        const modal = document.getElementById('ticket-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        setTimeout(() => {
            document.getElementById('ticket-modal-card').classList.remove('scale-95');
            document.getElementById('ticket-modal-card').classList.add('scale-100');
        }, 10);
    }

    function closeTicketModal() {
        const card = document.getElementById('ticket-modal-card');
        card.classList.remove('scale-100');
        card.classList.add('scale-95');

        setTimeout(() => {
            const modal = document.getElementById('ticket-modal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }, 150);
    }

    function previewTicketImages(input) {
        const maxImages = parseInt('{{ $maxImages }}') || 3;
        const files = Array.from(input.files);
        
        hideTicketImageError();

        if (files.length > maxImages) {
            document.getElementById('ticket-image-error').classList.remove('hidden');
            files.splice(maxImages); // truncate to max limit
        }

        selectedTicketFiles = files;
        renderTicketPreviews();
    }

    function renderTicketPreviews() {
        const container = document.getElementById('ticket-images-preview-container');
        container.innerHTML = '';

        selectedTicketFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'relative w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-sm shrink-0 group';
                item.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeTicketImage(${index})" 
                            class="absolute top-1 right-1 w-5 h-5 bg-slate-900/80 hover:bg-slate-900 text-white rounded-full flex items-center justify-center cursor-pointer border-none outline-none text-[10px] transition-colors shadow-sm">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                container.appendChild(item);
            };
            reader.readAsDataURL(file);
        });

        // Sync files array with actual input element
        syncTicketFileInput();
    }

    function removeTicketImage(index) {
        selectedTicketFiles.splice(index, 1);
        hideTicketImageError();
        renderTicketPreviews();
    }

    function hideTicketImageError() {
        document.getElementById('ticket-image-error').classList.add('hidden');
    }

    function syncTicketFileInput() {
        const input = document.getElementById('ticket-photo-upload');
        const dataTransfer = new DataTransfer();
        selectedTicketFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }
</script>
