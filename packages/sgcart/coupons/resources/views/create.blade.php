@extends('layouts.admin')

@section('title', 'Create Coupon — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.coupons.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Add Coupon</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / E-Commerce / Coupons / Add</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Enter Coupon Details</h2>
    </div>
    
    <form action="{{ route('admin.coupons.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <!-- Code Field -->
        <div>
            <label for="code" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Coupon Code</label>
            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 uppercase @error('code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="SAVE20">
            @error('code')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Type Dropdown -->
            <div>
                <label for="type" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Discount Type</label>
                <select name="type" id="type" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                    @foreach($types as $type)
                        <option value="{{ $type->value }}" {{ old('type') == $type->value ? 'selected' : '' }}>
                            {{ ucfirst($type->name) }} ({{ $type->value }})
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Value Field -->
            <div>
                <label for="value" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Discount Value</label>
                <input type="number" step="0.01" min="0.01" name="value" id="value" value="{{ old('value') }}" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('value') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="10.00">
                @error('value')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Min Cart Total Field -->
            <div>
                <label for="min_cart_total" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Minimum Cart Subtotal Required ($)</label>
                <input type="number" step="0.01" min="0" name="min_cart_total" id="min_cart_total" value="{{ old('min_cart_total', '0.00') }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('min_cart_total') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                @error('min_cart_total')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expires At Field -->
            <div>
                <label for="expires_at" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Expiry Date (Optional)</label>
                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('expires_at') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                @error('expires_at')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Status Checkbox -->
        <div>
            <label class="flex items-center gap-3 cursor-pointer select-none py-1">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active Status</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Determine if the coupon can be currently applied.</span>
                </div>
            </label>
            @error('is_active')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10">
                Save Coupon
            </button>
        </div>

    </form>
</div>
@endsection
