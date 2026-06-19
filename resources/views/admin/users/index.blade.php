@extends('layouts.admin')

@section('title', 'User Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">User Management</h1>
        <p class="text-sm text-slate-400 mt-0.5">Admin / Access Control / Users</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-user-plus"></i> Add User
    </a>
</div><!-- Users Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Table Card Header with Integrated Filters -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Users</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/40 dark:border-slate-700/50">
                {{ $users->total() }}
            </span>
        </div>
        
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-60">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Search users…">
            </div>

            <!-- Role Dropdown -->
            <div class="min-w-[130px]">
                <select name="role" onchange="this.form.submit()"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request()->filled('search') || request()->filled('role'))
                <a href="{{ route('admin.users.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">User</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Email</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Roles</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Date Registered</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                    <td class="px-5 py-3.5 font-medium whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ $user->name }}</p>
                                @if($user->id === auth()->id())
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold border border-slate-200 dark:border-slate-700/50 mt-0.5">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        @forelse($user->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 mr-1.5 border border-blue-200/20">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 italic">No role assigned</span>
                        @endforelse
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $user->created_at->format('d M Y, H:i A') }}</td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5 justify-end">
                            <a href="{{ route('admin.users.edit', $user->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors" title="Edit User">
                                <i class="fa-solid fa-pen text-slate-500 dark:text-slate-400 text-xs"></i>
                            </a>
                            
                            @if($user->id !== auth()->id())
                                <button onclick="openDeleteModal('{{ $user->ulid }}', '{{ $user->name }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer animate-fadeIn" title="Delete User">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            @else
                                <span class="w-8 h-8 rounded-lg border border-slate-100 dark:border-slate-800/40 opacity-40 cursor-not-allowed flex items-center justify-center" title="Self-deletion prohibited">
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

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(userId, name) {
        showConfirm(
            `Are you sure you want to delete User ${name}? This action cannot be undone.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/users/${userId}`;
                form.submit();
            },
            'Delete User?'
        );
    }
</script>
@endsection
