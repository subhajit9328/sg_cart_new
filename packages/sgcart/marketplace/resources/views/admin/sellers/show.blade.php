@extends('layouts.admin')

@section('title', 'Seller Profile — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">{{ $seller->shop_name }}</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Marketplace', 'url' => route('admin.sellers.index')],
            ['label' => 'Sellers', 'url' => route('admin.sellers.index')],
            ['label' => 'Profile']
        ]" />
    </div>
</div>

<div class="max-w-6xl mx-auto space-y-6">
    <!-- Seller Dashboard Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gross Sales Volume</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">₹{{ number_format($totalSales, 2) }}</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Fee Collected</p>
            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">₹{{ number_format($totalCommissionCollected, 2) }}</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Seller Status</p>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $seller->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : ($seller->status->value === 'pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400') }}">
                    {{ $seller->status->value }}
                </span>
                @if($seller->status->value === 'pending')
                    <div class="flex gap-2">
                        <form id="approve-detail-form" action="{{ route('admin.sellers.approve', $seller) }}" method="POST">
                            @csrf
                            <button type="button" onclick="confirmApproveDetail('pending')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-2.5 rounded text-[10px] uppercase transition-colors cursor-pointer">Approve</button>
                        </form>
                        <form id="reject-detail-form" action="{{ route('admin.sellers.reject', $seller) }}" method="POST">
                            @csrf
                            <input type="hidden" name="rejection_reason" id="rejection-reason-detail">
                            <button type="button" onclick="confirmRejectDetail()" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-1 px-2.5 rounded text-[10px] uppercase transition-colors cursor-pointer">Reject</button>
                        </form>
                    </div>
                @elseif($seller->status->value === 'approved')
                    <form id="suspend-detail-form" action="{{ route('admin.sellers.suspend', $seller) }}" method="POST">
                        @csrf
                        <input type="hidden" name="suspension_reason" id="suspension-reason-detail">
                        <button type="button" onclick="confirmSuspendDetail()" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-1 px-2.5 rounded text-[10px] uppercase transition-colors cursor-pointer">Suspend</button>
                    </form>
                @elseif($seller->status->value === 'suspended' || $seller->status->value === 'rejected')
                    <form id="approve-detail-form" action="{{ route('admin.sellers.approve', $seller) }}" method="POST">
                        @csrf
                        <button type="button" onclick="confirmApproveDetail('{{ $seller->status->value }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-2.5 rounded text-[10px] uppercase transition-colors cursor-pointer">Reactivate</button>
                    </form>
                @endif
            </div>
            @if(($seller->status->value === 'suspended' || $seller->status->value === 'rejected') && $seller->suspension_reason)
                <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/15 border border-rose-100 dark:border-rose-900/30 rounded-lg text-xs text-left">
                    <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">
                        {{ $seller->status->value === 'rejected' ? 'Rejection Reason:' : 'Suspension Reason:' }}
                    </span>
                    <p class="text-slate-655 dark:text-slate-400 leading-relaxed m-0">{{ $seller->suspension_reason }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Catalog Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">Seller Catalog (Products)</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 uppercase font-semibold">
                                <th class="py-3 pr-4">Product</th>
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4">Price</th>
                                <th class="py-3 px-4">Stock</th>
                                <th class="py-3 pl-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @forelse($products as $p)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-slate-800 dark:text-white">
                                        {{ $p->name }}
                                    </td>
                                    <td class="py-3 px-4 font-mono">{{ $p->sku }}</td>
                                    <td class="py-3 px-4">₹{{ number_format($p->price, 2) }}</td>
                                    <td class="py-3 px-4">{{ $p->stock }} units</td>
                                        @php
                                            $badgeClass = 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                                            if ($p->status->value === 'active') {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400';
                                            } elseif ($p->status->value === 'pending_approval') {
                                                $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400';
                                            } elseif ($p->status->value === 'rejected') {
                                                $badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-450';
                                            }
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $badgeClass }}">
                                            {{ str_replace('_', ' ', $p->status->value) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No products uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Actions Column -->
        <div class="space-y-6">
            <!-- Earning Config Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">Commission Override</h4>
                
                <form action="{{ route('admin.sellers.update-commission', $seller) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Seller Rate (%)</label>
                        <div class="relative">
                            <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                                   value="{{ old('commission_rate', $seller->commission_rate) }}"
                                   placeholder="Default (10.00)"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-sm focus:outline-none">
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-slate-400">%</span>
                        </div>
                        @error('commission_rate') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition-colors">
                        Save Rate Override
                    </button>
                </form>
            </div>

            <!-- Profile Details Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-3">
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Contact Information</h4>
                <div class="text-xs space-y-2">
                    <p><strong class="text-slate-400">Owner Name:</strong> <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $seller->name ?? 'N/A' }}</span></p>
                    <p><strong class="text-slate-400">Email:</strong> <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $seller->email ?? 'N/A' }}</span></p>
                    <p><strong class="text-slate-400">Phone:</strong> <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $seller->phone_no ?? 'N/A' }}</span></p>
                    <p><strong class="text-slate-400">Registered:</strong> <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $seller->created_at->format('M d, Y') }}</span></p>
                </div>
            </div>

            <!-- Description Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-2">
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Shop Description</h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $seller->shop_description ?? 'No description provided.' }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmApproveDetail(status = 'pending') {
        const text = status === 'pending'
            ? 'Are you sure you want to approve this seller account? They will be allowed to log in and list products.'
            : 'Are you sure you want to reactivate this seller account? They will be allowed to log in and active status will be restored.';
        const title = status === 'pending' ? 'Approve Seller' : 'Reactivate Seller';
        showConfirm(
            text,
            () => document.getElementById('approve-detail-form').submit(),
            title
        );
    }
    
    function confirmSuspendDetail() {
        showSuspendModal((reason) => {
            document.getElementById('suspension-reason-detail').value = reason;
            document.getElementById('suspend-detail-form').submit();
        });
    }

    function confirmRejectDetail() {
        showRejectModal((reason) => {
            document.getElementById('rejection-reason-detail').value = reason;
            document.getElementById('reject-detail-form').submit();
        });
    }

    function showRejectModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Seller Application</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
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
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
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
