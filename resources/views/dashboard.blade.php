@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('title', 'Dashboard - CPSP Portal')

@section('content')
@php
    $user = Auth::user();
    $currentSeatUser = $user->currentSeatUser();
    $currentSeat = $currentSeatUser?->seat;
    $isAdditional = $currentSeatUser?->is_additional;
    $seatName = $currentSeat?->seat_name ?? ($user->roles->first()?->name ?? 'User');
    
    // Permission-driven scopes
    $isCpspDesk = $currentSeat
        ? $currentSeat->hasRole('CPSP')
        : $user->hasRole('CPSP');
    $isExecutive = !$isCpspDesk && ($user->canAccess('view all petitions') || $user->canAccess('access admin dashboard'));
    $canSeeSeatChart = $user->canAccess('view seat distribution chart');
    $canSeeStatusChart = $user->canAccess('view petition status chart');
    $canSeeVacantSeats = $user->canAccess('view seats') || $user->canAccess('access admin dashboard');
    $canSeeRecentPetitions = $user->canAccess('view recent petitions');
    $isInwardDesk = !$isExecutive && !$isCpspDesk
        && ($user->canAccess('view file transfer') || ($currentSeat && stripos($currentSeat->seat_name, 'Inward') !== false));

    // Date filters
    $fromDate = request('from_date', now()->subDays(6)->format('Y-m-d'));
    $toDate = request('to_date', now()->format('Y-m-d'));

    // Base query for executive vs desk
    if ($isExecutive) {
        $basePetitionsQuery = \App\Models\Petition::whereNull('linked_petition_id');
    } elseif ($isInwardDesk) {
        $inwardUserIds = \App\Models\User::role(['inward', 'Inward'])->pluck('user_id')->push($user->user_id)->unique();
        $basePetitionsQuery = \App\Models\Petition::where(function($q) use ($inwardUserIds, $currentSeatUser) {
            $q->whereIn('user_id', $inwardUserIds);
            if ($currentSeatUser) {
                $q->orWhere('seat_id', $currentSeatUser->seat_id);
            }
        })->whereNull('linked_petition_id');
    } elseif ($currentSeatUser) {
        $basePetitionsQuery = \App\Models\Petition::where('seat_id', $currentSeatUser->seat_id)
            ->where('is_returned_to_inward', false)
            ->whereNull('linked_petition_id');
    } else {
        $basePetitionsQuery = \App\Models\Petition::where('user_id', $user->user_id)
            ->where('is_returned_to_inward', false)
            ->whereNull('linked_petition_id');
    }

    // High level metrics
    $totalPetitions = (clone $basePetitionsQuery)->count();
    $processed = (clone $basePetitionsQuery)->where('is_cpsp_processed', true)->count();
    $unprocessed = (clone $basePetitionsQuery)->where('is_cpsp_processed', false)->where('is_returned_to_inward', false)->count();
    $forwarded = (clone $basePetitionsQuery)->where('is_returned_to_inward', false)->where('status', 'Forwarded')->count();
    $vrReports = (clone $basePetitionsQuery)->where('is_returned_to_inward', false)->where('status', 'VR_Received')->count();
    $decisions = (clone $basePetitionsQuery)->where('is_returned_to_inward', false)->whereIn('status', [\App\Models\Petition::STATUS_SENT_TO_GOVT, \App\Models\Petition::STATUS_CLOSED])->count();

    // Inward Desk specific stats
    if ($isInwardDesk) {
        $todayInward = (clone $basePetitionsQuery)->whereDate('created_at', now()->toDateString())->count();
        $transferredToDesks = (clone $basePetitionsQuery)->whereNotNull('seat_id')->where('is_returned_to_inward', false)->count();
        $returnedToInward = (clone $basePetitionsQuery)->where('is_returned_to_inward', true)->count();
    }

    // Vacant Seats mapping
    $vacantSeats = $canSeeVacantSeats ? \App\Models\Seat::whereDoesntHave('seatUsers', function ($q) {
        $q->where('is_active', true);
    })->get() : collect([]);

    // Chart Data: Seat vs Petitions (Bar Chart)
    $seatStats = collect([]);
    if ($canSeeSeatChart) {
        $seatStats = \App\Models\Seat::where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['cpsp']);
            })
            ->withCount([
                'petitionsReceived' => function ($q) use ($fromDate, $toDate) {
                    $q->where('is_cpsp_processed', true)
                      ->whereBetween('date_of_petition_received', [$fromDate, $toDate]);
                }
            ])
            ->get();
    }
    $chartSeatLabels = json_encode($seatStats->pluck('seat_name')->toArray());
    $chartSeatData = json_encode($seatStats->pluck('petitions_received_count')->toArray());

    // Chart Data: Petition Status Distribution (Doughnut Chart)
    $statusStats = collect([]);
    if ($canSeeStatusChart) {
        $statusStats = \App\Models\Petition::whereBetween('date_of_petition_received', [$fromDate, $toDate])
            ->whereNull('linked_petition_id')
            ->where('is_cpsp_processed', true)
            ->groupBy('status')
            ->get(['status', \Illuminate\Support\Facades\DB::raw('count(*) as count')])
            ->pluck('count', 'status');
    }
    $chartStatusLabels = json_encode($statusStats->keys()->toArray());
    $chartStatusData = json_encode($statusStats->values()->toArray());

    // Petition trends and nature are scoped to the same seat as the dashboard metrics.
    $canSeeTrends = $user->canAccess('view petition trends chart');
    $canSeeNature = $user->canAccess('view petition nature chart');
    $chartPetitionsQuery = clone $basePetitionsQuery;
    if (!$isInwardDesk) {
        $chartPetitionsQuery->where('is_cpsp_processed', true);
    }

    $startDate = \Carbon\Carbon::today()->subDays(6);
    $endDate = \Carbon\Carbon::today();
    try {
        if (is_string(request('start_date'))) {
            $startDate = \Carbon\Carbon::parse(request('start_date'));
        }
        if (is_string(request('end_date'))) {
            $endDate = \Carbon\Carbon::parse(request('end_date'));
        }
    } catch (\Carbon\Exceptions\InvalidFormatException $e) {
        $startDate = \Carbon\Carbon::today()->subDays(6);
        $endDate = \Carbon\Carbon::today();
    }
    if ($startDate->gt($endDate)) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }
    $startDate->startOfDay();
    $endDate->endOfDay();

    $chartTrendLabels = [];
    $chartTrendCounts = [];
    if ($canSeeTrends) {
        $trendStats = (clone $chartPetitionsQuery)
            ->whereBetween('date_of_petition_received', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('DATE(date_of_petition_received) as petition_date, COUNT(*) as total')
            ->groupBy('petition_date')
            ->pluck('total', 'petition_date');

        foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateKey = $date->format('Y-m-d');
            $chartTrendLabels[] = $date->format('d M');
            $chartTrendCounts[] = (int) $trendStats->get($dateKey, 0);
        }
    }

    $natureStats = $canSeeNature
        ? (clone $chartPetitionsQuery)
            ->whereNotNull('nature_of_petition')
            ->where('nature_of_petition', '<>', '')
            ->selectRaw('nature_of_petition, COUNT(*) as total')
            ->groupBy('nature_of_petition')
            ->pluck('total', 'nature_of_petition')
        : collect();
    $chartNatureLabels = json_encode($natureStats->keys()->values());
    $chartNatureCounts = json_encode($natureStats->values()->map(fn ($count) => (int) $count)->values());
    $chartTrendLabelsJson = json_encode($chartTrendLabels);
    $chartTrendCountsJson = json_encode($chartTrendCounts);

    // Recent Petitions for table
    $recentPetitions = collect([]);
    if ($canSeeRecentPetitions) {
        $recentPetitions = (clone $basePetitionsQuery)
            ->with(['addresses', 'latestForwarding', 'decision', 'seat'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }
@endphp

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">

    <!-- Premium Unified Header Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 border border-slate-700/60 shadow-xl p-6 sm:p-8">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
            <div class="w-80 h-80 bg-indigo-500/15 rounded-full blur-[80px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
            <div class="w-80 h-80 bg-teal-500/15 rounded-full blur-[80px]"></div>
        </div>

        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6 z-10">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        {{ $seatName }}
                    </span>
                    @if($isAdditional)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            Additional Charge
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        {{ now()->format('d M, Y') }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    @if($isExecutive)
                        State-wide executive petition intelligence, seat distribution, and grievance workflow overview.
                    @elseif($isInwardDesk)
                        Inward desk operations, new petition registration, and seat transfer dispatch center.
                    @else
                        Active desk petition processing, inquiries, verification reports, and disposal tracking.
                    @endif
                </p>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if($user->canAccess('create petitions') || $user->canAccess('create inward petitions') || $user->canAccess('inward form'))
                    <a href="{{ route('petitions.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs shadow-lg shadow-teal-500/20 hover:scale-105 transition-all">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>New Petition</span>
                    </a>
                @endif

                @if($user->canAccess('view master reports'))
                    <a href="{{ route('petitions.reports') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700/80 shadow-sm transition-all">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-indigo-400"></i>
                        <span>Master Reports</span>
                    </a>
                @endif

                @if($user->canAccess('view compliances'))
                    <a href="{{ route('compliances.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700/80 shadow-sm transition-all">
                        <i data-lucide="check-square" class="w-4 h-4 text-teal-400"></i>
                        <span>Compliances</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Vacant Seats Alert (Only if user has view seats / admin access) -->
    @if($canSeeVacantSeats && $vacantSeats->count() > 0)
        <div class="p-5 bg-amber-500/10 border border-amber-500/20 rounded-2xl shadow-sm relative overflow-hidden group">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 bg-amber-500/20 text-amber-400 rounded-xl flex items-center justify-center shrink-0 border border-amber-500/30">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-200">Vacant Seats Detected ({{ $vacantSeats->count() }})</h4>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($vacantSeats as $vSeat)
                                <span class="px-2.5 py-0.5 bg-amber-500/20 border border-amber-500/30 rounded-lg text-[10px] font-bold text-amber-300 uppercase tracking-wider">
                                    {{ $vSeat->seat_name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($user->canAccess('create seat user') || $user->canAccess('access admin dashboard'))
                    <a href="{{ route('admin.seatuser.create') }}"
                       class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-amber-900/30">
                        Assign Seat
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Dynamic Metrics Cards Grid -->
    @if($isExecutive || $isCpspDesk)
        {{-- Executive / State-Wide Metrics Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">

            <!-- Card 1: Unprocessed -->
            <a href="{{ route('petitions.index', ['tab' => 'inward']) }}"
               class="group relative block p-5 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-cyan-500 to-blue-600 border border-cyan-400/30 shadow-md">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-cyan-600 bg-white shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="inbox" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        Pending
                    </span>
                </div>
                <div class="mt-4 relative z-10">
                    <p class="text-[11px] font-bold text-cyan-100 uppercase tracking-wider mb-1">Inward Petitions</p>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ $unprocessed }}</h3>
                </div>
            </a>

            <!-- Card 2: Processed -->
            <a href="{{ route('petitions.index', ['tab' => 'all']) }}"
               class="group relative block p-5 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-emerald-500 to-teal-700 border border-emerald-400/30 shadow-md">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-emerald-600 bg-white shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="file-check-2" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        Processed
                    </span>
                </div>
                <div class="mt-4 relative z-10">
                    <p class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider mb-1">Processed Petitions</p>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ $processed }}</h3>
                </div>
            </a>

            <!-- Card 3: Forwarded -->
            <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}"
               class="group relative block p-5 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-rose-500 to-pink-600 border border-pink-400/30 shadow-md">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-pink-600 bg-white shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="send" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        In Inquiry
                    </span>
                </div>
                <div class="mt-4 relative z-10">
                    <p class="text-[11px] font-bold text-pink-100 uppercase tracking-wider mb-1">Forwarded to Units</p>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ $forwarded }}</h3>
                </div>
            </a>

            <!-- Card 4: Verification Reports -->
            <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}"
               class="group relative block p-5 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-amber-500 to-orange-600 border border-orange-400/30 shadow-md">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-orange-600 bg-white shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="clipboard-list" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        Reported
                    </span>
                </div>
                <div class="mt-4 relative z-10">
                    <p class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mb-1">VR Reports Received</p>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ $vrReports }}</h3>
                </div>
            </a>

            <!-- Card 5: Final Decisions -->
            <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}"
               class="group relative block p-5 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-indigo-500 to-purple-600 border border-indigo-400/30 shadow-md">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-indigo-600 bg-white shadow-md group-hover:scale-110 transition-transform">
                        <i data-lucide="check-circle" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        Disposed
                    </span>
                </div>
                <div class="mt-4 relative z-10">
                    <p class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider mb-1">Final Decisions</p>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ $decisions }}</h3>
                </div>
            </a>

        </div>
    @elseif($isInwardDesk)
        {{-- Inward Desk Metrics Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-5 rounded-3xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Today</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $todayInward ?? 0 }}</h3>
                <p class="text-xs text-cyan-100 mt-1 font-semibold">Registered Today</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Total</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $totalPetitions }}</h3>
                <p class="text-xs text-emerald-100 mt-1 font-semibold">Total Inward Petitions</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="corner-up-right" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Dispatched</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $transferredToDesks ?? 0 }}</h3>
                <p class="text-xs text-indigo-100 mt-1 font-semibold">Transferred to Seats</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="corner-up-left" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Returned</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $returnedToInward ?? 0 }}</h3>
                <p class="text-xs text-rose-100 mt-1 font-semibold">Returned from Seats</p>
            </div>
        </div>
    @else
        {{-- CPSP Desk Seat Metrics Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-5 rounded-3xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="folder" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Assigned</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $totalPetitions }}</h3>
                <p class="text-xs text-blue-100 mt-1 font-semibold">Petitions in Seat</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Active</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $processed }}</h3>
                <p class="text-xs text-emerald-100 mt-1 font-semibold">Processed by Desk</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="send" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Inquiry</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $forwarded }}</h3>
                <p class="text-xs text-pink-100 mt-1 font-semibold">Forwarded to Units</p>
            </div>
            <div class="p-5 rounded-3xl bg-gradient-to-br from-purple-500 to-indigo-700 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Disposed</span>
                </div>
                <h3 class="text-3xl font-black mt-3">{{ $decisions }}</h3>
                <p class="text-xs text-purple-100 mt-1 font-semibold">Final Decisions Taken</p>
            </div>
        </div>
    @endif

    <!-- Visual Charts Section (Permission-Driven) -->
    @if($canSeeSeatChart || $canSeeStatusChart)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- Chart 1: Seat Workload Distribution -->
            @if($canSeeSeatChart)
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-600"></i>
                                Seat Distribution
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Processed Petitions Across Active Seats</p>
                        </div>

                        <!-- Date Filter Form -->
                        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-slate-200">
                            <input type="date" name="from_date" value="{{ $fromDate }}"
                                   class="bg-transparent border-none text-[11px] font-bold text-slate-800 focus:ring-0 py-1 cursor-pointer">
                            <span class="text-slate-300 text-xs">—</span>
                            <input type="date" name="to_date" value="{{ $toDate }}"
                                   class="bg-transparent border-none text-[11px] font-bold text-slate-800 focus:ring-0 py-1 cursor-pointer">
                            <button type="submit" class="p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all" title="Filter Range">
                                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            </button>
                            @if(request()->has('from_date') || request()->has('to_date'))
                                <a href="{{ route('dashboard') }}" class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-600 rounded-lg transition-all" title="Reset">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="relative w-full h-[320px]">
                        <canvas id="seatPetitionChart"></canvas>
                    </div>
                </div>
            @endif

            <!-- Chart 2: Petition Status Distribution -->
            @if($canSeeStatusChart)
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="pie-chart" class="w-5 h-5 text-purple-600"></i>
                                Petition Status Distribution
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Status breakdown of processed petitions</p>
                        </div>
                    </div>

                    <div class="relative w-full h-[320px] flex items-center justify-center">
                        <canvas id="statusDoughnutChart"></canvas>
                    </div>
                </div>
            @endif

        </div>
    @endif

    @if($canSeeTrends || $canSeeNature)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @if($canSeeTrends)
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="trending-up" class="w-5 h-5 text-indigo-600"></i>
                                Petition Trends
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Daily submissions for the selected period</p>
                        </div>
                        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-slate-200">
                            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                                   class="bg-transparent border-none text-[11px] font-bold text-slate-800 focus:ring-0 py-1 cursor-pointer">
                            <span class="text-slate-300 text-xs">—</span>
                            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                                   class="bg-transparent border-none text-[11px] font-bold text-slate-800 focus:ring-0 py-1 cursor-pointer">
                            <button type="submit" class="p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all" title="Filter Range">
                                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                    <div class="relative h-[320px]">
                        <canvas id="petitionTrendsChart"></canvas>
                    </div>
                </div>
            @endif

            @if($canSeeNature)
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-5 h-5 text-purple-600"></i>
                            By Nature
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Distribution of petition types</p>
                    </div>
                    <div class="relative h-[320px] flex items-center justify-center">
                        <canvas id="petitionNatureChart"></canvas>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($canSeeRecentPetitions)
    <!-- Recent Petitions Activity Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Recent Petitions</h3>
                <p class="text-xs text-slate-500">Latest active records in the system</p>
            </div>
            @if($user->canAccess('view master reports'))
                <a href="{{ route('petitions.reports') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    <span>View All Reports</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>

        @if($recentPetitions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-100 font-bold">
                            <th class="px-6 py-3.5">Receipt No & Date</th>
                            <th class="px-6 py-3.5">Complainant</th>
                            <th class="px-6 py-3.5">Nature</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($recentPetitions as $petition)
                            @php
                                $targetPetition = $petition->originalPetition ?? $petition;
                                $complainant = $targetPetition->addresses->where('person_type', 'Complainant')->first();
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <span class="font-bold text-slate-900 block">{{ $petition->receipt_no }}</span>
                                    <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                                </td>
                                <td class="px-6 py-3.5 font-semibold">
                                    {{ $complainant->person_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-500">
                                    {{ $petition->nature_of_petition ?? 'General' }}
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    @if(in_array($petition->status, ['Closed', 'Sent_to_Govt']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Finalized
                                        </span>
                                    @elseif(in_array($petition->status, ['Forwarded', 'VR_Received']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            In Inquiry
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            {{ $petition->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <a href="{{ route('petitions.show', $petition->hashid) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 font-bold rounded-lg transition-colors"
                                       title="View Details">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>View</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center text-slate-400">
                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                <p class="font-medium text-xs">No active petitions found in current view.</p>
            </div>
        @endif
    </div>
    @endif

</div>
@endsection

@section('scripts')
@if($canSeeSeatChart || $canSeeStatusChart || $canSeeTrends || $canSeeNature)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const colors = {
                primary: '#4f46e5',   // Indigo
                secondary: '#9333ea', // Purple
                teal: '#0d9488',
                amber: '#d97706',
                rose: '#e11d48',
                cyan: '#0284c7',
                slate: '#64748b'
            };

            // Seat Bar Chart
            const seatCtx = document.getElementById('seatPetitionChart');
            if (seatCtx) {
                new Chart(seatCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartSeatLabels !!},
                        datasets: [{
                            label: 'Processed Petitions',
                            data: {!! $chartSeatData !!},
                            backgroundColor: colors.primary,
                            hoverBackgroundColor: colors.secondary,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 12 },
                                bodyFont: { size: 13, weight: 'bold' },
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function (ctx) {
                                        return ctx.parsed.y + ' Processed Petitions';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { weight: '600', size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 10 }, precision: 0 }
                            }
                        }
                    }
                });
            }

            // Status Doughnut Chart
            const statusCtx = document.getElementById('statusDoughnutChart');
            if (statusCtx) {
                const labels = {!! $chartStatusLabels !!};
                const data = {!! $chartStatusData !!};

                if (labels.length === 0 || data.every(v => Number(v) === 0)) {
                    statusCtx.parentElement.innerHTML = '<div class="text-center text-slate-400 text-xs font-semibold"><i data-lucide="pie-chart" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>No processed petition status data available.</div>';
                } else {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: [
                                    colors.primary, colors.teal, colors.amber,
                                    colors.secondary, colors.rose, colors.cyan, colors.slate
                                ],
                                borderWidth: 0,
                                hoverOffset: 12
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 16,
                                        font: { size: 11, weight: '600' }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            const trendsCtx = document.getElementById('petitionTrendsChart');
            if (trendsCtx) {
                const labels = {!! $chartTrendLabelsJson !!};
                const data = {!! $chartTrendCountsJson !!};
                if (data.every(value => value === 0)) {
                    trendsCtx.parentElement.innerHTML = '<div class="text-center text-slate-400 text-xs font-semibold"><i data-lucide="activity" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>No petition data available for this period.</div>';
                } else {
                    new Chart(trendsCtx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'New Petitions',
                                data,
                                backgroundColor: colors.primary,
                                hoverBackgroundColor: colors.secondary,
                                borderRadius: 6,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } }
                            }
                        }
                    });
                }
            }

            const natureCtx = document.getElementById('petitionNatureChart');
            if (natureCtx) {
                const labels = {!! $chartNatureLabels !!};
                const data = {!! $chartNatureCounts !!};
                if (labels.length === 0) {
                    natureCtx.parentElement.innerHTML = '<div class="text-center text-slate-400 text-xs font-semibold"><i data-lucide="pie-chart" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>No petition nature data available.</div>';
                } else {
                    new Chart(natureCtx, {
                        type: 'doughnut',
                        data: {
                            labels,
                            datasets: [{
                                data,
                                backgroundColor: [
                                    colors.primary, colors.teal, colors.amber,
                                    colors.secondary, colors.rose, colors.cyan, colors.slate
                                ],
                                borderWidth: 0,
                                hoverOffset: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { usePointStyle: true, padding: 16 }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endif
@endsection
