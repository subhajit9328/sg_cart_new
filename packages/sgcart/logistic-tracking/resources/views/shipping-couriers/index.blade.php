@extends('layouts.admin')

@section('title', 'Shipping Couriers — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Shipping Couriers</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Logistics / Shipping Couriers</p>
    </div>
    <a href="{{ route('admin.couriers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 transition-all">
        <i class="fa-solid fa-plus"></i> Add Courier
    </a>
</div>

<!-- Couriers List Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">All Shipping Couriers</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Tracking URL</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Support Email</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($couriers as $courier)
                <tr>
                    <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                        {{ $courier->name }}
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        @if($courier->url)
                            <a href="{{ $courier->url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                Visit Website <i class="fa-solid fa-up-right-from-square text-[9px]"></i>
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-600 italic">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        @if($courier->support_email)
                            <a href="mailto:{{ $courier->support_email }}" class="text-slate-600 dark:text-slate-300 hover:underline">
                                {{ $courier->support_email }}
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-600 italic">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <form action="{{ route('admin.couriers.destroy', $courier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this courier?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Courier">
                                <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-slate-400 dark:text-slate-500 py-8">
                        <i class="fa-solid fa-truck-fast text-3xl mb-2 opacity-25 block"></i>
                        No shipping couriers added yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
