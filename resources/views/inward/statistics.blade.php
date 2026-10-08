@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-7xl')

@section('content')
<div class="space-y-6">
    <!-- Page Header (Print Hidden) -->
    <div class="print:hidden bg-white px-8 py-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-teal-400 via-emerald-500 to-teal-500"></div>
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-teal-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        
        <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 bg-gradient-to-br from-teal-50 to-emerald-50 rounded-2xl flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm transform hover:scale-105 transition-transform">
                <i data-lucide="bar-chart-2" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Inward Statistics</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">
                    Detailed seat-wise data sheet of petitions registered in Inward.
                </p>
            </div>
        </div>

<<<<<<< HEAD
        <div class="flex items-center gap-3">
            @if(Auth::user()->canAccess('export_statistics') || Auth::user()->canAccess('access admin dashboard'))
            <a href="{{ route('inward.statistics.export', request()->query()) }}" 
               class="px-4 py-2.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                Export CSV
            </a>
            <button onclick="window.print()" 
               class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print Data Sheet
            </button>
            @endif
            <a href="{{ route('petitions.index', ['tab' => 'inward']) }}" 
               class="px-4 py-2.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Inward Petitions
            </a>
        </div>
=======

>>>>>>> da6a7ac18f697e042582f40bbd68b1693797bcc2
    </div>

    <!-- Date Range Filter Form (Print Hidden) -->
    <div class="print:hidden bg-white py-4 px-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative z-10">
        <form method="GET" action="{{ route('inward.statistics') }}" class="flex flex-col md:flex-row md:items-end justify-between gap-5 w-full">
            
            <div class="flex flex-wrap items-end gap-5">
                <!-- Date From -->
                <div class="w-full sm:w-56">
                    <label for="date_from" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                        Date From
                    </label>
                    <div class="relative">
                        <input type="date" 
                               name="date_from" 
                               id="date_from" 
                               value="{{ $dateFrom }}" 
                               class="h-10 w-full px-4 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all">
                    </div>
                </div>

                <!-- Date To -->
                <div class="w-full sm:w-56">
                    <label for="date_to" class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                        Date To
                    </label>
                    <div class="relative">
                        <input type="date" 
                               name="date_to" 
                               id="date_to" 
                               value="{{ $dateTo }}" 
                               class="h-10 w-full px-4 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="h-10 px-5 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 outline-none focus:ring-4 focus:ring-slate-900/20">
                        <i data-lucide="filter" class="w-4 h-4 text-teal-400"></i>
                        Apply Filter
                    </button>
                    
                    @if($dateFrom || $dateTo)
                    <a href="{{ route('inward.statistics') }}" 
                       class="h-10 px-4 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 rounded-xl transition-all flex items-center gap-2 outline-none focus:ring-4 focus:ring-slate-100"
                       title="Reset date filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset
                    </a>
                    @endif
                </div>
            </div>

            <!-- Export Actions -->
            <div class="flex items-center gap-3 mt-3 md:mt-0">
                @if(Auth::user()->canAccess('export_statistics') || Auth::user()->canAccess('export statistics') || Auth::user()->canAccess('access admin dashboard'))
                <a href="{{ route('inward.statistics.export', request()->query()) }}" 
                   class="h-10 px-5 text-sm font-bold text-teal-700 bg-teal-50 border border-teal-100 hover:bg-teal-100 hover:border-teal-200 rounded-xl transition-all flex items-center gap-2 outline-none focus:ring-4 focus:ring-teal-500/20 shadow-sm whitespace-nowrap">
                    <i data-lucide="file-spreadsheet" class="w-4.5 h-4.5"></i>
                    Export Excel
                </a>
                @endif
            </div>

        </form>
    </div>


    <!-- Official Data Sheet Table Card -->
    <div class="bg-white border border-slate-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden print:border-none print:shadow-none">
        <div class="px-8 py-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:p-0 print:border-b-2 print:border-slate-800">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Seat-Wise Petition Statistics Data Sheet</h2>
                @if($dateFrom || $dateTo)
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="px-2 py-1 bg-slate-100 rounded text-xs font-semibold text-slate-600">Report Period</span>
                        <p class="text-sm text-slate-600 font-medium">
                            <span class="text-indigo-600 font-bold">{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }}</span>
                            <span class="mx-1 text-slate-400">to</span>
                            <span class="text-indigo-600 font-bold">{{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Today' }}</span>
                        </p>
                    </div>
                @else
                    <p class="text-sm text-slate-500 mt-1 font-medium">Overall petition summary assigned to CPSP seats.</p>
                @endif
            </div>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b-2 border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider print:bg-slate-200">
                        <th class="px-5 py-4">Concerned Seat</th>
                        <th class="px-5 py-4">Seat Occupant</th>
                        <th class="px-4 py-4 text-center">Total Inward</th>
                        @if(auth()->user()->canAccess('view processed statistics'))
                        <th class="px-4 py-4 text-center">Processed</th>
                        <th class="px-4 py-4 text-center">Pending</th>
                        <th class="px-6 py-4 w-40">Progress</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($seatStats as $index => $stat)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-5 py-4 font-extrabold text-slate-900">
                                {{ $stat['seat_name'] }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-600">
                                {{ $stat['occupant'] }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <a href="{{ route('petitions.index', ['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
                                   class="font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-indigo-700 px-2.5 py-0.5 rounded border border-slate-200 transition-colors block cursor-pointer text-center">
                                    {{ $stat['total_sent'] }}
                                </a>
                            </td>
                            @if(auth()->user()->canAccess('view processed statistics'))
                            <td class="px-4 py-4 text-center">
                                <a href="{{ route('petitions.index', ['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions']) }}"
                                   class="font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 hover:text-teal-900 px-2.5 py-0.5 rounded border border-teal-200 transition-colors block cursor-pointer text-center">
                                    {{ $stat['processed'] }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <a href="{{ route('petitions.index', ['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending']) }}"
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
                                        <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
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
                                    <p class="font-bold text-slate-500">No petition statistics found for the selected date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-900 text-white border-t-4 border-teal-500">
                        <td colspan="2" class="px-5 py-5 text-right uppercase tracking-wider font-extrabold text-xs text-slate-300">
                            Pendency Details (Total Summary):
                        </td>
                        <td class="px-4 py-5 text-center font-black text-lg text-white hover:text-indigo-300 transition-colors">
                            <a href="{{ route('petitions.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="block">
                                {{ $summaryTotals['totalSent'] }}
                            </a>
                        </td>
                        @if(auth()->user()->canAccess('view processed statistics'))
                        <td class="px-4 py-5 text-center font-black text-lg text-emerald-400 hover:text-emerald-300 transition-colors">
                            <a href="{{ route('petitions.index', ['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions']) }}" class="block">
                                {{ $summaryTotals['processed'] }}
                            </a>
                        </td>
                        <td class="px-4 py-5 text-center font-black text-lg text-rose-400 hover:text-rose-300 transition-colors">
                            <a href="{{ route('petitions.index', ['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending']) }}" class="block">
                                {{ $summaryTotals['pending'] }}
                            </a>
                        </td>
                        <td class="px-6 py-5">
                            @php
                                $totalPercent = $summaryTotals['totalSent'] > 0 ? round(($summaryTotals['processed'] / $summaryTotals['totalSent']) * 100) : 0;
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden shadow-inner">
                                    <div class="bg-gradient-to-r from-teal-400 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $totalPercent }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-300 w-8 text-right">{{ $totalPercent }}%</span>
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
