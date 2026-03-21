<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Admin Dashboard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@lucide/icons"></script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-900" x-data="{ sidebarOpen: false }">

    <div class="min-h-full">
        @auth
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 transition-transform duration-300 ease-in-out bg-slate-900 lg:translate-x-0">
            
            <!-- Sidebar Header -->
            <div class="flex items-center h-20 px-8 bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary shadow-lg shadow-primary/20">
                        <i data-lucide="layout-dashboard" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white uppercase">CPSP<span class="text-primary">Admin</span></span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Dashboard
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold tracking-wider text-slate-500 uppercase">Management</p>
                </div>

                <!-- Units -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium transition-colors text-slate-400 rounded-xl hover:text-white hover:bg-white/5 group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="building-2" class="w-5 h-5 transition-colors group-hover:text-primary"></i>
                            Units
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="pl-12 pr-4 mt-1 space-y-1">
                        <a href="{{ route('admin.units.create') }}" class="block py-2 text-sm text-slate-400 transition-colors hover:text-white">Add Unit</a>
                        <a href="{{ route('admin.units.index') }}" class="block py-2 text-sm text-slate-400 transition-colors hover:text-white">View Units</a>
                    </div>
                </div>

                <!-- Users -->
                <div x-data="{ open: {{ request()->is('admin/users*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium transition-colors text-slate-400 rounded-xl hover:text-white hover:bg-white/5 group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5 transition-colors group-hover:text-primary"></i>
                            Users
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="pl-12 pr-4 mt-1 space-y-1">
                        <a href="{{ route('users.create') }}" class="block py-2 text-sm {{ request()->routeIs('users.create') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Add User</a>
                        <a href="{{ route('users.index') }}" class="block py-2 text-sm {{ request()->routeIs('users.index') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">View/Edit Users</a>
                    </div>
                </div>

                         <!--Seats-->
                <div x-data="{ open: {{ request()->is('admin/seats*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium transition-colors text-slate-400 rounded-xl hover:text-white hover:bg-white/5 group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="seat" class="w-5 h-5 transition-colors group-hover:text-primary"></i>
                            Seats
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="pl-12 pr-4 mt-1 space-y-1">
                        <a href="{{ route('admin.seats.create') }}" class="block py-2 text-sm {{ request()->routeIs('admin.seats.create') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Add Seat</a>
                        <a href="{{ route('admin.seats.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.seats.index') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">View/Edit Seats</a>
                        <a href="{{ route('admin.seatuser.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.seatuser.index') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Assign Seats</a>
                    </div>
                </div>

                <!-- Petitions -->
                <div x-data="{ open: {{ request()->is('petitions*') || request()->is('admin/petitions*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium transition-colors text-slate-400 rounded-xl hover:text-white hover:bg-white/5 group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-text" class="w-5 h-5 transition-colors group-hover:text-primary"></i>
                            Petitions
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="pl-12 pr-4 mt-1 space-y-1">
                        <a href="{{ route('petitions.index') }}" class="block py-2 text-sm {{ request()->routeIs('petitions.index') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">All Petitions</a>
                        <a href="{{ route('admin.petitions.forwarded') }}" class="block py-2 text-sm {{ request()->routeIs('admin.petitions.forwarded') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Forwarded to Units</a>
                        <a href="{{ route('admin.petitions.vrs') }}" class="block py-2 text-sm {{ request()->routeIs('admin.petitions.vrs') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Verification Reports</a>
                        <a href="{{ route('admin.petitions.decisions') }}" class="block py-2 text-sm {{ request()->routeIs('admin.petitions.decisions') ? 'text-primary' : 'text-slate-400' }} transition-colors hover:text-white">Final Decisions</a>
                    </div>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-800">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-400 transition-colors rounded-xl hover:text-white hover:bg-white/5">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        Settings
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-6 mt-auto">
                <div class="px-4 py-4 rounded-2xl bg-slate-800/50 border border-slate-700/50">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-xs font-medium text-slate-400">System Online</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-500">Last backup: 2 mins ago</p>
                </div>
            </div>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <div class="{{ Auth::check() ? 'lg:pl-72' : '' }} flex flex-col min-h-screen">
            @auth
            <!-- Top Navigation -->
            <header class="sticky top-0 z-30 flex items-center h-20 px-4 bg-white/80 backdrop-blur-md border-b border-slate-200 sm:px-6 lg:px-8">
                <!-- Mobile Mobile Toggle -->
                <button type="button" 
                        class="p-2 -ml-2 text-slate-500 lg:hidden hover:text-slate-600 focus:outline-none"
                        @click="sidebarOpen = true">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>

                <div class="flex items-center justify-between flex-1 gap-x-4 lg:gap-x-6">
                    <!-- Search -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <input type="text" 
                               class="block w-full py-2 pl-10 pr-3 text-sm border-0 rounded-xl bg-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none" 
                               placeholder="Search anything...">
                    </div>

                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <button type="button" class="p-2 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none relative">
                            <i data-lucide="bell" class="w-6 h-6"></i>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>

                        <div class="w-px h-6 bg-slate-200"></div>

                        <!-- User Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" 
                                    @click.away="open = false"
                                    type="button" 
                                    class="flex items-center gap-3 p-1 transition-all rounded-full hover:bg-slate-100 group">
                                <img class="w-9 h-9 rounded-full ring-2 ring-white shadow-sm" 
                                     src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'John Doe') }}&background=4361ee&color=fff" 
                                     alt="Profile">
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="text-sm font-semibold text-slate-700">{{ Auth::user()->name ?? 'Administrator' }}</span>
                                    <i data-lucide="chevron-down" class="ml-2 w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </span>
                            </button>

                            <div x-show="open" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-50 w-56 mt-2 origin-top-right bg-white border border-slate-200 divide-y divide-slate-100 rounded-2xl shadow-xl focus:outline-none">
                                <div class="px-4 py-3">
                                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Signed in as</p>
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                        My Profile
                                    </a>
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                                        Account Settings
                                    </a>
                                </div>
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full gap-3 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
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
            <main class="flex-1 overflow-x-hidden p-6 lg:p-10">
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="p-6 text-center border-t border-slate-200 text-slate-500 text-xs">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved. Created with <span class="text-rose-500">♥</span> for better management.
            </footer>
        </div>
    </div>

    <!-- Lucide Initialization -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
