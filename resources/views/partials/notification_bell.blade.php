<div x-data="notificationBell()" x-init="init()" class="relative">
    <!-- Notification Bell Trigger Button -->
    <button @click="toggleDropdown()" 
            type="button"
            class="relative flex items-center justify-center w-10 h-10 rounded-full bg-slate-800/60 border border-slate-700/50 hover:bg-slate-700/80 hover:border-slate-600 text-slate-300 hover:text-white transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500/30 group"
            title="Notifications"
            aria-label="Notifications">
        <i data-lucide="bell" class="w-5 h-5 transition-transform duration-200 group-hover:rotate-12" :class="{ 'text-teal-400': unreadCount > 0 }"></i>

        <!-- Animated Pulse Dot & Badge when unread -->
        <span x-show="unreadCount > 0" x-cloak class="absolute -top-1 -right-1 flex h-5 min-w-[20px] px-1 items-center justify-center">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex items-center justify-center rounded-full h-5 min-w-[20px] px-1 text-[10px] font-black leading-none bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-sm border border-slate-900"
                  x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
        </span>
    </button>

    <!-- Notification Dropdown Panel -->
    <div x-show="open" 
         x-cloak 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2" 
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0" 
         x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-2xl bg-slate-900/95 backdrop-blur-xl border border-slate-700/60 shadow-2xl shadow-black/60 overflow-hidden divide-y divide-slate-800">
        
        <!-- Header -->
        <div class="px-4 py-3 bg-slate-950/40 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">
                        Notifications
                    </h3>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <template x-if="unreadCount > 0">
                    <button @click="markAllRead()" 
                            type="button" 
                            class="text-[11px] font-bold text-teal-400 hover:text-teal-300 hover:underline transition-colors flex items-center gap-1">
                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                        Mark all read
                    </button>
                </template>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="px-4 py-2 bg-slate-950/20 flex items-center gap-2 text-xs">
            <button @click="setTab('all')" 
                    type="button"
                    class="px-3 py-1 rounded-lg font-bold transition-all"
                    :class="activeTab === 'all' ? 'bg-slate-800 text-teal-400 border border-slate-700/80 shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                All
            </button>
            <button @click="setTab('unread')" 
                    type="button"
                    class="px-3 py-1 rounded-lg font-bold transition-all flex items-center gap-1.5"
                    :class="activeTab === 'unread' ? 'bg-slate-800 text-teal-400 border border-slate-700/80 shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                <span>Unread</span>
                <template x-if="unreadCount > 0">
                    <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-black rounded-full bg-rose-500 text-white" x-text="unreadCount"></span>
                </template>
            </button>
        </div>

        <!-- Notification List Area -->
        <div class="max-h-96 overflow-y-auto custom-scrollbar divide-y divide-slate-800/60 bg-slate-900/50">
            <!-- Loading State -->
            <div x-show="loading" class="py-8 flex flex-col items-center justify-center gap-2 text-slate-400">
                <i data-lucide="loader-2" class="w-5 h-5 animate-spin text-teal-400"></i>
                <span class="text-xs font-medium">Checking notifications...</span>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" class="py-10 px-4 flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-800/60 border border-slate-700/40 flex items-center justify-center text-slate-500 mb-2.5">
                    <i data-lucide="bell-off" class="w-6 h-6"></i>
                </div>
                <p class="text-sm font-bold text-slate-300">All caught up!</p>
                <p class="text-xs text-slate-400 mt-0.5">No new notifications</p>
            </div>

            <!-- Notification Items -->
            <template x-for="item in notifications" :key="item.id">
                <div @click="handleItemClick(item)"
                     class="group relative p-3.5 hover:bg-slate-800/40 transition-all cursor-pointer flex items-start gap-3"
                     :class="{ 'bg-slate-800/25 border-l-2 border-teal-400': !item.is_read }">
                    
                    <!-- Icon Bubble -->
                    <div class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs mt-0.5 border shadow-inner"
                         :class="{
                            'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': item.color === 'indigo',
                            'bg-rose-500/10 text-rose-400 border-rose-500/20': item.color === 'rose',
                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': item.color === 'emerald',
                            'bg-teal-500/10 text-teal-400 border-teal-500/20': item.color === 'teal',
                            'bg-amber-500/10 text-amber-400 border-amber-500/20': item.color === 'amber',
                         }">
                        <template x-if="item.icon === 'file-input'"><i data-lucide="file-input" class="w-4 h-4"></i></template>
                        <template x-if="item.icon === 'undo-2'"><i data-lucide="undo-2" class="w-4 h-4"></i></template>
                        <template x-if="item.icon === 'file-check'"><i data-lucide="file-check" class="w-4 h-4"></i></template>
                        <template x-if="item.icon === 'scale'"><i data-lucide="scale" class="w-4 h-4"></i></template>
                        <template x-if="item.icon === 'bell' || !['file-input','undo-2','file-check','scale'].includes(item.icon)"><i data-lucide="bell" class="w-4 h-4"></i></template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1">
                            <h4 class="text-xs font-bold text-slate-200 group-hover:text-teal-300 transition-colors leading-tight truncate" x-text="item.title"></h4>
                            <span class="text-[10px] font-semibold text-slate-400 whitespace-nowrap shrink-0" x-text="item.time_ago"></span>
                        </div>
                        <p class="text-[11px] text-slate-300 mt-1 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                    </div>

                    <!-- Action buttons on hover -->
                    <div class="shrink-0 flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <template x-if="!item.is_read">
                            <button @click.stop="markRead(item.id)" 
                                    type="button" 
                                    class="p-1 text-slate-400 hover:text-teal-400 rounded transition-colors"
                                    title="Mark as read">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-2 bg-slate-950/60 text-center">
            <span class="text-[10px] text-slate-400 font-medium">
                Auto-refreshes in real-time
            </span>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],
        activeTab: 'all',
        loading: false,
        pollInterval: null,

        init() {
            this.fetchCount();

            // Background polling every 20 seconds
            this.pollInterval = setInterval(() => {
                this.fetchCount();
            }, 20000);

            // Immediate refresh when tab regains focus
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.fetchCount();
                }
            });
        },

        fetchCount() {
            fetch('{{ route('notifications.unread_count') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.unreadCount = data.unread_count || 0;
            })
            .catch(() => {});
        },

        toggleDropdown() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        setTab(tab) {
            this.activeTab = tab;
            this.fetchNotifications();
        },

        fetchNotifications() {
            this.loading = true;
            fetch(`{{ route('notifications.index') }}?filter=${this.activeTab}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count ?? this.unreadCount;
                this.loading = false;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            })
            .catch(() => {
                this.loading = false;
            });
        },

        markRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(() => {
                const target = this.notifications.find(n => n.id === id);
                if (target && !target.is_read) {
                    target.is_read = true;
                    if (this.unreadCount > 0) this.unreadCount--;
                }
            })
            .catch(() => {});
        },

        markAllRead() {
            fetch('{{ route('notifications.mark_all_read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(() => {
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
            })
            .catch(() => {});
        },

        handleItemClick(item) {
            if (!item.is_read) {
                this.markRead(item.id);
            }
            if (item.link) {
                window.location.href = item.link;
            }
        }
    };
}
</script>

