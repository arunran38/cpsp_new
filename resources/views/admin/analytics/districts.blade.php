@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-6">
        <!-- Premium Header Section (Print Hidden) -->
        <div
            class="print:hidden relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>

            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        District Wise Analysis
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Detailed district-wise petition statistics and status data sheet.
                    </p>
                </div>
                <div class="flex items-center gap-3 z-10">
                    @if($canExport)
                        <a href="{{ route('admin.reports.district-analysis.export', request()->query()) }}" id="exportExcelBtn"
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
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4 text-teal-600"></i>
                        <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider">Filter Statistics</h3>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium hidden sm:flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>Filters update live</span>
                    </span>
                </div>

                <form action="{{ route('admin.reports.district-analysis') }}" method="GET" class="w-full" id="searchForm">
                    <input type="hidden" name="date_preset" id="date_preset" value="{{ $filters['date_preset'] ?? '' }}">

                    <!-- Period Presets Bar -->
                    <div class="mb-3.5 pb-3 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1 mr-1">
                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Period</span>
                            </label>
                            <div class="inline-flex items-center p-1 bg-blue-50/50 border border-blue-200/80 rounded-xl shadow-inner gap-1">
                                @php $activePreset = $filters['date_preset'] ?? ''; @endphp
                                <button type="button" data-preset="all_time"
                                    class="preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ empty($activePreset) || $activePreset === 'all_time' ? 'bg-white text-teal-700 shadow-sm border border-blue-200/60' : 'text-blue-600 hover:text-blue-900' }}">
                                    All Time
                                </button>
                                <button type="button" data-preset="today"
                                    class="preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activePreset === 'today' ? 'bg-white text-teal-700 shadow-sm border border-blue-200/60' : 'text-blue-600 hover:text-blue-900' }}">
                                    Today
                                </button>
                                <button type="button" data-preset="this_week"
                                    class="preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activePreset === 'this_week' ? 'bg-white text-teal-700 shadow-sm border border-blue-200/60' : 'text-blue-600 hover:text-blue-900' }}">
                                    This Week
                                </button>
                                <button type="button" data-preset="this_month"
                                    class="preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activePreset === 'this_month' ? 'bg-white text-teal-700 shadow-sm border border-blue-200/60' : 'text-blue-600 hover:text-blue-900' }}">
                                    This Month
                                </button>
                                <button type="button" data-preset="this_year"
                                    class="preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activePreset === 'this_year' ? 'bg-white text-teal-700 shadow-sm border border-blue-200/60' : 'text-blue-600 hover:text-blue-900' }}">
                                    This Year
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 xl:gap-4 items-end w-full">
                        <!-- Status Filter -->
                        <div class="sm:col-span-1 lg:col-span-2 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="activity" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Status</span>
                            </label>
                            <div class="relative w-full">
                                <select name="status" id="status"
                                    class="w-full px-3 pr-8 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300 appearance-none cursor-pointer"
                                    style="color: #1e40af !important;">
                                    <option value="">All Statuses</option>
                                    <option value="Received" {{ in_array($filters['status'] ?? '', ['Received', 'Processed']) ? 'selected' : '' }}>Processed</option>
                                    <option value="Forwarded" {{ ($filters['status'] ?? '') === 'Forwarded' ? 'selected' : '' }}>Forwarded</option>
                                    <option value="VR_Received" {{ ($filters['status'] ?? '') === 'VR_Received' ? 'selected' : '' }}>VR Received</option>
                                    <option value="Sent to Govt" {{ in_array($filters['status'] ?? '', ['Sent to Govt', 'Sent_to_Govt']) ? 'selected' : '' }}>Sent to Govt</option>
                                    <option value="Closed" {{ ($filters['status'] ?? '') === 'Closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Nature of Petition -->
                        <div class="sm:col-span-1 lg:col-span-2 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="tag" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Nature</span>
                            </label>
                            <div class="relative w-full">
                                <select name="nature_of_petition" id="nature_of_petition"
                                    class="w-full px-3 pr-8 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300 appearance-none cursor-pointer"
                                    style="color: #1e40af !important;">
                                    <option value="">All Natures</option>
                                    @foreach(['Amassment of Wealth', 'Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence', 'others'] as $nat)
                                        <option value="{{ $nat }}" {{ ($filters['nature_of_petition'] ?? '') === $nat ? 'selected' : '' }}>
                                            {{ $nat }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Mode of Petition -->
                        <div class="sm:col-span-1 lg:col-span-2 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="send" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Mode</span>
                            </label>
                            <div class="relative w-full">
                                <select name="mode_of_petition" id="mode_of_petition"
                                    class="w-full px-3 pr-8 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300 appearance-none cursor-pointer"
                                    style="color: #1e40af !important;">
                                    <option value="">All Modes</option>
                                    @foreach(['Email' => 'Email', 'Whatsapp' => 'Whatsapp', 'Tollfree' => 'Tollfree', 'Direct' => 'Direct', 'Unit' => 'Unit', 'Tapal' => 'Tapal', 'iaps' => 'iAPS', 'others' => 'Others'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($filters['mode_of_petition'] ?? '') === $val ? 'selected' : '' }}>
                                            {{ $lbl }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="sm:col-span-1 lg:col-span-2 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Date From</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-400">
                                    <i data-lucide="calendar" class="h-4 w-4"></i>
                                </div>
                                <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}"
                                    max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                    class="w-full pl-9 pr-2.5 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Date To -->
                        <div class="sm:col-span-1 lg:col-span-2 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Date To</span>
                            </label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-400">
                                    <i data-lucide="calendar" class="h-4 w-4"></i>
                                </div>
                                <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}"
                                    max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                    class="w-full pl-9 pr-2.5 h-11 bg-white border border-blue-200/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                            <p id="dateRangeError" class="text-[10px] text-rose-500 font-medium hidden mt-1" aria-live="polite"></p>
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

                                <a href="{{ route('admin.reports.district-analysis') }}" title="Reset All Filters"
                                    class="w-11 h-11 shrink-0 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-rose-600 shadow-sm flex items-center justify-center transition-all cursor-pointer">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table (Matches Department Analysis Layout) -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden mb-6">
            <div id="tableContainer">
                @include('admin.analytics.partials.districts_table')
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('searchForm');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const datePresetInput = document.getElementById('date_preset');
        const dateRangeError = document.getElementById('dateRangeError');
        const exportExcelBtn = document.getElementById('exportExcelBtn');
        const grandTotalBadgeCount = document.getElementById('grandTotalBadgeCount');
        const presetButtons = document.querySelectorAll('.preset-btn');

        let abortController = null;

        const validateDateRange = () => {
            if (!dateFromInput || !dateToInput) return true;
            const from = dateFromInput.value;
            const to = dateToInput.value;

            if (!from) {
                dateToInput.removeAttribute('min');
                if (dateRangeError) {
                    dateRangeError.textContent = '';
                    dateRangeError.classList.add('hidden');
                }
                return true;
            }

            dateToInput.setAttribute('min', from);
            if (to && to < from) {
                if (dateRangeError) {
                    dateRangeError.textContent = 'Date To must be ≥ Date From.';
                    dateRangeError.classList.remove('hidden');
                }
                return false;
            }

            if (dateRangeError) {
                dateRangeError.textContent = '';
                dateRangeError.classList.add('hidden');
            }
            return true;
        };

        const performSearch = () => {
            if (!validateDateRange()) return;

            const formData = new FormData(searchForm);
            const searchParams = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value !== null && typeof value === 'string' && value.trim() !== '') {
                    searchParams.set(key, value.trim());
                }
            }

            const queryString = searchParams.toString();
            const url = `${searchForm.action}${queryString ? '?' + queryString : ''}`;

            window.history.pushState({}, '', url);

            if (exportExcelBtn) {
                exportExcelBtn.href = `{{ route('admin.reports.district-analysis.export') }}${queryString ? '?' + queryString : ''}`;
            }

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                signal: abortController.signal
            })
            .then(response => response.json())
            .then(json => {
                const tableContainer = document.getElementById('tableContainer');
                if (tableContainer && json.tableHtml) {
                    tableContainer.innerHTML = json.tableHtml;
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }

                if (grandTotalBadgeCount && json.total !== undefined) {
                    grandTotalBadgeCount.textContent = Number(json.total).toLocaleString();
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Error fetching district analysis:', err);
                }
            });
        };

        // Date Preset Handlers
        presetButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const preset = this.getAttribute('data-preset');
                datePresetInput.value = preset;

                const today = new Date();
                const formatDate = (d) => {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (preset === 'today') {
                    const todayStr = formatDate(today);
                    dateFromInput.value = todayStr;
                    dateToInput.value = todayStr;
                } else if (preset === 'this_week') {
                    const day = today.getDay();
                    const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                    const monday = new Date(today.setDate(diff));
                    dateFromInput.value = formatDate(monday);
                    dateToInput.value = formatDate(new Date());
                } else if (preset === 'this_month') {
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    dateFromInput.value = formatDate(firstDay);
                    dateToInput.value = formatDate(new Date());
                } else if (preset === 'this_year') {
                    const firstDay = new Date(today.getFullYear(), 0, 1);
                    dateFromInput.value = formatDate(firstDay);
                    dateToInput.value = formatDate(new Date());
                } else if (preset === 'all_time') {
                    dateFromInput.value = '';
                    dateToInput.value = '';
                }

                presetButtons.forEach(b => {
                    b.className = 'preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-blue-600 hover:text-blue-900';
                });
                this.className = 'preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-white text-teal-700 shadow-sm border border-blue-200/60';

                performSearch();
            });
        });

        const filterInputs = searchForm.querySelectorAll('select, input[type="date"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                if (input === dateFromInput || input === dateToInput) {
                    datePresetInput.value = '';
                    presetButtons.forEach(b => {
                        b.className = 'preset-btn px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-blue-600 hover:text-blue-900';
                    });
                }
                performSearch();
            });
        });

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    });
    </script>
@endsection
