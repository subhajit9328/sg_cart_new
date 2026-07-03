@extends('layouts.admin')

@section('title', 'Product Approvals — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Product Approvals</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Marketplace'],
            ['label' => 'Approvals']
        ]" />
    </div>
</div>

@php
    $headers = [
        ['label' => 'Product Name', 'key' => 'name', 'sortable' => false],
        ['label' => 'Seller', 'key' => 'seller', 'sortable' => false],
        ['label' => 'SKU', 'key' => 'sku', 'sortable' => false],
        ['label' => 'Category', 'key' => 'category', 'sortable' => false],
        ['label' => 'Price', 'key' => 'price', 'sortable' => false],
        ['label' => 'Stock', 'key' => 'stock', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Pending Product Reviews"
    :totalCount="$products->total()"
    searchPlaceholder="Search products…"
    action="{{ route('admin.products.approvals') }}"
    tableId="approvalsTableWrapper"
    searchInputId="approvalSearchInput"
    totalCountId="approvalsTotalCount"
    clearBtnId="approvalsClearBtn"
    clearBtnWrapperId="approvalsClearBtnWrapper"
    :items="$products"
    :headers="$headers"
>
    @forelse($products as $p)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-medium whitespace-nowrap">
                <div class="flex items-center gap-3">
                    @if($p->image)
                        <img src="{{ Storage::url($p->image) }}" class="w-8 h-8 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                    @else
                        <div class="w-8 h-8 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-400">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ $p->name }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">SKU: {{ $p->sku }}</p>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-semibold text-blue-600">
                {{ $p->seller ? $p->seller->shop_name : 'Unknown Seller' }}
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-mono">{{ $p->sku }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $p->category->name ?? 'N/A' }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-semibold">${{ number_format($p->price, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->stock }} units</td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    <a href="{{ route('admin.products.show', $p->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors text-slate-500" title="View Product details">
                        <i class="fa-solid fa-eye text-xs"></i>
                    </a>
                    <form id="approve-form-{{ $p->id }}" action="{{ route('admin.products.approvals.approve', $p) }}" method="POST" class="inline">
                        @csrf
                        <button type="button" onclick="confirmApprove({{ $p->id }})" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center justify-center transition-colors cursor-pointer text-emerald-600" title="Approve Product">
                            <i class="fa-solid fa-check text-xs"></i>
                        </button>
                    </form>
                    <form id="reject-form-{{ $p->id }}" action="{{ route('admin.products.approvals.reject', $p) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="rejection_reason" id="rejection-reason-{{ $p->id }}">
                        <button type="button" onclick="confirmReject({{ $p->id }})" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500" title="Reject/Archive Product">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No products pending review.</td>
        </tr>
    @endforelse
</x-data-table>

@push('scripts')
<script>
    function confirmApprove(productId) {
        showConfirm(
            'Are you sure you want to approve this product and publish it live on the store?',
            () => document.getElementById(`approve-form-${productId}`).submit(),
            'Approve & Publish?'
        );
    }
    
    function confirmReject(productId) {
        showRejectModal((reason) => {
            document.getElementById(`rejection-reason-${productId}`).value = reason;
            document.getElementById(`reject-form-${productId}`).submit();
        });
    }

    function showRejectModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Listing</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for rejecting this product listing. The seller will see this reason in their dashboard.
                </p>
                <div class="mb-5">
                    <textarea id="rejectionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500" placeholder="e.g. Incomplete specifications, blurry photos, or incorrect category selection..."></textarea>
                    <p id="rejectionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Rejection reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Reject</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Trigger scale-in transition
        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#rejectionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#rejectionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#rejectionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                // Show loader on click reject without closing the modal
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#rejectionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Rejecting...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(reasonVal);
                }
                return;
            }

            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            modal.classList.remove('animate-fadeIn');
            modal.classList.add('animate-fadeOut');
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }
</script>
@endpush
@endsection
