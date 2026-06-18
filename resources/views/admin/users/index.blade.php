@extends('layouts.admin')

@section('title', 'User Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">User Management</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Access Control / Users</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10">
        <i class="fa-solid fa-user-plus"></i> Add User
    </a>
</div>

<!-- Search & Filter Bar -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-6 p-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
        <!-- Search Input -->
        <div class="flex-1 min-w-[250px] relative flex items-center">
            <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 pl-9 pr-4 text-sm placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                placeholder="Search users by name or email…">
        </div>

        <!-- Role Filter Dropdown -->
        <div class="min-w-[150px]">
            <select name="role" onchange="this.form.submit()"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Submit & Clear Buttons -->
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold shadow-lg shadow-blue-600/10 transition-colors">
                Apply
            </button>
            @if(request()->filled('search') || request()->filled('role'))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-sm font-semibold transition-colors text-center no-underline">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">User</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Roles</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Date Registered</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($users as $user)
                <tr>
                    <td class="px-5 py-4 font-medium whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                                @if($user->id === auth()->id())
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold border border-slate-200 dark:border-slate-700 mt-0.5">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        @forelse($user->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 mr-1.5">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 italic">No role assigned</span>
                        @endforelse
                    </td>
                    <td class="px-5 py-4 text-slate-400 whitespace-nowrap">{{ $user->created_at->format('d M Y, H:i A') }}</td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit User">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            
                            @if($user->id !== auth()->id())
                                <button onclick="openDeleteModal('{{ $user->id }}', '{{ $user->name }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors" title="Delete User">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            @else
                                <span class="w-8 h-8 rounded-lg border border-slate-100 dark:border-slate-800 opacity-40 cursor-not-allowed flex items-center justify-center" title="Self-deletion prohibited">
                                    <i class="fa-solid fa-trash text-slate-400 text-xs"></i>
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-users text-4xl mb-3 opacity-20 block"></i>
                        No users found matching the filter criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $users->links() }}
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
        <h3 class="font-display font-bold text-lg mb-2">Delete User?</h3>
        <p class="text-sm text-slate-400 mb-6">Are you sure you want to delete <strong id="deleteUserName" class="text-slate-800 dark:text-slate-200"></strong>? This action cannot be undone.</p>
        
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
    const deleteUserName = document.getElementById('deleteUserName');
    const deleteForm = document.getElementById('deleteForm');

    function openDeleteModal(userId, name) {
        deleteUserName.textContent = name;
        deleteForm.action = `/admin/users/${userId}`;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }
</script>
@endsection
