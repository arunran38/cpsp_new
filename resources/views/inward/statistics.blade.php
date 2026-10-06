@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-6">
    <!-- Page Header (Print Hidden) -->
    <div class="print:hidden bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-600 via-indigo-600 to-purple-600"></div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inward Statistics</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Seat-wise Data Sheet report of petitions registered in Inward and assigned to CPSP Seats.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()->canAccess('export_statistics') || Auth::user()->canAccess('export statistics') || Auth::user()->canAccess('access admin dashboard'))
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
    </div>

    <!-- Date Range Filter Form (Print Hidden) -->
    <div class="print:hidden bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('inward.statistics') }}" class="flex flex-wrap items-end gap-3.5">
            <!-- Date From -->
            <div class="w-full sm:w-64">
                <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-teal-600"></i>
                    Date From
                </label>
                <input type="date" 
                       name="date_from" 
                       id="date_from" 
                       value="{{ $dateFrom }}" 
                       class="h-10 w-full px-3.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/10 outline-none transition-all">
            </div>

            <!-- Date To -->
            <div class="w-full sm:w-64">
                <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-teal-600"></i>
                    Date To
                </label>
                <input type="date" 
                       name="date_to" 
                       id="date_to" 
                       value="{{ $dateTo }}" 
                       class="h-10 w-full px-3.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/10 outline-none transition-all">
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center gap-2 pt-1 sm:pt-0">
                <button type="submit" 
                        class="h-10 px-5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Apply Filter
                </button>
                @if($dateFrom || $dateTo)
                <a href="{{ route('inward.statistics') }}" 
                   class="h-10 px-4 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all flex items-center gap-1.5"
                   title="Reset date filter">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>


    <!-- Official Data Sheet Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none">
        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between print:bg-white print:p-0 print:border-b-2 print:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900">Inward Seat-Wise Petition Statistics Data Sheet</h2>
                @if($dateFrom || $dateTo)
                    <p class="text-xs text-slate-500 mt-0.5">
                        Report Period: 
                        <span class="font-semibold text-slate-700">{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Beginning' }}</span>
                        to 
                        <span class="font-semibold text-slate-700">{{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Today' }}</span>
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-0.5">Overall petition summary assigned to CPSP seats.</p>
                @endif
            </div>
            <span class="print:hidden text-xs font-semibold px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100">
                Data Sheet Report
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 border-b border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-wider print:bg-slate-200">
                        <th class="px-5 py-3.5 text-center w-12 border-r border-slate-200">#</th>
                        <th class="px-5 py-3.5 border-r border-slate-200">Concerned Seat</th>
                        <th class="px-5 py-3.5 border-r border-slate-200">Seat Occupant (Officer)</th>
                        <th class="px-5 py-3.5 text-center border-r border-slate-200">Received (New)</th>
                        <th class="px-5 py-3.5 text-right print:hidden">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($seatStats as $index => $stat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 text-center font-medium text-slate-400 border-r border-slate-200">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-900 border-r border-slate-200">
                                {{ $stat['seat_name'] }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-600 border-r border-slate-200">
                                {{ $stat['occupant'] }}
                            </td>
                            <td class="px-5 py-3.5 text-center border-r border-slate-200">
                                <span class="font-semibold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-100">
                                    {{ $stat['received'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right print:hidden">
                                <a href="{{ route('petitions.index', array_merge(request()->query(), ['tab' => 'inward', 'search' => $stat['seat_name']])) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-100 transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    View Petitions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-semibold text-slate-500">No petition statistics found for the selected date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300 text-xs">
                        <td colspan="3" class="px-5 py-4 text-right uppercase tracking-wider border-r border-slate-200">
                            Total Summary:
                        </td>
                        <td class="px-5 py-4 text-center text-sky-800 border-r border-slate-200 font-extrabold text-sm">
                            {{ $summaryTotals['received'] }}
                        </td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
