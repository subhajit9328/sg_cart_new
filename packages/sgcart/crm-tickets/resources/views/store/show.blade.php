@extends('layouts.store')

@section('title', 'Ticket #' . $ticket->id . ' — sgcart')

@section('content')
<div class="storefront-container py-8 max-w-4xl mx-auto">
    <!-- Back to Order Details Link -->
    <div class="mb-6">
        @if($ticket->ticketable_type === 'App\Models\Order')
            <a href="{{ route('store.account.order.view', $ticket->ticketable->ulid ?? $ticket->ticketable_id) }}" 
               class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wider flex items-center gap-1.5" 
               style="text-decoration:none">
                <i class="fa-solid fa-arrow-left-long"></i> Back to Order Details
            </a>
        @else
            <a href="{{ route('store.account', 'orders') }}" 
               class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wider flex items-center gap-1.5" 
               style="text-decoration:none">
                <i class="fa-solid fa-arrow-left-long"></i> Back to My Account
            </a>
        @endif
    </div>

    <!-- Ticket Summary Card -->
    <div class="bg-white border border-[#e8e4df] rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
            <div>
                <h1 class="font-display font-extrabold text-lg text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-headset text-accent"></i> Support Ticket #{{ $ticket->id }}
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Opened on {{ $ticket->created_at->format('M d, Y h:i A') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @php
                    $statusEnum = $ticket->status ? \SGCart\CrmTickets\Enums\TicketStatus::fromDb($ticket->status->name) : null;
                @endphp
                @if($statusEnum)
                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusEnum->badgeClasses() }}">
                        Status: {{ $statusEnum->value }}
                    </span>
                @endif

                @if($ticket->priority)
                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $ticket->priority->badgeClasses() }}">
                        Priority: {{ $ticket->priority->value }}
                    </span>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Subject</h4>
                <p class="text-sm font-semibold text-slate-800">{{ $ticket->subject }}</p>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Description</h4>
                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
            </div>

            <!-- Ticket-level attachments -->
            @if($ticket->attachments->where('ticket_comment_id', null)->isNotEmpty())
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ticket Attachments</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments->where('ticket_comment_id', null) as $att)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}"
                                 alt="Attachment"
                                 class="w-16 h-16 object-cover rounded-xl border border-slate-200 cursor-zoom-in hover:brightness-95 transition-all shadow-xs"
                                 onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}')">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Conversation Timeline -->
    <div class="bg-white border border-[#e8e4df] rounded-2xl overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-comments text-slate-400"></i>
                Ticket Thread
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-500 border border-slate-300/35">
                    {{ $ticket->comments->count() }}
                </span>
            </h3>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($ticket->comments as $comment)
                @php
                    $isAdmin = $comment->commentable_type === 'App\Models\User';
                    $commenterName = $comment->commentable->name ?? 'User';
                @endphp
                <div class="p-6 {{ $isAdmin ? 'bg-slate-50/40' : '' }}">
                    <div class="flex items-start gap-3.5">
                        <!-- Avatar -->
                        <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-white text-xs font-bold {{ $isAdmin ? 'bg-accent' : 'bg-slate-500' }}">
                            {{ strtoupper(substr($commenterName, 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="text-xs font-bold text-slate-800">{{ $commenterName }}</span>
                                @if($isAdmin)
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">Support Staff</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wider">You</span>
                                @endif
                                <span class="text-[10px] text-slate-400 font-mono">{{ $comment->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="text-xs text-slate-650 leading-relaxed whitespace-pre-line">{!! e($comment->body) !!}</div>

                            <!-- Comment Attachments -->
                            @if($comment->attachments->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach($comment->attachments as $att)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}"
                                             alt="Attachment"
                                             class="w-14 h-14 object-cover rounded-lg border border-slate-200 cursor-zoom-in hover:brightness-95 transition-all shadow-xs"
                                             onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}')">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    <i class="fa-regular fa-comments text-xl mb-2 block opacity-40"></i>
                    No responses yet. We will get back to you shortly.
                </div>
            @endforelse
        </div>

        <!-- Add Reply Form (Only if ticket is not resolved/rejected or customer wants to follow up) -->
        <div class="p-6 border-t border-slate-100 bg-slate-50/30">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Add a Reply</h4>
            <form action="{{ route('store.tickets.comments.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <textarea name="body" rows="4" required
                              class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-850 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent resize-none"
                              placeholder="Type your reply here..."></textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    @php
                        $maxCommentImages = config('crm-tickets.max_attachments_per_comment', 3);
                    @endphp
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500 text-xs font-semibold cursor-pointer transition-colors">
                            <i class="fa-solid fa-paperclip text-xs"></i>
                            Attach Images
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden" onchange="updateCommentFiles(this)">
                        </label>
                        <span id="comment-files-count" class="text-[10px] text-slate-400 hidden"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm px-5 py-2.5 rounded-xl text-xs font-semibold cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Lightbox Modal -->
<div id="lightbox-modal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/85 hidden" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white hover:text-slate-350 bg-transparent border-none cursor-pointer outline-none">
        <i class="fa-solid fa-xmark text-2xl"></i>
    </button>
    <img id="lightbox-image" src="" alt="Zoomed Attachment" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
</div>

<script>
    function openLightbox(src) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-image');
        img.src = src;
        modal.classList.remove('hidden');
    }

    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        modal.classList.add('hidden');
    }

    function updateCommentFiles(input) {
        const maxImages = parseInt('{{ $maxCommentImages }}') || 3;
        const count = input.files.length;
        const countSpan = document.getElementById('comment-files-count');
        
        if (count > maxImages) {
            alert('You can only attach up to ' + maxImages + ' images per reply.');
            // Reset input
            input.value = '';
            countSpan.classList.add('hidden');
            return;
        }

        if (count > 0) {
            countSpan.textContent = count + ' file' + (count > 1 ? 's' : '') + ' selected';
            countSpan.classList.remove('hidden');
        } else {
            countSpan.classList.add('hidden');
        }
    }
</script>
@endsection
