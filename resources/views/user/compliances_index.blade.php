@extends('layouts.user')

@section('title', 'Petition Compliances')
@section('container_width', 'max-w-full')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
            showModal: false, 
            selectedDecision: null,
            activeTab: '{{ request('tab', 'pending') }}',
            filterDecision: '{{ request('filter_decision', '') }}',
            openModal(decision) {
                this.selectedDecision = decision;
                this.showModal = true;
            }
        }">

        <!-- Premium Header Section -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-8">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>

            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1
                        class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1 flex items-center gap-2">
                        <i data-lucide="check-square" class="w-7 h-7 text-emerald-400"></i>
                        Petition Compliances
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Track and update execution details for VC, VE, PE, CV, and SC orders.
                    </p>
                </div>
                <div class="z-10">
                    <a href="{{ route('compliances.reports') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all shadow-sm backdrop-blur-sm">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        Statistical Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards (Matched Design) -->
        <div class="grid w-full grid-cols-1 items-stretch gap-5 px-4 py-4 md:grid-cols-3">
            <!-- Pending Card -->
            <div @click="activeTab = 'pending'; filterDecision = ''"
                 class="group relative flex h-[260px] cursor-pointer flex-col overflow-hidden rounded-3xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-900/10">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-rose-500/10 blur-2xl transition-all duration-300 group-hover:bg-rose-500/20"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-rose-600 shadow-sm ring-1 ring-rose-100 transition-transform duration-300 group-hover:scale-105">
                        <i data-lucide="clock" class="h-5 w-5"></i>
                    </div>
                    <span class="rounded-full border border-rose-200 bg-white/80 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-rose-700 shadow-sm">
                        Pending
                    </span>
                </div>

                <div class="relative z-10 mt-auto grid grid-rows-[96px_44px] gap-3">
                    <div class="flex flex-col justify-end">
                        <p class="text-6xl font-black leading-none tracking-tight text-rose-900">{{ $pendingCounts['Total'] }}</p>
                        <h3 class="mt-2 text-sm font-bold uppercase tracking-[0.18em] text-rose-700/80">Total Pending</h3>
                    </div>
                    
                    <div class="flex flex-wrap content-end gap-1.5 border-t border-rose-200/60 pt-2.5">
                        @php
                            $decisionBadgeStyles = [
                                'VC' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                                'PE' => 'border-blue-200 bg-blue-50 text-blue-700',
                                'VE' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
                                'CV' => 'border-purple-200 bg-purple-50 text-purple-700',
                                'SC' => 'border-orange-200 bg-orange-50 text-orange-700',
                            ];
                        @endphp
                        @foreach(['VC', 'PE', 'VE', 'CV', 'SC'] as $type)
                            <span class="inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-bold shadow-sm {{ $decisionBadgeStyles[$type] }}">
                                {{ $type }}: {{ $pendingCounts[$type] }}
                            </span>
                        @endforeach
                        @if($pendingCounts['Overdue'] > 0)
                            <span class="inline-flex items-center gap-1 rounded bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm animate-pulse">
                                <i data-lucide="alert-triangle" class="w-2.5 h-2.5"></i>
                                >10d: {{ $pendingCounts['Overdue'] }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Complied Card -->
            <div @click="activeTab = 'complied'"
                 class="group relative flex h-[260px] cursor-pointer flex-col overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-900/10">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-500/10 blur-2xl transition-all duration-300 group-hover:bg-emerald-500/20"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100 transition-transform duration-300 group-hover:scale-105">
                        <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                    </div>
                    <span class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                        Complied
                    </span>
                </div>

                <div class="relative z-10 mt-auto grid grid-rows-[96px_44px] gap-3">
                    <div class="flex flex-col justify-end">
                        <p class="text-6xl font-black leading-none tracking-tight text-emerald-900">{{ $totalCompliedCount }}</p>
                        <h3 class="mt-2 text-sm font-bold uppercase tracking-[0.18em] text-emerald-700/80">Total Complied</h3>
                    </div>
                    <div aria-hidden="true"></div>
                </div>
            </div>

            <!-- Chart Card -->
            <div class="group relative flex h-[260px] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                <div class="relative z-10 flex min-h-0 flex-1 flex-col justify-center">
                    <canvas id="pendingChart" class="h-full min-h-0 w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-semibold">Success</h3>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-semibold">Error</h3>
                    <p class="text-sm mt-1">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Premium Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6 bg-white p-2 rounded-2xl shadow-sm border border-slate-200/80 ring-1 ring-slate-900/5">
            
            <div class="flex flex-wrap items-center gap-3 w-full">
                <!-- Tabs (Segmented Control) -->
                <div class="flex items-center p-1 bg-slate-50/80 rounded-xl border border-slate-100 shrink-0">
                    <button @click="activeTab = 'pending'; filterDecision = ''"
                        :class="activeTab === 'pending' ? 'bg-white text-rose-600 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700'"
                        class="relative flex items-center justify-center gap-2 px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        <i data-lucide="clock" class="w-4 h-4"></i> Pending
                    </button>
                    <button @click="activeTab = 'complied'; filterDecision = ''"
                        :class="activeTab === 'complied' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700'"
                        class="relative flex items-center justify-center gap-2 px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        <i data-lucide="check-square" class="w-4 h-4"></i> Completed
                    </button>
                </div>

                <div class="hidden lg:block w-px h-8 bg-slate-200 shrink-0 ml-auto"></div>

                <!-- Date Filter & Search Form -->
                <form x-ref="filterForm" x-data="{ fromDate: '{{ $dateFrom ?? '' }}', toDate: '{{ $dateTo ?? '' }}', today: '{{ date('Y-m-d') }}' }" 
                      action="{{ route('compliances.index') }}" method="GET" class="flex flex-wrap items-center gap-2 shrink-0 ml-auto lg:ml-0">
                    
                    <input type="hidden" name="tab" :value="activeTab">
                    <input type="hidden" name="filter_decision" :value="filterDecision">
                    
                    <!-- Search Input -->
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="File / Order No..." 
                               class="w-40 sm:w-48 text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 py-2 pl-9 pr-3 shadow-sm text-slate-700 font-medium bg-white">
                    </div>

                    <input type="date" name="date_from" x-model="fromDate" :max="today" @change="if(toDate && fromDate > toDate) toDate = fromDate" 
                           class="w-32 sm:w-36 text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 py-2 shadow-sm text-indigo-900 font-medium bg-white">
                    <span class="text-indigo-400 text-sm font-medium px-1">to</span>
                    <input type="date" name="date_to" x-model="toDate" :min="fromDate" :max="today" 
                           class="w-32 sm:w-36 text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 py-2 shadow-sm text-indigo-900 font-medium bg-white">
                    
                    <!-- Unit Filter (Only for Complied) -->
                    <select name="unit_id" x-show="activeTab === 'complied'" x-cloak
                            class="w-40 sm:w-48 text-sm border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 py-2 shadow-sm text-slate-700 font-medium bg-white">
                        <option value="">All Units</option>
                        @foreach($filterUnits as $unit)
                            <option value="{{ $unit->unit_id }}" {{ (isset($unitId) && $unitId == $unit->unit_id) ? 'selected' : '' }}>
                                {{ $unit->unit_name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-emerald-50 text-emerald-700 font-semibold rounded-xl hover:bg-emerald-100 transition-colors border border-emerald-200 text-sm flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="filter" class="w-4 h-4"></i> Apply
                    </button>
                    @if(request('date_from') || request('date_to') || request('search') || request('unit_id'))
                        <a href="{{ route('compliances.index') }}" class="px-3 py-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors text-sm font-semibold border border-transparent">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <div class="hidden lg:block w-px h-8 bg-slate-200 shrink-0"></div>

                <!-- Custom Filter Dropdown -->
                <div x-show="activeTab === 'pending'" x-cloak x-data="{ openFilter: false }" class="relative w-full shrink-0 sm:w-48 lg:w-52">
                    <button @click="openFilter = !openFilter" @click.away="openFilter = false" type="button" 
                            class="flex w-full items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:border-teal-300 hover:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        
                        <div class="flex min-w-0 items-center gap-2 text-left">
                            <i data-lucide="filter" class="h-4 w-4 shrink-0 text-teal-600"></i>
                            <span class="truncate" x-text="
                                filterDecision === 'VC' ? 'Vigilance Case (VC)' :
                                filterDecision === 'PE' ? 'Preliminary Enquiry (PE)' :
                                filterDecision === 'VE' ? 'Vigilance Enquiry (VE)' :
                                filterDecision === 'CV' ? 'Confidential Verification (CV)' :
                                filterDecision === 'SC' ? 'Surprise Check (SC)' :
                                'All Pending'
                            "></span>
                        </div>
                        
                        <div class="ml-1 flex shrink-0 items-center gap-1.5">
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-teal-100 bg-white px-1.5 text-xs font-bold text-teal-700" 
                                  x-text="
                                  filterDecision === 'VC' ? '{{ $pendingCounts['VC'] }}' :
                                  filterDecision === 'PE' ? '{{ $pendingCounts['PE'] }}' :
                                  filterDecision === 'VE' ? '{{ $pendingCounts['VE'] }}' :
                                  filterDecision === 'CV' ? '{{ $pendingCounts['CV'] }}' :
                                  filterDecision === 'SC' ? '{{ $pendingCounts['SC'] }}' :
                                  '{{ $pendingCounts['Total'] }}'
                                  "></span>
                            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform" :class="openFilter ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="openFilter" x-transition.opacity.duration.200ms x-cloak 
                        class="absolute left-0 z-50 mt-2 w-64 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        @php
                            $options = [
                                ['value' => '', 'label' => 'All Pending', 'count' => $pendingCounts['Total']],
                                ['value' => 'VC', 'label' => 'Vigilance Case (VC)', 'count' => $pendingCounts['VC']],
                                ['value' => 'PE', 'label' => 'Preliminary Enquiry (PE)', 'count' => $pendingCounts['PE']],
                                ['value' => 'VE', 'label' => 'Vigilance Enquiry (VE)', 'count' => $pendingCounts['VE']],
                                ['value' => 'CV', 'label' => 'Confidential Verification (CV)', 'count' => $pendingCounts['CV']],
                                ['value' => 'SC', 'label' => 'Surprise Check (SC)', 'count' => $pendingCounts['SC']],
                            ];
                        @endphp

                        @foreach($options as $option)
                            <button @click="filterDecision = '{{ $option['value'] }}'; $refs.filterForm.submit()" type="button"
                                    class="w-full flex items-center justify-between gap-4 px-4 py-2.5 text-sm font-medium transition-colors"
                                    :class="filterDecision === '{{ $option['value'] }}' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'">
                                <span class="flex items-center gap-2 whitespace-nowrap">
                                    <i data-lucide="check" class="w-4 h-4 shrink-0" :class="filterDecision === '{{ $option['value'] }}' ? 'text-indigo-600' : 'text-transparent'"></i>
                                    {{ $option['label'] }}
                                </span>
                                <span class="inline-flex items-center justify-center min-w-[24px] h-[24px] px-1.5 text-xs font-bold rounded-full border transition-colors shrink-0"
                                      :class="filterDecision === '{{ $option['value'] }}' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'">
                                    {{ $option['count'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Export Button -->
                <form action="{{ route('compliances.export') }}" method="GET" class="shrink-0 lg:ml-0">
                    <input type="hidden" name="tab" :value="activeTab">
                    <input type="hidden" name="filter_decision" :value="filterDecision">
                    <input type="hidden" name="date_from" value="{{ $dateFrom ?? '' }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo ?? '' }}">
                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                    <input type="hidden" name="unit_id" value="{{ $unitId ?? '' }}">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-indigo-600 bg-white border border-indigo-200 rounded-xl hover:bg-indigo-50 hover:text-indigo-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Export
                    </button>
                </form>
            </div>
        </div>

        <!-- Pending Tab Content -->
        <div x-show="activeTab === 'pending'" x-cloak
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sl No</th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order No & Date
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">File No & Date
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Decision
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendency
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingDecisions as $index => $decision)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 text-sm font-medium text-slate-500">
                                    {{ $pendingDecisions->firstItem() + $index }}
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="block text-sm font-semibold text-slate-900">{{ $decision->directorate_order_number ?? 'N/A' }}</span>
                                    <span class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($decision->decision_date)->format('d-M-Y') }}</span>
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    <a href="{{ route('petitions.show', $decision->petition->petition_id) }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 block mb-1">
                                        {{ $decision->petition->file_no ?? 'N/A' }}
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <span class="text-slate-500 text-xs">{{ $decision->petition->file_created_date ? \Carbon\Carbon::parse($decision->petition->file_created_date)->format('d-M-Y') : 'N/A' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ \App\Models\Decision::getDecisionLabel($decision->final_decision) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $daysPending = \Carbon\Carbon::parse($decision->decision_date)->startOfDay()->diffInDays(now()->startOfDay());
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                        if ($daysPending > 10) {
                                            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                        } elseif ($daysPending > 5) {
                                            $badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                        {{ $daysPending }} {{ Str::plural('Day', $daysPending) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <button
                                        @click="openModal({{ json_encode(['id' => $decision->decision_id, 'file_no' => $decision->petition->file_no ?? 'N/A', 'type' => $decision->final_decision]) }})"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 shadow-sm transition-colors">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Update Entry
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <i data-lucide="check-circle-2" class="w-12 h-12 mx-auto text-slate-300 mb-3"></i>
                                    <p class="text-sm font-medium">No pending compliances found.</p>
                                    <p class="text-xs mt-1">All orders have been updated.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Complied Tab Content -->
        <div x-show="activeTab === 'complied'" x-cloak
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sl No</th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order No & Date
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Decision
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action No &
                                Date</th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit</th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">File No & Date
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Updated By
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($compliedDecisions as $index => $decision)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 text-sm font-medium text-slate-500">
                                    {{ $compliedDecisions->firstItem() + $index }}
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="block text-sm font-semibold text-slate-900">{{ $decision->directorate_order_number ?? 'N/A' }}</span>
                                    <span class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($decision->decision_date)->format('d-M-Y') }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ \App\Models\Decision::getDecisionLabel($decision->final_decision) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    <span
                                        class="block font-semibold text-slate-900">{{ $decision->petition->compliance->action_number }}</span>
                                    <span
                                        class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($decision->petition->compliance->action_date)->format('d-M-Y') }}</span>
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-slate-700">
                                    {{ $decision->petition->compliance->unit->unit_name ?? 'N/A' }}
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    <a href="{{ route('petitions.show', $decision->petition->petition_id) }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 block mb-1">
                                        {{ $decision->petition->file_no ?? 'N/A' }}
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <span class="text-slate-500 text-xs">{{ $decision->petition->file_created_date ? \Carbon\Carbon::parse($decision->petition->file_created_date)->format('d-M-Y') : 'N/A' }}</span>
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 border border-slate-200 uppercase">
                                            {{ substr($decision->petition->compliance->createdBy->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="block font-semibold text-slate-900">{{ $decision->petition->compliance->createdBy->name ?? 'Unknown' }}</span>
                                            <span class="block text-indigo-600 font-medium text-[10px] uppercase tracking-wide mt-0.5">{{ $decision->petition->compliance->createdBy->seatUsers->first()->seat->seat_name ?? 'No Seat Assigned' }}</span>
                                            <span class="block text-slate-500 text-[11px] mt-0.5">{{ $decision->petition->compliance->created_at ? \Carbon\Carbon::parse($decision->petition->compliance->created_at)->format('d-M-Y h:i A') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <i data-lucide="info" class="w-12 h-12 mx-auto text-slate-300 mb-3"></i>
                                    <p class="text-sm font-medium">No complied petitions found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Compliance Modal -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
            <div @click.away="showModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="check-square" class="w-5 h-5 text-emerald-600"></i>
                            Update Action Taken
                        </h3>
                        <p class="text-xs text-slate-500 mt-1" x-text="`For File No: ${selectedDecision?.file_no}`">
                        </p>
                    </div>
                    <button type="button" @click="showModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('compliances.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="decision_id" :value="selectedDecision?.id">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <span x-text="selectedDecision?.type + ' Number'"></span> <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="action_number"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm text-slate-800"
                            placeholder="e.g. PE-123/2026/VACB" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Action Date <span
                                class="text-rose-500">*</span></label>
                        <input type="date" name="action_date" max="{{ date('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm text-slate-800"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Action Taking Unit <span
                                class="text-rose-500">*</span></label>
                        <select name="unit_id"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm text-slate-800"
                            required>
                            <option value="">Select Unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Remarks (Optional)</label>
                        <textarea name="remarks" rows="2"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm text-slate-800"
                            placeholder="Any additional notes..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm transition-colors flex items-center gap-2">
                            Save Details <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('pendingChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['VC', 'PE', 'VE', 'CV', 'SC'],
                    datasets: [{
                        data: [
                            {{ $pendingCounts['VC'] }},
                            {{ $pendingCounts['PE'] }},
                            {{ $pendingCounts['VE'] }},
                            {{ $pendingCounts['CV'] }},
                            {{ $pendingCounts['SC'] }}
                        ],
                        backgroundColor: [
                            '#4f46e5', // indigo-600
                            '#2563eb', // blue-600
                            '#0891b2', // cyan-600
                            '#9333ea', // purple-600
                            '#ea580c'  // orange-600
                        ],
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 18,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'x',
                    animation: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            offset: true,
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0,
                                color: '#475569',
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            display: false,
                            beginAtZero: true,
                            ticks: {
                                display: false
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: 10,
                            right: 10,
                            bottom: 10,
                            left: 10
                        }
                    }
                }
            });
        });
    </script>
@endsection