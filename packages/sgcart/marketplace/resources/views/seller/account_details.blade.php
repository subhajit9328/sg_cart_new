@extends('marketplace::layouts.seller')

@section('title', 'Account Details — Seller Portal')

@section('content')
<style>
    /* Ensure no black browser outlines on inputs */
    input:focus, select:focus, textarea:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important; /* Indigo ring for Seller Portal */
        border-color: #6366f1 !important;
    }
    button:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    /* Force Select2 container to span 100% width */
    .select2-container {
        width: 100% !important;
    }

    /* Match Select2 styling with other inputs (rounded-xl, slate-50 / slate-800 background, text-xs) */
    .select2-container--default .select2-selection--single {
        background-color: #f8fafc !important; /* bg-slate-50 */
        border: 1px solid #e2e8f0 !important; /* border-slate-200 */
        border-radius: 0.75rem !important; /* rounded-xl (12px) */
        height: 34px !important; /* Matches regular py-2 inputs exactly */
        display: flex !important;
        align-items: center !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    
    .dark .select2-container--default .select2-selection--single {
        background-color: #1e293b !important; /* bg-slate-800 */
        border: 1px solid #334155 !important; /* border-slate-700 */
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important; /* text-slate-800 */
        font-size: 0.75rem !important; /* text-xs */
        padding-left: 0.875rem !important; /* px-3.5 (14px) */
        padding-right: 2.5rem !important;
        line-height: 32px !important;
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important; /* text-slate-100 */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
        right: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Focus styling for select2 */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366f1 !important; /* focus:border-indigo-500 */
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
        outline: none !important;
    }

    /* Disabled state styling for select2 */
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        opacity: 0.7 !important;
        cursor: not-allowed !important;
    }
    .dark .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #0f172a !important;
        border-color: #1e293b !important;
    }

    /* Style native select elements before Select2 initializes to prevent layout shifting */
    select.select2-select:not(.select2-hidden-accessible) {
        background-color: #f8fafc !important; /* bg-slate-50 */
        border: 1px solid #e2e8f0 !important; /* border-slate-200 */
        border-radius: 0.75rem !important; /* rounded-xl (12px) */
        height: 34px !important;
        padding: 0 2.5rem 0 0.875rem !important;
        font-size: 0.75rem !important; /* text-xs */
        color: #1e293b !important;
        width: 100% !important;
        outline: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        background-size: 10px !important;
    }
    .dark select.select2-select:not(.select2-hidden-accessible) {
        background-color: #1e293b !important; /* bg-slate-800 */
        border: 1px solid #334155 !important;
        color: #f1f5f9 !important;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>") !important;
    }
</style>
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="font-display text-2xl font-bold">Payment Account Details</h1>
        <p class="text-sm text-slate-400 mt-0.5">Submit and manage your bank details or payment credentials to receive payouts from the platform.</p>
    </div>

    <!-- Alert Messages -->
    @if(session('info'))
        <div class="p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/30 rounded-xl text-blue-800 dark:text-blue-405 text-sm font-medium animate-fadeIn">
            <i class="fa-solid fa-circle-info mr-2"></i> {{ session('info') }}
        </div>
    @endif

    <!-- Verification Status Banner -->
    @php
        $status = $seller->account_verification_status ?? 'unsubmitted';
    @endphp

    @if($status === 'unsubmitted')
        <div class="bg-amber-50 dark:bg-amber-955/10 border border-amber-200 dark:border-amber-900/30 rounded-2xl p-5 flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-800 dark:text-white">Account Details Required</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    You have not submitted your payout bank account information. You must submit valid banking details to be eligible to receive payouts from administrators.
                </p>
            </div>
        </div>
    @elseif($status === 'pending')
        <div class="bg-blue-50 dark:bg-blue-950/15 border border-blue-200 dark:border-blue-900/30 rounded-2xl p-5 flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center flex-shrink-0 animate-pulse">
                <i class="fa-solid fa-circle-info text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-800 dark:text-white">Account details submitted successfully and are pending admin verification.</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed font-medium">
                    Your details are currently locked and under review. If you want to make changes or updates to this information, please contact an administrator to release the edit lock.
                </p>
            </div>
        </div>
    @elseif($status === 'verified')
        <div class="bg-emerald-50 dark:bg-emerald-955/10 border border-emerald-200 dark:border-emerald-900/30 rounded-2xl p-5 flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-800 dark:text-white">Account Verified</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Your payment details are verified by the administrator. If you want to change these details in the future, please contact an administrator to request editing access.
                </p>
            </div>
        </div>
    @elseif($status === 'rejected')
        <div class="bg-rose-50 dark:bg-rose-955/10 border border-rose-200 dark:border-rose-900/30 rounded-2xl p-5 flex gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-slate-800 dark:text-white">Account Details Rejected</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Your submitted account details were rejected by the administrator. Please read the reason below, correct your inputs, and submit again.
                </p>
                @if($seller->account_rejection_reason)
                    <div class="mt-3 p-3 bg-rose-100/50 dark:bg-rose-950/20 border border-rose-200/50 rounded-xl text-xs text-rose-800 dark:text-rose-450">
                        <strong class="font-semibold">Reason for rejection:</strong>
                        <p class="mt-1 leading-relaxed">{{ $seller->account_rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Configure Payment Account</h2>
        </div>

        <form id="payoutAccountForm" action="{{ route('seller.account-details.update') }}" method="POST" class="p-6 space-y-4" novalidate>
            @csrf

            @if($status === 'verified')
                <div class="p-4 bg-emerald-50 dark:bg-emerald-955/15 border border-emerald-250 dark:border-emerald-900/35 rounded-xl text-xs text-emerald-800 dark:text-emerald-450 leading-relaxed flex gap-2">
                    <i class="fa-solid fa-lock text-sm mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Payment Details Locked:</strong> Your payment credentials are verified and locked to prevent unauthorized changes. If you need to update this information, please contact an administrator to release the edit lock.
                    </div>
                </div>
            @elseif($status === 'pending')
                <div class="p-4 bg-amber-50 dark:bg-amber-955/15 border border-amber-250 dark:border-amber-900/35 rounded-xl text-xs text-amber-800 dark:text-amber-450 leading-relaxed flex gap-2">
                    <i class="fa-solid fa-lock text-sm mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Pending Review (Locked):</strong> Your payment credentials have been submitted for verification. While pending admin review, they cannot be edited.
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Account Holder Name -->
                <div class="flex flex-col gap-1">
                    <label for="account_holder_name" class="text-[10px] font-bold text-slate-400 uppercase">Account Holder Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="account_holder_name" name="account_holder_name" 
                           value="{{ old('account_holder_name', $seller->account_details['account_holder_name'] ?? '') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500 transition-colors"
                           required placeholder="e.g. John Doe" {{ ($status === 'verified' || $status === 'pending') ? 'disabled' : '' }}>
                    @error('account_holder_name') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-account_holder_name" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>

                <!-- Bank Name -->
                <div class="flex flex-col gap-1">
                    <label for="bank_name" class="text-[10px] font-bold text-slate-400 uppercase">Bank Name <span class="text-rose-500">*</span></label>
                    <x-select2 
                        name="bank_name" 
                        id="bank_name" 
                        placeholder="Select your Bank"
                        :allowClear="false"
                        :searchable="true"
                        required
                        :disabled="($status === 'verified' || $status === 'pending') ? 'disabled' : null"
                    >
                        @php
                            $currentBank = old('bank_name', $seller->account_details['bank_name'] ?? '');
                            $banksList = config('marketplace.banks', []);
                            $bankExists = in_array($currentBank, $banksList);
                        @endphp
                        @if($currentBank && !$bankExists)
                            <option value="{{ $currentBank }}" selected>{{ $currentBank }}</option>
                        @endif
                        @foreach($banksList as $bank)
                            <option value="{{ $bank }}" {{ $currentBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                    </x-select2>
                    @error('bank_name') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-bank_name" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Account Number -->
                <div class="flex flex-col gap-1">
                    <label for="account_number" class="text-[10px] font-bold text-slate-400 uppercase">Account Number <span class="text-rose-500">*</span></label>
                    <input type="text" id="account_number" name="account_number" 
                           value="{{ old('account_number', $seller->account_details['account_number'] ?? '') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500 transition-colors"
                           required placeholder="e.g. 50100234567890" {{ ($status === 'verified' || $status === 'pending') ? 'disabled' : '' }}>
                    @error('account_number') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-account_number" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>

                <!-- IFSC Code -->
                <div class="flex flex-col gap-1">
                    <label for="ifsc_code" class="text-[10px] font-bold text-slate-400 uppercase">IFSC / Routing Code <span class="text-rose-500">*</span></label>
                    <input type="text" id="ifsc_code" name="ifsc_code" 
                           value="{{ old('ifsc_code', $seller->account_details['ifsc_code'] ?? '') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500 transition-colors"
                           required placeholder="e.g. HDFC0001234" {{ ($status === 'verified' || $status === 'pending') ? 'disabled' : '' }}>
                    @error('ifsc_code') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-ifsc_code" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Branch Name -->
                <div class="flex flex-col gap-1">
                    <label for="branch_name" class="text-[10px] font-bold text-slate-400 uppercase">Branch Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="branch_name" name="branch_name" 
                           value="{{ old('branch_name', $seller->account_details['branch_name'] ?? '') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500 transition-colors"
                           required placeholder="e.g. Connaught Place Branch" {{ ($status === 'verified' || $status === 'pending') ? 'disabled' : '' }}>
                    @error('branch_name') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-branch_name" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>

                <!-- UPI ID -->
                <div class="flex flex-col gap-1">
                    <label for="upi_id" class="text-[10px] font-bold text-slate-400 uppercase">UPI ID <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="text" id="upi_id" name="upi_id" 
                           value="{{ old('upi_id', $seller->account_details['upi_id'] ?? '') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500 transition-colors"
                           placeholder="e.g. name@upi" {{ ($status === 'verified' || $status === 'pending') ? 'disabled' : '' }}>
                    @error('upi_id') <p class="text-rose-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    <p id="client-error-upi_id" class="text-rose-500 text-[10px] mt-0.5 hidden"></p>
                </div>
            </div>

            @if($status !== 'verified' && $status !== 'pending')
                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs tracking-wider uppercase transition-colors cursor-pointer border-none outline-none">
                        Submit Details
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('payoutAccountForm');
        if (!form) return;

        // Validation Rules
        const rules = {
            account_holder_name: {
                validate: (val) => val.trim().length > 0,
                message: "Account holder name is required."
            },
            bank_name: {
                validate: (val) => val && val.trim().length > 0,
                message: "Please select your bank."
            },
            account_number: {
                validate: (val) => /^\d{9,18}$/.test(val),
                message: "Account number must be numeric and between 9 and 18 digits."
            },
            ifsc_code: {
                validate: (val) => /^[A-Z]{4}0[A-Z0-9]{6}$/i.test(val),
                message: "Must be a valid 11-digit IFSC code (e.g., SBIN0001234)."
            },
            branch_name: {
                validate: (val) => val.trim().length > 0,
                message: "Branch name is required."
            },
            upi_id: {
                validate: (val) => !val || /^[\w.\-_]{2,256}@[a-zA-Z]{2,64}$/.test(val),
                message: "Invalid UPI ID format (e.g., name@upi)."
            }
        };

        // Function to validate a single field
        function validateField(fieldId) {
            const input = document.getElementById(fieldId);
            if (!input) return true; 
            if (input.disabled) return true; 

            const val = input.value;
            const rule = rules[fieldId];
            const errorEl = document.getElementById('client-error-' + fieldId);

            if (rule && !rule.validate(val)) {
                if (errorEl) {
                    errorEl.textContent = rule.message;
                    errorEl.classList.remove('hidden');
                }
                input.classList.add('border-rose-500', 'focus:border-rose-500');
                input.classList.remove('border-slate-200', 'dark:border-slate-700', 'focus:border-indigo-500');
                
                // For select2
                if (fieldId === 'bank_name') {
                    const s2Selection = input.nextElementSibling?.querySelector('.select2-selection');
                    if (s2Selection) {
                        s2Selection.style.setProperty('border-color', '#f43f5e', 'important'); 
                    }
                }
                return false;
            } else {
                if (errorEl) {
                    errorEl.classList.add('hidden');
                }
                input.classList.remove('border-rose-500', 'focus:border-rose-500');
                input.classList.add('border-slate-200', 'dark:border-slate-700');
                
                // For select2
                if (fieldId === 'bank_name') {
                    const s2Selection = input.nextElementSibling?.querySelector('.select2-selection');
                    if (s2Selection) {
                        s2Selection.style.removeProperty('border-color');
                    }
                }
                return true;
            }
        }

        // Attach listeners for live inline validation
        ['account_holder_name', 'account_number', 'ifsc_code', 'branch_name', 'upi_id'].forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (input) {
                input.addEventListener('input', () => validateField(fieldId));
                input.addEventListener('blur', () => validateField(fieldId));
            }
        });

        // Bank name Select2 change listener
        const bankSelect = document.getElementById('bank_name');
        if (bankSelect) {
            jQuery(bankSelect).on('change', () => {
                validateField('bank_name');
            });
        }

        // Form submit validation check
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            // Run validation on all fields
            let isFormValid = true;
            Object.keys(rules).forEach(fieldId => {
                const isValid = validateField(fieldId);
                if (!isValid) isFormValid = false;
            });

            if (!isFormValid) {
                // Focus first invalid input
                const firstInvalid = Object.keys(rules).find(fieldId => {
                    const input = document.getElementById(fieldId);
                    return input && !input.disabled && !rules[fieldId].validate(input.value);
                });
                if (firstInvalid) {
                    const input = document.getElementById(firstInvalid);
                    if (input) input.focus();
                }
                return;
            }

            const text = "Are you sure you want to submit your payment account details for verification? Once submitted, you cannot edit them until reviewed and approved by an administrator.";

            if (typeof showConfirm === 'function') {
                showConfirm(
                    text,
                    () => {
                        if (typeof window.showFullPageLoader === 'function') {
                            window.showFullPageLoader();
                        }
                        form.submit();
                    },
                    "Confirm Submission"
                );
            } else {
                if (confirm(text)) {
                    if (typeof window.showFullPageLoader === 'function') {
                        window.showFullPageLoader();
                    } else {
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.style.opacity = '0.7';
                            btn.style.cursor = 'not-allowed';
                            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Submitting...';
                        }
                    }
                    form.submit();
                }
            }
        });
    });
</script>
@endpush
@endsection
