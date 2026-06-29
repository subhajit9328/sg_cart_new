@extends('layouts.admin')

@section('title', 'Add Shipping Courier — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.couriers.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Add Shipping Courier</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Logistics / Shipping Couriers / Add</p>
    </div>
</div>

<!-- Form Container -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Enter Courier Details</h2>
    </div>
    
    <form action="{{ route('admin.couriers.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Courier Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="e.g., DHL Express">
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Website/Tracking URL Field -->
            <div>
                <label for="url" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Website / Tracking URL</label>
                <input type="url" name="url" id="url" value="{{ old('url') }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('url') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="https://...">
                @error('url')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Support Email Field -->
            <div>
                <label for="support_email" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Support Email</label>
                <input type="email" name="support_email" id="support_email" value="{{ old('support_email') }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('support_email') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    placeholder="support@courier.com">
                @error('support_email')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all cursor-pointer">
                Save Courier
            </button>
            <a href="{{ route('admin.couriers.index') }}" class="inline-flex items-center justify-center border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold py-2.5 px-6 rounded-lg transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
