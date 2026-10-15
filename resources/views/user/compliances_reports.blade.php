@extends('layouts.user')

@section('title', 'Compliance Statistical Reports')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-8">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1 flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-7 h-7 text-emerald-400"></i>
                        Compliance Statistics
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Generate and view statistical reports for petition compliances based on order dates.
                    </p>
                </div>
                <div>
                    <a href="{{ route('compliances.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Compliances
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
            <form action="{{ route('compliances.reports') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
                
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div class="w-full sm:w-auto flex gap-3">
                    <button type="submit" class="flex-grow sm:flex-grow-0 inline-flex items-center justify-center gap-2 px-6 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="search" class="w-4 h-4"></i> Generate Report
                    </button>
                    @if($dateFrom || $dateTo)
                        <a href="{{ route('compliances.reports') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-colors" title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Statistics Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800">
                    Compliance Summary 
                    @if($dateFrom && $dateTo)
                        <span class="text-slate-500 font-medium text-sm ml-2">({{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})</span>
                    @elseif($dateFrom)
                        <span class="text-slate-500 font-medium text-sm ml-2">(From {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }})</span>
                    @elseif($dateTo)
                        <span class="text-slate-500 font-medium text-sm ml-2">(Up to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})</span>
                    @endif
                </h2>
                <a href="{{ route('compliances.reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i> Export to Excel
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Decision Type</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Total Orders</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Complied</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(['VC' => 'Vigilance Case (VC)', 'PE' => 'Preliminary Enquiry (PE)', 'VE' => 'Vigilance Enquiry (VE)', 'CV' => 'Confidential Verification (CV)', 'SC' => 'Surprise Check (SC)'] as $code => $label)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-semibold text-slate-700">
                                    {{ $label }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] h-[32px] px-2 text-sm font-bold rounded-full bg-slate-100 text-slate-700">
                                        {{ $stats[$code]['Total'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] h-[32px] px-2 text-sm font-bold rounded-full {{ $stats[$code]['Complied'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $stats[$code]['Complied'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] h-[32px] px-2 text-sm font-bold rounded-full {{ $stats[$code]['Pending'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $stats[$code]['Pending'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/80 border-t-2 border-slate-200">
                        <tr>
                            <td class="py-4 px-6 font-bold text-slate-900 uppercase">Grand Total</td>
                            <td class="py-4 px-6 text-center font-bold text-slate-900 text-lg">{{ $totals['Total'] }}</td>
                            <td class="py-4 px-6 text-center font-bold text-emerald-700 text-lg">{{ $totals['Complied'] }}</td>
                            <td class="py-4 px-6 text-center font-bold text-rose-700 text-lg">{{ $totals['Pending'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection
