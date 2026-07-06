@extends('layouts.admin')

@section('title', 'Customer Behavior — SGCart Admin')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            Customer Behavior Report
        </h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Reporting'],
            ['label' => 'Customer Behavior']
        ]" />
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Customer Behaviour values from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
        </p>
    </div>

    <form method="GET" action="{{ route('admin.reports.behavior') }}"
          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
        <div class="flex items-center gap-2">
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">From</label>
                <input type="date" name="date_from" value="{{ request('date_from', $date_from->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
            </div>
            <span class="text-slate-400 mt-4">→</span>
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">To</label>
                <input type="date" name="date_to" value="{{ request('date_to', $date_to->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
            </div>
        </div>
        <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline mt-4 sm:mt-auto">            <i class="fa-solid fa-filter mr-1.5"></i>
            Apply</button>
        <a href="{{ route('admin.reports.behavior') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 mt-4 sm:mt-auto text-center">Reset</a>
        <a href="{{ route('admin.reports.behavior.export', request()->only(['date_from', 'date_to'])) }}"
           class="px-4! py-2! rounded-xl text-sm font-semibold border! border-slate-200! !dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors mt-4 sm:mt-auto self-end sm:self-auto text-center">
            Export CSV
        </a>
    </form>
</div>

{{-- ── Quick Date Shortcuts ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-2 mb-6" id="dateShortcuts">
    @php
        $shortcuts = [
            'Today'        => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'Last 7 days'  => [now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
            'This month'   => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'Last month'   => [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'Last 90 days' => [now()->subDays(89)->format('Y-m-d'), now()->format('Y-m-d')],
            'This year'    => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
        ];
    @endphp
    @foreach($shortcuts as $label => [$f, $t])
        <a href="{{ route('admin.reports.behavior', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Main detailed table --}}
@php
    $headers = [
        ['label' => 'Customer Name', 'key' => 'name', 'sortable' => false],
        ['label' => 'Email', 'key' => 'email', 'sortable' => false],
        ['label' => 'Total Orders', 'key' => 'total_orders', 'sortable' => true, 'align' => 'right'],
        ['label' => 'Reviews Submitted', 'key' => 'reviews_count', 'sortable' => true, 'align' => 'right'],
        ['label' => 'Total Spent (Range)', 'key' => 'total_sales', 'sortable' => true, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="User Activity & Reviews"
    :totalCount="$behavior->total()"
    action="{{ route('admin.reports.behavior') }}"
    tableId="detailedBehaviorTableWrapper"
    searchInputId="detailedBehaviorSearchInput"
    totalCountId="detailedBehaviorTotalCount"
    :items="$behavior"
    :headers="$headers"
>
    <x-slot name="filters">
        <input type="hidden" name="date_from" value="{{ request('date_from', $date_from->format('Y-m-d')) }}">
        <input type="hidden" name="date_to" value="{{ request('date_to', $date_to->format('Y-m-d')) }}">
    </x-slot>

    @forelse($behavior as $row)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
            <td class="px-4 py-3.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $row->name }}</td>
            <td class="px-4 py-3.5 text-sm text-slate-500 dark:text-slate-400">{{ $row->email }}</td>
            <td class="px-4 py-3.5 text-sm text-right font-medium">{{ number_format($row->total_orders) }}</td>
            <td class="px-4 py-3.5 text-sm text-right">{{ number_format($row->reviews_count) }}</td>
            <td class="px-4 py-3.5 text-sm text-right font-bold text-slate-900 dark:text-slate-100">₹{{ number_format($row->total_sales, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-16 text-center text-slate-400">
                <i class="fa-solid fa-users-viewfinder text-4xl mb-3 block"></i>
                <p class="text-sm font-medium">No behavior profiles found for this date range.</p>
            </td>
        </tr>
    @endforelse
</x-data-table>

@endsection
