@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-6">
    <!-- Premium Header Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="text-center sm:text-left z-10">
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                        Department Wise Analysis
                    </h1>
                    <span id="sectionBadge"
                        class="px-2.5 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-bold uppercase tracking-wider border border-indigo-500/30">
                        @if(($section ?? '') === 'cpsp')
                            CPSP Section
                        @elseif(($section ?? '') === 't_section')
                            T-Section
                        @else
                            All Sections
                        @endif
                    </span>
                </div>
                <p class="text-slate-300 font-medium max-w-2xl text-sm">
                    Statistical overview of petitions grouped by accused departments.
                </p>
            </div>
        </div>
    </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden print-hide mb-6">
            <div class="p-4 sm:p-5 bg-slate-50/50">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="filter" class="w-4 h-4 text-teal-600"></i>
                    <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider">Filter Statistics</h3>
                </div>
                <form action="{{ route('admin.departments.analysis') }}" method="GET" class="w-full" id="searchForm">
                    <div class="flex flex-col sm:flex-row flex-wrap xl:flex-nowrap items-stretch sm:items-end gap-2.5 sm:gap-3 xl:gap-3.5 w-full">
                        <!-- Search Departments (Expands to occupy available space) -->
                        <div class="flex-1 min-w-[180px] flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="search" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Search Departments</span>
                            </label>
                            <div class="relative flex items-center w-full">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-400">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="w-full pl-9 pr-3 h-11 bg-white rounded-xl border border-blue-200/80 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 text-xs sm:text-sm font-semibold text-slate-700 transition-all hover:border-blue-300"
                                    placeholder="Enter department name">
                            </div>
                        </div>

                        <!-- Section Segmented Buttons -->
                        <div class="w-full sm:w-auto min-w-[270px] lg:min-w-[300px] xl:w-[320px] shrink-0 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="layers" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Section</span>
                            </label>
                            <div class="flex items-center p-1 bg-blue-50/50 border border-blue-200/80 rounded-xl w-full h-11 shadow-inner">
                                @if(($canCpsp ?? true) && ($canViewTSection ?? false))
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="all" class="sr-only peer"
                                            {{ ($section ?? 'all') === 'all' ? 'checked' : '' }}>
                                        <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-2.5">
                                            All Sections
                                        </span>
                                    </label>
                                @endif
                                @if($canCpsp ?? true)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="cpsp" class="sr-only peer"
                                            {{ ($section ?? '') === 'cpsp' ? 'checked' : '' }}>
                                        <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-2.5">
                                            CPSP
                                        </span>
                                    </label>
                                @endif
                                @if($canViewTSection ?? false)
                                    <label class="flex-1 cursor-pointer h-full">
                                        <input type="radio" name="section" value="t_section" class="sr-only peer"
                                            {{ ($section ?? '') === 't_section' ? 'checked' : '' }}>
                                        <span class="w-full h-full text-xs font-bold text-blue-600 rounded-lg flex items-center justify-center whitespace-nowrap transition-all duration-150 select-none peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm hover:text-blue-900 px-2.5">
                                            T-Section
                                        </span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="w-full sm:w-36 lg:w-36 shrink-0 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Date From</span>
                            </label>
                            <div class="relative w-full">
                                <input type="date" id="date_from" placeholder="DD-MM-YYYY" name="date_from"
                                    value="{{ request('date_from') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                    class="w-full px-3 h-11 bg-white rounded-xl border border-blue-200/80 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 text-xs sm:text-sm font-semibold text-slate-700 transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        <!-- Date To -->
                        <div class="w-full sm:w-36 lg:w-36 shrink-0 flex flex-col justify-end">
                            <label class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 mb-1.5 h-4">
                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-blue-500"></i>
                                <span>Date To</span>
                            </label>
                            <div class="relative w-full">
                                <input type="date" id="date_to" placeholder="DD-MM-YYYY" name="date_to"
                                    value="{{ request('date_to') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                    class="w-full px-3 h-11 bg-white rounded-xl border border-blue-200/80 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 text-xs sm:text-sm font-semibold text-slate-700 transition-all hover:border-blue-300"
                                    style="color: #1e40af !important;">
                            </div>
                            <p id="dateRangeError" class="mt-1 text-xs text-rose-600 hidden" aria-live="polite"></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="w-full sm:w-auto shrink-0 flex flex-col justify-end">
                            <div class="h-4 mb-1.5 hidden lg:block"></div>
                            <div class="flex items-center gap-2 h-11">
                                <a href="{{ route('admin.departments.analysis.export', request()->query()) }}" id="exportExcelBtn"
                                    class="h-11 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold px-3 sm:px-3.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm transition-all whitespace-nowrap cursor-pointer">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    <span>Export Excel</span>
                                </a>

                                <a href="{{ route('admin.departments.analysis') }}"
                                    class="h-11 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-3 sm:px-3.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm transition-all whitespace-nowrap cursor-pointer"
                                    title="Clear all filters">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div id="tableContainer">
                @include('admin.departments.partials.table')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('searchForm');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');
            const dateRangeError = document.getElementById('dateRangeError');

            let timer;
            let abortController = null;

            const validateDateRange = () => {
                if (!dateFromInput || !dateToInput) return true;
                const dateFrom = dateFromInput.value;
                const dateTo = dateToInput.value;

                if (!dateFrom) {
                    dateToInput.removeAttribute('min');
                    dateRangeError.textContent = '';
                    dateRangeError.classList.add('hidden');
                    return true;
                }

                dateToInput.setAttribute('min', dateFrom);

                if (dateTo && dateTo < dateFrom) {
                    dateRangeError.textContent = 'Date To must be greater than Date From.';
                    dateRangeError.classList.remove('hidden');
                    return false;
                }

                dateRangeError.textContent = '';
                dateRangeError.classList.add('hidden');
                return true;
            };

            const performSearch = (fetchUrl = null) => {
                if (!validateDateRange()) {
                    return;
                }

                const formData = new FormData(searchForm);
                const searchParams = new URLSearchParams(formData);

                let url = fetchUrl;
                if (!url) {
                    url = `${searchForm.action}?${searchParams.toString()}`;
                }

                window.history.pushState({}, '', url);

                const selectedRadio = searchForm.querySelector('input[name="section"]:checked');
                const sectionBadge = document.getElementById('sectionBadge');
                if (selectedRadio && sectionBadge) {
                    const secVal = selectedRadio.value;
                    sectionBadge.textContent = secVal === 'cpsp' ? 'CPSP Section' : (secVal === 't_section' ? 'T-Section' : 'All Sections');
                }

                const exportBtn = document.getElementById('exportExcelBtn');
                if (exportBtn) {
                    const urlObj = new URL(url, window.location.origin);
                    exportBtn.href = `{{ route('admin.departments.analysis.export') }}?${urlObj.searchParams.toString()}`;
                }

                if (abortController) {
                    abortController.abort();
                }
                abortController = new AbortController();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: abortController.signal
                })
                    .then(response => response.text())
                    .then(html => {
                        const currentTable = document.getElementById('tableContainer');
                        if (currentTable) {
                            currentTable.innerHTML = html;
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        }
                    })
                    .catch(error => {
                        if (error.name !== 'AbortError') {
                            console.error('Error fetching search results:', error);
                        }
                    });
            };

            const textInputs = searchForm.querySelectorAll('input[type="text"]');
            textInputs.forEach(input => {
                input.addEventListener('keyup', (e) => {
                    clearTimeout(timer);
                    timer = setTimeout(performSearch, 500);
                });
            });

            const changeInputs = searchForm.querySelectorAll('input[type="date"], select, input[type="radio"]');
            changeInputs.forEach(input => {
                input.addEventListener('change', () => {
                    if (validateDateRange()) {
                        performSearch();
                    }
                });
            });

            if (dateFromInput) dateFromInput.addEventListener('change', validateDateRange);
            if (dateToInput) dateToInput.addEventListener('change', validateDateRange);

            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                performSearch();
            });

            // AJAX Pagination
            document.getElementById('tableContainer').addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && link.href && link.href.includes('page=')) {
                    e.preventDefault();
                    performSearch(link.href);
                }
            });
        });
    </script>
    </div>
@endsection