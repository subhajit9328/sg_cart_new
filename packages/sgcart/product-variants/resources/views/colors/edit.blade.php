@extends('layouts.admin')

@section('title', 'Edit Color Swatch — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.colors.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Color Swatch</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Colors', 'url' => route('admin.colors.index')],
            ['label' => 'Edit']
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden max-w-2xl">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Update Swatch Details</h2>
    </div>

    <form method="POST" action="{{ route('admin.colors.update', $color->id) }}" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Color Name <span class="text-rose-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $color->name) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 @enderror"
                    placeholder="e.g. Royal Blue">
                @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="hex_code" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Hex Code <span class="text-rose-600">*</span></label>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 text-sm">#</span>
                        <input type="text" name="hex_code" id="hex_code" value="{{ old('hex_code', str_replace('#', '', $color->hex_code)) }}" required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 pl-8 pr-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('hex_code') border-rose-500 @enderror"
                            placeholder="e.g. 000000">
                    </div>
                    <div class="w-11 h-11 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden relative shadow-sm">
                        <input type="color" id="colorPicker" value="{{ str_starts_with($color->hex_code, '#') ? $color->hex_code : '#' . $color->hex_code }}" class="absolute inset-0 w-full h-full border-0 cursor-pointer p-0">
                    </div>
                </div>
                @error('hex_code') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.colors.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Update Color
            </button>
        </div>
    </form>
</div>

<script>
    const hexInput = document.getElementById('hex_code');
    const colorPicker = document.getElementById('colorPicker');

    // Sync input to color picker
    hexInput.addEventListener('input', function(e) {
        let val = e.target.value.trim();
        if(val.startsWith('#')) {
            val = val.substring(1);
            hexInput.value = val;
        }
        if(val.length === 6 || val.length === 3) {
            colorPicker.value = '#' + val;
        }
    });

    // Sync picker to input
    colorPicker.addEventListener('input', function(e) {
        hexInput.value = e.target.value.substring(1).toUpperCase();
    });
</script>
@endsection
