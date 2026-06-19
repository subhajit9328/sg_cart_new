@extends('layouts.admin')

@section('title', 'Edit Role — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.roles.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold">Edit Role</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Access Control'],
            ['label' => 'Roles', 'url' => route('admin.roles.index')],
            ['label' => 'Edit'],
            ['label' => $role->name, 'mono' => true]
        ]" />
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm w-full overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <h2 class="font-semibold text-sm">Update Role Information</h2>
        @if($role->name === 'Super Admin')
            <span class="text-xs font-semibold text-amber-500 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-200/50 dark:border-amber-800/20 flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-[10px]"></i> System Protected
            </span>
        @endif
    </div>
    
    <form action="{{ route('admin.roles.update', $role->ulid) }}" method="POST" class="p-6 space-y-5">
        @csrf
        @method('PUT')
        
        <!-- Role Name Field -->
        <div>
            <label for="name" class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2">Role Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                {{ $role->name === 'Super Admin' ? 'disabled' : '' }}
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100 disabled:opacity-60 disabled:cursor-not-allowed @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                placeholder="Manager">
            
            @if($role->name === 'Super Admin')
                <input type="hidden" name="name" value="Super Admin">
                <p class="text-xs text-slate-400 mt-1.5">For safety, the Super Admin role name cannot be modified.</p>
            @endif
            
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Permissions Checkbox Grid -->
        <div>
            <label class="block text-slate-700 dark:text-slate-300 text-sm font-semibold mb-2.5">Assign Permissions</label>
            
            @if($role->name === 'Super Admin')
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-lg text-emerald-800 dark:text-emerald-400 text-sm flex gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <div>
                        <p class="font-semibold">Super Admin Role Permissions</p>
                        <p class="text-xs text-slate-400 dark:text-emerald-500/80 mt-0.5">The Super Admin role is automatically assigned all permissions. Modifying permissions on this role is locked.</p>
                    </div>
                </div>
                
                <!-- Silent field output to keep existing permissions -->
                @foreach($permissions as $permission)
                    <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                @endforeach
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 rounded-lg">
                    @foreach($permissions as $permission)
                        <label class="flex items-start gap-3 cursor-pointer select-none py-1">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 cursor-pointer mt-0.5">
                            <div>
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">{{ $permission->name }}</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Standard permission tag mapping</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
            
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
                Update Role
            </button>
        </div>

    </form>
</div>
@endsection
