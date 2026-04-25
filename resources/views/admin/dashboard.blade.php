@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    @php
        $fromDate = request('from_date', now()->subDays(6)->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        // Basic metrics filtered by date range
        $totalUsers = \App\Models\User::countUser(); // Lifetime total as per agreement

        $totalPetitions = \App\Models\Petition::whereBetween('date_of_petition_received', [$fromDate, $toDate])->count();

        $vrReports = \App\Models\PetitionForwarding::where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('vr_received_at_cpsp_date', [$fromDate, $toDate])
                ->orWhereBetween('vr_date', [$fromDate, $toDate]);
        })
            ->whereHas('petition', function ($query) {
                $query->where('status', 'VR_Received');
            })
            ->count();

        $forwarded = \App\Models\PetitionForwarding::whereBetween('forwarded_date', [$fromDate, $toDate])
            ->whereHas('petition', function ($query) {
                $query->where('status', 'Forwarded');
            })
            ->count();

        $decisions = \App\Models\Decision::whereBetween('decision_date', [$fromDate, $toDate])->count();

        // Vacant Seats mapping
        $vacantSeats = \App\Models\Seat::whereDoesntHave('seatUsers', function ($q) {
            $q->where('is_active', true);
        })->get();

        // Chart Data: Seat vs Petitions (Bar Chart)
        $seatStats = \App\Models\Seat::where('is_active', true)
            ->withCount([
                'petitionsReceived' => function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('date_of_petition_received', [$fromDate, $toDate]);
                }
            ])
            ->get();

        $chartSeatLabels = json_encode($seatStats->pluck('seat_name')->toArray());
        $chartSeatData = json_encode($seatStats->pluck('petitions_received_count')->toArray());

        // Chart Data: Petition Status Distribution (Filtered)
        $statusStats = \App\Models\Petition::whereBetween('date_of_petition_received', [$fromDate, $toDate])
            ->groupBy('status')
            ->get(['status', \Illuminate\Support\Facades\DB::raw('count(*) as count')])
            ->pluck('count', 'status');

        $chartStatusLabels = json_encode($statusStats->keys()->toArray());
        $chartStatusData = json_encode($statusStats->values()->toArray());

        $latestUsers = \App\Models\User::orderBy('created_at', 'desc')->take(5)->get();
    @endphp

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">

        <!-- Header alert (Premium Amber Style) -->

        <!-- Header alert (Premium Amber Style) -->
        @if($vacantSeats->count() > 0)
            <div
                class="p-6 bg-amber-50 border border-amber-100 rounded-3xl shadow-sm transition-all hover:shadow-md relative overflow-hidden group">
                <div
                    class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-all duration-500">
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-amber-600 shadow-sm border border-amber-100">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-amber-900">Vacant Seats Detected</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($vacantSeats as $vSeat)
                                    <span
                                        class="px-3 py-1 bg-white border border-amber-200 rounded-lg text-[10px] font-black text-amber-700 shadow-sm uppercase tracking-wider">
                                        {{ $vSeat->seat_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.seatuser.create') }}"
                        class="px-6 py-3 bg-amber-600 text-white text-sm font-black rounded-xl hover:bg-amber-700 transition-all shadow-xl shadow-amber-200/50 hover:-translate-y-0.5 active:translate-y-0">
                        Assign Now
                    </a>
                </div>
            </div>
        @endif

        <!-- 4 High-End Stat Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Total Petitions -->
            <a href="{{ route('petitions.reports', ['tab' => 'all']) }}"
                class="block p-6 bg-indigo-50 border border-indigo-100 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300">
                <div class="flex items-start justify-between relative z-10">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white border border-indigo-100/50 shadow-sm font-black">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-white text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">
                        Total
                    </span>
                </div>
                <div class="mt-6 relative z-10">
                    <p class="text-xs font-bold text-indigo-700/60 uppercase tracking-widest leading-none">Total Petitions
                    </p>
                    <h3 class="text-4xl font-black text-indigo-900 mt-2 tracking-tight">{{ $totalPetitions }}</h3>
                </div>
                <div
                    class="absolute -bottom-10 -right-10 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl group-hover:bg-indigo-500/10 transition-all duration-500">
                </div>
            </a>


            <!-- Card 2: Forwarded -->
            <a href="{{ route('petitions.reports', ['tab' => 'forwarded']) }}"
                class="p-6 bg-rose-50 border border-rose-100 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-rose-500/10 transition-all duration-300">
                <div class="flex items-start justify-between relative z-10">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-rose-600 transition-colors group-hover:bg-rose-600 group-hover:text-white border border-rose-100/50 shadow-sm">
                        <i data-lucide="send" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-white text-rose-600 border border-rose-100 uppercase tracking-widest shadow-sm">
                        In Prog
                    </span>
                </div>
                <div class="mt-6 relative z-10">
                    <p class="text-xs font-bold text-rose-700/60 uppercase tracking-widest leading-none">Forwarded</p>
                    <h3 class="text-4xl font-black text-rose-900 mt-2 tracking-tight">{{ $forwarded }}</h3>
                </div>
                <div
                    class="absolute -bottom-10 -right-10 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-all duration-500">
                </div>
            </a>

            <!-- Card 3: Verification Reports -->
            <a href="{{ route('petitions.reports', ['tab' => 'vrs']) }}"
                class="p-6 bg-amber-50 border border-amber-100 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300">
                <div class="flex items-start justify-between relative z-10">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white border border-amber-100/50 shadow-sm">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-white text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">
                        Reported
                    </span>
                </div>
                <div class="mt-6 relative z-10">
                    <p class="text-xs font-bold text-amber-700/60 uppercase tracking-widest leading-none">Verification
                        Reports Received</p>
                    <h3 class="text-4xl font-black text-amber-900 mt-2 tracking-tight">{{ $vrReports }}</h3>
                </div>
                <div
                    class="absolute -bottom-10 -right-10 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-all duration-500">
                </div>
            </a>


            <!-- Card 4: Decisions -->
            <a href="{{ route('petitions.reports', ['tab' => 'decisions']) }}"
                class="p-6 bg-emerald-50 border border-emerald-100 rounded-3xl shadow-sm relative overflow-hidden group hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300">
                <div class="flex items-start justify-between relative z-10">
                    <div
                        class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white border border-emerald-100/50 shadow-sm">
                        <i data-lucide="check-square" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-white text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">
                        Completed
                    </span>
                </div>
                <div class="mt-6 relative z-10">
                    <p class="text-xs font-bold text-emerald-700/60 uppercase tracking-widest leading-none">Decisions</p>
                    <h3 class="text-4xl font-black text-emerald-900 mt-2 tracking-tight">{{ $decisions }}</h3>
                </div>
                <div
                    class="absolute -bottom-10 -right-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all duration-500">
                </div>
            </a>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div
                class="lg:col-span-1 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm relative overflow-hidden flex flex-col">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6 relative z-10">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-500"></i> Seat Distribution
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Petition load distribution across active seats</p>
                    </div>

                    <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                            <input type="date" name="from_date" value="{{ $fromDate }}"
                                class="bg-transparent border-none text-[10px] font-bold text-slate-600 focus:ring-0 py-1 cursor-pointer">
                            <span class="text-slate-300 text-[10px] font-black mx-1">—</span>
                            <input type="date" name="to_date" value="{{ $toDate }}"
                                class="bg-transparent border-none text-[10px] font-bold text-slate-600 focus:ring-0 py-1 cursor-pointer">
                        </div>
                        <button type="submit"
                            class="p-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                        </button>
                        @if(request()->has('from_date') || request()->has('to_date'))
                            <a href="{{ route('admin.dashboard') }}"
                                class="p-2 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </form>
                </div>
                <div class="relative flex-grow w-full z-10 min-h-[350px]">
                    <canvas id="seatPetitionChart"></canvas>
                </div>
            </div>

            <div
                class="col-span-full lg:col-span-1 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-6 relative z-10">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-5 h-5 text-purple-500"></i> Petition Status
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Overall status distribution</p>
                    </div>
                </div>
                <div class="relative h-[300px] w-full flex items-center justify-center z-10">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            // --- Seat Petitions Bar Chart ---
            const seatCtx = document.getElementById('seatPetitionChart');
            if (seatCtx) {
                new Chart(seatCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartSeatLabels !!},
                        datasets: [{
                            label: 'Petitions',
                            data: {!! $chartSeatData !!},
                            backgroundColor: colors.primary,
                            hoverBackgroundColor: colors.secondary,
                            borderRadius: 6,
                            borderSkipped: false,
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
                                borderRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        return context.parsed.y + ' Petitions';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: true,
                                    drawOnChartArea: false,
                                    drawTicks: true,
                                    color: gridColor,
                                    lineWidth: 2
                                },
                                ticks: { font: { weight: '600', size: 10 }, padding: 8 }
                            },
                            y: {
                                grid: { color: gridColor, drawBorder: false },
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 10 }, precision: 0 }
                            }
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
                            backgroundColor: [
                                colors.primary, colors.success, colors.warning,
                                colors.secondary, colors.danger, colors.info, '#94a3b8'
                            ],
                            borderWidth: 0,
                            hoverOffset: 15
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
                                    font: { weight: '500', size: 12 }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 11, weight: 'normal' },
                                bodyFont: { size: 14, weight: 'bold' },
                                padding: 12,
                                borderRadius: 8,
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection