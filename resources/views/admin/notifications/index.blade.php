@extends('layouts.admin')

@section('title', 'All Notifications — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Notifications</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Notifications']
        ]" />
    </div>
    
    <div class="flex items-center gap-2">
        <button id="pageMarkAllReadBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium shadow-lg shadow-indigo-600/10 transition-colors border-none outline-none cursor-pointer">
            <i class="fa-solid fa-check-double"></i> Mark All as Read
        </button>
    </div>
</div>

@php
    $headers = [
        ['label' => 'Notification', 'key' => 'title', 'sortable' => false],
        ['label' => 'Message', 'key' => 'message', 'sortable' => false],
        ['label' => 'Date', 'key' => 'created_at', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Notifications History"
    :totalCount="$notifications->total()"
    searchPlaceholder="Search notifications…"
    action="{{ route('admin.notifications.all') }}"
    tableId="notificationsTableWrapper"
    searchInputId="notificationSearchInput"
    totalCountId="notificationsTotalCount"
    clearBtnId="notificationsClearBtn"
    clearBtnWrapperId="notificationsClearBtnWrapper"
    :items="$notifications"
    :headers="$headers"
    :filterKeys="['status']"
>
    <x-slot name="filters">
        <div class="min-w-[130px]">
            <select name="status" id="notificationStatusFilter"
                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 text-xs text-slate-700 dark:text-slate-300 outline-none focus:border-blue-500 transition-all cursor-pointer">
                <option value="">All Statuses</option>
                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
            </select>
        </div>
    </x-slot>

    @forelse($notifications as $notification)
        @php
            $isUnread = is_null($notification->read_at);
            $type = $notification->data['type'] ?? 'info';
            $icon = $notification->data['icon'] ?? 'fa-circle-info';
            $url = $notification->data['url'] ?? '#';
            $title = $notification->data['title'] ?? 'Notification';
            $message = $notification->data['message'] ?? '';
            
            // Determine badge colors based on notification type
            $colorClasses = 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
            if ($type === 'success') $colorClasses = 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
            elseif ($type === 'warning') $colorClasses = 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
            elseif ($type === 'danger' || $type === 'error') $colorClasses = 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400';
            elseif ($type === 'payout') $colorClasses = 'bg-teal-500/10 text-teal-600 dark:bg-teal-500/20 dark:text-teal-400';
            elseif ($type === 'order') $colorClasses = 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400';
            elseif ($type === 'product') $colorClasses = 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400';
        @endphp
        <tr class="{{ $isUnread ? 'bg-indigo-50/10 dark:bg-indigo-950/5 font-medium' : '' }} hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $colorClasses }} flex items-center justify-center flex-shrink-0 relative">
                        <i class="fa-solid {{ $icon }} text-sm"></i>
                        @if($isUnread)
                            <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-indigo-600 dark:bg-indigo-400 border border-white dark:border-slate-900 rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $message }}">
                {{ $message }}
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                {{ $notification->created_at->format('d M Y, h:i A') }} ({{ $notification->created_at->diffForHumans() }})
            </td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($isUnread)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></span>
                        Unread
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700/50">
                        Read
                    </span>
                @endif
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex gap-1.5 justify-end">
                    @if($isUnread)
                        <button onclick="markSingleRead('{{ $notification->id }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center justify-center transition-colors cursor-pointer" title="Mark as Read">
                            <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </button>
                    @endif
                    @if($url && $url !== '#')
                        <a href="{{ $url }}" onclick="handleNotificationClick(event, '{{ $notification->id }}', '{{ $url }}', {{ $isUnread ? 'true' : 'false' }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold no-underline transition-colors">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-5 py-12 text-center text-slate-400 dark:text-slate-600">
                <i class="fa-solid fa-bell-slash text-4xl mb-3 opacity-20 block"></i>
                No notifications found.
            </td>
        </tr>
    @endforelse
</x-data-table>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Status filter select trigger change on select
        const statusFilter = document.getElementById('notificationStatusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                statusFilter.closest('form').submit();
            });
        }

        // Mark all notifications as read button
        const markAllBtn = document.getElementById('pageMarkAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => {
                const originalHtml = markAllBtn.innerHTML;
                markAllBtn.disabled = true;
                markAllBtn.classList.add('opacity-75', 'cursor-not-allowed');
                markAllBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('/admin/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        // Restore state if not success
                        markAllBtn.disabled = false;
                        markAllBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                        markAllBtn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    console.error('Error marking all notifications read:', err);
                    // Restore state on error
                    markAllBtn.disabled = false;
                    markAllBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    markAllBtn.innerHTML = originalHtml;
                });
            });
        }
    });

    function markSingleRead(id) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`/admin/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(err => console.error('Error marking notification read:', err));
    }

    function handleNotificationClick(event, id, redirectUrl, isUnread) {
        if (!isUnread) return; // Proceed to link normally if already read

        event.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`/admin/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            keepalive: true
        }).catch(err => console.error('Error marking notification read:', err));

        window.location.href = redirectUrl;
    }
</script>
@endsection
