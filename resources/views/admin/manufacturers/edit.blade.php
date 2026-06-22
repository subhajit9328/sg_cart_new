@extends('layouts.admin')

@section('title', 'Edit Manufacturer — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.manufacturers.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Manufacturer</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Catalogue'],
            ['label' => 'Manufacturers', 'url' => route('admin.manufacturers.index')],
            ['label' => 'Edit'],
            ['label' => $manufacturer->name, 'mono' => true]
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Update Manufacturer Information</h2>
    </div>

    <form method="POST" action="{{ route('admin.manufacturers.update', $manufacturer->ulid) }}" enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf
        @method('PUT')

        <!-- Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Manufacturer Name <span class="text-rose-600">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $manufacturer->name) }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Apple Inc.">
            @error('name') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Website -->
            <div>
                <label for="website" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Website URL</label>
                <input type="url" name="website" id="website" value="{{ old('website', $manufacturer->website) }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                @error('website') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Contact Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $manufacturer->email) }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                @error('email') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Phone Number</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $manufacturer->phone) }}"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100">
                @error('phone') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Address -->
        <div>
            <label for="address" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Office Address</label>
            <textarea name="address" id="address" rows="2"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                placeholder="1 Infinite Loop, Cupertino, CA 95014...">{{ old('address', $manufacturer->address) }}</textarea>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                placeholder="Brief details about the manufacturer...">{{ old('description', $manufacturer->description) }}</textarea>
        </div>

        <!-- Logo Upload and Display -->
        <div class="space-y-3">
            <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Manufacturer Logo</label>
            
            @if($manufacturer->logo)
                <div class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 rounded-xl w-fit">
                    <img src="{{ Storage::url($manufacturer->logo) }}" class="w-16 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-800 bg-white">
                    <div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Current Logo</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Uploading a new file will replace this logo.</p>
                    </div>
                </div>
            @endif

            <input type="file" name="logo" accept="image/*"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-800 dark:text-slate-200 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-500/10 dark:file:text-blue-400 hover:file:bg-blue-100 cursor-pointer">
        </div>

        <!-- Status Checkbox -->
        <div class="pt-2">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $manufacturer->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Active Status</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Determine if this manufacturer/vendor is publicly visible.</span>
                </div>
            </label>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.manufacturers.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 no-underline">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10 border-none cursor-pointer">
                Update Manufacturer
            </button>
        </div>

    </form>
</div>
@endsection
