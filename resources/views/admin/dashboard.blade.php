@extends('layouts.admin')

@section('title', 'Dashboard — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Dashboard</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back, <span class="font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>!</p>
    </div>
    <div class="flex gap-2">
        <button class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
            <i class="fa-solid fa-calendar-days"></i> Last 7 days
        </button>
        <button class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            <i class="fa-solid fa-download"></i> Export Reports
        </button>
    </div>
</div>

<!-- Stats cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <!-- Total Users -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Total Users</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalUsers }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 mt-2">
                <i class="fa-solid fa-arrow-up"></i> Active
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    
    <!-- Total Roles -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Active Roles</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalRoles }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 mt-2">
                <i class="fa-solid fa-check"></i> Configured
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
    </div>

    <!-- Total Permissions -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Spatie Permissions</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalPermissions }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 mt-2">
                <i class="fa-solid fa-lock"></i> Secured
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-key"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
    <!-- Chart Overview -->
    <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-sm">System Access Summary</h2>
                <p class="text-xs text-slate-400 mt-0.5">Mock analytics of weekly operations</p>
            </div>
            <span class="text-xs font-medium text-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 rounded-lg">Online</span>
        </div>
        <div class="p-5">
            <div class="flex items-end gap-3 h-44">
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/20 hover:bg-blue-600 transition-colors" style="height:35%" title="Mon"></div>
                    <span class="text-xs text-slate-400">Mon</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/40 hover:bg-blue-600 transition-colors" style="height:55%" title="Tue"></div>
                    <span class="text-xs text-slate-400">Tue</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/30 hover:bg-blue-600 transition-colors" style="height:45%" title="Wed"></div>
                    <span class="text-xs text-slate-400">Wed</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/70 hover:bg-blue-600 transition-colors" style="height:75%" title="Thu"></div>
                    <span class="text-xs text-slate-400">Thu</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/60 hover:bg-blue-600 transition-colors" style="height:65%" title="Fri"></div>
                    <span class="text-xs text-slate-400">Fri</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600 hover:scale-y-105 transition-all" style="height:95%" title="Sat"></div>
                    <span class="text-xs text-slate-400">Sat</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/50 hover:bg-blue-600 transition-colors" style="height:50%" title="Sun"></div>
                    <span class="text-xs text-slate-400">Sun</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Actions -->
    <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800"><h2 class="font-semibold text-sm">Security Log Highlights</h2></div>
        <div class="p-5 space-y-4">
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-check"></i></div>
                <div>
                    <p class="text-sm">Database successfully seeded and ready.</p>
                    <p class="text-xs text-slate-400">Just now</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-user-plus"></i></div>
                <div>
                    <p class="text-sm">Admin user session initialized successfully.</p>
                    <p class="text-xs text-slate-400">2 minutes ago</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                    <p class="text-sm">Spatie standard policies registered.</p>
                    <p class="text-xs text-slate-400">1 hour ago</p>
                </div>
            </div>
        </div>
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
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60">User</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60">Roles</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50 dark:bg-slate-800/60">Joined Date</th>
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
