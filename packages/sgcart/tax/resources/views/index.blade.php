@extends('layouts.admin')

@section('title', 'Tax Rate Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Tax Rate Management</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Finance'],
            ['label' => 'Tax Rates']
        ]" />
    </div>
    <a href="{{ route('admin.tax.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-plus"></i> Add Tax Rate
    </a>
</div>@php
    $headers = [
        ['label' => 'Name', 'key' => 'name', 'sortable' => true],
        ['label' => 'Type', 'key' => 'type', 'sortable' => true],
        ['label' => 'Rate', 'key' => 'rate', 'sortable' => true],
        ['label' => 'Region', 'key' => 'region', 'sortable' => false],
        ['label' => 'Status', 'key' => 'is_active', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start mb-6">
    <!-- Tax Rates Table -->
    <x-data-table
        title="Tax Rates"
        :totalCount="$rates->total()"
        searchPlaceholder="Search rates by name..."
        action="{{ route('admin.tax.index') }}"
        tableId="taxRatesTableWrapper"
        searchInputId="searchRatesInput"
        totalCountId="taxRatesTotalCount"
        :items="$rates"
        :headers="$headers"
    >
        @forelse($rates as $rate)
            <tr>
                <td class="px-5 py-4 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                    {{ $rate->name }}
                </td>
                <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap capitalize">
                    @if($rate->type === 'percent')
                        <span class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                            <i class="fa-solid fa-percent text-xs"></i> Percentage
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                            <i class="fa-solid fa-indian-rupee-sign text-xs"></i> Flat Rate
                        </span>
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium whitespace-nowrap">
                    @if($rate->type === 'percent')
                        {{ number_format($rate->rate, 1) }}%
                    @else
                        ₹{{ number_format($rate->rate, 2) }}
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap text-xs">
                    @if($rate->country || $rate->state || $rate->zip)
                        <span class="inline-flex items-center gap-1 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/80 px-2 py-0.5 rounded text-slate-500">
                            <i class="fa-solid fa-earth-americas text-[10px]"></i>
                            {{ $rate->state ?? '*' }}, {{ $rate->country ?? '*' }} {{ $rate->zip ? "($rate->zip)" : '' }}
                        </span>
                    @else
                        <span class="text-slate-400 italic">Global / Default</span>
                    @endif
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
                        <a href="{{ route('admin.tax.edit', $rate->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Tax Rate">
                            <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                        </a>

                        <button onclick='openDeleteModal(@json($rate->id), @json($rate->name))' class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Tax Rate">
                            <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-percent text-4xl mb-3 opacity-20 block"></i>
                    No tax rates registered yet. Click "Add Tax Rate" to create one.
                </td>
            </tr>
        @endforelse
    </x-data-table>

    <!-- Global Tax Settings Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-2">
            <i class="fa-solid fa-sliders text-blue-500"></i>
            <h2 class="font-semibold text-sm">Global Settings</h2>
        </div>
        <form action="{{ route('admin.tax.settings.update') }}" method="POST" class="p-5 space-y-4">
            @csrf

            <div class="space-y-3">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Calculation Mode</span>

                <!-- Option 1: Single Standard -->
                <div class="border rounded-xl p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-all relative overflow-hidden config-card {{ $calculationMode === 'single_standard' ? 'border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-500/5 ring-1 ring-blue-500' : 'border-slate-200 dark:border-slate-800 bg-transparent' }}" onclick="toggleConfig(this)">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="tax_calculation_mode" value="single_standard" {{ $calculationMode === 'single_standard' ? 'checked' : '' }} class="mt-0.5 focus:ring-blue-500 h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 cursor-pointer">
                        <div class="flex-1">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Global Standard Rate</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
                                Applies the first active tax rate globally to all orders, regardless of customer shipping destination.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Option 2: Regional Dynamic -->
                <div class="border rounded-xl p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-all relative overflow-hidden config-card {{ $calculationMode === 'regional_dynamic' ? 'border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-500/5 ring-1 ring-blue-500' : 'border-slate-200 dark:border-slate-800 bg-transparent' }}" onclick="toggleConfig(this)">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="tax_calculation_mode" value="regional_dynamic" {{ $calculationMode === 'regional_dynamic' ? 'checked' : '' }} class="mt-0.5 focus:ring-blue-500 h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 cursor-pointer">
                        <div class="flex-1">
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Dynamic Regional Taxes</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
                                Resolves and applies specific tax rates depending on shipping address State, Country, or Zipcode. Falls back to default.
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

    function openDeleteModal(rateId, name) {
        showConfirm(
            `Are you sure you want to delete tax rate "${name}"? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/tax/${rateId}`;
                form.submit();
            },
            'Delete Tax Rate?'
        );
    }
</script>
@endsection
