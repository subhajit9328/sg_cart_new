@extends('marketplace::layouts.seller')

@section('title', 'Add Size Label — Seller Portal')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('seller.sizes.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left text-xs"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Add Size Label</h1>
        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">Create a new custom size option for your product variations.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden max-w-2xl">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm text-slate-700 dark:text-slate-200">Enter Size Details</h2>
    </div>

    <form method="POST" action="{{ route('seller.sizes.store') }}" class="p-6 space-y-6">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Size Name <span class="text-rose-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 @enderror"
                    placeholder="e.g. Medium">
                @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="code" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Size Code <span class="text-rose-600">*</span></label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('code') border-rose-500 focus:border-rose-500 @enderror"
                    placeholder="e.g. M">
                @error('code') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('seller.sizes.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Create Size
            </button>
        </div>
    </form>
</div>
@endsection
