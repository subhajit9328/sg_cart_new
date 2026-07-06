@extends('marketplace::layouts.seller')

@section('title', 'Size Labels — Seller Portal')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Size Labels</h1>
        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">Manage size options for your product variations.</p>
    </div>
    <a href="{{ route('seller.sizes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-plus text-xs"></i> Add Size
    </a>
</div>

@php
    $headers = [
        ['label' => 'Size Name', 'key' => 'name', 'sortable' => true],
        ['label' => 'Size Code', 'key' => 'code', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Available Sizes"
    :totalCount="$sizes->total()"
    searchPlaceholder="Search sizes…"
    action="{{ route('seller.sizes.index') }}"
    tableId="sizesTableWrapper"
    searchInputId="sizesSearchInput"
    totalCountId="sizesTotalCount"
    :items="$sizes"
    :headers="$headers"
>
    @forelse($sizes as $size)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm">
                {{ $size->name }}
                @if($size->seller_id === null)
                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Global</span>
                @else
                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">Custom</span>
                @endif
            </td>
            <td class="px-5 py-3.5 text-slate-550 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                {{ $size->code }}
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    @if($size->seller_id !== null)
                        <a href="{{ route('seller.sizes.edit', $size->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Size">
                            <i class="fa-solid fa-pen text-slate-550 dark:text-slate-400 text-xs"></i>
                        </a>
                        <button onclick="openDeleteModal('{{ $size->id }}', '{{ addslashes($size->name) }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500" title="Delete Size">
                            <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                        </button>
                    @else
                        <span class="w-8 h-8 rounded-lg border border-slate-200/50 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-650" title="Global Attribute (Locked)">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="px-5 py-12 text-center text-slate-450 dark:text-slate-500">
                <i class="fa-solid fa-ruler-horizontal text-4xl mb-3 opacity-20 block"></i>
                No size labels found.
            </td>
        </tr>
    @endforelse
</x-data-table>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    function openDeleteModal(id, name) {
        showConfirm(
            `Are you sure you want to delete size label "${name}"?`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/seller/sizes/${id}`;
                form.submit();
            },
            'Delete Size?'
        );
    }
</script>
@endpush
