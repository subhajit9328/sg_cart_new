@extends('layouts.admin')

@section('title', 'Search Settings — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-800 dark:text-slate-100">Search Settings</h1>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Configure the AI credentials and models used for image search.</p>
    </div>
</div>

<div class="mx-auto flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/30 rounded-xl text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class=" bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-md">
            <!-- Card Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-800 shrink-0 shadow-2xs bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                AI Image Search Provider
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Status Indicator Badge -->
                <div class="flex items-center shrink-0">
                    @if($setting->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>

            <!-- Form Details -->
            <form action="{{ route('admin.image-search.settings.update') }}" method="POST" id="search-settings-form" class="p-5 flex-1 flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-4">
                    <!-- Provider Input -->
                    <div>
                        <label for="provider" class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">AI Provider <span class="text-rose-600">*</span></label>
                        <input type="text" name="provider" id="provider" value="{{ old('provider', $setting->provider) }}" class="w-full bg-white dark:bg-slate-800 border @error('provider') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs" placeholder="e.g. gemini or openai" required>
                        @error('provider')
                            <p class="text-rose-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Model Input -->
                    <div>
                        <label for="model" class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Image Analyzer Model <span class="text-rose-600">*</span></label>
                        <input type="text" name="model" id="model" value="{{ old('model', $setting->model) }}" class="w-full bg-white dark:bg-slate-800 border @error('model') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs" placeholder="e.g. gemini-2.5-flash or gpt-4o" required>
                        @error('model')
                            <p class="text-rose-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key Input -->
                    <div>
                        <label for="api_key" class="block text-[10px] font-bold text-slate-400 dark:text-slate-400 mb-1.5 uppercase tracking-wider">API Key <span class="text-rose-600">*</span></label>
                        <input type="password" name="api_key" id="api_key" value="{{ old('api_key', $setting->api_key) }}" class="w-full bg-white dark:bg-slate-800 border @error('api_key') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 dark:text-slate-100 transition-all shadow-2xs" placeholder="••••••••••••••••••••••••••••••••" oninvalid="this.setCustomValidity('API Key required.')" oninput="this.setCustomValidity('')" required>
                        @error('api_key')
                            <p class="text-rose-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </form>

            <!-- Action Footer -->
            <div class="px-5 pb-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <!-- Toggle Status Button Form -->
                <form action="{{ route('admin.image-search.settings.update') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold transition-all border shadow-2xs flex items-center justify-center gap-1.5
                        @if($setting->is_active)
                            bg-rose-50 hover:bg-rose-100/80 border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:border-rose-900/40 dark:text-rose-400
                        @else
                            bg-emerald-50 hover:bg-emerald-100/80 border-emerald-200 text-emerald-600 dark:bg-emerald-950/20 dark:hover:bg-emerald-900/30 dark:border-emerald-900/40 dark:text-emerald-400
                        @endif">
                        @if($setting->is_active)
                            <i class="fa-solid fa-power-off text-xs"></i> Deactivate
                        @else
                            <i class="fa-solid fa-circle-check text-xs"></i> Activate
                        @endif
                    </button>
                </form>

                <!-- Save Settings Button -->
                <button type="submit" form="search-settings-form"
                    @if($errors->any()) disabled @endif
                    class="flex-1 text-white py-2.5 rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5
                    @if($errors->any()) bg-slate-300 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed @else bg-blue-600 hover:bg-blue-700 @endif">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('search-settings-form');
        const saveBtn = document.querySelector('button[form="search-settings-form"]');
        const providerInput = document.getElementById('provider');
        const modelInput = document.getElementById('model');
        const apiKeyInput = document.getElementById('api_key');

        function checkValidity() {
            const isProviderValid = providerInput.value.trim() !== '';
            const isModelValid = modelInput.value.trim() !== '';
            const isApiKeyValid = apiKeyInput.value.trim() !== '';
            
            const isValid = isProviderValid && isModelValid && isApiKeyValid;
            
            if (isValid) {
                saveBtn.removeAttribute('disabled');
                saveBtn.classList.remove('bg-slate-300', 'dark:bg-slate-800', 'text-slate-400', 'dark:text-slate-500', 'cursor-not-allowed');
                saveBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');
            } else {
                saveBtn.setAttribute('disabled', 'disabled');
                saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'text-white');
                saveBtn.classList.add('bg-slate-300', 'dark:bg-slate-800', 'text-slate-400', 'dark:text-slate-500', 'cursor-not-allowed');
            }
        }

        if (providerInput && modelInput && apiKeyInput && saveBtn) {
            [providerInput, modelInput, apiKeyInput].forEach(input => {
                input.addEventListener('input', function() {
                    // Clear error styling and messages on user input
                    input.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                    input.classList.add('border-slate-200', 'dark:border-slate-700');
                    const errorMsg = input.parentNode.querySelector('.text-rose-500');
                    if (errorMsg) {
                        errorMsg.style.display = 'none';
                    }
                    checkValidity();
                });
            });
            // Initial check
            checkValidity();
        }

        const forms = document.querySelectorAll('form');
        forms.forEach(f => {
            f.addEventListener('submit', function () {
                let submitBtn = null;
                if (f.id) {
                    submitBtn = document.querySelector(`button[form="${f.id}"]`);
                }
                if (!submitBtn) {
                    submitBtn = f.querySelector('button[type="submit"]');
                }

                if (submitBtn && !submitBtn.disabled) {
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
</script>
@endpush
