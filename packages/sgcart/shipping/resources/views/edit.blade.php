@extends('layouts.admin')

@section('title', 'Edit Shipping Rate — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.shipping.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Shipping Rate</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Logistics / Shipping Rates / Edit</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Update Shipping Rate Details</h2>
    </div>
    
    <form action="{{ route('admin.shipping.update', $rate->id) }}" method="POST" class="p-6 space-y-5">
        @csrf
        @method('PUT')
        
        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Shipping Option Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $rate->name) }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Standard Shipping, Express Delivery, etc.">
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Rate Type -->
            <div>
                <label for="type" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Rate Type</label>
                <select name="type" id="type" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                    <option value="flat" {{ old('type', $rate->type ?? 'flat') == 'flat' ? 'selected' : '' }}>Flat Rate (₹)</option>
                    <option value="percent" {{ old('type', $rate->type ?? 'flat') == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                </select>
                @error('type')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cost Field -->
            <div>
                <label for="cost" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Charge / Rate Value</label>
                <input type="number" step="0.01" min="0" name="cost" id="cost" value="{{ old('cost', $rate->cost) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('cost') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="99.00">
                @error('cost')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Min Order Amount Field -->
            <div>
                <label for="min_order_amount" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Min. Cart Subtotal (₹)</label>
                <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $rate->min_order_amount) }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('min_order_amount') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="0.00">
                @error('min_order_amount')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Status Checkbox -->
        <div>
            <label class="flex items-center gap-3 cursor-pointer select-none py-1">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rate->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active Status</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Determine if this shipping rate is selectable during checkout.</span>
                </div>
            </label>
            @error('is_active')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.shipping.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10">
                Update Shipping Rate
            </button>
        </div>

    </form>
</div>
@endsection
