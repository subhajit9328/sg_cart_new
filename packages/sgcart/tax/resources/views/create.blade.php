@extends('layouts.admin')

@section('title', 'Create Tax Rate — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tax.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Add Tax Rate</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Finance'],
            ['label' => 'Tax Rates', 'url' => route('admin.tax.index')],
            ['label' => 'Add']
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Enter Tax Rate Details</h2>
    </div>
    
    <form action="{{ route('admin.tax.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Tax Option Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="GST, VAT, State Sales Tax, etc.">
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Rate Type -->
            <div>
                <label for="type" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Tax Type</label>
                <select name="type" id="type" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                    <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="flat" {{ old('type') == 'flat' ? 'selected' : '' }}>Flat Rate (₹)</option>
                </select>
                @error('type')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rate Field -->
            <div>
                <label for="rate" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Tax Rate Value</label>
                <input type="number" step="0.01" min="0" name="rate" id="rate" value="{{ old('rate') }}" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('rate') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="18.00">
                @error('rate')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Geographic Scope (Optional)</span>
            <p class="text-[10px] text-slate-400 mb-3">Leave empty for globally applicable rates. Values must match the customer's shipping address during checkout.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Country -->
                <div>
                    <label for="country" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-2">Country</label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 transition-all text-slate-800 dark:text-slate-100"
                        placeholder="India, United States, etc.">
                    @error('country')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-2">State / Region</label>
                    <input type="text" name="state" id="state" value="{{ old('state') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none transition-all text-slate-800 dark:text-slate-100"
                        placeholder="Maharashtra, New York, etc.">
                    @error('state')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Zip -->
                <div>
                    <label for="zip" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-2">Zipcode / Postal Code</label>
                    <input type="text" name="zip" id="zip" value="{{ old('zip') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none transition-all text-slate-800 dark:text-slate-100"
                        placeholder="400001, 10001, etc.">
                    @error('zip')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Status Checkbox -->
        <div>
            <label class="flex items-center gap-3 cursor-pointer select-none py-1">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active Status</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Determine if this tax rate is active and eligible for checkout.</span>
                </div>
            </label>
            @error('is_active')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.tax.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10">
                Save Tax Rate
            </button>
        </div>

    </form>
</div>
@endsection
