@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500"
    x-data="seatManagement({
        initialTotal: {{ $seats->total() }},
        initialSearch: '{{ addslashes($search ?? '') }}',
        searchUrl: '{{ route('admin.seats.index') }}'
    })">
    <!-- Premium Header Section with Integrated Live Search -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] via-[#1e293b] to-[#0f172a] border border-slate-700/60 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-5 sm:px-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 z-10">
            <!-- Left: Title, Subtitle & Total Pill -->
            <div class="shrink-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-0.5">
                        Seats Management
                    </h1>
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800/90 border border-slate-700/80 text-xs font-bold text-slate-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span x-text="`${totalCount} ${searchQuery ? 'Matches' : 'Total'}`">
                            {{ $seats->total() }} Total
                        </span>
                    </div>
                </div>
                <p class="text-sky-400 font-medium max-w-2xl text-xs sm:text-sm mt-0.5">
                    Manage primary occupants and additional charges across units.
                </p>
            </div>

            <!-- Right: Search Input + Add Seat Action -->
            <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative w-full sm:w-80 md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        @input.debounce.300ms="performSearch()"
                        placeholder="Search seats by name, occupant, unit..." 
                        style="color: #ffffff !important;"
                        class="block w-full pl-10 pr-9 py-2 bg-slate-900/90 border border-slate-600/80 rounded-xl text-sm font-semibold !text-white placeholder:text-slate-400 focus:outline-none focus:border-sky-400 focus:bg-slate-950 focus:ring-2 focus:ring-sky-400/20 transition-all shadow-inner"
                    />
                    <!-- Clear search button -->
                    <button 
                        type="button" 
                        x-show="searchQuery && searchQuery.length > 0" 
                        @click="clearSearch()" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition-colors"
                        title="Clear search"
                        style="display: none;">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                    <!-- Loading spinner -->
                    <div x-show="isLoading" class="absolute inset-y-0 right-8 flex items-center pr-2" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-sky-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Reset Button when filtered -->
                <button 
                    type="button" 
                    x-show="searchQuery && searchQuery.length > 0" 
                    @click="clearSearch()" 
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white border border-slate-700 transition-all text-center shrink-0"
                    style="display: none;">
                    Reset
                </button>

                @if(Auth::user()->canAccess('create seats'))
                <div class="shrink-0">
                    <a href="{{ route('admin.seats.create') }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 transition-all text-sm whitespace-nowrap">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Add New Seat</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stunning Table Container -->
    <div id="seats-table-container" class="bg-white border border-slate-200 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative transition-opacity duration-200" :class="{ 'opacity-60': isLoading }">
        <!-- Subtle background glow -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-50/50 rounded-full blur-[100px] pointer-events-none -z-10"></div>
        
        @include('admin.seats.partials.list-table', ['seats' => $seats, 'search' => $search ?? ''])
    </div>
</div>

@section('scripts')
<script>
    function seatManagement(config) {
        return {
            searchQuery: config.initialSearch || '',
            totalCount: config.initialTotal || 0,
            isLoading: false,
            searchUrl: config.searchUrl,

            init() {
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('#seats-table-container .pagination a, #seats-table-container nav a');
                    if (link && link.href) {
                        e.preventDefault();
                        this.performSearch(link.href);
                    }
                });
            },

            async performSearch(url = null) {
                this.isLoading = true;
                try {
                    let fetchUrl = url;
                    if (!fetchUrl) {
                        const u = new URL(this.searchUrl, window.location.origin);
                        if (this.searchQuery.trim() !== '') {
                            u.searchParams.set('search', this.searchQuery.trim());
                        }
                        fetchUrl = u.toString();
                    }

                    const response = await fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.totalCount = data.total;
                        const container = document.getElementById('seats-table-container');
                        if (container) {
                            container.innerHTML = data.html;
                            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                                lucide.createIcons();
                            }
                        }
                        const stateUrl = new URL(fetchUrl);
                        window.history.replaceState({}, '', stateUrl.toString());
                    }
                } catch (error) {
                    console.error('Failed to perform live search for seats:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            clearSearch() {
                this.searchQuery = '';
                this.performSearch();
            }
        };
    }
</script>
@endsection
@endsection
