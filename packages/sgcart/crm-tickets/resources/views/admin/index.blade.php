@extends('layouts.admin')

@section('title', 'Support Tickets — SGCart Admin')

@section('content')

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Support Tickets</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Support'],
            ['label' => 'Tickets']
        ]" />
    </div>
</div>

<!-- KPI Metrics Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-6">
    <!-- Card 1: Total Tickets -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shrink-0">
            <i class="fa-solid fa-headset"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Tickets</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($totalTickets) }}</span>
        </div>
    </div>

    <!-- Card 2: Open -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center text-sky-600 dark:text-sky-400 text-lg shrink-0">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Open</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($openCount) }}</span>
        </div>
    </div>

    <!-- Card 3: In Review -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
            <i class="fa-solid fa-magnifying-glass-chart"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">In Review</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($reviewCount) }}</span>
        </div>
    </div>

    <!-- Card 4: Resolved -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Resolved</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($resolvedCount) }}</span>
        </div>
    </div>

    <!-- Card 5: Rejected -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg shrink-0">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Rejected</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($rejectedCount) }}</span>
        </div>
    </div>
</div>

<!-- Tickets Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Filters -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Tickets</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $tickets->total() }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <form id="ticket-search-form" action="{{ route('admin.tickets.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <!-- Search Box -->
                <div class="relative flex items-center w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" id="ticket-search-input" value="{{ request('search') }}"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="Search by subject, customer…">
                </div>

                <!-- Status Filter -->
                <select name="status_id" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>

                <!-- Priority Filter -->
                <select name="priority" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                    <option value="">All Priorities</option>
                    @foreach(\SGCart\CrmTickets\Enums\TicketPriority::cases() as $p)
                        <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->value }}</option>
                    @endforeach
                </select>

                @if(request()->anyFilled(['search', 'status_id', 'priority']))
                    <a href="{{ route('admin.tickets.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center">
                        Clear Filters
                    </a>
                @endif
            </form>

            @if($tickets->isNotEmpty() && auth()->user()->can('manage tickets'))
                <button type="button" onclick="toggleBulkSelect()" class="px-3 py-1.5 bg-white text-slate-650 border border-slate-200 hover:text-slate-900 rounded-lg text-xs font-bold cursor-pointer transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-list-check text-xs"></i> Bulk Action
                </button>
            @endif
        </div>
    </div>

    <!-- Bulk Action Header (Hidden by default) -->
    @can('manage tickets')
        <div id="bulk-actions-bar" class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 bg-blue-50/50 dark:bg-blue-950/10 justify-between items-center gap-3 hidden" style="display: none;">
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-semibold text-slate-500">Selected <strong id="checked-count" class="text-blue-600">0</strong> tickets</span>
                <span class="text-slate-300">|</span>
                <button type="button" onclick="submitBulkAction('delete')" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer border-none flex items-center gap-1">
                    <i class="fa-solid fa-trash-can text-[10px]"></i> Delete Selected
                </button>
            </div>
            <button type="button" onclick="cancelBulkSelect()" class="text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer bg-transparent border-none outline-none">
                Cancel
            </button>
        </div>
    @endcan

    <!-- Table -->
    <form id="bulk-action-form" action="{{ route('admin.tickets.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        @can('manage tickets')
                            <th class="py-3 px-5 w-10 bulk-checkbox-col hidden" style="display: none;">
                                <input type="checkbox" id="bulk-toggle-all" onclick="toggleAllCheckboxes(this)" class="cursor-pointer">
                            </th>
                        @endcan
                        <th class="py-3 px-5">ID</th>
                        <th class="py-3 px-5">Customer</th>
                        <th class="py-3 px-5">Subject</th>
                        <th class="py-3 px-5">Linked To</th>
                        <th class="py-3 px-5">Priority</th>
                        <th class="py-3 px-5">Status</th>
                        <th class="py-3 px-5">Created At</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors">
                            @can('manage tickets')
                                <td class="py-4 px-5 w-10 bulk-checkbox-col hidden" style="display: none;">
                                    <input type="checkbox" name="bulk_ids[]" value="{{ $ticket->id }}" onclick="updateCheckedCount()" class="bulk-row-checkbox cursor-pointer">
                                </td>
                            @endcan

                            <!-- ID -->
                            <td class="py-4 px-5 font-mono text-slate-400">
                                #{{ $ticket->id }}
                            </td>

                            <!-- Customer -->
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-850 dark:text-white">{{ $ticket->customer->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $ticket->customer->email ?? '' }}</div>
                            </td>

                            <!-- Subject -->
                            <td class="py-4 px-5 max-w-xs">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="font-bold text-slate-850 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors" style="text-decoration: none;">
                                    {{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}
                                </a>
                            </td>

                            <!-- Linked To -->
                            <td class="py-4 px-5">
                                @if($ticket->ticketable)
                                    @if($ticket->ticketable_type === 'App\Models\Order')
                                        <a href="{{ route('admin.orders.show', $ticket->ticketable->ulid ?? $ticket->ticketable_id) }}" class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 font-semibold hover:underline" style="text-decoration: none;">
                                            <i class="fa-solid fa-receipt text-[10px]"></i>
                                            {{ $ticket->ticketable->order_number ?? 'Order' }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">{{ class_basename($ticket->ticketable_type) }} #{{ $ticket->ticketable_id }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Priority Badge -->
                            <td class="py-4 px-5">
                                @php $priorityEnum = $ticket->priority; @endphp
                                @if($priorityEnum)
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $priorityEnum->badgeClasses() }}">
                                        {{ $priorityEnum->value }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-5">
                                @php
                                    $statusEnum = $ticket->status ? \SGCart\CrmTickets\Enums\TicketStatus::fromDb($ticket->status->name) : null;
                                @endphp
                                @if($statusEnum)
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusEnum->badgeClasses() }}">
                                        {{ $statusEnum->value }}
                                    </span>
                                @endif
                            </td>

                            <!-- Created At -->
                            <td class="py-4 px-5 text-slate-400 dark:text-slate-500 font-mono text-[10px]">
                                {{ $ticket->created_at->format('M d, Y h:i A') }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-850 hover:bg-blue-50 dark:hover:bg-blue-500/10 inline-flex items-center justify-center text-blue-600 dark:text-blue-400 transition-colors" title="View Ticket" style="text-decoration: none;">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    @can('manage tickets')
                                        <button type="button" onclick="confirmDeleteTicket('{{ $ticket->id }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-850 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400 transition-colors cursor-pointer border-none outline-none" title="Delete Ticket">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-headset text-2xl mb-2 block opacity-40"></i>
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <!-- Pagination -->
    @if($tickets->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10">
            {{ $tickets->links() }}
        </div>
    @endif
</div>

<!-- Hidden Delete Ticket Form -->
<form id="delete-ticket-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDeleteTicket(ticketId) {
        showConfirm(
            'Are you sure you want to delete this ticket? This action cannot be undone.',
            () => {
                const form = document.getElementById('delete-ticket-form');
                form.action = `/admin/tickets/${ticketId}`;
                form.submit();
            },
            'Delete Ticket?'
        );
    }

    function toggleBulkSelect() {
        const checkboxCols = document.querySelectorAll('.bulk-checkbox-col');
        const bulkBar = document.getElementById('bulk-actions-bar');
        const masterToggle = document.getElementById('bulk-toggle-all');
        const rowCheckboxes = document.querySelectorAll('.bulk-row-checkbox');

        checkboxCols.forEach(col => {
            col.classList.remove('hidden');
            col.style.display = 'table-cell';
        });

        if (bulkBar) {
            bulkBar.classList.remove('hidden');
            bulkBar.style.display = 'flex';
        }

        if (masterToggle) {
            masterToggle.checked = true;
        }
        rowCheckboxes.forEach(cb => {
            cb.checked = true;
        });

        updateCheckedCount();
    }

    function cancelBulkSelect() {
        const checkboxCols = document.querySelectorAll('.bulk-checkbox-col');
        const bulkBar = document.getElementById('bulk-actions-bar');
        const masterToggle = document.getElementById('bulk-toggle-all');
        const rowCheckboxes = document.querySelectorAll('.bulk-row-checkbox');

        checkboxCols.forEach(col => {
            col.classList.add('hidden');
            col.style.display = 'none';
        });

        if (bulkBar) {
            bulkBar.classList.add('hidden');
            bulkBar.style.display = 'none';
        }

        if (masterToggle) {
            masterToggle.checked = false;
        }
        rowCheckboxes.forEach(cb => {
            cb.checked = false;
        });

        updateCheckedCount();
    }

    function toggleAllCheckboxes(master) {
        const rowCheckboxes = document.querySelectorAll('.bulk-row-checkbox');
        rowCheckboxes.forEach(cb => {
            cb.checked = master.checked;
        });
        updateCheckedCount();
    }

    function updateCheckedCount() {
        const rowCheckboxes = document.querySelectorAll('.bulk-row-checkbox');
        const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
        const countSpan = document.getElementById('checked-count');
        if (countSpan) {
            countSpan.textContent = checkedCount;
        }

        const masterToggle = document.getElementById('bulk-toggle-all');
        if (masterToggle) {
            masterToggle.checked = rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length;
        }
    }

    function submitBulkAction(actionType) {
        const rowCheckboxes = document.querySelectorAll('.bulk-row-checkbox');
        const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;

        if (checkedCount === 0) {
            alert('Please select at least one ticket to perform this action.');
            return;
        }

        let confirmMsg = `Are you sure you want to delete the ${checkedCount} selected tickets? This action cannot be undone.`;

        showConfirm(confirmMsg, () => {
            const form = document.getElementById('bulk-action-form');
            const actionInput = document.getElementById('bulk-action-input');
            if (form && actionInput) {
                actionInput.value = actionType;
                form.submit();
            }
        }, `Delete Tickets?`);
    }

    // Search Debounce and Focus Integration
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout = null;
        const searchInput = document.getElementById('ticket-search-input');
        const searchForm = document.getElementById('ticket-search-form');

        if (searchInput && searchForm) {
            // Automatically submit form 250ms after user stops typing
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 250);
            });

            // Focus and position cursor at the end of text when search results load
            if (searchInput.value.length > 0) {
                searchInput.focus();
                const tempVal = searchInput.value;
                searchInput.value = '';
                searchInput.value = tempVal;
            }
        }
    });
</script>

@endsection
