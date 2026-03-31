<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Admin Dashboard'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    <!-- Icons (Lucide & Font Awesome fallback) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Custom scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e3a8a;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #60a5fa;
        }
        
        /* Smooth transitions for active states */
        .nav-item-transition {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Alpine.js x-cloak */
        [x-cloak] { display: none !important; }
        
        /* Active state styling for top-level links */
        .active-nav {
            background: rgba(30, 58, 138, 0.6) !important;
            background-color: #1e3a8a !important;
            color: #eff6ff !important;
            border-left: 3px solid #14b8a6 !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .active-nav i {
            color: #2dd4bf !important;
        }
        
        /* Inactive nav hover state */
        .inactive-nav {
            color: #93c5fd;
            border-left: 3px solid transparent;
        }
        
        .inactive-nav:hover {
            background-color: rgba(30, 58, 138, 0.5);
            color: #dbeafe;
        }
        
        /* Submenu link styles */
        .active-submenu {
            background: rgba(20, 184, 166, 0.15);
            color: #eff6ff;
            border-left: 2px solid #14b8a6;
        }
        
        .submenu-link {
            color: #93c5fd;
            border-left: 2px solid transparent;
        }
        
        .submenu-link:hover {
            color: #dbeafe;
            border-left-color: #3b82f6;
        }

        /* Slide down animation for dropdown */
        .slide-down-enter-active,
        .slide-down-leave-active {
            transition: all 0.25s ease-out;
            overflow-y: hidden;
        }
        .slide-down-enter-from,
        .slide-down-leave-to {
            opacity: 0;
            max-height: 0;
            transform: translateY(-8px);
        }
        .slide-down-enter-to,
        .slide-down-leave-from {
            opacity: 1;
            max-height: 400px;
            transform: translateY(0);
        }
    </style>
    
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-200 bg-slate-950 selection:bg-indigo-500/30 relative" x-data="{ sidebarOpen: false }">

    <!-- Ambient background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-600/10 blur-[120px]"></div>
        <div class="absolute top-1/2 right-0 w-80 h-80 rounded-full bg-purple-600/10 blur-[110px]"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 rounded-full bg-slate-700/10 blur-[130px]"></div>
    </div>

    <div class="min-h-full relative z-10">
        @auth
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-md lg:hidden"
             @click="sidebarOpen = false"
             style="display: none;"></div>

        <!-- Sidebar Component -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 flex flex-col w-80 transition-transform duration-300 ease-out bg-slate-900 shadow-2xl shadow-black/30 lg:translate-x-0 border-r border-slate-800/60">
            
            <!-- Sidebar Header -->
            <div class="flex items-center h-20 px-6 border-b border-slate-800/80 bg-slate-900/80">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/20">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold tracking-tight text-white uppercase hover:text-indigo-200 transition-colors">
                            Admin<span class="text-indigo-400"> Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Container -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar space-y-1.5">
                
                <!-- Management Section -->
                <div class="pt-2 pb-2">
                    <p class="px-4 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">System Control</p>
                </div>

                <!-- Units Link -->
                <a href="{{ route('admin.units.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl group {{ request()->is('admin/units*') ? 'bg-slate-800/60 text-white' : 'inactive-nav' }}">
                    <i data-lucide="building-2" class="w-5 h-5 {{ request()->is('admin/units*') ? 'text-indigo-400' : '' }}"></i>
                    <span>Units</span>
                </a>

                <!-- Users Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        clickOpen: {{ request()->is('admin/users*') ? 'true' : 'false' }},
                        hoverTimeout: null,
                        toggle() { this.clickOpen = !this.clickOpen }
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 200)"
                    class="relative">
                    
                    <button @click="toggle()" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-xl group"
                            :class="(hoverOpen || clickOpen) ? 'bg-slate-800/60 text-white' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5" :class="(hoverOpen || clickOpen) ? 'text-indigo-400' : ''"></i>
                            <span>Users</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': hoverOpen || clickOpen }"></i>
                    </button>
                    
                    <div x-show="hoverOpen || clickOpen" x-cloak x-transition:enter="slide-down-enter-active" x-transition:enter-start="slide-down-enter-from" x-transition:enter-end="slide-down-enter-to"
                         class="pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('users.create') }}" class="nav-item-transition block py-2 px-3 text-sm rounded-lg {{ request()->routeIs('users.create') ? 'active-submenu' : 'submenu-link' }}">Add User</a>
                        <a href="{{ route('users.index') }}" class="nav-item-transition block py-2 px-3 text-sm rounded-lg {{ request()->routeIs('users.index') ? 'active-submenu' : 'submenu-link' }}">View/Edit Users</a>
                    </div>
                </div>

                <!-- Seats Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        clickOpen: {{ request()->is('admin/seats*') ? 'true' : 'false' }},
                        hoverTimeout: null,
                        toggle() { this.clickOpen = !this.clickOpen }
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 200)"
                    class="relative">
                    
                    <button @click="toggle()" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-xl group"
                            :class="(hoverOpen || clickOpen) ? 'bg-slate-800/60 text-white' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="layout" class="w-5 h-5" :class="(hoverOpen || clickOpen) ? 'text-indigo-400' : ''"></i>
                            <span>Seats</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': hoverOpen || clickOpen }"></i>
                    </button>
                    
                    <div x-show="hoverOpen || clickOpen" x-cloak x-transition:enter="slide-down-enter-active" x-transition:enter-start="slide-down-enter-from" x-transition:enter-end="slide-down-enter-to"
                         class="pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('admin.seats.create') }}" class="nav-item-transition block py-2 px-3 text-sm rounded-lg {{ request()->routeIs('admin.seats.create') ? 'active-submenu' : 'submenu-link' }}">Add Seat</a>
                        <a href="{{ route('admin.seats.index') }}" class="nav-item-transition block py-2 px-3 text-sm rounded-lg {{ request()->routeIs('admin.seats.index') ? 'active-submenu' : 'submenu-link' }}">View/Edit Seats</a>
                    </div>
                </div>

                <div class="pt-6 pb-2">
                    <p class="px-4 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Operations</p>
                </div>

                <!-- Petitions Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        clickOpen: {{ request()->routeIs('petitions.*') && !request()->routeIs('petitions.reports') ? 'true' : 'false' }},
                        hoverTimeout: null,
                        toggle() { this.clickOpen = !this.clickOpen }
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 200)"
                    class="relative">
                    
                    <button @click="toggle()" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-xl group"
                            :class="(hoverOpen || clickOpen) ? 'bg-slate-800/60 text-white' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-text" class="w-5 h-5" :class="(hoverOpen || clickOpen) ? 'text-indigo-400' : ''"></i>
                            <span>Petitions</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': hoverOpen || clickOpen }"></i>
                    </button>
                    
                    <div x-show="hoverOpen || clickOpen" x-cloak x-transition:enter="slide-down-enter-active" x-transition:enter-start="slide-down-enter-from" x-transition:enter-end="slide-down-enter-to"
                         class="pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('petitions.index') }}" class="nav-item-transition block py-2 px-3 text-sm rounded-lg {{ request()->routeIs('petitions.index') ? 'active-submenu' : 'submenu-link' }}">View Petitions</a>
                    </div>
                </div>

                <!-- Reports -->
                <a href="{{ route('petitions.reports') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl group {{ request()->routeIs('petitions.reports') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 transition-colors"></i>
                    <span>Reports</span>
                </a>

                <!-- Recycle Bin -->
                <a href="{{ route('admin.trash.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl group {{ request()->routeIs('admin.trash.*') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="trash-2" class="w-5 h-5 text-rose-400 group-hover:text-rose-300 transition-colors"></i>
                    <span class="group-hover:text-rose-100 transition-colors">Recycle Bin</span>
                </a>

            
            </nav>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <div class="{{ Auth::check() ? 'lg:pl-80' : '' }} flex flex-col min-h-screen">
            @auth
            <!-- Top Navigation -->
             @php
                $isAdditional = Auth::check() && Auth::user()->currentSeatUser()?->is_additional;
                $headerClasses = $isAdditional 
                    ? 'bg-amber-950/60 border-amber-800/40 shadow-amber-900/20' 
                    : 'bg-slate-900/70 border-slate-800/50 shadow-sm';
            @endphp
            <header class="sticky top-0 z-30 flex items-center h-16 px-5 backdrop-blur-xl border-b transition-colors duration-300 lg:px-8 {{ $headerClasses }}">
                <button type="button" 
                        class="p-2 -ml-2 text-slate-300 rounded-lg lg:hidden hover:bg-slate-800 hover:text-white transition-colors focus:outline-none"
                        @click="sidebarOpen = true">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <div class="flex items-center justify-between flex-1 gap-x-4 lg:gap-x-6">
                    <div class="hidden lg:flex items-center gap-2 text-sm">
                        <!-- Breadcrumb removed -->
                    </div>

                    <div class="flex items-center gap-x-4 ml-auto">
                        <!-- Notifications removed -->

                        <!-- Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" 
                                    @click.away="open = false"
                                    type="button" 
                                    class="flex items-center gap-2 p-1 rounded-full hover:ring-2 hover:ring-indigo-500/40 transition-all">
                                @if(Auth::user()->profilePhoto)
                                    <img class="w-10 h-10 rounded-full border border-slate-700 shadow-sm object-cover" 
                                         src="{{ asset('storage/' . Auth::user()->profilePhoto->file_path) }}" 
                                         alt="Profile">
                                @else
                                    <img class="w-10 h-10 rounded-full border border-slate-700 shadow-sm" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin User') }}&background=4f46e5&color=fff&bold=true" 
                                         alt="Profile">
                                @endif
                                <div class="hidden lg:flex flex-col items-start gap-0.5 text-left">
                                    <span class="text-sm font-bold text-slate-200 leading-none">
                                        {{ Auth::user()->name }}
                                    </span>
                                    <span class="text-[10px] font-medium {{ $isAdditional ? 'text-amber-400' : 'text-indigo-400' }} uppercase tracking-wider leading-none flex items-center gap-1.5">
                                        {{ Auth::user()->currentSeatUser()?->seat?->seat_name ?? 'No Seat' }}
                                        @if($isAdditional)
                                            <span class="text-[8px] bg-amber-500/20 text-amber-300 px-1 py-0.5 rounded shadow-sm border border-amber-500/20">Addl. Charge</span>
                                        @endif
                                    </span>
                                </div>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-50 w-56 mt-2 origin-top-right bg-slate-800 border border-slate-700 rounded-2xl shadow-xl focus:outline-none">
                                @php
                                    $activeSeats = Auth::user()->seatUsers()->where('is_active', true)->with('seat')->get();
                                    $currentSeatId = Auth::user()->currentSeatUser()?->seat_id;
                                @endphp

                                @if($activeSeats->count() > 1)
                                <div class="py-1 border-b border-slate-700 bg-slate-800/50">
                                    <div class="px-4 py-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">
                                        Switch Seat
                                    </div>
                                    @foreach($activeSeats as $seatAssignment)
                                        @if($seatAssignment->seat_id !== $currentSeatId)
                                        <form method="POST" action="{{ route('seat.switch', $seatAssignment->seat_id) }}">
                                            @csrf
                                            <button type="submit" class="flex items-center w-full gap-3 px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors text-left group">
                                                <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-400 group-hover:rotate-180 transition-transform duration-300"></i>
                                                <span class="font-medium text-slate-200">{{ $seatAssignment->seat->seat_name }}</span>
                                            </button>
                                        </form>
                                        @else
                                        <div class="flex items-center w-full gap-3 px-4 py-2 text-sm text-white bg-slate-700/30 cursor-default relative overflow-hidden">
                                            <div class="absolute inset-y-0 left-0 w-1 bg-emerald-400"></div>
                                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                            <span class="font-medium">{{ $seatAssignment->seat->seat_name }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                                @endif

                                <!-- Email section removed -->
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors">
                                        <i data-lucide="user" class="w-4 h-4 text-indigo-400"></i>
                                        My Profile
                                    </a>
                                    <a href="{{ route('profile.password.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors">
                                        <i data-lucide="key-round" class="w-4 h-4 text-amber-400"></i>
                                        Change Password
                                    </a>
                                </div>
                                <div class="py-1 border-t border-slate-700">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full gap-3 px-4 py-2 text-sm text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors">
                                            <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            @endauth

            <!-- Main Page Content -->
            <main class="flex-1 overflow-x-hidden p-6 lg:p-8">
                <div class="mx-auto @yield('container_width', 'max-w-7xl')">
                    @yield('content')
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="py-4 text-center border-t border-slate-800/50 text-slate-500 text-xs">
                &copy; {{ date('Y') }} — Centralized Processing System of Petitions Cell
            </footer>
        </div>
    </div>

    <!-- Lucide Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        
        document.addEventListener('alpine:init', () => {
             setTimeout(() => lucide.createIcons(), 50);
        });
    </script>
    @yield('scripts')
</body>
</html>
