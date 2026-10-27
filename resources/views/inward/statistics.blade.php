@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-7xl')

@section('content')
    <div class="space-y-6">
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
                        Inward Statistics
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Detailed seat-wise data sheet of petitions registered in Inward.
                    </p>
                </div>
                <div class="flex items-center gap-3 z-10">
                    @if(Auth::user()->canAccess('export statistics') || Auth::user()->canAccess('access admin dashboard'))
                        <a href="{{ route('inward.statistics.export', request()->query()) }}"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 rounded-xl hover:bg-emerald-500/20 transition-all font-semibold text-sm shadow-sm">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            Excel Export
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden print-hide mb-6">
            <div class="p-4 sm:p-5 bg-slate-50/50">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="filter" class="w-4 h-4 text-teal-600"></i>
                    <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider">Filter Statistics</h3>
                </div>
                <form method="GET" action="{{ route('inward.statistics') }}" class="w-full">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 xl:gap-4 items-end w-full">
                        <!-- Section Filter (Segmented Control) -->
                        <div class="sm:col-span-2 lg:col-span-4 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="layers" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Section</span>
                            </label>
                            <div class="flex items-center p-1 bg-blue-50/50 border border-blue-200/80 rounded-xl w-full h-11 shadow-inner">
                                @if($canViewTSection ?? false)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="all" class="sr-only peer"
                                            {{ ($section ?? 'all') === 'all' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-3">
                                            All Sections
                                        </span>
                                    </label>
                                @endif

                                <label class="flex-1 cursor-pointer h-full">
                                    <input type="radio" name="section" value="cpsp" class="sr-only peer"
                                        {{ ($section ?? '') === 'cpsp' || !($canViewTSection ?? false) ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-3">
                                        CPSP
                                    </span>
                                </label>

                                @if($canViewTSection ?? false)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="t_section" class="sr-only peer"
                                            {{ ($section ?? '') === 't_section' ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-3">
                                            T-Section
                                        </span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="sm:col-span-1 lg:col-span-3 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <span>Date From</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="h-4 w-4 text-blue-400"></i>
                                </div>
                                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" placeholder="DD-MM-YYYY"
                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                    class="w-full pl-10 pr-3 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Date To -->
                        <div class="sm:col-span-1 lg:col-span-3 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <span>Date To</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="h-4 w-4 text-blue-400"></i>
                                </div>
                                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" placeholder="DD-MM-YYYY"
                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                    class="w-full pl-10 pr-3 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="sm:col-span-2 lg:col-span-2 flex flex-col justify-end">
                            <div class="h-4 mb-1.5 hidden lg:block"></div>
                            <div class="flex items-center gap-2 w-full h-11">
                                <button type="submit"
                                    class="flex-1 h-11 px-4 text-sm font-bold text-white bg-teal-600 border border-teal-600 rounded-xl hover:bg-teal-700 hover:border-teal-700 shadow-sm shadow-teal-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                                    <i data-lucide="filter" class="w-4 h-4 text-white"></i>
                                    <span>Apply</span>
                                </button>

                                @if($dateFrom || $dateTo || (($section ?? 'all') !== 'all'))
                                    <a href="{{ route('inward.statistics') }}" title="Clear Filters"
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
                class="px-8 py-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:p-0 print:border-b-2 print:border-slate-800">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">
                        @if(($section ?? '') === 'cpsp')
                            CPSP Seats - Petition Statistics Data Sheet
                        @elseif(($section ?? '') === 't_section')
                            T-Section - Petition Statistics Data Sheet
                        @else
                            Seat-Wise Petition Statistics Data Sheet
                        @endif
                    </h2>
                    @if($dateFrom || $dateTo)
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="px-2 py-1 bg-slate-100 rounded text-xs font-semibold text-slate-600">Report
                                Period</span>
                            <p class="text-sm text-slate-600 font-medium">
                                <span
                                    class="text-indigo-600 font-bold">{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }}</span>
                                <span class="mx-1 text-slate-400">to</span>
                                <span
                                    class="text-indigo-600 font-bold">{{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Today' }}</span>
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 mt-1 font-medium">
                            @if(($section ?? '') === 'cpsp')
                                Overall petition summary assigned to CPSP seats.
                            @elseif(($section ?? '') === 't_section')
                                Overall petition summary assigned to T-Section.
                            @else
                                Overall petition summary assigned to CPSP and T-Section seats.
                            @endif
                        </p>
                    @endif
                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b-2 border-slate-100 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider print:bg-slate-200">
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
                                    <a href="{{ route('inward.enter_petition', array_filter(['seat_id' => $stat['seat_id'], 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1])) }}#view-inward-petitions"
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
                                <a href="{{ route('inward.enter_petition', array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to'), 'hide_form' => 1])) }}#view-inward-petitions"
                                    class="block">
                                    {{ $summaryTotals['totalSent'] }}
                                </a>
                            </td>
                            @if(auth()->user()->canAccess('view processed statistics'))
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-emerald-400 hover:text-emerald-300 transition-colors">
                                    <a href="{{ route('petitions.index', ['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'All_Final_Decisions']) }}"
                                        class="block">
                                        {{ $summaryTotals['processed'] }}
                                    </a>
                                </td>
                                <td
                                    class="px-4 py-5 text-center font-black text-lg text-rose-400 hover:text-rose-300 transition-colors">
                                    <a href="{{ route('petitions.index', ['date_from' => request('date_from'), 'date_to' => request('date_to'), 'status' => 'Pending']) }}"
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
        </div>
    </div>
@endsection