@extends('marketplace::layouts.seller')

@section('title', 'Payout History — Seller Portal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="font-display text-2xl font-bold">Payout History</h1>
        <p class="text-sm text-slate-400 mt-0.5">Track all payments processed by administrators to your bank account.</p>
    </div>

    <!-- Table -->
    @php
        $headers = [
            ['label' => 'Date', 'key' => 'payout_date', 'sortable' => false],
            ['label' => 'Payout Reference', 'key' => 'ulid', 'sortable' => false],
            ['label' => 'Payment Method', 'key' => 'payment_method', 'sortable' => false],
            ['label' => 'UTR / Txn Ref', 'key' => 'transaction_reference', 'sortable' => false],
            ['label' => 'Settled Amount', 'key' => 'amount', 'sortable' => false, 'align' => 'right'],
            ['label' => 'Settled Orders', 'key' => 'orders', 'sortable' => false],
        ];
    @endphp

    <x-data-table
        title="All Disbursed Payouts"
        :totalCount="$payouts->total()"
        searchPlaceholder="Search payouts…"
        action="{{ route('seller.payouts') }}"
        tableId="payoutsTableWrapper"
        searchInputId="payoutSearchInput"
        totalCountId="payoutsTotalCount"
        clearBtnId="payoutsClearBtn"
        clearBtnWrapperId="payoutsClearBtnWrapper"
        :items="$payouts"
        :headers="$headers"
        :filterKeys="['date_range']"
    >
        <x-slot name="filters">
            <x-date-picker id="payoutDateRangePicker" name="date_range" enableTime="false" dateFormat="d-m-Y" placeholder="Filter by date range…" width="w-60" />
        </x-slot>

        @forelse($payouts as $item)
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    {{ $item->payout_date->format('d M Y, H:i A') }}
                </td>
                <td class="px-5 py-3.5 font-mono font-semibold text-xs text-slate-850 dark:text-slate-200 whitespace-nowrap">
                    #{{ $item->ulid }}
                </td>
                <td class="px-5 py-3.5 text-xs whitespace-nowrap">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $item->payment_method === 'bank_transfer' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400' : ($item->payment_method === 'upi' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-955/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                        {{ str_replace('_', ' ', $item->payment_method) }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs whitespace-nowrap">
                    @if($item->transaction_reference)
                        <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-300">
                            {{ $item->transaction_reference }}
                        </span>
                    @else
                        <span class="text-slate-400 italic">N/A</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-xs text-emerald-600 dark:text-emerald-450 font-bold text-right whitespace-nowrap">
                    ₹{{ number_format($item->amount, 2) }}
                </td>
                <td class="px-5 py-3.5 text-xs max-w-[220px]">
                    @php
                        $orderRefs = $item->commissions->map(fn($c) => $c->order->order_number ?? $c->order_id)->unique();
                    @endphp
                    <div class="flex flex-wrap gap-1">
                        @forelse($orderRefs as $ref)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-850 text-slate-655 dark:text-slate-400 border border-slate-200/40 dark:border-slate-700/40">
                                #{{ $ref }}
                            </span>
                        @empty
                            <span class="text-slate-450 italic">No linked orders</span>
                        @endforelse
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-6 text-center text-slate-450 dark:text-slate-600 italic">No payout records found.</td>
            </tr>
        @endforelse
    </x-data-table>
</div>
@endsection
