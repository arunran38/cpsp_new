@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
@php
    // Basic metrics from existing model methods
    $totalUsers = \App\Models\User::countUser();
    $totalPetitions = \App\Models\Petition::count();
    $vrReports = \App\Models\PetitionForwarding::countOfVr();
    $forwarded = \App\Models\Petition::countForwardedPetitions();
    $decisions = \App\Models\Petition::countDecisionPetitions();

    // Vacant Seats mapping
    $vacantSeats = \App\Models\Seat::whereDoesntHave('seatUsers', function ($q) {
        $q->where('is_active', true);
    })->get();

    // Chart Data: User Trends (Last 6 Months)
    $last6Months = collect();
    for($i = 5; $i >= 0; $i--) {
        $last6Months->push(now()->startOfMonth()->subMonths($i)->format('M Y'));
    }
    
    $userStats = \App\Models\User::all()->map(function ($u) {
        return $u->created_at ? $u->created_at->format('M Y') : null;
    })->filter()->countBy();
    
    $userTrendData = $last6Months->map(fn($m) => $userStats->get($m, 0));
    
    $chartUserLabels = json_encode($last6Months->toArray());
    $chartUserData = json_encode($userTrendData->toArray());

    // Chart Data: Petition Status Distribution
    $statusStats = \App\Models\Petition::groupBy('status')->get(['status', \Illuminate\Support\Facades\DB::raw('count(*) as count')])->pluck('count', 'status');
    $chartStatusLabels = json_encode($statusStats->keys()->toArray());
    $chartStatusData = json_encode($statusStats->values()->toArray());

    // Latest Activity (Mocked if no dedicated activity table exists, using recent petitions/users)
    $latestUsers = \App\Models\User::orderBy('created_at', 'desc')->take(5)->get();
@endphp

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header alert (Mockup style) -->
    @if($vacantSeats->count() > 0)
        <div class="p-6 bg-[#fffbeb] border border-[#fef3c7] rounded-3xl shadow-sm transition-all hover:shadow-md">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#fef3c7] rounded-2xl flex items-center justify-center text-[#d97706] shadow-inner">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-[#92400e]">Vacant Seats Detected</h4>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($vacantSeats as $vSeat)
                                <span class="px-3 py-1 bg-white border border-[#fef3c7] rounded-lg text-[10px] font-bold text-[#b45309] shadow-sm uppercase tracking-wider">
                                    {{ $vSeat->seat_name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.seatuser.create') }}" class="px-6 py-3 bg-[#d97706] text-white text-sm font-black rounded-xl hover:bg-[#b45309] transition-all shadow-xl shadow-amber-200/50 hover:-translate-y-0.5 active:translate-y-0">
                    Assign Now
                </a>
            </div>
        </div>
    @endif

    <!-- 4 High-End Stat Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Total Petitions -->
        <div class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white border border-indigo-100/50">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">
                    +12%
                </span>
            </div>
            <div class="mt-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Total Petitions</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $totalPetitions }}</h3>
            </div>
        </div>


        <!-- Card 2: Forwarded -->
        <a href="{{ route('petitions.reports', ['tab' => 'forwarded']) }}" class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-rose-500/10 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 transition-colors group-hover:bg-rose-500 group-hover:text-white border border-rose-100/50">
                    <i data-lucide="send" class="w-6 h-6"></i>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-widest">
                    -2%
                </span>
            </div>
            <div class="mt-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Forwarded</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $forwarded }}</h3>
            </div>
        </a>

                <!-- Card 3: Verification Reports -->
        <a href="{{ route('petitions.reports', ['tab' => 'vrs']) }}" class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 transition-colors group-hover:bg-amber-500 group-hover:text-white border border-amber-100/50">
                    <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">
                    +5%
                </span>
            </div>
            <div class="mt-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Verification Reports</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $vrReports }}</h3>
            </div>
        </a>


        <!-- Card 4: Decisions -->
        <a href="{{ route('petitions.reports', ['tab' => 'decisions']) }}" class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 transition-colors group-hover:bg-emerald-500 group-hover:text-white border border-emerald-100/50">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">
                    +18%
                </span>
            </div>
            <div class="mt-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Decisions</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2 tracking-tight">{{ $decisions }}</h3>
            </div>
        </a>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- User Growth Trend -->
        <div class="p-8 bg-white border border-slate-200 rounded-[2.5rem] shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900">System Growth</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">User Registrations</p>
                </div>
            </div>
            <div class="h-72 w-full relative">
                <canvas id="userTrendChart"></canvas>
            </div>
        </div>

        <!-- Petition Distribution -->
        <div class="p-8 bg-white border border-slate-200 rounded-[2.5rem] shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900">Petition Status</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Global Distribution</p>
                </div>
            </div>
            <div class="h-72 w-full relative flex items-center justify-center">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colors = {
            indigo: '#6366f1',
            emerald: '#10b981',
            rose: '#f43f5e',
            amber: '#f59e0b',
            slate: '#94a3b8'
        };

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#94a3b8';

        // --- User Trend Chart ---
        const userCtx = document.getElementById('userTrendChart');
        if (userCtx) {
            new Chart(userCtx, {
                type: 'line',
                data: {
                    labels: {!! $chartUserLabels !!},
                    datasets: [{
                        label: 'New Users',
                        data: {!! $chartUserData !!},
                        borderColor: colors.indigo,
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: colors.indigo,
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: 'bold', size: 10 } } },
                        y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { stepSize: 1, font: { weight: 'bold', size: 10 } } }
                    }
                }
            });
        }

        // --- Status Doughnut Chart ---
        const statusCtx = document.getElementById('statusDoughnutChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! $chartStatusLabels !!},
                    datasets: [{
                        data: {!! $chartStatusData !!},
                        backgroundColor: [colors.rose, colors.emerald, colors.amber, colors.indigo, colors.slate],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 30, font: { weight: 'bold', size: 10 } } }
                    }
                }
            });
        }
    });
</script>
@endsection
