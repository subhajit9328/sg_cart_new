@extends('layouts.admin')

@section('title', 'Dashboard — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Dashboard</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back, <span class="font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>!</p>
    </div>
</div>

<!-- Recent Users Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h2 class="font-semibold text-sm">Recently Registered Users</h2>
        @can('manage users')
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Manage all users <i class="fa-solid fa-arrow-right ml-1 text-xs"></i></a>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">User</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Roles</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($recentUsers as $user)
                <tr>
                    <td class="px-5 py-3.5 font-medium flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-500 dark:text-slate-400 text-xs">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        {{ $user->name }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $user->email }}</td>
                    <td class="px-5 py-3.5">
                        @foreach($user->roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 mr-1">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
