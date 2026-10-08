@extends('layouts.admin')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden mb-6">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-600 via-indigo-500 to-teal-500"></div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Department Wise Analysis</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 border border-teal-200">
                        Admin
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Statistical overview of petitions grouped by accused departments.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden mb-6">
            <div class="p-6 bg-slate-50">
                <form action="{{ route('admin.departments.analysis') }}" method="GET" class="flex flex-wrap gap-4 items-end"
                    id="searchForm">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-indigo-700 mb-1">Search Departments</label>
                        <div class="relative flex items-center">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 text-indigo-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full pl-9 pr-3 py-[9px] rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                placeholder="Enter department name">
                        </div>
                    </div>

                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-indigo-700 mb-1">Date From</label>
                        <input type="date" id="date_from" placeholder="DD-MM-YYYY" name="date_from"
                            value="{{ request('date_from') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-indigo-700 mb-1">Date To</label>
                        <input type="date" id="date_to" placeholder="DD-MM-YYYY" name="date_to"
                            value="{{ request('date_to') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                        <p id="dateRangeError" class="mt-1 text-xs text-rose-600 hidden" aria-live="polite"></p>
                    </div>

                    <div class="flex-none flex items-center gap-2">
                        <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                        <a href="{{ route('admin.departments.analysis.export', request()->query()) }}" id="exportExcelBtn"
                            class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                            <i data-lucide="download" class="w-4 h-4"></i> Export Excel
                        </a>

                        <a href="{{ route('admin.departments.analysis') }}"
                            class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap"
                            title="Clear all filters">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div id="tableContainer">
                @include('admin.partials.department_table')
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

            const changeInputs = searchForm.querySelectorAll('input[type="date"], select');
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