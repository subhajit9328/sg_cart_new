@extends('layouts.admin')

@section('title', 'Role Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Role Management</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Access Control / Roles</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-shield-plus"></i> Add Role
    </a>
</div>

<!-- Roles Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Role Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Guard</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Assigned Permissions</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($roles as $role)
                <tr>
                    <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-blue-500"></i>
                            {{ $role->name }}
                            @if($role->name === 'Super Admin')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold border border-amber-200/50 dark:border-amber-800/30">System</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-400 whitespace-nowrap">{{ $role->guard_name }}</td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-1.5 max-w-xl">
                            @if($role->name === 'Super Admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                    All Permissions (*)
                                </span>
                            @else
                                @forelse($role->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">No permissions assigned</span>
                                @endforelse
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit Role">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            
                            @if($role->name !== 'Super Admin')
                                <button onclick="openDeleteModal('{{ $role->id }}', '{{ $role->name }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete Role">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            @else
                                <span class="w-8 h-8 rounded-lg border border-slate-100 dark:border-slate-800 opacity-40 cursor-not-allowed flex items-center justify-center" title="System protection lock">
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
                        No roles created yet.
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-6 w-full max-w-sm relative z-10 animate-fadeIn">
        <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-display font-bold text-lg mb-2">Delete Role?</h3>
        <p class="text-sm text-slate-400 mb-6">Are you sure you want to delete <strong id="deleteRoleName" class="text-slate-800 dark:text-slate-200"></strong>? Users assigned to this role will lose their permissions.</p>
        
        <form id="deleteForm" method="POST" class="flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium transition-colors shadow-lg shadow-rose-600/10">
                Delete
            </button>
        </form>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteRoleName = document.getElementById('deleteRoleName');
    const deleteForm = document.getElementById('deleteForm');

    function openDeleteModal(roleId, name) {
        deleteRoleName.textContent = name;
        deleteForm.action = `/admin/roles/${roleId}`;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }
</script>
@endsection
