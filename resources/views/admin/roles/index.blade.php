@extends('layouts.admin')

@section('title', 'Role Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Role Management</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Access Control'],
            ['label' => 'Roles']
        ]" />
    </div>
    <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-user-shield"></i> Add Role
    </a>
</div><!-- Roles Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Search -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Roles</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $roles->total() }}
            </span>
        </div>
        
        <form action="{{ route('admin.roles.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Search roles…">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('admin.roles.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center animate-fadeIn">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Role Name</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Guard</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Assigned Permissions</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($roles as $role)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-blue-500"></i>
                            <span>{{ $role->name }}</span>
                            @if($role->name === 'Super Admin' || $role->name === 'super-admin')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold border border-amber-200/50 dark:border-amber-800/30">System</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-400 whitespace-nowrap">{{ $role->guard_name }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-wrap gap-1.5 max-w-xl">
                            @if($role->name === 'Super Admin' || $role->name === 'super-admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-250/20">
                                    All Permissions (*)
                                </span>
                            @else
                                @forelse($role->permissions as $permission)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/40">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-450 italic">No permissions assigned</span>
                                @endforelse
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5 justify-end">
                            <a href="{{ route('admin.roles.edit', $role->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Role">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            
                            @if($role->name !== 'Super Admin' && $role->name !== 'super-admin')
                                <button onclick="openDeleteModal('{{ $role->ulid }}', '{{ $role->name }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer animate-fadeIn" title="Delete Role">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            @else
                                <span class="w-8 h-8 rounded-lg border border-slate-100 dark:border-slate-800/40 opacity-40 cursor-not-allowed flex items-center justify-center" title="System protection lock">
                                    <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-shield-halved text-4xl mb-3 opacity-20 block"></i>
                        No roles found matching the criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($roles->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $roles->links() }}
        </div>
    @endif
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(roleId, name) {
        showConfirm(
            `Are you sure you want to delete Role ${name}? Users assigned to this role will lose their permissions.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/roles/${roleId}`;
                form.submit();
            },
            'Delete Role?'
        );
    }
</script>
@endsection
