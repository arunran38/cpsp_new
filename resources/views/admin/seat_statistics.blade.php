@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')

    <div class="space-y-6">
        <!-- Page Header -->
        <div
            class="bg-white px-4 py-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center gap-4 relative overflow-hidden print-hide">

            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

            <!-- Left Section -->
            <div
                class="bg-white px-4 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden print-hide">

                <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

                <!-- Left Section -->
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                        <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            Seat-wise Diagnostics
                        </h1>
                        <p class="text-sm font-medium text-slate-500 mt-0.5">
                            Comprehensive view of office performance per unit/seat.
                        </p>
                    </div>
                </div>

                <!-- Right Section -->
                <div class="ml-auto flex items-center">
                    <a href="{{ route('admin.seats.statistics.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                        class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-sm flex items-center gap-2 transition-all focus:ring-4 focus:ring-emerald-500/20">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        Excel Export
                    </a>
                </div>

            </div>

            <!-- Filters -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 print-hide">
                <form method="GET" action="{{ route('admin.seats.statistics') }}"
                    class="flex flex-col sm:flex-row items-end gap-5">

                    <div class="w-full sm:w-1/3 space-y-1.5">
                        <label class="text-sm font-bold text-slate-700">Date From</label>
                        <input type="date" placeholder="DD-MM-YYYY" name="date_from" value="{{ $dateFrom }}" max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm font-medium">
                    </div>

                    <div class="w-full sm:w-1/3 space-y-1.5">
                        <label class="text-sm font-bold text-slate-700">Date To</label>
                        <input type="date" placeholder="DD-MM-YYYY" name="date_to" value="{{ $dateTo }}" max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 text-sm font-medium">
                    </div>

                    <div class="w-full sm:w-1/3 flex gap-3">
                        <button type="submit"
                            class="w-full px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm flex items-center justify-center gap-2">
                            <i data-lucide="filter" class="w-4 h-4"></i> Apply
                        </button>

                        @if($dateFrom || $dateTo)
                            <a href="{{ route('admin.seats.statistics') }}"
                                class="px-5 py-2.5 text-sm font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 flex items-center justify-center">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </form>
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
                                <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                    title="New Petitions">
                                    New Petitions</th>
                                <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                    title="Forwarded Petitions">Forwarded Petitions</th>
                                <th class="px-6 py-4 align-middle border-r border-slate-200" rowspan="2"
                                    title="Verification Reports">Verification Reports Received</th>
                                <th class="px-6 py-2 border-b border-slate-200 bg-slate-100/50" colspan="7">Final Decisions
                                </th>
                            </tr>
                            <tr class="bg-slate-50">
                                <th class="px-4 py-2 border-r border-slate-200 bg-slate-100/50"
                                    title="Total Final Decisions">
                                    Final Decisions</th>
                                <th class="px-3 py-2 border-r border-slate-200">PE</th>
                                <th class="px-3 py-2 border-r border-slate-200">SC</th>
                                <th class="px-3 py-2 border-r border-slate-200">QV</th>
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
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_pe_count }}</td>
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_sc_count }}</td>
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_qv_count }}</td>
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_icell_count }}</td>
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_closed_count }}</td>
                                    <td class="px-6 py-4 text-center">{{ $seat->decisions_sent_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-10 text-slate-500">
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
                                    <td class="text-center">{{ $seats->sum('decisions_pe_count') }}</td>
                                    <td class="text-center">{{ $seats->sum('decisions_sc_count') }}</td>
                                    <td class="text-center">{{ $seats->sum('decisions_qv_count') }}</td>
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
