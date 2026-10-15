@extends('layouts.admin')

@section('content')
@php
    $usersData = $users->map(function($u) {
        return [
            'id' => (string)$u->user_id,
            'name' => $u->name,
            'pen' => (string)($u->pen ?? ''),
            'designation' => $u->designation ?? '',
        ];
    })->values();

    $seatsData = $seats->map(function($s) {
        return [
            'id' => (string)$s->seat_id,
            'name' => $s->seat_name,
        ];
    })->values();
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Premium Ambient Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-xl mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-4 text-center sm:text-left z-10">
                <a href="{{ route('admin.seats.index') }}" 
                   class="w-11 h-11 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 text-white flex items-center justify-center transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 shrink-0"
                   title="Back to Seats">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                        <i data-lucide="user-plus" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                            Assign Seat to User
                        </h1>
                        <p class="text-slate-300 font-medium text-xs sm:text-sm mt-0.5">
                            Create a new organizational seat assignment and configure occupancy modes.
                        </p>
                    </div>
                </div>
            </div>
            <div class="shrink-0 z-10">
                <a href="{{ route('admin.seats.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-200 hover:text-white bg-slate-800/90 hover:bg-slate-800 border border-slate-700 transition-all shadow-sm">
                    <i data-lucide="briefcase" class="w-4 h-4 text-teal-400"></i>
                    <span>Seats Directory</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Error Alert Banner -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-rose-900">Please correct the following errors:</h3>
                <ul class="mt-1 list-disc list-inside text-xs font-semibold text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Main Card Container -->
    <div class="bg-white rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.08)] relative border border-slate-100"
         x-data="{ isAdditional: {{ old('is_additional') || request('is_additional') ? 'true' : 'false' }} }">
        
        <!-- Rainbow Accent Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-teal-500 to-emerald-500 rounded-t-[2rem]"></div>

        <!-- Subtle ambient background blur -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50/60 rounded-full blur-[100px] pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-50/60 rounded-full blur-[100px] pointer-events-none -z-10"></div>

        <!-- Card Section Header -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100/80 border border-indigo-200/60 flex items-center justify-center text-indigo-600 shadow-xs">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider">Assignment Details</h2>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-200/70 text-slate-700">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>{{ $users->count() }} Officers</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>{{ $seats->count() }} Active Seats</span>
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.seatuser.store') }}" class="p-6 sm:p-8 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                <!-- Target Seat Searchable Dropdown -->
                <div class="space-y-2 relative"
                     x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ (old('seat_id') ?? ($selectedSeatId ?? '')) }}',
                        seats: {{ Js::from($seatsData) }},
                        activeIndex: 0,
                        get selectedSeat() {
                            return this.seats.find(s => String(s.id) === String(this.selectedId)) || null;
                        },
                        get filteredSeats() {
                            if (!this.search.trim()) return this.seats;
                            const q = this.search.toLowerCase().trim();
                            return this.seats.filter(s => s.name && s.name.toLowerCase().includes(q));
                        },
                        selectSeat(seat) {
                            if (!seat) return;
                            this.selectedId = seat.id;
                            this.open = false;
                            this.search = '';
                            this.activeIndex = 0;
                        },
                        clear() {
                            this.selectedId = '';
                            this.search = '';
                            this.activeIndex = 0;
                        }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false"
                     @keydown.arrow-down.prevent="if(open && filteredSeats.length > 0) activeIndex = (activeIndex + 1) % filteredSeats.length"
                     @keydown.arrow-up.prevent="if(open && filteredSeats.length > 0) activeIndex = (activeIndex - 1 + filteredSeats.length) % filteredSeats.length">
                    
                    <label class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="briefcase" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>Target Seat</span>
                        <span class="text-rose-500">*</span>
                    </label>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="seat_id" :value="selectedId">

                    <!-- Trigger Button -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-600 transition-colors pointer-events-none z-10">
                            <i data-lucide="armchair" class="w-4 h-4"></i>
                        </div>

                        <button type="button"
                                @click="open = !open; if(open) { activeIndex = 0; $nextTick(() => $refs.seatSearchInput.focus()); }"
                                class="w-full pl-11 pr-10 py-3 bg-slate-50/80 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all shadow-xs text-left flex items-center justify-between min-h-[50px] outline-none"
                                :class="open ? 'bg-white ring-4 ring-indigo-500/10 border-indigo-500' : 'hover:border-slate-300'">
                            
                            <div class="flex items-center gap-2.5 min-w-0 truncate">
                                <template x-if="selectedSeat">
                                    <span class="font-bold text-slate-900 text-sm truncate" x-text="selectedSeat.name"></span>
                                </template>
                                <template x-if="!selectedSeat">
                                    <span class="text-sm font-semibold text-slate-400">Select Seat</span>
                                </template>
                            </div>

                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                <span x-show="selectedSeat" 
                                      @click.stop="clear()" 
                                      class="p-1 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
                                      title="Clear selection">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-600' : ''"></i>
                            </div>
                        </button>

                        <!-- Dropdown Menu with Live Search -->
                        <div x-show="open" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute left-0 right-0 z-50 mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                            
                            <!-- Search Header -->
                            <div class="p-3 bg-slate-50/90 border-b border-slate-100">
                                <div class="relative flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                    <input type="text" 
                                           x-model="search" 
                                           x-ref="seatSearchInput" 
                                           @input="activeIndex = 0"
                                           @click.stop
                                           @keydown.enter.prevent="if (filteredSeats.length > 0) selectSeat(filteredSeats[activeIndex])"
                                           placeholder="Type seat name..." 
                                           class="w-full pl-10 pr-9 py-2.5 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 placeholder:text-slate-400 outline-none transition-all">
                                    
                                    <button x-show="search.length > 0" 
                                            @click.stop="search = ''; $refs.seatSearchInput.focus()" 
                                            type="button" 
                                            class="absolute right-2.5 p-1 text-slate-400 hover:text-slate-600 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between px-1 mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <span>Matches: <strong class="text-indigo-600 font-extrabold" x-text="filteredSeats.length"></strong></span>
                                    <span>Use <kbd class="px-1 py-0.5 rounded bg-slate-200 font-mono text-slate-600">↑</kbd><kbd class="px-1 py-0.5 rounded bg-slate-200 font-mono text-slate-600 ml-0.5">↓</kbd> then <kbd class="px-1 py-0.5 rounded bg-slate-200 font-mono text-slate-600">Enter ↵</kbd></span>
                                </div>
                            </div>

                            <!-- Seats Results List -->
                            <div class="p-1.5 max-h-64 overflow-y-auto custom-scrollbar space-y-1">
                                <template x-for="(s, index) in filteredSeats" :key="s.id">
                                    <div @click="selectSeat(s)" 
                                         @mouseenter="activeIndex = index"
                                         class="px-3.5 py-2.5 rounded-xl transition-all flex items-center justify-between gap-3 cursor-pointer group"
                                         :class="selectedId == s.id 
                                            ? 'bg-indigo-50 border border-indigo-200 text-indigo-900 font-bold' 
                                            : (activeIndex === index ? 'bg-slate-100 text-slate-900 ring-1 ring-indigo-500/20' : 'hover:bg-slate-50 text-slate-700')">
                                        
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black uppercase shrink-0 transition-colors"
                                                 :class="selectedId == s.id ? 'bg-indigo-600 text-white' : (activeIndex === index ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M3 11v5a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0Z"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
                                            </div>
                                            <p class="text-sm font-bold truncate leading-tight" x-text="s.name"></p>
                                        </div>

                                        <div x-show="selectedId == s.id" class="text-indigo-600 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- Zero Results State -->
                                <div x-show="filteredSeats.length === 0" class="py-8 px-4 text-center">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path><path d="m9 9 4 4M13 9l-4 4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">No seat found</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">No match for "<span class="font-semibold text-slate-600" x-text="search"></span>"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('seat_id')
                        <p class="text-xs font-bold text-rose-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Officer / User Searchable Dropdown with Name or PEN Number -->
                <div class="space-y-2 relative" 
                     x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ old('user_id', '') }}',
                        users: {{ Js::from($usersData) }},
                        activeIndex: 0,
                        filterMode: 'all', // 'all', 'name', 'pen'
                        get selectedUser() {
                            return this.users.find(u => String(u.id) === String(this.selectedId)) || null;
                        },
                        get filteredUsers() {
                            if (!this.search.trim()) return this.users;
                            const q = this.search.toLowerCase().trim();
                            if (this.filterMode === 'pen') {
                                return this.users.filter(u => u.pen && u.pen.toLowerCase().includes(q));
                            } else if (this.filterMode === 'name') {
                                return this.users.filter(u => u.name && u.name.toLowerCase().includes(q));
                            }
                            return this.users.filter(u => 
                                (u.name && u.name.toLowerCase().includes(q)) || 
                                (u.pen && u.pen.toLowerCase().includes(q)) ||
                                (u.designation && u.designation.toLowerCase().includes(q))
                            );
                        },
                        openWithMode(mode = 'all') {
                            this.filterMode = mode;
                            this.open = true;
                            this.activeIndex = 0;
                            this.$nextTick(() => {
                                if (this.$refs.userSearchInput) {
                                    this.$refs.userSearchInput.focus();
                                    if (mode === 'pen') {
                                        this.$refs.userSearchInput.placeholder = 'Enter PEN number (e.g. 795022)...';
                                    } else {
                                        this.$refs.userSearchInput.placeholder = 'Type officer name or PEN number...';
                                    }
                                }
                            });
                        },
                        selectUser(user) {
                            if (!user) return;
                            this.selectedId = user.id;
                            this.open = false;
                            this.search = '';
                            this.activeIndex = 0;
                        },
                        clear() {
                            this.selectedId = '';
                            this.search = '';
                            this.activeIndex = 0;
                        }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false"
                     @keydown.arrow-down.prevent="if(open && filteredUsers.length > 0) activeIndex = (activeIndex + 1) % filteredUsers.length"
                     @keydown.arrow-up.prevent="if(open && filteredUsers.length > 0) activeIndex = (activeIndex - 1 + filteredUsers.length) % filteredUsers.length">
                    
                    <label class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>Officer / User</span>
                        <span class="text-rose-500">*</span>
                    </label>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="user_id" :value="selectedId">

                    <!-- Trigger Button -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-teal-600 transition-colors pointer-events-none z-10">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>

                        <button type="button"
                                @click="open ? (open = false) : openWithMode('all')"
                                class="w-full pl-11 pr-10 py-3 bg-slate-50/80 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all shadow-xs text-left flex items-center justify-between min-h-[50px] outline-none"
                                :class="open ? 'bg-white ring-4 ring-teal-500/10 border-teal-500' : 'hover:border-slate-300'">
                            
                            <div class="flex items-center gap-2.5 min-w-0 truncate">
                                <template x-if="selectedUser">
                                    <div class="flex items-center gap-2 min-w-0 truncate">
                                        <span class="font-bold text-slate-900 text-sm truncate" x-text="selectedUser.name"></span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-teal-50 text-teal-700 border border-teal-200/80 shrink-0">
                                            PEN: <span x-text="selectedUser.pen || 'N/A'"></span>
                                        </span>
                                    </div>
                                </template>
                                <template x-if="!selectedUser">
                                    <span class="text-sm font-semibold text-slate-400">Select Officer</span>
                                </template>
                            </div>

                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                <span x-show="selectedUser" 
                                      @click.stop="clear()" 
                                      class="p-1 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
                                      title="Clear selection">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-teal-600' : ''"></i>
                            </div>
                        </button>

                        <!-- Dropdown Menu with Live Search -->
                        <div x-show="open" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute left-0 right-0 z-50 mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                            
                            <!-- Search Header -->
                            <div class="p-3 bg-slate-50/90 border-b border-slate-100">
                                <div class="relative flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                    <input type="text" 
                                           x-model="search" 
                                           x-ref="userSearchInput" 
                                           @input="activeIndex = 0"
                                           @click.stop
                                           @keydown.enter.prevent="if (filteredUsers.length > 0) selectUser(filteredUsers[activeIndex])"
                                           :placeholder="filterMode === 'pen' ? 'Enter PEN number (e.g. 795022)...' : (filterMode === 'name' ? 'Enter officer name...' : 'Type officer name or PEN number...')"
                                           class="w-full pl-10 pr-9 py-2.5 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 text-slate-900 placeholder:text-slate-400 outline-none transition-all">
                                    
                                    <button x-show="search.length > 0" 
                                            @click.stop="search = ''; $refs.userSearchInput.focus()" 
                                            type="button" 
                                            class="absolute right-2.5 p-1 text-slate-400 hover:text-slate-600 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Filter Mode Chips / Tabs -->
                                <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-200/60">
                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                                @click.stop="filterMode = 'all'; $refs.userSearchInput.focus()"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase transition-all"
                                                :class="filterMode === 'all' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-200'">
                                            All
                                        </button>
                                        <button type="button" 
                                                @click.stop="filterMode = 'name'; $refs.userSearchInput.focus()"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase transition-all"
                                                :class="filterMode === 'name' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-200'">
                                            By Name
                                        </button>
                                        <button type="button" 
                                                @click.stop="filterMode = 'pen'; $refs.userSearchInput.focus()"
                                                class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase transition-all"
                                                :class="filterMode === 'pen' ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-200/70 text-slate-600 hover:bg-slate-200'">
                                            By PEN
                                        </button>
                                    </div>

                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">
                                        <span>Matches: <strong class="text-teal-600 font-extrabold" x-text="filteredUsers.length"></strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Officers Results List -->
                            <div class="p-1.5 max-h-64 overflow-y-auto custom-scrollbar space-y-1">
                                <template x-for="(u, index) in filteredUsers" :key="u.id">
                                    <div @click="selectUser(u)" 
                                         @mouseenter="activeIndex = index"
                                         class="px-3.5 py-2.5 rounded-xl transition-all flex items-center justify-between gap-3 cursor-pointer group"
                                         :class="selectedId == u.id 
                                            ? 'bg-teal-50 border border-teal-200 text-teal-900 font-bold' 
                                            : (activeIndex === index ? 'bg-slate-100 text-slate-900 ring-1 ring-teal-500/20' : 'hover:bg-slate-50 text-slate-700')">
                                        
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black uppercase shrink-0 transition-colors"
                                                 :class="selectedId == u.id ? 'bg-teal-600 text-white' : (activeIndex === index ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-600')">
                                                <span x-text="u.name ? u.name.charAt(0) : 'U'"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold truncate leading-tight" x-text="u.name"></p>
                                                <p x-show="u.designation" class="text-[11px] text-slate-400 font-medium truncate mt-0.5" x-text="u.designation"></p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-bold transition-colors"
                                                  :class="selectedId == u.id ? 'bg-teal-200/70 text-teal-900' : (activeIndex === index ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600')">
                                                PEN: <span x-text="u.pen || 'N/A'" class="ml-1"></span>
                                            </span>
                                            <div x-show="selectedId == u.id" class="text-teal-600 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Zero Results State -->
                                <div x-show="filteredUsers.length === 0" class="py-8 px-4 text-center">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path><path d="m9 9 4 4M13 9l-4 4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">No officer found</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        No match for "<span class="font-semibold text-slate-600" x-text="search"></span>" 
                                        <span x-text="filterMode === 'pen' ? 'by PEN number' : (filterMode === 'name' ? 'by Name' : 'by Name or PEN')"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('user_id')
                        <p class="text-xs font-bold text-rose-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Additional Charge Option (Interactive Selection Card) -->
            <div class="rounded-2xl border transition-all duration-300 p-5 sm:p-6"
                 :class="isAdditional 
                    ? 'bg-amber-50/60 border-amber-300 shadow-md shadow-amber-500/5 ring-2 ring-amber-400/20' 
                    : 'bg-slate-50/70 border-slate-200 hover:border-slate-300 hover:bg-slate-100/50'">
                
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors duration-300"
                             :class="isAdditional ? 'bg-amber-500 text-white shadow-md shadow-amber-500/30' : 'bg-slate-200 text-slate-600'">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="text-sm font-black text-slate-900" x-text="isAdditional ? 'Additional Charge' : 'Primary Charge'">
                                    Primary Charge
                                </span>
                                <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider transition-all duration-300"
                                      :class="isAdditional 
                                        ? 'bg-amber-100 text-amber-800 border border-amber-300' 
                                        : 'bg-teal-100 text-teal-800 border border-teal-200'">
                                    <span x-text="isAdditional ? 'Additional Charge' : 'Primary Charge'"></span>
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                <template x-if="isAdditional">
                                    <span>Holds seat concurrently without revoking other assignments.</span>
                                </template>
                                <template x-if="!isAdditional">
                                    <span>Primary post; other active seats will be revoked.</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- Modern Toggle Switch Button -->
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                        <input type="checkbox" name="is_additional" value="1" 
                               x-model="isAdditional"
                               class="sr-only peer">
                        <div class="w-12 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div class="pt-6 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all text-center shadow-xs">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-7 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 rounded-xl shadow-md shadow-teal-500/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Assign Seat</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
