@extends('layouts.admin')

@section('title', 'Payment Gateways — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-800 dark:text-slate-100">Payment Gateway Settings</h1>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Activate and configure credentials for storefront checkout gateways.</p>
    </div>
</div>


<div class="max-w-7xl mx-auto flex flex-col gap-6">
    <!-- 3-Column Grid of Premium Gateway Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($gateways as $gateway)
            @php
                $dbMethod = $dbMethods->get($gateway->getId());
                $isEnabled = $dbMethod ? $dbMethod->is_enabled : false;
                $configValues = $dbMethod ? ($dbMethod->config ?? []) : [];
            @endphp
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-md">
                <!-- Gateway Header -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <!-- Icon -->
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-800 shrink-0 shadow-2xs
                            @if($gateway->getId() === 'cod') bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400
                            @elseif($gateway->getId() === 'razorpay') bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400
                            @else bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 @endif">
                            <i class="fa-solid @if($gateway->getId() === 'cod') fa-truck-ramp-box @elseif($gateway->getId() === 'razorpay') fa-wallet @else fa-credit-card @endif text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-xs truncate">
                                    {{ $dbMethod->name ?? $gateway->getName() }}
                                </h3>
                                <span class="font-mono text-[8px] font-bold text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700/60 uppercase tracking-wider">
                                    {{ $gateway->getId() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Indicator Badge -->
                    <div class="flex items-center shrink-0">
                        @if($isEnabled)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700">
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>                <!-- Gateway Form Details (Main Settings Form) -->
                <form action="{{ route('admin.payments.settings.update') }}" method="POST" id="config-form-{{ $gateway->getId() }}" class="p-5 flex-1 flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="gateway_id" value="{{ $gateway->getId() }}">
                    @if($isEnabled)
                        <input type="hidden" name="settings[{{ $gateway->getId() }}][is_enabled]" value="1">
                    @endif

                    <div class="flex flex-col gap-4">
                        <!-- Section 1: Display Info -->
                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Display Name</label>
                                <input type="text" name="settings[{{ $gateway->getId() }}][name]" value="{{ $dbMethod->name ?? $gateway->getName() }}" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs" required>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Description</label>
                                <textarea name="settings[{{ $gateway->getId() }}][description]" rows="2" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs resize-none" required>{{ $dbMethod->description ?? $gateway->getDescription() }}</textarea>
                            </div>
                        </div>

                        <!-- Section 2: Config Credentials -->
                        @if(count($gateway->getConfigSchema()) > 0)
                            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-2">
                                <div class="flex items-center gap-1.5 mb-3">
                                    <i class="fa-solid fa-key text-[10px] text-slate-400 dark:text-slate-500"></i>
                                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">API Credentials</span>
                                </div>
                                
                                <div class="flex flex-col gap-3">
                                    @foreach($gateway->getConfigSchema() as $key => $field)
                                        @php
                                            $value = $configValues[$key] ?? $field['default'] ?? null;
                                            $isRequired = !empty($field['required']);
                                        @endphp
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">
                                                {{ $field['label'] }} @if($isRequired)<span class="text-rose-500">*</span>@endif
                                            </label>
                                            @if($field['type'] === 'textarea')
                                                <textarea name="settings[{{ $gateway->getId() }}][config][{{ $key }}]" rows="2" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 shadow-2xs resize-none" {{ $isRequired ? 'required' : '' }}>{{ $value }}</textarea>
                                            @elseif($field['type'] === 'select')
                                                <select name="settings[{{ $gateway->getId() }}][config][{{ $key }}]" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 shadow-2xs" {{ $isRequired ? 'required' : '' }}>
                                                    @foreach($field['options'] as $optVal => $optLabel)
                                                        <option value="{{ $optVal }}" {{ $value == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="{{ $field['type'] }}" name="settings[{{ $gateway->getId() }}][config][{{ $key }}]" value="{{ $value }}" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 shadow-2xs" {{ $isRequired ? 'required' : '' }}>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </form>

                <!-- Action Footer (Outside configuration form to prevent nested form issues) -->
                <div class="px-5 pb-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <!-- Toggle Status Button Form -->
                    <form action="{{ route('admin.payments.settings.update') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="gateway_id" value="{{ $gateway->getId() }}">
                        <input type="hidden" name="action" value="toggle">
                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold transition-all border shadow-2xs flex items-center justify-center gap-1.5
                            @if($isEnabled)
                                bg-rose-50 hover:bg-rose-100/80 border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:border-rose-900/40 dark:text-rose-400
                            @else
                                bg-emerald-50 hover:bg-emerald-100/80 border-emerald-200 text-emerald-600 dark:bg-emerald-950/20 dark:hover:bg-emerald-900/30 dark:border-emerald-900/40 dark:text-emerald-400
                            @endif">
                            @if($isEnabled)
                                <i class="fa-solid fa-power-off text-xs"></i> Deactivate
                            @else
                                <i class="fa-solid fa-circle-check text-xs"></i> Activate
                            @endif
                        </button>
                    </form>

                    <!-- Save Settings Button (Submits config-form) -->
                    <button type="submit" form="config-form-{{ $gateway->getId() }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Save Settings
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-400">
                <i class="fa-solid fa-credit-card text-3xl mb-3 opacity-20 block"></i>
                No payment gateways registered.
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function () {
                let submitBtn = null;
                if (form.id) {
                    submitBtn = document.querySelector(`button[form="${form.id}"]`);
                }
                if (!submitBtn) {
                    submitBtn = form.querySelector('button[type="submit"]');
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

                    const icon = submitBtn.querySelector('i');
                    if (icon) {
                        submitBtn.dataset.originalIconClass = icon.className;
                        icon.className = 'fa-solid fa-spinner animate-spin text-xs';
                    } else {
                        submitBtn.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-spinner animate-spin text-xs mr-1.5 tmp-spinner"></i>');
                    }
                }
            });
        });
    });

    window.addEventListener('pageshow', function () {
        const submitBtns = document.querySelectorAll('button[type="submit"], button[form]');
        submitBtns.forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            
            const icon = btn.querySelector('i');
            if (icon) {
                if (btn.dataset.originalIconClass) {
                    icon.className = btn.dataset.originalIconClass;
                    delete btn.dataset.originalIconClass;
                } else if (icon.classList.contains('tmp-spinner')) {
                    icon.remove();
                }
            }
        });
    });
</script>
@endpush
