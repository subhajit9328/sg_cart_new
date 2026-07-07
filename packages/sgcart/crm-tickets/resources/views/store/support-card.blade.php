<!-- Order Support Card -->
<div class="bg-white border border-[#e8e4df] rounded-2xl p-6">
    <h4 class="font-display font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
        <span class="flex items-center gap-1.5"><i class="fa-solid fa-headset text-slate-400"></i> Order Support</span>
    </h4>
    
    @if(isset($order->tickets) && $order->tickets->isNotEmpty())
        <div class="space-y-3 mb-4">
            @foreach($order->tickets as $ticket)
                @php
                    $statusEnum = $ticket->status ? \SGCart\CrmTickets\Enums\TicketStatus::fromDb($ticket->status->name) : null;
                @endphp
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-[10px] font-mono text-slate-400">#{{ $ticket->id }}</span>
                            @if($statusEnum)
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ $statusEnum->badgeClasses() }}">
                                    {{ $statusEnum->value }}
                                </span>
                            @endif
                        </div>
                        <h5 class="text-xs font-semibold text-slate-800 truncate" title="{{ $ticket->subject }}">
                            {{ $ticket->subject }}
                        </h5>
                    </div>
                    <a href="{{ route('store.tickets.show', $ticket->id) }}" class="text-xs font-bold text-accent hover:text-slate-900 transition-colors ml-3 flex items-center gap-1 shrink-0" style="text-decoration:none">
                        View <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-slate-500 leading-relaxed mb-4">
            Having trouble or have questions about this order? Raise a support ticket and our customer service team will assist you.
        </p>
    @endif

    <button type="button" onclick="openTicketModal()" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all text-center no-underline cursor-pointer border-none outline-none">
        <i class="fa-solid fa-circle-plus text-xs"></i> Raise Support Ticket
    </button>
</div>
