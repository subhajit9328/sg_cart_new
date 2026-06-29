@extends('layouts.admin')

@section('title', 'Shipping Rate Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Shipping Rate Management</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Logistics / Shipping Rates</p>
    </div>
    <a href="{{ route('admin.shipping.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-plus"></i> Add Shipping Rate
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start mb-6">
    <!-- Shipping Rates Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-sm">Shipping Charge Rates</h2>
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="searchRatesInput" placeholder="Search rates by name..." class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Charge / Rate</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Min. Cart Subtotal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($rates as $rate)
                    <tr>
                        <td class="px-5 py-4 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                            {{ $rate->name }}
                        </td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap capitalize">
                            @if(($rate->type ?? 'flat') === 'percent')
                                <span class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                                    <i class="fa-solid fa-percent text-xs"></i> Percent
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                                    <i class="fa-solid fa-indian-rupee-sign text-xs"></i> Flat Rate
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium whitespace-nowrap">
                            @if(($rate->type ?? 'flat') === 'percent')
                                {{ number_format($rate->cost, 1) }}%
                            @else
                                ₹{{ number_format($rate->cost, 2) }}
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            ₹{{ number_format($rate->min_order_amount, 2) }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($rate->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                    Disabled
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('admin.shipping.edit', $rate->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Shipping Rate">
                                    <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                                </a>
                                
                                <button onclick="openDeleteModal('{{ $rate->id }}', '{{ $rate->name }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Shipping Rate">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-truck text-4xl mb-3 opacity-20 block"></i>
                            No shipping rates registered yet. Click "Add Shipping Rate" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($rates->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $rates->links() }}
            </div>
        @endif
    </div>

    <!-- Global Shipping Settings Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-2">
            <i class="fa-solid fa-sliders text-blue-500"></i>
            <h2 class="font-semibold text-sm">Global Settings</h2>
        </div>
        <form action="{{ route('admin.shipping.settings.update') }}" method="POST" class="p-5 space-y-4">
            @csrf
            
            <div class="space-y-3">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Calculation &amp; Selection Mode</span>
                
                <!-- Option 1: Customer Choice -->
                <div class="border rounded-xl p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-all relative overflow-hidden config-card {{ $selectionMode === 'user_choice' ? 'border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-500/5 ring-1 ring-blue-500' : 'border-slate-200 dark:border-slate-800 bg-transparent' }}" onclick="toggleConfig(this)">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="shipping_selection_mode" value="user_choice" {{ $selectionMode === 'user_choice' ? 'checked' : '' }} class="mt-0.5 focus:ring-blue-500 h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 cursor-pointer">
                        <div class="flex-1">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Customer Choice</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
                                Presents all eligible shipping rates to customers during checkout. Cart displays "Calculated at checkout".
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Option 2: Automated Cheapest -->
                <div class="border rounded-xl p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-all relative overflow-hidden config-card {{ $selectionMode === 'auto_cheapest' ? 'border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-500/5 ring-1 ring-blue-500' : 'border-slate-200 dark:border-slate-800 bg-transparent' }}" onclick="toggleConfig(this)">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="shipping_selection_mode" value="auto_cheapest" {{ $selectionMode === 'auto_cheapest' ? 'checked' : '' }} class="mt-0.5 focus:ring-blue-500 h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 cursor-pointer">
                        <div class="flex-1">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Cheapest Rate (Automated)</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
                                Automatically resolves and applies the cheapest eligible rate. Cart shows estimated price. Hides selection on checkout.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold shadow-lg shadow-blue-600/10 hover:shadow-blue-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-[10px]"></i> Save Settings
            </button>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function toggleConfig(element) {
        document.querySelectorAll('.config-card').forEach(card => {
            card.classList.remove('border-blue-600', 'dark:border-blue-500', 'bg-blue-50/10', 'dark:bg-blue-500/5', 'ring-1', 'ring-blue-500');
            card.classList.add('border-slate-200', 'dark:border-slate-800', 'bg-transparent');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        element.classList.remove('border-slate-200', 'dark:border-slate-800', 'bg-transparent');
        element.classList.add('border-blue-600', 'dark:border-blue-500', 'bg-blue-50/10', 'dark:bg-blue-500/5', 'ring-1', 'ring-blue-500');
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchRatesInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('tbody tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    if (row.querySelector('td[colspan]')) return; // Skip empty row

                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    if (name.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                let emptyRow = document.getElementById('noMatchingRatesRow');
                if (visibleCount === 0 && rows.length > 0 && !rows[0].querySelector('td[colspan]')) {
                    if (!emptyRow) {
                        emptyRow = document.createElement('tr');
                        emptyRow.id = 'noMatchingRatesRow';
                        emptyRow.innerHTML = `
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-4xl mb-3 opacity-20 block"></i>
                                No shipping rates match your search query.
                            </td>
                        `;
                        document.querySelector('tbody').appendChild(emptyRow);
                    } else {
                        emptyRow.style.display = '';
                    }
                } else if (emptyRow) {
                    emptyRow.style.display = 'none';
                }
            });
        }
    });

    function openDeleteModal(rateId, name) {
        showConfirm(
            `Are you sure you want to delete shipping rate "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/shipping/${rateId}`;
                form.submit();
            },
            'Delete Shipping Rate?'
        );
    }
</script>
@endsection
