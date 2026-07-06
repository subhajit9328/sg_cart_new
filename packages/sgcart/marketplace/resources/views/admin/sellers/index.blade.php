@extends('layouts.admin')

@section('title', 'Manage Sellers — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Manage Sellers</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Marketplace'],
            ['label' => 'Sellers']
        ]" />
    </div>
</div>

@php
    $headers = [
        ['label' => 'Shop Details', 'key' => 'shop_name', 'sortable' => false],
        ['label' => 'Owner', 'key' => 'owner', 'sortable' => false],
        ['label' => 'Email', 'key' => 'email', 'sortable' => false],
        ['label' => 'Products', 'key' => 'products_count', 'sortable' => false],
        ['label' => 'Comm. Rate', 'key' => 'commission_rate', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="All Sellers"
    :totalCount="$sellers->total()"
    searchPlaceholder="Search sellers…"
    action="{{ route('admin.sellers.index') }}"
    tableId="sellersTableWrapper"
    searchInputId="sellerSearchInput"
    totalCountId="sellersTotalCount"
    clearBtnId="sellersClearBtn"
    clearBtnWrapperId="sellersClearBtnWrapper"
    :items="$sellers"
    :headers="$headers"
    :filterKeys="['status']"
>
    <x-slot name="filters">
        <div class="min-w-[130px]">
            <select name="status" onchange="this.form.submit()"
                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all cursor-pointer">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
    </x-slot>

    @forelse($sellers as $seller)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-medium whitespace-nowrap">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ $seller->shop_name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">slug: {{ $seller->shop_slug }}</p>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $seller->name ?? 'N/A' }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $seller->email ?? 'N/A' }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-semibold">
                {{ $seller->products()->count() }} listings
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-mono">
                {{ $seller->commission_rate ? $seller->commission_rate . '%' : 'Default (10%)' }}
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $seller->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : ($seller->status->value === 'pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400') }}">
                    {{ $seller->status->value }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    <a href="{{ route('admin.sellers.show', $seller) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Manage Seller">
                        <i class="fa-solid fa-gear text-slate-500 dark:text-slate-400 text-xs"></i>
                    </a>
                    @if($seller->status->value === 'pending' || $seller->status->value === 'suspended' || $seller->status->value === 'rejected')
                        <form id="approve-form-{{ $seller->id }}" action="{{ route('admin.sellers.approve', $seller) }}" method="POST" class="inline">
                            @csrf
                            <button type="button" onclick="confirmApprove({{ $seller->id }}, '{{ $seller->status->value }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center justify-center transition-colors cursor-pointer text-emerald-600" title="{{ $seller->status->value === 'pending' ? 'Approve' : 'Re-approve' }} Seller">
                                <i class="fa-solid fa-check text-xs"></i>
                            </button>
                        </form>
                        @if($seller->status->value === 'pending')
                            <form id="reject-form-{{ $seller->id }}" action="{{ route('admin.sellers.reject', $seller) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="rejection_reason" id="rejection-reason-{{ $seller->id }}">
                                <button type="button" onclick="confirmReject({{ $seller->id }})" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500" title="Reject Seller">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </form>
                        @endif
                    @elseif($seller->status->value === 'approved')
                        <form id="suspend-form-{{ $seller->id }}" action="{{ route('admin.sellers.suspend', $seller) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="suspension_reason" id="suspension-reason-{{ $seller->id }}">
                            <button type="button" onclick="confirmSuspend({{ $seller->id }})" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500" title="Suspend Seller">
                                <i class="fa-solid fa-ban text-xs"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No sellers found.</td>
        </tr>
    @endforelse
</x-data-table>

@push('scripts')
<script>
    function confirmApprove(sellerId, status = 'pending') {
        const text = status === 'pending'
            ? 'Are you sure you want to approve this seller account? They will be allowed to log in and list products.'
            : 'Are you sure you want to reactivate this seller account? They will be allowed to log in and active status will be restored.';
        const title = status === 'pending' ? 'Approve Seller' : 'Reactivate Seller';
        showConfirm(
            text,
            () => document.getElementById(`approve-form-${sellerId}`).submit(),
            title
        );
    }
    
    function confirmSuspend(sellerId) {
        showSuspendModal((reason) => {
            document.getElementById(`suspension-reason-${sellerId}`).value = reason;
            document.getElementById(`suspend-form-${sellerId}`).submit();
        });
    }

    function confirmReject(sellerId) {
        showRejectModal((reason) => {
            document.getElementById(`rejection-reason-${sellerId}`).value = reason;
            document.getElementById(`reject-form-${sellerId}`).submit();
        });
    }

    function showRejectModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Seller Registration</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-550 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for rejecting this seller application. They will see this reason on their dashboard.
                </p>
                <div class="mb-5">
                    <textarea id="rejectionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500" placeholder="e.g. Incomplete warehouse address, invalid company documents, or shop name policy violation..."></textarea>
                    <p id="rejectionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Rejection reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Reject</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

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
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }

    function showSuspendModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Suspend Seller</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-550 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for suspending this seller account. Their products will be hidden from the storefront.
                </p>
                <div class="mb-5">
                    <textarea id="suspensionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500" placeholder="e.g. Violation of marketplace policy, repeated negative reviews, or inactive store..."></textarea>
                    <p id="suspensionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Suspension reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Suspend</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#suspensionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#suspensionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#suspensionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#suspensionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Suspending...';
                
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
