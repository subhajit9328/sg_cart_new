@extends('layouts.admin')

@section('title', 'Create Role — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.roles.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Add Role</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Access Control / Roles / Add</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <h2 class="font-semibold text-sm">Enter Role Information</h2>
    </div>
    
    <form action="{{ route('admin.roles.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <!-- Role Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Role Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Manager">
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Permissions Checkbox Grid -->
        <div>
            <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2.5">Assign Permissions</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-lg">
                @foreach($permissions as $permission)
                    <label class="flex items-start gap-3 cursor-pointer select-none py-1">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                            {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}
                            class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer mt-0.5">
                        <div>
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">{{ $permission->name }}</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Standard permission tag mapping</span>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('permissions')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium transition-colors shadow-lg shadow-blue-600/10">
                Save Role
            </button>
        </div>

    </form>
</div>
@endsection
