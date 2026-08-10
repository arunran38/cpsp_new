@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')

    <div class="space-y-6">
        <!-- Page Header & Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print-hide">
            <div class="flex flex-col lg:flex-row">

                <!-- Left Section: Header info -->
                <div
                    class="flex-1 p-6 lg:p-8 flex items-center relative overflow-hidden bg-gradient-to-br from-indigo-50/50 to-white">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-600"></div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-5 w-full">
                        <div
                            class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-indigo-100 flex shrink-0 items-center justify-center text-indigo-600 relative group">
                            <div
                                class="absolute inset-0 bg-indigo-600 rounded-2xl opacity-0 group-hover:opacity-5 transition-opacity">
                            </div>
                            <i data-lucide="bar-chart-2" class="w-7 h-7"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                                    Diagnostics
                                </h1>
                                <span
                                    class="px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-wider border border-indigo-200/50">
                                    Seat-wise
                                </span>
                            </div>
                            <p class="text-sm font-medium text-slate-500 mt-1">
                                Comprehensive view of office performance per unit/seat.
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="{{ route('admin.seats.statistics.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                                class="w-full sm:w-auto px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 hover:border-emerald-300 shadow-sm flex items-center justify-center gap-2 transition-all group">
                                <i data-lucide="file-spreadsheet"
                                    class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition-transform"></i>
                                Excel Export
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Section: Filters -->
                <div
                    class="bg-slate-50/50 border-t lg:border-t-0 lg:border-l border-slate-200 p-6 lg:p-8 flex items-center justify-center relative">
                    <form method="GET" action="{{ route('admin.seats.statistics') }}" class="w-full">
                        <div class="flex flex-col sm:flex-row items-end gap-4 w-full">
                            <div class="w-full sm:w-auto flex-1 space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Date From</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="h-4 w-4 text-slate-400"></i>
                                    </div>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}" placeholder="DD-MM-YYYY"
                                        max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-slate-300" style="color: #1e40af !important;">
                                </div>
                            </div>
 
                            <div class="w-full sm:w-auto flex-1 space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Date To</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="h-4 w-4 text-slate-400"></i>
                                    </div>
                                    <input type="date" name="date_to" value="{{ $dateTo }}" placeholder="DD-MM-YYYY"
                                        max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-slate-300" style="color: #1e40af !important;">
                                </div>
                            </div>

                            <div class="w-full sm:w-auto flex items-center gap-2">
                                <button type="submit"
                                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 rounded-xl hover:bg-indigo-700 hover:border-indigo-700 shadow-sm shadow-indigo-200 flex items-center justify-center gap-2 transition-all">
                                    <i data-lucide="filter" class="w-4 h-4"></i> Apply
                                </button>

                                @if($dateFrom || $dateTo)
                                    <a href="{{ route('admin.seats.statistics') }}" title="Clear Filters"
                                        class="px-3.5 py-2.5 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-600 shadow-sm flex items-center justify-center transition-all">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Print Header -->
        <div class="hidden print-block mb-8 text-center border-b pb-6 border-slate-300">
            <h2 class="text-3xl font-black text-slate-900 mb-2">Seat Wise Details of Petitions</h2>
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
                            <th class="px-6 py-4 text-left align-middle border-r border-slate-200" rowspan="2">Seat</th>
                            <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                title="Total Petitions">Total Petitions</th>
                            <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2" title="New Petitions">
                                New Petitions</th>
                            <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                title="Forwarded Petitions">Forwarded Petitions</th>
                            <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                title="Verification Reports">Verification Reports Received</th>
                            <th class="px-6 py-2 border-b border-slate-200 bg-slate-100/50" colspan="9">Final Decisions
                            </th>
                        </tr>
                        <tr class="bg-slate-50">
                            <th class="px-4 py-2 border-r border-slate-200 bg-slate-100/50" title="Total Final Decisions">
                                Final Decisions</th>
                            <th class="px-3 py-2 border-r border-slate-200">VC</th>
                            <th class="px-3 py-2 border-r border-slate-200">VE</th>
                            <th class="px-3 py-2 border-r border-slate-200">PE</th>
                            <th class="px-3 py-2 border-r border-slate-200">SC</th>
                            <th class="px-3 py-2 border-r border-slate-200">CV</th>
                            <th class="px-3 py-2 border-r border-slate-200">ICell</th>
                            <th class="px-3 py-2 border-r border-slate-200">Closed</th>
                            <th class="px-3 py-2">Sent to Govt</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($seats as $seat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $seat->seat_name }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $seat->activeAssignment?->user?->name ?? 'Unassigned' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">{{ $seat->petitions_all_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->petitions_received_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->forwardings_out_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->vr_received_count }}</td>
                                <td class="px-6 py-4 text-center font-bold bg-slate-50">{{ $seat->decisions_made_count }}
                                </td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_vc_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_ve_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_pe_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_sc_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_cv_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_icell_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_closed_count }}</td>
                                <td class="px-6 py-4 text-center">{{ $seat->decisions_sent_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-10 text-slate-500">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($seats->isNotEmpty())
                        <tfoot class="bg-indigo-50 font-bold text-indigo-900">
                            <tr>
                                <!-- FIXED HERE -->
                                <td class="px-6 py-4 text-right">TOTAL</td>

                                <td class="text-center">{{ $seats->sum('petitions_all_count') }}</td>
                                <td class="text-center">{{ $seats->sum('petitions_received_count') }}</td>
                                <td class="text-center">{{ $seats->sum('forwardings_out_count') }}</td>
                                <td class="text-center">{{ $seats->sum('vr_received_count') }}</td>
                                <td class="text-center font-bold bg-indigo-100/50">{{ $seats->sum('decisions_made_count') }}
                                </td>
                                <td class="text-center">{{ $seats->sum('decisions_vc_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_ve_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_pe_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_sc_count') }}</td>
                                <td class="text-center">{{ $seats->sum('decisions_cv_count') }}</td>
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