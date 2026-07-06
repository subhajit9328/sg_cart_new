@props([
    'align' => 'right',
])

<!-- Mobile Backdrop Overlay -->
<div id="notificationBackdrop" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 sm:hidden"></div>

<div class="relative inline-block text-left" id="notificationDropdownContainer">
    <!-- Trigger Button -->
    <button type="button" id="notificationBellBtn" class="relative p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-xl transition-all cursor-pointer border-none outline-none bg-transparent">
        <i class="fa-solid fa-bell text-lg"></i>
        <!-- Pulse Indicator Badge -->
        <span id="notificationBadge" class="hidden absolute top-1.5 right-1.5 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
        </span>
    </button>

    <!-- Dropdown Panel (Bottom Sheet on Mobile, Popover on Desktop) -->
    <div id="notificationMenu" class="hidden fixed sm:absolute left-0 sm:left-auto right-0 bottom-0 sm:bottom-auto sm:top-full mt-2 w-full sm:w-96 bg-white dark:bg-slate-900 border-t sm:border border-slate-100 dark:border-slate-800/40 rounded-t-3xl sm:rounded-2xl shadow-2xl z-50 overflow-hidden transition-all animate-slideUp sm:animate-fadeIn">
        <!-- Mobile Drawer Drag Handle -->
        <div class="h-1.5 w-12 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto my-3 sm:hidden flex-shrink-0"></div>

        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800/40 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">Notifications</h3>
                <span id="notificationCountBadge" class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">0</span>
            </div>
            <button type="button" id="markAllReadBtn" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 bg-transparent border-none outline-none cursor-pointer">
                Mark all as read
            </button>
        </div>

        <!-- Notification List -->
        <div id="notificationList" class="max-h-[50vh] sm:max-h-[320px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
            <!-- Loading State -->
            <div id="notificationLoader" class="p-6 text-center text-slate-400 dark:text-slate-600">
                <i class="fa-solid fa-circle-notch fa-spin text-lg"></i>
            </div>
            
            <!-- Empty State -->
            <div id="notificationEmptyState" class="hidden p-8 text-center">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-bell-slash text-base"></i>
                </div>
                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">No new notifications</p>
                <p class="text-[10px] text-slate-450 mt-0.5">We'll alert you when something happens.</p>
            </div>

            <!-- List Container -->
            <div id="notificationItemsContainer"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('notificationDropdownContainer');
        if (!container) return;

        const bellBtn = document.getElementById('notificationBellBtn');
        const menu = document.getElementById('notificationMenu');
        const badge = document.getElementById('notificationBadge');
        const countBadge = document.getElementById('notificationCountBadge');
        const markAllBtn = document.getElementById('markAllReadBtn');
        const loader = document.getElementById('notificationLoader');
        const emptyState = document.getElementById('notificationEmptyState');
        const itemsContainer = document.getElementById('notificationItemsContainer');

        const isSeller = window.location.pathname.startsWith('/seller');
        const baseUrl = isSeller ? '/seller/notifications' : '/admin/notifications';

        const backdrop = document.getElementById('notificationBackdrop');

        function toggleMenu() {
            const isClosed = menu.classList.contains('hidden');
            if (isClosed) {
                menu.classList.remove('hidden');
                backdrop.classList.remove('hidden');
                if (window.innerWidth < 640) {
                    document.body.style.overflow = 'hidden';
                }
                fetchNotifications();
            } else {
                menu.classList.add('hidden');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Toggle dropdown open/close
        bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        // Close when clicking backdrop
        backdrop.addEventListener('click', (e) => {
            menu.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });

        // Fetch notifications list
        function fetchNotifications() {
            loader.classList.remove('hidden');
            emptyState.classList.add('hidden');
            itemsContainer.innerHTML = '';

            fetch(baseUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                updateBadge(data.unread_count);

                if (data.notifications.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    data.notifications.forEach(item => {
                        itemsContainer.appendChild(createNotificationItem(item));
                    });
                }
            })
            .catch(err => {
                loader.classList.add('hidden');
                emptyState.classList.remove('hidden');
                console.error('Error fetching notifications:', err);
            });
        }

        // Create DOM element for a single notification item
        function createNotificationItem(item) {
            const row = document.createElement('a');
            row.href = item.url || '#';
            row.className = 'flex gap-3 px-4 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors no-underline text-left cursor-pointer';

            // Determine badge colors based on notification type
            let colorClasses = 'bg-blue-500/10 text-blue-600';
            if (item.type === 'success') colorClasses = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-450';
            else if (item.type === 'warning') colorClasses = 'bg-amber-500/10 text-amber-600 dark:text-amber-450';
            else if (item.type === 'danger' || item.type === 'error') colorClasses = 'bg-rose-500/10 text-rose-600 dark:text-rose-450';
            else if (item.type === 'payout') colorClasses = 'bg-teal-500/10 text-teal-600 dark:text-teal-450';
            else if (item.type === 'order') colorClasses = 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-450';
            else if (item.type === 'product') colorClasses = 'bg-violet-500/10 text-violet-600 dark:text-violet-450';

            row.innerHTML = `
                <div class="w-8 h-8 rounded-lg ${colorClasses} flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid ${item.icon || 'fa-bell'} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">${item.title}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-normal">${item.message}</p>
                    <p class="text-[9px] text-slate-400 mt-1">${item.created_at}</p>
                </div>
            `;

            // Click listener to mark read and redirect
            row.addEventListener('click', (e) => {
                e.preventDefault();
                markAsRead(item.id, item.url);
            });

            return row;
        }

        // Mark single notification as read
        function markAsRead(id, redirectUrl) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Close the menu and backdrop
            menu.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';

            fetch(`${baseUrl}/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(() => {
                if (redirectUrl && redirectUrl !== '#') {
                    window.location.href = redirectUrl;
                } else {
                    fetchNotifications();
                }
            })
            .catch(err => {
                console.error('Error marking notification read:', err);
                if (redirectUrl && redirectUrl !== '#') {
                    window.location.href = redirectUrl;
                }
            });
        }

        // Mark all notifications as read
        markAllBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`${baseUrl}/read-all`, {
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
                    updateBadge(0);
                    fetchNotifications();
                }
            })
            .catch(err => console.error('Error marking all notifications read:', err));
        });

        // Update counts & visibility of indicators
        function updateBadge(count) {
            countBadge.innerText = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Periodically check for unread count in background (every 60 seconds)
        function checkUnreadCount() {
            fetch(baseUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                updateBadge(data.unread_count);
            })
            .catch(err => console.error('Error background checking notifications:', err));
        }

        checkUnreadCount();
        setInterval(checkUnreadCount, 60000);
    });
</script>
