@extends('layouts.admin')

@section('title', 'User Management — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">User Management</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Access Control'],
            ['label' => 'Users']
        ]" />
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline">
        <i class="fa-solid fa-user-plus"></i> Add User
    </a>
</div>@php
    $headers = [
        ['label' => 'User', 'key' => 'name', 'sortable' => true],
        ['label' => 'Email', 'key' => 'email', 'sortable' => true],
        ['label' => 'Roles', 'key' => 'roles', 'sortable' => false],
        ['label' => 'Date Registered', 'key' => 'created_at', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="All Users"
    :totalCount="$users->total()"
    searchPlaceholder="Search users…"
    action="{{ route('admin.users.index') }}"
    tableId="usersTableWrapper"
    searchInputId="userSearchInput"
    totalCountId="usersTotalCount"
    clearBtnId="usersClearBtn"
    clearBtnWrapperId="usersClearBtnWrapper"
    :items="$users"
    :headers="$headers"
    :filterKeys="['role']"
>
    <x-slot name="filters">
        <!-- Role Dropdown -->
        <div class="min-w-[130px]">
            <select name="role"
                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all cursor-pointer">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-slot>

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
                        <button onclick='openDeleteModal(@json($user->ulid), @json($user->name))' class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer animate-fadeIn" title="Delete User">
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
</x-data-table>

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
