@extends('layouts.admin')

@section('title', 'Traffic Analysis — SGCart Admin')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            Traffic Analysis
        </h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Reporting'],
            ['label' => 'Traffic Analysis']
        ]" />
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Visitor sources, page views, and referrers from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
        </p>
    </div>

    <form method="GET" action="{{ route('admin.reports.traffic') }}"
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
        <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline mt-4 sm:mt-auto">Apply</button>
        <a href="{{ route('admin.reports.traffic') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 mt-4 sm:mt-auto text-center">Reset</a>
        <a href="{{ route('admin.reports.traffic.export', request()->only(['date_from', 'date_to'])) }}"
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
        <a href="{{ route('admin.reports.traffic', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Top Row Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    {{-- Page views count --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Page Views</p>
        <p class="text-3xl font-extrabold text-blue-600 font-display leading-tight">{{ number_format($traffic['page_views']) }}</p>
        <p class="text-xs text-slate-400">Total requests logged in selected period.</p>
    </div>

    {{-- Unique visitors --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Unique Visitors (Sessions)</p>
        <p class="text-3xl font-extrabold text-emerald-600 font-display leading-tight">{{ number_format($traffic['unique_visitors']) }}</p>
        <p class="text-xs text-slate-400">Unique user session identifier records.</p>
    </div>
</div>

{{-- Middle Row: Top Pages & Top Referrers list --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Top Pages --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-blue-500"></i>Top Viewed Pages
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Page Path</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Views</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($traffic['top_pages'] as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-medium text-slate-700 dark:text-slate-300 font-mono">{{ $row->path }}</td>
                        <td class="px-4 py-3.5 text-sm text-right font-bold text-slate-900 dark:text-slate-100">{{ number_format($row->views) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center py-10 text-slate-400">No page views recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Referrers --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square text-violet-500"></i>Top Traffic Sources
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Referrer Domain</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Views</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($traffic['top_referrers'] as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-medium text-slate-700 dark:text-slate-300 font-mono">{{ $row->referrer }}</td>
                        <td class="px-4 py-3.5 text-sm text-right font-bold text-slate-900 dark:text-slate-100">{{ number_format($row->count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center py-10 text-slate-400 font-medium">Direct visitors / Search engines.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Traffic Logs list --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden mb-6 shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Live Traffic Logs</h2>
    </div>

    @if($page_views->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <i class="fa-solid fa-circle-exclamation text-4xl mb-3 block"></i>
            <p class="text-sm font-medium">No page views logged for this date range.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Page</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Customer Name</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">IP Address</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Date/Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($page_views as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-medium text-slate-900 dark:text-slate-100 font-mono">{{ $row->path }}</td>
                        <td class="px-4 py-3.5 text-sm">{{ $row->customer_name ?? 'Guest Visitor' }}</td>
                        <td class="px-4 py-3.5 text-sm font-mono text-slate-500">{{ $row->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-sm text-slate-450">{{ $row->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($page_views->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-400">
                Showing {{ $page_views->firstItem() }}–{{ $page_views->lastItem() }} of {{ $page_views->total() }} views
            </p>
            <div class="flex items-center gap-1">
                @if($page_views->onFirstPage())
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $page_views->previousPageUrl() }}" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                @if($page_views->hasMorePages())
                    <a href="{{ $page_views->nextPageUrl() }}" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection
