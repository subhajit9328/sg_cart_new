@extends('layouts.admin')

@section('title', 'Ticket #' . $ticket->id . ' — SGCart Admin')

@section('content')

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Ticket #{{ $ticket->id }}</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Support'],
            ['label' => 'Tickets', 'url' => route('admin.tickets.index')],
            ['label' => '#' . $ticket->id]
        ]" />
    </div>
    <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold transition-colors flex items-center gap-1.5" style="text-decoration: none;">
        <i class="fa-solid fa-arrow-left text-xs"></i> Back to Tickets
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ============ Left Column: Ticket Detail + Comments ============ -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Ticket Detail Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ $ticket->subject }}</h2>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <span>Opened by <strong class="text-slate-600 dark:text-slate-300">{{ $ticket->customer->name ?? 'Unknown' }}</strong></span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="px-6 py-5">
                <div class="prose prose-sm dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed">
                    {!! nl2br(e($ticket->description)) !!}
                </div>

                <!-- Ticket-level attachments -->
                @if($ticket->attachments->where('ticket_comment_id', null)->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Attachments</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ticket->attachments->where('ticket_comment_id', null) as $att)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}"
                                     alt="Ticket Attachment"
                                     class="w-16 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-800 cursor-zoom-in hover:brightness-90 transition-all shadow-sm"
                                     onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}')">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Comments Thread -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-comments"></i>
                    Conversation
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                        {{ $ticket->comments->count() }}
                    </span>
                </h3>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ticket->comments as $comment)
                    @php
                        $isAdmin = $comment->commentable_type === 'App\Models\User';
                        $commenterName = $comment->commentable->name ?? 'Unknown';
                    @endphp
                    <div class="px-6 py-5 {{ $isAdmin ? 'bg-blue-50/30 dark:bg-blue-950/10' : '' }}">
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-white text-xs font-bold {{ $isAdmin ? 'bg-blue-600' : 'bg-slate-500' }}">
                                {{ strtoupper(substr($commenterName, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $commenterName }}</span>
                                    @if($isAdmin)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 uppercase">Staff</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 uppercase">Customer</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $comment->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                    {!! nl2br(e($comment->body)) !!}
                                </div>

                                <!-- Comment attachments -->
                                @if($comment->attachments->isNotEmpty())
                                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                                        @foreach($comment->attachments as $att)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}"
                                                 alt="Comment Attachment"
                                                 class="w-14 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-800 cursor-zoom-in hover:brightness-90 transition-all shadow-sm"
                                                 onclick="openLightbox('{{ \Illuminate\Support\Facades\Storage::url($att->file_path) }}')">
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                        <i class="fa-regular fa-comments text-xl mb-2 block opacity-40"></i>
                        No comments yet.
                    </div>
                @endforelse
            </div>

            <!-- Admin Reply Form -->
            @can('manage tickets')
                <div class="px-6 py-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30">
                    <form action="{{ route('admin.tickets.comments.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="comment-body" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5 block">Reply</label>
                            <textarea id="comment-body" name="body" rows="3" required
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 resize-y"
                                placeholder="Type your reply…"></textarea>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-semibold cursor-pointer transition-colors">
                                <i class="fa-solid fa-paperclip text-xs"></i>
                                Attach Images
                                <input type="file" name="images[]" multiple accept="image/*" class="hidden" onchange="updateFileLabel(this)">
                            </label>
                            <span id="file-count" class="text-[10px] text-slate-400 hidden"></span>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer border-none flex items-center gap-1.5">
                                <i class="fa-solid fa-paper-plane text-xs"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>
    </div>

    <!-- ============ Right Column: Sidebar Info ============ -->
    <div class="space-y-6">

        <!-- Status & Priority Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ticket Details</h3>
            </div>
            <div class="px-5 py-4 space-y-4">
                <!-- Status -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Status</label>
                    @can('manage tickets')
                        <form action="{{ route('admin.tickets.updateStatus', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="flex items-center gap-2">
                                <select name="status_id" class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                                    @foreach($statuses as $st)
                                        <option value="{{ $st->id }}" {{ $ticket->status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white rounded-lg text-[10px] font-bold transition-colors cursor-pointer border-none">
                                    Update
                                </button>
                            </div>
                        </form>
                    @else
                        @php $statusEnum = $ticket->status ? \SGCart\CrmTickets\Enums\TicketStatus::fromDb($ticket->status->name) : null; @endphp
                        @if($statusEnum)
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusEnum->badgeClasses() }}">
                                {{ $statusEnum->value }}
                            </span>
                        @endif
                    @endcan
                </div>

                <!-- Priority -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Priority</label>
                    @can('manage tickets')
                        <form action="{{ route('admin.tickets.updatePriority', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="flex items-center gap-2">
                                <select name="priority" class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                                    @foreach(\SGCart\CrmTickets\Enums\TicketPriority::cases() as $p)
                                        <option value="{{ $p->value }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ $p->value }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white rounded-lg text-[10px] font-bold transition-colors cursor-pointer border-none">
                                    Update
                                </button>
                            </div>
                        </form>
                    @else
                        @if($ticket->priority)
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ticket->priority->badgeClasses() }}">
                                {{ $ticket->priority->value }}
                            </span>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer</h3>
            </div>
            <div class="px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 text-sm font-bold shrink-0">
                        {{ strtoupper(substr($ticket->customer->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $ticket->customer->name ?? 'Unknown' }}</div>
                        <div class="text-[10px] text-slate-400">{{ $ticket->customer->email ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linked Entity Card -->
        @if($ticket->ticketable)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Linked Resource</h3>
                </div>
                <div class="px-5 py-4">
                    @if($ticket->ticketable_type === 'App\Models\Order')
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 text-sm shrink-0">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <a href="{{ route('admin.orders.show', $ticket->ticketable->ulid ?? $ticket->ticketable_id) }}" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline" style="text-decoration: none;">
                                    {{ $ticket->ticketable->order_number ?? 'Order' }}
                                </a>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $ticket->ticketable->created_at ? $ticket->ticketable->created_at->format('M d, Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-xs text-slate-500">
                            {{ class_basename($ticket->ticketable_type) }} #{{ $ticket->ticketable_id }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

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

    function updateFileLabel(input) {
        const count = input.files.length;
        const countSpan = document.getElementById('file-count');
        if (count > 0) {
            countSpan.textContent = count + ' file' + (count > 1 ? 's' : '') + ' selected';
            countSpan.classList.remove('hidden');
        } else {
            countSpan.classList.add('hidden');
        }
    }
</script>

@endsection
