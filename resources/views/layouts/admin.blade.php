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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Malayalam:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Icons (Lucide & Font Awesome fallback) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --sidebar-width: 20rem;
            --transition-main: cubic-bezier(0.23, 1, 0.32, 1);
        }

        /* Custom scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(30, 58, 138, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #60a5fa;
        }
        
        /* Smooth transitions */
        .nav-item-transition {
            transition: all 0.3s var(--transition-main);
        }
        
        /* Alpine.js x-cloak */
        [x-cloak] { display: none !important; }

        /* Glassmorphism effects */
        .glass-sidebar {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-header {
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Modern Active state styling */
        .active-nav {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 0%, transparent 100%) !important;
            color: #ffffff !important;
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            box-shadow: inset 1px 0 0 0 #6366f1;
        }
        
        .active-nav::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #6366f1;
            box-shadow: 0 0 15px 2px rgba(99, 102, 241, 0.8);
            border-radius: 4px;
        }

        .active-nav i {
            color: #818cf8 !important;
            filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.6));
            animation: pulse-icon 2s infinite;
        }
        
        @keyframes pulse-icon {
            0% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(129, 140, 248, 0.4)); }
            50% { transform: scale(1.1); filter: drop-shadow(0 0 10px rgba(129, 140, 248, 0.8)); }
            100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(129, 140, 248, 0.4)); }
        }
        
        /* Modern Inactive nav hover state */
        .inactive-nav {
            color: #94a3b8;
            border-radius: 0.75rem;
            position: relative;
        }
        
        .inactive-nav::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: #6366f1;
            opacity: 0;
            transition: all 0.3s var(--transition-main);
            border-radius: 4px;
        }
        
        .inactive-nav:hover {
            background-color: rgba(255, 255, 255, 0.04);
            color: #f8fafc;
            transform: translateX(6px);
        }

        .inactive-nav:hover::before {
            opacity: 1;
            top: 10%;
            bottom: 10%;
            box-shadow: 0 0 8px rgba(99, 102, 241, 0.5);
        }

        .inactive-nav:hover i {
            color: #818cf8 !important;
            transform: scale(1.15) rotate(5deg);
            transition: all 0.3s var(--transition-main);
        }
        
        /* Modern Submenu link styles */
        .active-submenu {
            background: linear-gradient(90deg, rgba(20, 184, 166, 0.1) 0%, transparent 100%) !important;
            color: #5eead4 !important;
            font-weight: 600 !important;
            position: relative;
        }
        
        .active-submenu::before {
            content: '';
            position: absolute;
            left: 0;
            top: 25%;
            bottom: 25%;
            width: 2px;
            background: #2dd4bf;
            box-shadow: 0 0 8px rgba(45, 212, 191, 0.8);
            border-radius: 2px;
        }
        
        .active-submenu .submenu-indicator {
            opacity: 1 !important;
            transform: scale(1.2);
            box-shadow: 0 0 8px rgba(94, 234, 212, 0.6);
        }
        
        .submenu-link {
            color: #94a3b8;
            transition: all 0.2s var(--transition-main);
            position: relative;
        }
        
        .submenu-link:hover {
            color: #f8fafc;
            transform: translateX(4px);
            background-color: rgba(255, 255, 255, 0.02);
        }

        .submenu-link:hover .submenu-indicator {
            opacity: 0.5;
            background-color: #5eead4;
        }
        
        /* Submenu container enhancement */
        .submenu-container {
            position: relative;
        }
        
        .submenu-container::before {
            content: '';
            position: absolute;
            left: 27px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: linear-gradient(to bottom, 
                rgba(99, 102, 241, 0.5), 
                rgba(99, 102, 241, 0.1) 50%,
                transparent);
        }

        /* Slide down animation with scale effect */
        .slide-down-enter-active,
        .slide-down-leave-active {
            transition: all 0.4s var(--transition-main);
            overflow: hidden;
        }
        .slide-down-enter-from,
        .slide-down-leave-to {
            opacity: 0;
            max-height: 0;
            transform: translateY(-10px) scale(0.98);
        }
        .slide-down-enter-to,
        .slide-down-leave-from {
            opacity: 1;
            max-height: 500px;
            transform: translateY(0) scale(1);
        }
        
        /* Active indicator dot */
        .submenu-indicator {
            display: inline-block;
            width: 5px;
            height: 5px;
            background-color: #475569;
            border-radius: 50%;
            margin-right: 12px;
            opacity: 0.3;
            transition: all 0.3s var(--transition-main);
        }
        
        /* Parent and Open states */
        .menu-open {
            background: rgba(255, 255, 255, 0.02) !important;
            color: #f8fafc !important;
        }
        
        /* Profile Dropdown Professional Polish */
        .profile-dropdown-menu {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }

        /* Hover animation for icons */
        .icon-bounce {
            transition: transform 0.3s var(--transition-main);
        }
        .group:hover .icon-bounce {
            transform: translateY(-2px);
        }
    </style>
    
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-200 bg-slate-950 selection:bg-indigo-500/30 relative" x-data="{ 
        sidebarOpen: false,
        usersOpen: {{ request()->routeIs('users.*') ? 'true' : 'false' }},
        seatsOpen: {{ request()->routeIs('admin.seats.*') && !request()->routeIs('admin.seats.statistics') ? 'true' : 'false' }},
        petitionsOpen: {{ request()->routeIs('petitions.*') && !request()->routeIs('petitions.reports') ? 'true' : 'false' }}
    }">

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
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 transition-transform duration-300 ease-out bg-[#0f172a] border-r border-slate-800 shadow-xl">
            
            <!-- Sidebar Header -->
            <div class="flex items-center h-16 px-6 border-b border-slate-800/80 bg-[#0f172a]">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 shadow-lg shadow-teal-500/20 flex-shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="text-[22px] tracking-tight text-white font-extrabold leading-none flex items-center">
                            ADMIN <span class="text-teal-400 font-black ml-1">DASHBOARD</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Container -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar space-y-1.5">
                
                <!-- Management Section -->
                @if(Auth::user()->canAccess('view units') || Auth::user()->canAccess('view users') || Auth::user()->canAccess('access admin dashboard') || Auth::user()->canAccess('view seats'))
                <div class="pt-2 pb-2">
                    <p class="px-4 text-xs font-bold tracking-wider text-blue-500 uppercase">System Control</p>
                </div>
                @endif

                @if(Auth::user()->canAccess('view units'))
                <!-- Units Link -->
                <a href="{{ route('admin.units.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('admin.units.*') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="building-2" class="w-5 h-5 icon-bounce {{ request()->routeIs('admin.units.*') ? 'text-indigo-400' : '' }}"></i>
                    <span>Units</span>
                </a>
                @endif

                @if(Auth::user()->canAccess('view departments'))
                <!-- Departments Link -->
                <a href="{{ route('admin.departments.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('admin.departments.*') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="building" class="w-5 h-5 icon-bounce {{ request()->routeIs('admin.departments.*') ? 'text-indigo-400' : '' }}"></i>
                    <span>Departments</span>
                </a>
                @endif

                @if(Auth::user()->canAccess('view designations'))
                <!-- Designations Link -->
                <a href="{{ route('admin.designations.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('admin.designations.*') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="briefcase" class="w-5 h-5 icon-bounce {{ request()->routeIs('admin.designations.*') ? 'text-indigo-400' : '' }}"></i>
                    <span>Designations</span>
                </a>
                @endif

                @if(Auth::user()->canAccess('view users'))
                <!-- Users Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        hoverTimeout: null
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 300)"
                    class="relative">
                    
                    <button @click="usersOpen = !usersOpen" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-[15px] font-medium rounded-xl group"
                            :class="(usersOpen || hoverOpen) ? 'menu-open' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5 icon-bounce" :class="(usersOpen || hoverOpen) || {{ request()->routeIs('users.*') ? 'true' : 'false' }} ? 'text-indigo-400' : ''"></i>
                            <span>Users</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': usersOpen || hoverOpen }"></i>
                    </button>
                    
                    <div x-show="usersOpen || hoverOpen" 
                         x-cloak 
                         x-transition:enter="slide-down-enter-active" 
                         x-transition:enter-start="slide-down-enter-from" 
                         x-transition:enter-end="slide-down-enter-to"
                         x-transition:leave="slide-down-leave-active"
                         x-transition:leave-start="slide-down-leave-from"
                         x-transition:leave-end="slide-down-leave-to"
                         class="submenu-container pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('users.create') }}" 
                           @click="usersOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('users.create') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="user-plus" class="w-4 h-4 mr-2 {{ request()->routeIs('users.create') ? 'text-teal-400' : '' }}"></i>
                            <span>Add User</span>
                        </a>
                        <a href="{{ route('users.index') }}" 
                           @click="usersOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('users.index') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="users" class="w-4 h-4 mr-2 {{ request()->routeIs('users.index') ? 'text-teal-400' : '' }}"></i>
                            <span>View/Edit Users</span>
                        </a>
                    </div>
                </div>
                @endif

                @if(Auth::user()->canAccess('access admin dashboard'))
                <!-- Roles & Permissions Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        hoverTimeout: null,
                        rolesOpen: {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }}
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 300)"
                    class="relative">
                    
                    <button @click="rolesOpen = !rolesOpen" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-[15px] font-medium rounded-xl group"
                            :class="(rolesOpen || hoverOpen) ? 'menu-open' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="shield-check" class="w-5 h-5 icon-bounce" :class="(rolesOpen || hoverOpen) || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'text-indigo-400' : ''"></i>
                            <span>Roles & Permissions</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': rolesOpen || hoverOpen }"></i>
                    </button>
                    
                    <div x-show="rolesOpen || hoverOpen" 
                         x-cloak 
                         x-transition:enter="slide-down-enter-active" 
                         x-transition:enter-start="slide-down-enter-from" 
                         x-transition:enter-end="slide-down-enter-to"
                         x-transition:leave="slide-down-leave-active"
                         x-transition:leave-start="slide-down-leave-from"
                         x-transition:leave-end="slide-down-leave-to"
                         class="submenu-container pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('admin.roles.index') }}" 
                           @click="rolesOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.roles.*') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="shield" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.roles.*') ? 'text-teal-400' : '' }}"></i>
                            <span>Roles</span>
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" 
                           @click="rolesOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.permissions.*') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="key" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.permissions.*') ? 'text-teal-400' : '' }}"></i>
                            <span>Permissions</span>
                        </a>
                    </div>
                </div>
                @endif

                @if(Auth::user()->canAccess('view seats'))
                <!-- Seats Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        hoverTimeout: null,
                        seatsOpen: {{ request()->routeIs('admin.seats.*') && !request()->routeIs('admin.seats.statistics') ? 'true' : 'false' }}
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 300)"
                    class="relative">
                    
                    <button @click="seatsOpen = !seatsOpen" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-[15px] font-medium rounded-xl group"
                            :class="(seatsOpen || hoverOpen) ? 'menu-open' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="layout" class="w-5 h-5 icon-bounce" :class="(seatsOpen || hoverOpen) || {{ request()->routeIs('admin.seats.*') && !request()->routeIs('admin.seats.statistics') ? 'true' : 'false' }} ? 'text-indigo-400' : ''"></i>
                            <span>Seats</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': seatsOpen || hoverOpen }"></i>
                    </button>
                    
                    <div x-show="seatsOpen || hoverOpen" 
                         x-cloak 
                         x-transition:enter="slide-down-enter-active" 
                         x-transition:enter-start="slide-down-enter-from" 
                         x-transition:enter-end="slide-down-enter-to"
                         x-transition:leave="slide-down-leave-active"
                         x-transition:leave-start="slide-down-leave-from"
                         x-transition:leave-end="slide-down-leave-to"
                         class="submenu-container pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('admin.seats.create') }}" 
                           @click="seatsOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.seats.create') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="plus-square" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.seats.create') ? 'text-teal-400' : '' }}"></i>
                            <span>Add Seat</span>
                        </a>
                        <a href="{{ route('admin.seats.index') }}" 
                           @click="seatsOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.seats.index') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="layout-grid" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.seats.index') ? 'text-teal-400' : '' }}"></i>
                            <span>View/Edit Seats</span>
                        </a>
                    </div>
                </div>
                @endif

                @if(Auth::user()->canAccess('view petitions') || Auth::user()->canAccess('view master reports') || Auth::user()->canAccess('view seat diagnostics') || Auth::user()->canAccess('view recycle bin') || Auth::user()->canAccess('inward_form'))
                <div class="pt-6 pb-2">
                    <p class="px-4 text-xs font-bold tracking-wider text-blue-500 uppercase">Operations</p>
                </div>
                @endif


                @if(Auth::user()->canAccess('view petitions'))
                <!-- View Petitions - Direct Link -->
                <a href="{{ route('petitions.index', ['tab' => 'inward']) }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('petitions.index') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="file-text" class="w-5 h-5 icon-bounce {{ request()->routeIs('petitions.index') ? 'text-indigo-400' : '' }}"></i>
                    <span>View Petitions</span>
                </a>
                @endif

                @if(Auth::user()->canAccess('view master reports'))
                <!-- Reports -->
                <a href="{{ route('petitions.reports') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('petitions.reports') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 icon-bounce transition-colors"></i>
                    <span>Master Reports</span>
                </a>
                @endif

                @if(Auth::user()->canAccess('view seat diagnostics'))
                <!-- Statistical Reports Dropdown -->
                <div x-data="{ 
                        hoverOpen: false,
                        hoverTimeout: null,
                        reportsOpen: {{ request()->routeIs('admin.seats.statistics') || request()->routeIs('admin.departments.analysis') || request()->routeIs('inward.statistics') ? 'true' : 'false' }}
                    }" 
                    @mouseenter="hoverOpen = true; clearTimeout(hoverTimeout)"
                    @mouseleave="hoverTimeout = setTimeout(() => { hoverOpen = false }, 300)"
                    class="relative">
                    
                    <button @click="reportsOpen = !reportsOpen" 
                            class="nav-item-transition flex items-center justify-between w-full px-4 py-3 text-[15px] font-medium rounded-xl group"
                            :class="(reportsOpen || hoverOpen) ? 'menu-open' : 'inactive-nav'">
                        <div class="flex items-center gap-3">
                            <i data-lucide="pie-chart" class="w-5 h-5 icon-bounce" :class="(reportsOpen || hoverOpen) || request()->routeIs('admin.seats.statistics') || request()->routeIs('admin.departments.analysis') || request()->routeIs('inward.statistics') ? 'text-indigo-400' : ''"></i>
                            <span>Statistical Reports</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': reportsOpen || hoverOpen }"></i>
                    </button>
                    
                    <div x-show="reportsOpen || hoverOpen" 
                         x-cloak 
                         x-transition:enter="slide-down-enter-active" 
                         x-transition:enter-start="slide-down-enter-from" 
                         x-transition:enter-end="slide-down-enter-to"
                         x-transition:leave="slide-down-leave-active"
                         x-transition:leave-start="slide-down-leave-from"
                         x-transition:leave-end="slide-down-leave-to"
                         class="submenu-container pl-11 pr-2 mt-1 space-y-1 overflow-hidden">
                        
                        <a href="{{ route('admin.seats.statistics') }}" 
                           @click="reportsOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.seats.statistics') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="laptop" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.seats.statistics') ? 'text-teal-400' : '' }}"></i>
                            <span>Seat Diagnostics</span>
                        </a>
                        
                        <a href="{{ route('admin.departments.analysis') }}" 
                           @click="reportsOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('admin.departments.analysis') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="building-2" class="w-4 h-4 mr-2 {{ request()->routeIs('admin.departments.analysis') ? 'text-teal-400' : '' }}"></i>
                            <span>Department Wise</span>
                        </a>

                        @if(Auth::user()->canAccess('inward_statistics') || Auth::user()->canAccess('inward statistics') || Auth::user()->canAccess('access admin dashboard'))
                        <a href="{{ route('inward.statistics') }}" 
                           @click="reportsOpen = true"
                           class="nav-item-transition flex items-center py-2.5 px-3 text-[15px] rounded-lg {{ request()->routeIs('inward.statistics') ? 'active-submenu' : 'submenu-link' }}">
                            <span class="submenu-indicator"></span>
                            <i data-lucide="clock" class="w-4 h-4 mr-2 {{ request()->routeIs('inward.statistics') ? 'text-teal-400' : '' }}"></i>
                            <span>Pendency Details</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if(Auth::user()->canAccess('view recycle bin'))
                <!-- Recycle Bin -->
                <a href="{{ route('admin.trash.index') }}" 
                   class="nav-item-transition flex items-center gap-3 px-4 py-3 text-[15px] font-medium rounded-xl group {{ request()->routeIs('admin.trash.*') ? 'active-nav' : 'inactive-nav' }}">
                    <i data-lucide="trash-2" class="w-5 h-5 icon-bounce text-rose-400 group-hover:text-rose-300 transition-colors"></i>
                    <span class="group-hover:text-rose-100 transition-colors">Recycle Bin</span>
                </a>
                @endif

            
            </nav>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <div class="{{ Auth::check() ? 'lg:pl-72' : '' }} flex flex-col min-h-screen transition-all duration-300">
            @auth
            <!-- Top Navigation -->
            <header class="sticky top-0 z-30 flex items-center h-16 px-5 lg:px-8 bg-[#0f172a] border-b border-slate-800 transition-all duration-300 shadow-sm">
                <button type="button" 
                        class="p-2 -ml-2 text-slate-300 rounded-xl lg:hidden hover:bg-slate-800 hover:text-white transition-colors focus:outline-none"
                        @click="sidebarOpen = true">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <div class="flex items-center justify-between flex-1 gap-x-4 lg:gap-x-6">
                    <div class="hidden lg:flex items-center gap-2 text-sm">
                        <!-- Breadcrumb removed -->
                    </div>

                    <div class="flex items-center gap-x-4 ml-auto">
                        @php
                            $myAdditionalSeats = Auth::user()->seatUsers()->where('is_active', true)->where('is_additional', true)->with('seat')->get();
                        @endphp
                        @if($myAdditionalSeats->count() > 0)
                            <div class="mr-2">
                                <form method="POST" action="{{ route('seat.switch', $myAdditionalSeats->first()->seat_id) }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-all text-xs font-bold shadow-lg shadow-indigo-500/20 group">
                                        <i data-lucide="user-cog" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i>
                                        <span class="hidden md:inline">Switch to My Seat</span>
                                        <span class="md:hidden">User View</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                        <!-- Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" 
                                    @click.away="open = false"
                                    type="button" 
                                    class="flex items-center gap-3 pl-1.5 pr-4 py-1.5 rounded-full bg-slate-800/60 border border-slate-700/50 hover:bg-slate-700/80 hover:border-slate-600 transition-all shadow-sm">
                                @php
                                    $hasPhoto = Auth::user()->profilePhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->profilePhoto->file_path);
                                    $isAdditional = Auth::check() && Auth::user()->currentSeatUser()?->is_additional;
                                @endphp
                                @if($hasPhoto)
                                    <img class="w-10 h-10 rounded-full border border-slate-700 shadow-sm object-cover" 
                                         src="{{ asset('storage/' . Auth::user()->profilePhoto->file_path) }}" 
                                         alt="Profile">
                                @else
                                    <div class="w-10 h-10 rounded-full border border-slate-700 shadow-sm bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="hidden lg:flex flex-col items-start gap-0.5 text-left">
                                    <span class="text-[15px] font-bold text-slate-200 leading-none">
                                        {{ Auth::user()->name }}
                                    </span>
                                    <span class="text-[11px] font-bold {{ $isAdditional ? 'text-amber-400' : 'text-indigo-400' }} uppercase tracking-wider leading-none flex items-center gap-1.5">
                                        {{ Auth::user()->currentSeatUser()?->seat?->seat_name ?? 'No Seat' }}
                                        @if($isAdditional)
                                            <span class="text-[9px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded shadow-sm border border-amber-500/20">Addl. Charge</span>
                                        @endif
                                    </span>
                                </div>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-50 w-56 mt-2 origin-top-right profile-dropdown-menu rounded-2xl shadow-xl focus:outline-none overflow-hidden">
                                @php
                                    $activeSeats = Auth::user()->seatUsers()->where('is_active', true)->with('seat')->get();
                                    $currentSeatId = Auth::user()->currentSeatUser()?->seat_id;
                                @endphp

                                @if(!Auth::user()->canAccess('access admin dashboard') && $activeSeats->count() > 1)
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
                <div class="mx-auto @yield('container_width', 'w-full')">
                    @yield('content')
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="py-6 text-center border-t border-slate-800/50 text-slate-400 text-xs">
                <div class="mb-2 uppercase tracking-widest opacity-80">
                    &copy; {{ date('Y') }} — Centralized Processing System of Petitions Cell
                </div>
                <div class="text-[10px] text-slate-500 font-medium">
                    Designed by <span class="text-indigo-400">Software Development Division</span>, 
                    <span class="text-indigo-400">VACB Directorate</span>, Kerala
                </div>
            </footer>
        </div>
    </div>

    <!-- Lucide Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        
        // Handle re-rendering for Alpine.js dynamic components if needed
        document.addEventListener('alpine:initialized', () => {
            // Only run if icons aren't already created
            lucide.createIcons();
        });
        // Global date picker trigger
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'INPUT' && e.target.type === 'date') {
                try { e.target.showPicker(); } catch(err) {}
            } else {
                let wrapper = e.target.closest('div');
                if (wrapper && wrapper.children.length > 0) {
                    for (let i = 0; i < wrapper.children.length; i++) {
                        if (wrapper.children[i].tagName === 'INPUT' && wrapper.children[i].type === 'date') {
                            try { wrapper.children[i].showPicker(); } catch(err) {}
                            break;
                        }
                    }
                }
            }
        });

        // Global file type validation
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'INPUT' && e.target.type === 'file') {
                const input = e.target;
                const accept = input.getAttribute('accept');
                if (!accept) return;

                const allowedExtensions = accept.split(',').map(ext => ext.trim().replace('.', '').toLowerCase());
                const errorId = (input.id || input.name) + '_file_error';
                let errorElement = document.getElementById(errorId);

                if (!errorElement) {
                    errorElement = document.createElement('p');
                    errorElement.id = errorId;
                    errorElement.className = 'text-xs font-medium text-rose-500 mt-1 file-validation-error';
                    input.parentNode.appendChild(errorElement);
                }
                
                errorElement.innerText = '';
                
                if (input.files.length > 0) {
                    let hasError = false;
                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];
                        const ext = file.name.split('.').pop().toLowerCase();
                        
                        if (accept.includes('image/*')) {
                            if (!['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                errorElement.innerText = 'Invalid file type. Only image files are allowed.';
                                hasError = true;
                                break;
                            }
                        } else if (!allowedExtensions.includes(ext)) {
                            const allowedTypes = allowedExtensions.map(e => e.toUpperCase()).join(', ');
                            errorElement.innerText = 'Invalid file type. Only ' + allowedTypes + ' are allowed.';
                            hasError = true;
                            break;
                        }
                    }
                    if (hasError) {
                        input.value = '';
                    }
                }
            }
        });
    </script>
    @yield('scripts')
    @include('sweetalert::alert')
    @include('partials.sweetalert-session')
</body>
</html>
