@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')

    <div class="space-y-6">
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2 print-hide">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <div class="flex flex-wrap justify-center sm:justify-start items-center gap-2.5 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                            Diagnostics
                        </h1>
                        <span
                            class="px-2.5 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-bold uppercase tracking-wider border border-indigo-500/30">
                            @if(($section ?? '') === 'cpsp')
                                CPSP Seats
                            @elseif(($section ?? '') === 't_section')
                                T-Section
                            @else
                                Seat-wise
                            @endif
                        </span>
                    </div>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Filter and analyze petition data
                    </p>
                </div>
                <div class="flex items-center gap-3 z-10">
                    <a href="{{ route('admin.seats.statistics.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'section' => $section ?? 'all']) }}"
                        class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 rounded-xl hover:bg-emerald-500/20 transition-all font-semibold text-sm shadow-sm">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        Excel Export
                    </a>
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
                <form method="GET" action="{{ route('admin.seats.statistics') }}" class="w-full">
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
                            <p id="dateRangeError" class="text-xs text-rose-600 mt-1 hidden font-semibold"></p>
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
                                    <a href="{{ route('admin.seats.statistics') }}" title="Clear Filters"
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

        <!-- Print Header -->
        <div class="hidden print-block mb-8 text-center border-b pb-6 border-slate-300">
            <h2 class="text-3xl font-black text-slate-900 mb-2">
                @if(($section ?? '') === 'cpsp')
                    CPSP Seats - Details of Petitions
                @elseif(($section ?? '') === 't_section')
                    T-Section - Details of Petitions
                @else
                    Seat Wise Details of Petitions
                @endif
            </h2>
            <p class="text-slate-600 font-medium text-lg">
                @if($dateFrom && $dateTo)
                    From: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} &nbsp; To:
                    {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                @elseif($dateFrom)
                    Since: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                @elseif($dateTo)
                    Up To: {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                @else
                    (All Time)
                @endif
            </p>

            <p class="text-slate-500 text-sm mt-1">
                Generated on {{ now()->format('M d, Y h:i A') }}
            </p>
        </div>

        <!-- Table -->
        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b text-xs font-bold text-slate-500 uppercase text-center">
                        <tr>
                            <th class="px-4 py-3 text-left align-middle border-r border-slate-200" rowspan="2">Seat</th>
                            <th class="px-4 py-3 align-middle border-r border-slate-200" rowspan="2"
                                title="Total Petitions">Total<br>Petitions</th>
                            <th class="px-4 py-3 align-middle border-r border-slate-200" rowspan="2"
                                title="Inward Petitions Pending">
                                Inward<br>Petitions<br>Pending</th>
                            <th class="px-4 py-3 align-middle border-r border-slate-200" rowspan="2"
                                title="Processed Petitions">
                                Processed<br>Petitions</th>
                            <th class="px-4 py-3 align-middle border-r border-slate-200" rowspan="2"
                                title="Forwarded Petitions">Forwarded<br>Petitions</th>
                            <th class="px-4 py-3 align-middle border-r border-slate-200" rowspan="2"
                                title="Verification Reports">VR<br>Received</th>
                            <th class="px-4 py-2 border-b border-slate-200 bg-slate-100/50" colspan="10">Final Decisions
                            </th>
                        </tr>
                        <tr class="bg-slate-50">
                            <th class="px-3 py-2 border-r border-slate-200 bg-slate-100/50" title="Total Final Decisions">
                                Final<br>Decisions</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">VC</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">VE</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">PE</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">SC</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">CV</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">Internal<br>Vigilance</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">ICell</th>
                            <th class="px-2 py-2 border-r border-slate-200 w-20">Closed</th>
                            <th class="px-2 py-2 w-20">Sent to<br>Govt</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($seats as $seat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $seat->seat_name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $seat->activeAssignment?->user?->name ?? 'Unassigned' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $seat->petitions_all_count }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ $seat->inward_pending_count }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-emerald-600">
                                    {{ $seat->petitions_processed_count }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ $seat->forwardings_out_count }}</td>
                                <td class="px-4 py-3 text-center">{{ $seat->vr_received_count }}</td>
                                <td class="px-4 py-3 text-center font-bold bg-slate-50">{{ $seat->decisions_made_count }}
                                </td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_vc_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_ve_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_pe_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_sc_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_cv_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_internal_vigilance_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_icell_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_closed_count }}</td>
                                <td class="px-2 py-3 text-center">{{ $seat->decisions_sent_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-10 text-slate-500">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($seats->isNotEmpty())
                        <tfoot class="bg-indigo-50 font-bold text-indigo-900">
                            <tr>
                                <!-- FIXED HERE -->
                                <td class="px-4 py-3 text-right">TOTAL</td>

                                <td class="text-center font-bold">{{ $seats->sum('petitions_all_count') }}</td>
                                <td class="text-center text-amber-700">{{ $seats->sum('inward_pending_count') }}</td>
                                <td class="text-center text-emerald-700">{{ $seats->sum('petitions_processed_count') }}</td>
                                <td class="text-center">{{ $seats->sum('forwardings_out_count') }}</td>
                                <td class="text-center">{{ $seats->sum('vr_received_count') }}</td>
                                <td class="text-center font-bold bg-indigo-100/50">{{ $seats->sum('decisions_made_count') }}
                                </td>
                                <td class="text-center">{{ $seats->sum('decisions_vc_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_ve_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_pe_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_sc_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_cv_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_internal_vigilance_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_icell_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_closed_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_sent_count') }}</td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>
        </div>


    </div>

    <style>
        @media print {
            body {
                visibility: hidden;
            }

            main,
            .space-y-6 {
                visibility: visible;
                position: absolute;
                width: 100%;
                top: 0;
                left: 0;
            }

            .print-hide {
                display: none !important;
            }

            .print-block {
                display: block !important;
            }

            table,
            th,
            td {
                border: 1px solid #ccc;
                border-collapse: collapse;
            }
        }
    </style>

@endsection