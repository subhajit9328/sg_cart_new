@extends('layouts.admin')

@section('title', 'Coupon Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Coupon Management</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / E-Commerce / Coupons</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-plus"></i> Add Coupon
    </a>
</div>

<!-- Coupons Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Code</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Value</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Min. Cart Subtotal</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Expires At</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($coupons as $coupon)
                <tr>
                    <td class="px-5 py-4 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                        <span class="inline-block px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono tracking-wider border border-slate-200 dark:border-slate-700">
                            {{ $coupon->code }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap capitalize">
                        @if($coupon->type->value === 'percent')
                            <span class="inline-flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                                <i class="fa-solid fa-percent text-xs"></i> Percent
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                                <i class="fa-solid fa-dollar-sign text-xs"></i> Flat
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium whitespace-nowrap">
                        @if($coupon->type->value === 'percent')
                            {{ number_format($coupon->value, 0) }}%
                        @else
                            ${{ number_format($coupon->value, 2) }}
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        ${{ number_format($coupon->min_cart_total, 2) }}
                    </td>
                    <td class="px-5 py-4 text-slate-400 whitespace-nowrap">
                        @if($coupon->expires_at)
                            <span class="{{ \Carbon\Carbon::parse($coupon->expires_at)->isPast() ? 'text-rose-500 font-medium' : '' }}">
                                {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 italic">Never</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        @if($coupon->is_active && (!$coupon->expires_at || !\Carbon\Carbon::parse($coupon->expires_at)->isPast()))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                {{ (!$coupon->is_active) ? 'Disabled' : 'Expired' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Coupon">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            
                            <button onclick="openDeleteModal('{{ $coupon->id }}', '{{ $coupon->code }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Coupon">
                                <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-ticket text-4xl mb-3 opacity-20 block"></i>
                        No coupons found. Click "Add Coupon" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($coupons->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $coupons->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-6 w-full max-w-sm relative z-10 animate-fadeIn">
        <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-display font-bold text-lg mb-2">Delete Coupon?</h3>
        <p class="text-sm text-slate-400 mb-6">Are you sure you want to delete coupon <strong id="deleteCouponCode" class="text-slate-800 dark:text-slate-200"></strong>? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" class="flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium transition-colors shadow-lg shadow-rose-600/10">
                Delete
            </button>
        </form>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteCouponCode = document.getElementById('deleteCouponCode');
    const deleteForm = document.getElementById('deleteForm');

    function openDeleteModal(couponId, code) {
        deleteCouponCode.textContent = code;
        deleteForm.action = `/admin/coupons/${couponId}`;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }
</script>
@endsection
