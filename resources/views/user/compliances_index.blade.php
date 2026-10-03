@extends('layouts.user')

@section('title', 'Petition Compliances')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
            showModal: false, 
            selectedDecision: null,
            activeTab: 'pending',
            filterDecision: '',
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

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Pending Card -->
            <div class="bg-white rounded-2xl p-6 border border-rose-200 shadow-sm flex flex-col justify-between cursor-pointer hover:shadow-md hover:border-rose-400 transition-all group"
                @click="activeTab = 'pending'; filterDecision = ''">
                <div class="flex items-start gap-4 mb-4">
                    <div
                        class="p-4 bg-rose-50 rounded-xl text-rose-600 group-hover:bg-rose-100 group-hover:scale-105 transition-all">
                        <i data-lucide="clock" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wide">Total Pending</p>
                        <h3 class="text-4xl font-black text-rose-700 leading-none mt-1">{{ $pendingCounts['Total'] }}</h3>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-100">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        VC: <span class="text-indigo-900">{{ $pendingCounts['VC'] }}</span>
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        PE: <span class="text-blue-900">{{ $pendingCounts['PE'] }}</span>
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">
                        VE: <span class="text-cyan-900">{{ $pendingCounts['VE'] }}</span>
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100">
                        CV: <span class="text-purple-900">{{ $pendingCounts['CV'] }}</span>
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-orange-50 text-orange-700 border border-orange-100">
                        SC: <span class="text-orange-900">{{ $pendingCounts['SC'] }}</span>
                    </span>
                    @if($pendingCounts['Overdue'] > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 shadow-sm animate-pulse">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            Overdue (>30 Days): <span class="text-rose-900">{{ $pendingCounts['Overdue'] }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Complied Card -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-200 shadow-sm flex items-center gap-4 cursor-pointer hover:shadow-md hover:border-emerald-400 transition-all group"
                @click="activeTab = 'complied'">
                <div
                    class="p-4 bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-100 group-hover:scale-105 transition-all">
                    <i data-lucide="check-circle-2" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wide">Total Complied</p>
                    <h3 class="text-4xl font-black text-emerald-700 leading-none mt-1">{{ $compliedDecisions->count() }}
                    </h3>
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

        <!-- Toolbar: Tabs & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
            <!-- Tabs (Segmented Control) -->
            <div class="flex items-center p-1 bg-slate-50/80 rounded-xl border border-slate-100">
                <button @click="activeTab = 'pending'; filterDecision = ''"
                    :class="activeTab === 'pending' ? 'bg-white text-rose-600 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700'"
                    class="relative flex items-center justify-center gap-2 px-6 py-2 text-sm font-bold rounded-lg transition-all duration-200 w-full sm:w-auto">
                    <i data-lucide="clock" class="w-4 h-4"></i> 
                    Pending
                </button>
                <button @click="activeTab = 'complied'; filterDecision = ''"
                    :class="activeTab === 'complied' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700'"
                    class="relative flex items-center justify-center gap-2 px-6 py-2 text-sm font-bold rounded-lg transition-all duration-200 w-full sm:w-auto">
                    <i data-lucide="check-square" class="w-4 h-4"></i> 
                    Completed
                </button>
            </div>

            <!-- Actions (Filter & Export) -->
            <div class="flex items-center gap-3 pr-1 w-full sm:w-auto">
                <!-- Custom Filter Dropdown -->
                <div x-show="activeTab === 'pending'" x-cloak x-data="{ openFilter: false }" class="relative flex-grow sm:w-72">
                    <button @click="openFilter = !openFilter" @click.away="openFilter = false" type="button" 
                            class="w-full flex items-center justify-between pl-3 pr-4 py-2 rounded-xl border border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-700 font-semibold hover:border-slate-300 transition-colors">
                        
                        <div class="flex items-center gap-2 text-left">
                            <i data-lucide="filter" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span class="truncate" x-text="
                                filterDecision === 'VC' ? 'Vigilance Case (VC)' :
                                filterDecision === 'PE' ? 'Preliminary Enquiry (PE)' :
                                filterDecision === 'VE' ? 'Vigilance Enquiry (VE)' :
                                filterDecision === 'CV' ? 'Confidential Verification (CV)' :
                                filterDecision === 'SC' ? 'Surprise Check (SC)' :
                                'All Pending'
                            "></span>
                        </div>
                        
                        <div class="flex items-center gap-2 ml-2 shrink-0">
                            <span class="inline-flex items-center justify-center min-w-[24px] h-[24px] px-1.5 text-xs font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200" 
                                  x-text="
                                  filterDecision === 'VC' ? '{{ $pendingCounts['VC'] }}' :
                                  filterDecision === 'PE' ? '{{ $pendingCounts['PE'] }}' :
                                  filterDecision === 'VE' ? '{{ $pendingCounts['VE'] }}' :
                                  filterDecision === 'CV' ? '{{ $pendingCounts['CV'] }}' :
                                  filterDecision === 'SC' ? '{{ $pendingCounts['SC'] }}' :
                                  '{{ $pendingCounts['Total'] }}'
                                  "></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="openFilter ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="openFilter" x-transition.opacity.duration.200ms x-cloak 
                         class="absolute z-50 w-max min-w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg py-1">
                        
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
                            <button @click="filterDecision = '{{ $option['value'] }}'; openFilter = false" type="button"
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
                <form action="{{ route('compliances.export') }}" method="GET" class="flex-shrink-0">
                    <input type="hidden" name="tab" :value="activeTab">
                    <input type="hidden" name="filter_decision" :value="filterDecision">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
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
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petition No
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order No & Date
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
                            <tr x-show="filterDecision === '' || filterDecision === '{{ $decision->final_decision }}'"
                                class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 text-sm font-medium text-slate-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4 px-6">
                                    <a href="{{ route('petitions.show', $decision->petition->petition_id) }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5">
                                        {{ $decision->petition->receipt_no }}
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="block text-sm font-semibold text-slate-900">{{ $decision->directorate_order_number ?? 'N/A' }}</span>
                                    <span class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($decision->decision_date)->format('d-M-Y') }}</span>
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
                                        if ($daysPending > 30) {
                                            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                        } elseif ($daysPending > 15) {
                                            $badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                        {{ $daysPending }} {{ Str::plural('Day', $daysPending) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <button
                                        @click="openModal({{ json_encode(['id' => $decision->decision_id, 'petition_no' => $decision->petition->receipt_no, 'type' => $decision->final_decision]) }})"
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
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petition No
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order No & Date
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Decision
                            </th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action No &
                                Date</th>
                            <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($compliedDecisions as $index => $decision)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 text-sm font-medium text-slate-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4 px-6">
                                    <a href="{{ route('petitions.show', $decision->petition->petition_id) }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5">
                                        {{ $decision->petition->receipt_no }}
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
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
                        <p class="text-xs text-slate-500 mt-1" x-text="`For Petition No: ${selectedDecision?.petition_no}`">
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
@endsection