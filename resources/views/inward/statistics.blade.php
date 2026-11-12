@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-6" x-data="{ 
    activeTab: '{{ request('tab', 'seats') }}',
    setTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }
}">
        <!-- Premium Header Section (Print Hidden) -->
        <div
            class="print:hidden relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>

            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        {{ request()->routeIs('admin.reports.pendency*') ? 'Pendency Details' : 'Inward Statistics' }}
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        {{ request()->routeIs('admin.reports.pendency*') ? 'Detailed seat-wise petition pendency and status data sheet.' : 'Detailed seat-wise data sheet of petitions registered in Inward.' }}
                    </p>
                </div>
                <div class="flex items-center gap-3 z-10">
                    @if(Auth::user()->canAccess('export statistics') || (request()->routeIs('admin.reports.pendency*') && Auth::user()->canAccess('view pendency details')))
                        <a href="{{ route(request()->routeIs('admin.reports.pendency*') ? 'admin.reports.pendency.export' : 'inward.statistics.export', array_merge(request()->query(), ['creator_id' => $selectedCreator ?? 'all', 'origin' => $origin ?? 'inward'])) }}"
                            :href="'{{ route(request()->routeIs('admin.reports.pendency*') ? 'admin.reports.pendency.export' : 'inward.statistics.export', array_merge(request()->query(), ['creator_id' => $selectedCreator ?? 'all', 'origin' => $origin ?? 'inward'])) }}' + (activeTab === 'creators' ? '&tab=creators' : '&tab=seats')"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 rounded-xl hover:bg-emerald-500/20 transition-all font-semibold text-sm shadow-sm">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            Excel Export
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white border border-slate-200/90 shadow-md shadow-slate-100/80 rounded-2xl overflow-hidden print-hide mb-6 border-t-2 border-t-teal-500">
            <div class="p-4 sm:p-5 bg-gradient-to-b from-slate-50/80 to-white">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-600">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Filter Statistics</h3>
                </div>

                <style>
                    @media (min-width: 1024px) {
                        .inward-stats-filter-row {
                            display: grid !important;
                            grid-template-columns: minmax(180px, 1.35fr) minmax(180px, 1.35fr) minmax(160px, 1.2fr) minmax(120px, 0.85fr) minmax(120px, 0.85fr) auto !important;
                            align-items: flex-end !important;
                        }
                    }
                </style>

                <form method="GET" action="{{ request()->routeIs('admin.reports.pendency*') ? route('admin.reports.pendency') : route('inward.statistics') }}" class="w-full">
                    <input type="hidden" name="tab" :value="activeTab" value="{{ request('tab', 'seats') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 inward-stats-filter-row gap-2.5 xl:gap-3 items-end w-full">
                        <!-- Section Filter -->
                        <div class="flex flex-col justify-end w-full">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4 whitespace-nowrap">
                                <i data-lucide="layers" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Section</span>
                            </label>
                            <div class="flex items-center p-1 bg-blue-50/50 border border-blue-200/80 rounded-xl w-full h-11 shadow-inner">
                                @if(($canCpsp ?? true) && ($canViewTSection ?? false))
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="all" class="sr-only peer"
                                            {{ ($section ?? '') === 'all' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1">
                                            All Sections
                                        </span>
                                    </label>
                                @endif
                                @if($canCpsp ?? true)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="cpsp" class="sr-only peer"
                                            {{ ($section ?? '') === 'cpsp' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1">
                                            CPSP
                                        </span>
                                    </label>
                                @endif
                                @if($canViewTSection ?? false)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="t_section" class="sr-only peer"
                                            {{ ($section ?? '') === 't_section' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1">
                                            T-Section
                                        </span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Petition Origin Filter -->
                        <div class="flex flex-col justify-end w-full">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4 whitespace-nowrap">
                                <i data-lucide="git-branch" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Petition Origin</span>
                            </label>
                            <div class="flex items-center p-1 bg-blue-50/50 border border-blue-200/80 rounded-xl w-full h-11 shadow-inner">
                                <label class="flex-1 cursor-pointer h-full">
                                    <input type="radio" name="origin" value="inward" class="sr-only peer"
                                        {{ ($origin ?? 'inward') === 'inward' ? 'checked' : '' }}
                                        @if(!($canFilterOrigin ?? false)) disabled @else onchange="this.form.submit()" @endif>
                                    <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1"
                                        title="Only petitions registered through Inward">
                                        Inward
                                    </span>
                                </label>
                                @if($canFilterOrigin ?? false)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="origin" value="direct" class="sr-only peer"
                                            {{ ($origin ?? '') === 'direct' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1"
                                            title="Direct entries at CPSP or T-Section without Inward">
                                            Direct
                                        </span>
                                    </label>
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="origin" value="all" class="sr-only peer"
                                            {{ ($origin ?? '') === 'all' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-[11px] xl:text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-1"
                                            title="All petitions (Inward & Direct)">
                                            All Origins
                                        </span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Petition Creator / Created By Filter -->
                        <div class="flex flex-col justify-end w-full">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4 whitespace-nowrap">
                                <i data-lucide="user-check" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Petition Creator</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-400">
                                    <i data-lucide="user" class="h-4 w-4"></i>
                                </div>
                                <select name="creator_id" id="creator_id"
                                    class="w-full pl-9 pr-7 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs xl:text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300 appearance-none cursor-pointer truncate"
                                    style="color: #1e40af !important;"
                                    onchange="this.form.submit()">
                                    
                                    @if(($canViewAllStats ?? false) || !($canCreateInward ?? false))
                                        <option value="all" {{ ($selectedCreator ?? 'all') === 'all' ? 'selected' : '' }}>
                                            All Petitions
                                        </option>
                                    @endif

                                    @if($canCreateInward ?? false)
                                        <option value="my_petitions" {{ ($selectedCreator ?? '') === 'my_petitions' || (($selectedCreator ?? '') == Auth::id() && !($canViewAllStats ?? false)) ? 'selected' : '' }}>
                                            My Petitions
                                        </option>
                                    @endif

                                    @if(($canViewOtherStats ?? false) || ($canViewAllStats ?? false))
                                        @foreach($creators as $creator)
                                            @if($creator->user_id !== Auth::id())
                                                <option value="{{ $creator->user_id }}" {{ (string)($selectedCreator ?? '') === (string)$creator->user_id ? 'selected' : '' }}>
                                                    {{ $creator->inward_display_name ?? $creator->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="flex flex-col justify-end w-full">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4 whitespace-nowrap">
                                <span>Date From</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-2.5 xl:pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="h-3.5 w-3.5 xl:h-4 xl:w-4 text-blue-400"></i>
                                </div>
                                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" placeholder="DD-MM-YYYY"
                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                    class="w-full pl-8 xl:pl-9 pr-2 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs xl:text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Date To -->
                        <div class="flex flex-col justify-end w-full">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4 whitespace-nowrap">
                                <span>Date To</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-2.5 xl:pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="h-3.5 w-3.5 xl:h-4 xl:w-4 text-blue-400"></i>
                                </div>
                                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" placeholder="DD-MM-YYYY"
                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                    class="w-full pl-8 xl:pl-9 pr-2 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs xl:text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col justify-end shrink-0 w-full sm:w-auto">
                            <div class="h-4 mb-1.5 hidden lg:block"></div>
                            <div class="flex items-center gap-2 h-11">
                                <button type="submit"
                                    class="h-11 px-3.5 xl:px-4 text-xs xl:text-sm font-bold text-white bg-teal-600 border border-teal-600 rounded-xl hover:bg-teal-700 hover:border-teal-700 shadow-sm shadow-teal-500/20 flex items-center justify-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                                    <i data-lucide="filter" class="w-3.5 h-3.5 text-white"></i>
                                    <span>Apply</span>
                                </button>

                                @php
                                    $defaultCreatorScope = ($canCreateInward ?? false) ? (($canViewAllStats ?? false) ? 'all' : 'my_petitions') : 'all';
                                    $defaultSec = $defaultSection ?? 'all';
                                    $hasActiveFilters = $dateFrom || $dateTo || (($section ?? $defaultSec) !== $defaultSec) || (($selectedCreator ?? '') !== $defaultCreatorScope) || (($origin ?? 'inward') !== 'inward');
                                @endphp
                                @if($hasActiveFilters)
                                    <a href="{{ request()->routeIs('admin.reports.pendency*') ? route('admin.reports.pendency') : route('inward.statistics') }}" title="Clear Filters"
                                        class="w-11 h-11 shrink-0 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-600 shadow-sm flex items-center justify-center transition-all">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Official Data Sheet Table Card -->
        <div
            class="bg-white border border-slate-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden print:border-none print:shadow-none">
            <div
                class="px-8 py-5 border-b border-slate-100 bg-white flex flex-col md:flex-row md:items-center justify-between gap-4 print:p-0 print:border-b-2 print:border-slate-800">
                <div>
                    @php
                        $sectionTitle = match($section ?? 'cpsp') {
                            'all' => 'All Seats',
                            't_section' => 'T-Section Seats',
                            default => 'CPSP Seats'
                        };
                        $reportType = request()->routeIs('admin.reports.pendency*') ? 'Petition Pendency Data Sheet' : 'Petition Statistics Data Sheet';
                    @endphp

                    <!-- Tab 1 Title (Seat-wise) -->
                    <div x-show="activeTab === 'seats'">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            {{ $sectionTitle }} - {{ $reportType }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200 shadow-sm">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-teal-600"></i>
                                <span>Creator: {{ $selectedCreatorLabel ?? 'All Creators' }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ ($origin ?? 'inward') === 'inward' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : (($origin ?? '') === 'direct' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-purple-50 text-purple-800 border-purple-200') }} border shadow-sm">
                                <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                                <span>Origin: {{ $originLabel ?? 'Inward Registered' }}</span>
                            </span>
                            @if($dateFrom || $dateTo)
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-slate-100 rounded-full text-xs font-semibold text-slate-700 border border-slate-200">
                                    <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Today' }}</span>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 font-medium">
                                    Overall petition summary assigned to {{ ($section ?? 'cpsp') === 'all' ? 'all' : (($section ?? 'cpsp') === 't_section' ? 'T-Section' : 'CPSP') }} seats.
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Tab 2 Title (Creator-wise) -->
                    <div x-show="activeTab === 'creators'" style="display: none;">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            Petition Created By - Staff Summary Data Sheet
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-sm">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-600"></i>
                                <span>Total Staff / Creators: {{ count($creatorStats) }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ ($origin ?? 'inward') === 'inward' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : (($origin ?? '') === 'direct' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-purple-50 text-purple-800 border-purple-200') }} border shadow-sm">
                                <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                                <span>Origin: {{ $originLabel ?? 'Inward Registered' }}</span>
                            </span>
                            @if($dateFrom || $dateTo)
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-slate-100 rounded-full text-xs font-semibold text-slate-700 border border-slate-200">
                                    <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Today' }}</span>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 font-medium">
                                    Overall petitions registered by staff members and inward officers.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tab Segmented Control -->
                <div class="flex items-center p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80 self-start md:self-center shadow-inner">
                    <button type="button"
                        @click="setTab('seats')"
                        :class="activeTab === 'seats' ? 'bg-white text-teal-800 shadow-sm font-extrabold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all duration-150 cursor-pointer">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-teal-600"></i>
                        <span>Seat-wise Summary</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" title="Total Petitions: {{ $summaryTotals['totalSent'] ?? 0 }}">
                            {{ $summaryTotals['totalSent'] ?? 0 }}
                        </span>
                    </button>
                    <button type="button"
                        @click="setTab('creators')"
                        :class="activeTab === 'creators' ? 'bg-white text-teal-800 shadow-sm font-extrabold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all duration-150 cursor-pointer">
                        <i data-lucide="users" class="w-4 h-4 text-teal-600"></i>
                        <span>Creator-wise Summary</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" title="Total Petitions: {{ $creatorSummaryTotals['totalCreated'] ?? 0 }}">
                            {{ $creatorSummaryTotals['totalCreated'] ?? 0 }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Tab 1: Seat-wise Table -->
            <div x-show="activeTab === 'seats'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b-2 border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider print:bg-slate-200">
                            <th class="px-5 py-4">Concerned Seat</th>
                            <th class="px-5 py-4">Seat Occupant</th>
                            <th class="px-4 py-4 text-center">{{ ($origin ?? 'inward') === 'inward' ? 'Total Inward' : (($origin ?? '') === 'direct' ? 'Total Direct' : 'Total Petitions') }}</th>
                            @if(auth()->user()->canAccess('view processed statistics'))
                                <th class="px-4 py-4 text-center">Processed</th>
                                <th class="px-4 py-4 text-center">Pending</th>
                                <th class="px-6 py-4 w-40">Progress</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @php
                            $creatorParam = ($selectedCreator && $selectedCreator !== 'all') ? ['creator_id' => $selectedCreator] : [];
                        @endphp
                        @forelse($seatStats as $index => $stat)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-5 py-4 font-extrabold text-slate-900">
                                    {{ $stat['seat_name'] }}
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-600">
                                    {{ $stat['occupant'] }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ ($origin ?? 'inward') === 'inward' 
                                            ? route('inward.enter_petition', array_filter(array_merge(['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1], $creatorParam))) . '#view-inward-petitions'
                                            : route('petitions.reports', array_filter(array_merge(['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to')], $creatorParam))) }}"
                                        class="font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-indigo-700 px-2.5 py-0.5 rounded border border-slate-200 transition-colors block cursor-pointer text-center">
                                        {{ $stat['total_sent'] }}
                                    </a>
                                </td>
                                @if(auth()->user()->canAccess('view processed statistics'))
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('petitions.index', array_filter(array_merge(['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions'], $creatorParam))) }}"
                                            class="font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 hover:text-teal-900 px-2.5 py-0.5 rounded border border-teal-200 transition-colors block cursor-pointer text-center">
                                            {{ $stat['processed'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('petitions.index', array_filter(array_merge(['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending'], $creatorParam))) }}"
                                            class="font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 hover:text-rose-900 px-2.5 py-0.5 rounded border border-rose-200 transition-colors block cursor-pointer text-center">
                                            {{ $stat['pending'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $percent = $stat['total_sent'] > 0 ? round(($stat['processed'] / $stat['total_sent']) * 100) : 0;
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner">
                                                <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500"
                                                    style="width: {{ $percent }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-500 w-8 text-right">{{ $percent }}%</span>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                                            <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                        </div>
                                        <p class="font-bold text-slate-500">No petition statistics found for the selected date
                                            range.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-900 text-white border-t-4 border-teal-500">
                            <td colspan="2"
                                class="px-5 py-5 text-right uppercase tracking-wider font-extrabold text-xs text-slate-300">
                                Total Summary
                            </td>
                            <td
                                class="px-4 py-5 text-center font-black text-lg text-white hover:text-indigo-300 transition-colors">
                                <a href="{{ route('inward.enter_petition', array_filter(array_merge(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1], $creatorParam))) }}#view-inward-petitions"
                                    class="block">
                                    {{ $summaryTotals['totalSent'] }}
                                </a>
                            </td>
                            @if(auth()->user()->canAccess('view processed statistics'))
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-emerald-400 hover:text-emerald-300 transition-colors">
                                    <a href="{{ route('petitions.index', array_filter(array_merge(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions'], $creatorParam))) }}"
                                        class="block">
                                        {{ $summaryTotals['processed'] }}
                                    </a>
                                </td>
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-rose-400 hover:text-rose-300 transition-colors">
                                    <a href="{{ route('petitions.index', array_filter(array_merge(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending'], $creatorParam))) }}"
                                        class="block">
                                        {{ $summaryTotals['pending'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $totalPercent = $summaryTotals['totalSent'] > 0 ? round(($summaryTotals['processed'] / $summaryTotals['totalSent']) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden shadow-inner">
                                            <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $totalPercent }}%"></div>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold text-slate-300 w-8 text-right">{{ $totalPercent }}%</span>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Tab 2: Creator-wise Table -->
            <div x-show="activeTab === 'creators'" style="display: none;" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b-2 border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider print:bg-slate-200">
                            <th class="px-5 py-4 w-14 text-center">#</th>
                            <th class="px-5 py-4">Creator / Staff Name</th>
                            <th class="px-5 py-4">Assigned Section / Seat</th>
                            <th class="px-4 py-4 text-center">Total Created</th>
                            @if(auth()->user()->canAccess('view processed statistics'))
                                <th class="px-4 py-4 text-center">Processed</th>
                                <th class="px-4 py-4 text-center">Pending</th>
                                <th class="px-6 py-4 w-40">Progress</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($creatorStats as $index => $cStat)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-5 py-4 text-center font-bold text-slate-400 text-xs">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-700 font-extrabold text-xs shadow-sm">
                                            {{ strtoupper(substr($cStat['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                                <span>{{ $cStat['name'] }}</span>
                                                @if(!empty($cStat['pen']))
                                                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                        PEN: {{ $cStat['pen'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 font-medium">
                                                {{ $cStat['designation'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold {{ $cStat['section'] === 'Inward Section' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($cStat['section'] === 'CPSP' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                            <i data-lucide="shield" class="w-3 h-3"></i>
                                            <span>{{ $cStat['seat_name'] ?? $cStat['section'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ ($origin ?? 'inward') === 'inward' 
                                            ? route('inward.enter_petition', array_filter(['creator_id' => $cStat['user_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1])) . '#view-inward-petitions'
                                            : route('petitions.reports', array_filter(['creator_id' => $cStat['user_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}"
                                        class="font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-indigo-700 px-2.5 py-0.5 rounded border border-slate-200 transition-colors block cursor-pointer text-center">
                                        {{ $cStat['total_created'] }}
                                    </a>
                                </td>
                                @if(auth()->user()->canAccess('view processed statistics'))
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('petitions.index', array_filter(['creator_id' => $cStat['user_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions'])) }}"
                                            class="font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 hover:text-teal-900 px-2.5 py-0.5 rounded border border-teal-200 transition-colors block cursor-pointer text-center">
                                            {{ $cStat['processed'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('petitions.index', array_filter(['creator_id' => $cStat['user_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending'])) }}"
                                            class="font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 hover:text-rose-900 px-2.5 py-0.5 rounded border border-rose-200 transition-colors block cursor-pointer text-center">
                                            {{ $cStat['pending'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $cPercent = $cStat['total_created'] > 0 ? round(($cStat['processed'] / $cStat['total_created']) * 100) : 0;
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner">
                                                <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500"
                                                    style="width: {{ $cPercent }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-500 w-8 text-right">{{ $cPercent }}%</span>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->canAccess('view processed statistics') ? 7 : 4 }}" class="px-6 py-16 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                                            <i data-lucide="users" class="w-8 h-8 text-slate-300"></i>
                                        </div>
                                        <p class="font-bold text-slate-500">No creator statistics found for the selected date range and filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-900 text-white border-t-4 border-teal-500">
                            <td colspan="3"
                                class="px-5 py-5 text-right uppercase tracking-wider font-extrabold text-xs text-slate-300">
                                Total Summary
                            </td>
                            <td
                                class="px-4 py-5 text-center font-black text-lg text-white hover:text-indigo-300 transition-colors">
                                <a href="{{ route('inward.enter_petition', array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1])) }}#view-inward-petitions"
                                    class="block">
                                    {{ $creatorSummaryTotals['totalCreated'] }}
                                </a>
                            </td>
                            @if(auth()->user()->canAccess('view processed statistics'))
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-emerald-400 hover:text-emerald-300 transition-colors">
                                    <a href="{{ route('petitions.index', array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions'])) }}"
                                        class="block">
                                        {{ $creatorSummaryTotals['processed'] }}
                                    </a>
                                </td>
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-rose-400 hover:text-rose-300 transition-colors">
                                    <a href="{{ route('petitions.index', array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending'])) }}"
                                        class="block">
                                        {{ $creatorSummaryTotals['pending'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $cTotalPercent = $creatorSummaryTotals['totalCreated'] > 0 ? round(($creatorSummaryTotals['processed'] / $creatorSummaryTotals['totalCreated']) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden shadow-inner">
                                            <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $cTotalPercent }}%"></div>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold text-slate-300 w-8 text-right">{{ $cTotalPercent }}%</span>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection