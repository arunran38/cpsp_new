@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')

@section('title', 'User Dashboard')

@section('content')
@php
    $userId = Auth::id();
    
    // Fetch user petitions
    if (auth()->user()->role === 'admin') {
        $petitions = \App\Models\Petition::all();
    } else {
        $petitions = \App\Models\Petition::where('user_id', $userId)->get();
    }
    
    // Stat Metrics
    $totalPetitions = $petitions->count();
    $forwarded = $petitions->filter(fn($p) => $p->status === 'Forwarded')->count();
    $vrsReceived = $petitions->filter(fn($p) => $p->status === 'VR_Received')->count();
    $finalDecisions = $petitions->filter(fn($p) => in_array($p->status, ['Closed', 'Sent_to_Govt']))->count();

    // Chart Data: Petitions by Nature
    $natureStats = $petitions->groupBy('nature_of_petition')->map->count();
    $chartNatures = json_encode($natureStats->keys()->toArray());
    $chartNatureCounts = json_encode($natureStats->values()->toArray());

    // Chart Data: Petitions over time (Last 6 months)
    $last6Months = collect();
    for($i = 5; $i >= 0; $i--) {
        // Use 1st of month to avoid boundary issues during end-of-month
        $last6Months->push(now()->startOfMonth()->subMonths($i)->format('M Y'));
    }
    
    $monthlyStats = $petitions->map(function ($p) {
        return \Carbon\Carbon::parse($p->date_of_petition_received)->format('M Y');
    })->countBy();
    
    $trendData = $last6Months->map(function ($month) use ($monthlyStats) {
        return $monthlyStats->get($month, 0);
    });

    $chartMonths = json_encode($last6Months->toArray());
    $chartTrendCounts = json_encode($trendData->toArray());

    // Recent Petitions Table Data
    if (auth()->user()->role === 'admin') {
        $recentPetitions = \App\Models\Petition::with(['addresses', 'latestForwarding', 'decision'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    } else {
        $recentPetitions = \App\Models\Petition::with(['addresses', 'latestForwarding', 'decision'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }
@endphp

<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-indigo-500 rounded-full blur-[80px] opacity-20 pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-teal-500 rounded-full blur-[90px] opacity-20 pointer-events-none"></div>
        
        <div class="flex items-center gap-4 z-10">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-500/30">
                <i data-lucide="layout-dashboard" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Dashboard Overview</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Welcome back, {{ Auth::user()->name ?? 'User' }}! Here's your petition analytics.</p>
            </div>
        </div>
        <div class="flex items-center gap-3 z-10 hidden sm:flex">
            @if(auth()->user()->role === 'user')
                <a href="{{ route('petitions.create') }}" class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-white transition-all rounded-xl shadow-lg shadow-indigo-500/30 bg-indigo-600 hover:bg-indigo-700 hover:-translate-y-0.5 focus:ring-4 focus:ring-indigo-500/20 active:translate-y-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    New Petition
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Stat Card 1 -->
        <a href="{{ route('petitions.reports', ['tab' => 'all']) }}" class="group block p-6 transition-all bg-white border border-slate-200 rounded-3xl relative overflow-hidden hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -z-10 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="flex items-center justify-between z-10 relative">
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                    Total
                </span>
            </div>
            <div class="mt-5 relative z-10">
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $totalPetitions }}</p>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Total Petitions</h3>
            </div>
        </a>

        <!-- Stat Card 2 -->
        <a href="{{ route('petitions.reports', ['tab' => 'forwarded']) }}" class="group block p-6 transition-all bg-white border border-slate-200 rounded-3xl relative overflow-hidden hover:shadow-2xl hover:shadow-amber-500/10 hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-bl-full -z-10 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="flex items-center justify-between z-10 relative">
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/30">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                    In Prog
                </span>
            </div>
            <div class="mt-5 relative z-10">
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $forwarded }}</p>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Forwarded Units</h3>
            </div>
        </a>

        <!-- Stat Card 3 -->
        <a href="{{ route('petitions.reports', ['tab' => 'vrs']) }}" class="group block p-6 transition-all bg-white border border-slate-200 rounded-3xl relative overflow-hidden hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -z-10 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="flex items-center justify-between z-10 relative">
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white shadow-lg shadow-purple-500/30">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                    Reported
                </span>
            </div>
            <div class="mt-5 relative z-10">
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $vrsReceived }}</p>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">VRs Received</h3>
            </div>
        </a>

        <!-- Stat Card 4 -->
        <a href="{{ route('petitions.reports', ['tab' => 'decisions']) }}" class="group block p-6 transition-all bg-white border border-slate-200 rounded-3xl relative overflow-hidden hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -z-10 transition-transform group-hover:scale-110 duration-500"></div>
            <div class="flex items-center justify-between z-10 relative">
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                    Completed
                </span>
            </div>
            <div class="mt-5 relative z-10">
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $finalDecisions }}</p>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Final Decisions</h3>
            </div>
        </a>
    </div>

    <!-- Visualizations Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <!-- Monthly Trends Chart (Spans 2 columns on wide screens) -->
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-6 relative z-10">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-5 h-5 text-indigo-500"></i> Petition Trends
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Submissions over the last 6 months</p>
                </div>
            </div>
            <div class="relative h-[300px] w-full z-10">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Petition Natures Breakdown -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-6 relative z-10">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-purple-500"></i> By Nature
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Distribution of petition types</p>
                </div>
            </div>
            <div class="relative h-[300px] w-full flex items-center justify-center z-10">
                <canvas id="natureChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Petitions Activity -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 border border-slate-200">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <h2 class="text-base font-bold text-slate-900">Recent Petitions</h2>
            </div>
            <a href="{{ route('petitions.index') }}" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 hover:text-indigo-700 transition-colors uppercase tracking-wider">View Full List</a>
        </div>
        
        <div class="p-0">
            @if($recentPetitions->count() > 0)
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-400 font-bold uppercase tracking-widest bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 whitespace-nowrap">Petition #</th>
                                <th class="px-6 py-4 whitespace-nowrap">Subject Nature</th>
                                <th class="px-6 py-4 whitespace-nowrap">Submitted On</th>
                                <th class="px-6 py-4 whitespace-nowrap">Status</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            @foreach($recentPetitions as $petition)
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $petition->petition_no }}</div>
                                    </td>
                                    <td class="px-6 py-4 min-w-[200px]">
                                        <span class="text-slate-600 font-medium truncate line-clamp-1 w-64" title="{{ $petition->nature_of_petition }}">{{ $petition->nature_of_petition }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-slate-500 font-medium">
                                            {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                                Finalized
                                            </span>
                                        @elseif($petition->status === 'Forwarded' || $petition->status === 'VR_Received')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-blue-100 text-blue-700">
                                                In Progress
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                                                {{ $petition->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('petitions.show', $petition->petition_id) }}" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition-all">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center flex flex-col items-center">
                    <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mb-3 border border-slate-100">
                        <i data-lucide="clipboard-x" class="w-6 h-6 text-slate-300"></i>
                    </div>
                    <span class="text-slate-500 font-medium text-sm">No petitions found on your account.</span>
                    <a href="{{ route('petitions.create') }}" class="mt-3 text-sm font-bold text-indigo-600 hover:text-indigo-800 underline underline-offset-4">Create your first petition</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Colors & Design System Tokens ---
        const colors = {
            primary: '#6366f1',    // Indigo 500
            secondary: '#a855f7',  // Purple 500
            success: '#14b8a6',    // Teal 500
            warning: '#f59e0b',    // Amber 500
            danger: '#f43f5e',     // Rose 500
            info: '#3b82f6',       // Blue 500
            slate: '#cbd5e1',      // Slate 300
            slateDark: '#475569',  // Slate 600
        };

        const gridColor = '#f1f5f9';
        const fontFamily = "'Inter', sans-serif";

        Chart.defaults.font.family = fontFamily;
        Chart.defaults.color = colors.slateDark;

        // --- 2. Trend Area Chart ---
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const chartMonths = {!! $chartMonths !!};
            const chartTrendCounts = {!! $chartTrendCounts !!};

            const gradient = trendCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)'); // Indigo 500 w/ opacity
            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

            if (chartMonths.length === 0 || chartTrendCounts.every(val => val === 0)) {
                trendCtx.style.display = 'none';
                trendCtx.parentElement.innerHTML += '<div class="absolute inset-0 flex flex-col justify-center items-center text-slate-400 text-sm font-medium"><i data-lucide="activity" class="w-8 h-8 mb-2 opacity-50"></i>Not enough data for trends</div>';
            } else {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: chartMonths,
                        datasets: [{
                            label: 'New Petitions',
                            data: chartTrendCounts,
                            borderColor: colors.primary,
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: colors.primary,
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 13 },
                                bodyFont: { size: 14, weight: 'bold' },
                                padding: 12,
                                displayColors: false,
                                cornerRadius: 8,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { size: 12, weight: '500' } }
                            },
                            y: {
                                grid: { color: gridColor, drawBorder: false },
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 12 } }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' },
                    }
                });
            }
        }

        // --- 3. Nature Doughnut Chart ---
        const natureCtx = document.getElementById('natureChart');
        if (natureCtx) {
            const chartNatures = {!! $chartNatures !!};
            const chartNatureCounts = {!! $chartNatureCounts !!};

            if (chartNatures.length === 0) {
                natureCtx.style.display = 'none';
                natureCtx.parentElement.innerHTML += '<div class="absolute inset-0 flex flex-col justify-center items-center text-slate-400 text-sm font-medium"><i data-lucide="pie-chart" class="w-8 h-8 mb-2 opacity-50"></i>No Data Available</div>';
            } else {
                new Chart(natureCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartNatures,
                        datasets: [{
                            data: chartNatureCounts,
                            backgroundColor: [
                                colors.primary, colors.success, colors.warning, 
                                colors.secondary, colors.danger, colors.info, '#94a3b8'
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { size: 12, weight: '500' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 11, weight: 'normal' },
                                bodyFont: { size: 14, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                            }
                        }
                    }
                });
            }
        }
    });
</script>
@endsection