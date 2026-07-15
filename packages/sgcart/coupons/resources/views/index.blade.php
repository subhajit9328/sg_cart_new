@extends('layouts.admin')

@section('title', 'Coupon Management — SGCart Admin')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold">Coupon Management</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'E-Commerce'],
                ['label' => 'Coupons']
            ]" />
        </div>
        <a href="{{ route('admin.coupons.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
            <i class="fa-solid fa-plus"></i> Add Coupon
        </a>
    </div>

    @php
        $headers = [
            ['label' => 'Code', 'key' => 'code', 'sortable' => true],
            ['label' => 'Type', 'key' => 'type', 'sortable' => true],
            ['label' => 'Value', 'key' => 'value', 'sortable' => true],
        ];
        if (config('coupons.features.min_cart_total', true)) {
            $headers[] = ['label' => 'Min. Cart Subtotal', 'key' => 'min_cart_total', 'sortable' => true];
        }
        if (config('coupons.features.expires_at', true)) {
            $headers[] = ['label' => 'Expires At', 'key' => 'expires_at', 'sortable' => true];
        }
        $headers[] = ['label' => 'Status', 'key' => 'is_active', 'sortable' => true];
        $headers[] = ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'];
    @endphp

    <x-data-table
        title="Shipping Charge Rates"
        :totalCount="$coupons->total()"
        searchPlaceholder="Search coupons by code..."
        action="{{ route('admin.coupons.index') }}"
        tableId="couponsTableWrapper"
        searchInputId="couponSearchInput"
        totalCountId="couponsTotalCount"
        :items="$coupons"
        :headers="$headers"
    >
        @forelse($coupons as $coupon)
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                <td class="px-5 py-4 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                <span
                    class="inline-block px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono tracking-wider border border-slate-200 dark:border-slate-700">
                    {{ $coupon->code }}
                </span>
                </td>
                <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap capitalize">
                    @if($coupon->type->value === 'percent')
                        <span class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-450">
                        <i class="fa-solid fa-percent text-xs"></i> Percent
                    </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                        <i class="fa-solid fa-indian-rupee-sign text-xs"></i> Flat
                    </span>
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium whitespace-nowrap">
                    {{ floor($coupon->value) == $coupon->value ? number_format($coupon->value) : number_format($coupon->value, 2) }}
                </td>
                @if(config('coupons.features.min_cart_total', true))
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        ₹{{ number_format($coupon->min_cart_total, 2) }}
                    </td>
                @endif
                @if(config('coupons.features.expires_at', true))
                    <td class="px-5 py-4 text-slate-400 dark:text-slate-400 whitespace-nowrap">
                        @if($coupon->expires_at)
                            <span
                                class="{{ \Carbon\Carbon::parse($coupon->expires_at)->isPast() ? 'text-rose-500 font-medium' : '' }}">
                            {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') }}
                        </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 italic">Never</span>
                        @endif
                    </td>
                @endif
                <td class="px-5 py-4 whitespace-nowrap">
                    @if($coupon->is_active && (!$coupon->expires_at || !\Carbon\Carbon::parse($coupon->expires_at)->isPast()))
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        Active
                    </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/20">
                        {{ (!$coupon->is_active) ? 'Disabled' : 'Expired' }}
                    </span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    <div class="inline-flex gap-1.5">
                        <a href="{{ route('admin.coupons.edit', $coupon->id) }}"
                           class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors"
                           title="Edit Coupon">
                            <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                        </a>

                        <button onclick="openDeleteModal('{{ $coupon->id }}', '{{ $coupon->code }}')"
                                class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors"
                                title="Delete Coupon">
                            <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                @php
                    $cols = 5;
                    if(config('coupons.features.min_cart_total', true)) $cols++;
                    if(config('coupons.features.expires_at', true)) $cols++;
                @endphp
                <td colspan="{{ $cols }}" class="px-5 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-ticket text-4xl mb-3 opacity-20 block"></i>
                    No coupons found. Click "Add Coupon" to create one.
                </td>
            </tr>
        @endforelse
    </x-data-table>

    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openDeleteModal(couponId, code) {
            showConfirm(
                `Are you sure you want to delete coupon ${code}? This action cannot be undone.`,
                () => {
                    const form = document.getElementById('deleteForm');
                    form.action = `/admin/coupons/${couponId}`;
                    form.submit();
                },
                'Delete Coupon?'
            );
        }
    </script>
@endsection
