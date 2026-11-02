@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('title', 'Audit Log Management')

@section('content')
<div class="space-y-6">
    <!-- Premium Header Section (Department Management Style) -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="text-center sm:text-left z-10">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                    Audit Log Management
                </h1>
                <p class="text-sky-400 font-medium max-w-2xl text-xs sm:text-sm">
                    Track and monitor system user activity, searches, and data operations.
                </p>
            </div>
            <div class="flex items-center gap-3 z-10">
                <a href="{{ route('admin.audit_logs.export', request()->query()) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-white font-bold text-xs sm:text-sm border border-slate-700/80 shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <i data-lucide="download" class="w-4 h-4 text-sky-400"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Metric Counter Cards (Clean Uniform Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Logs</div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ number_format($stats['total_logs']) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                <i data-lucide="history" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-sky-500 uppercase tracking-wider">Searches Today</div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ number_format($stats['searches_today']) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600">
                <i data-lucide="search" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-emerald-500 uppercase tracking-wider">CRUD Today</div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ number_format($stats['crud_today']) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <i data-lucide="database" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-indigo-500 uppercase tracking-wider">Active Users</div>
                <div class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ number_format($stats['active_users_today']) }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filters Section (Uniform Card Styling) -->
    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Search Query -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Search Keywords</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                               placeholder="Keywords, IP, user name..." 
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                    </div>
                </div>

                <!-- Action Dropdown -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Action</label>
                    <select name="action" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Module Dropdown -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Module</label>
                    <select name="module" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ ($filters['module'] ?? '') === $module ? 'selected' : '' }}>
                                {{ $module }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- From Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" 
                           class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" 
                           class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-sky-400"></i> Apply Filters
                    </button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('admin.audit_logs.index') }}" 
                           class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            Reset
                        </a>
                    @endif
                </div>

                <div class="text-xs text-slate-500">
                    Showing <span class="font-bold text-slate-700">{{ $logs->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700">{{ $logs->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700">{{ $logs->total() }}</span> records
                </div>
            </div>
        </form>
    </div>

    <!-- Audit Log Details Sub-Header Section (Department Details Style) -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-md">
        <div class="absolute top-0 right-0 -translate-y-8 translate-x-1/4 pointer-events-none">
            <div class="w-48 h-48 bg-indigo-500/15 rounded-full blur-[40px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 pointer-events-none">
            <div class="w-48 h-48 bg-teal-500/15 rounded-full blur-[40px]"></div>
        </div>
        
        <div class="relative px-6 py-3.5 sm:px-8 flex items-center justify-between z-10">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight drop-shadow-sm mb-0.5">
                    Audit Log Details
                </h2>
                <p class="text-sky-400 font-medium text-xs">
                    Chronological view of all system activities and user operations.
                </p>
            </div>
            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800/80 border border-slate-700/60 text-xs font-bold text-slate-300">
                <i data-lucide="history" class="w-3.5 h-3.5 text-sky-400"></i>
                <span>{{ number_format($logs->total()) }} Total Logs</span>
            </div>
        </div>
    </div>

    <!-- Data Table Container (Matching Department rounded-3xl table style) -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Timestamp</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">User & Seat</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Action / Module</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Activity Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">IP & Method</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/40 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">
                                    {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                                </span>
                            </td>
                            
                            <!-- Timestamp & Session Duration -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-xs">
                                    {{ $log->created_at ? $log->created_at->format('d M Y, h:i:s A') : 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[11px] text-slate-400">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                    </span>
                                    @if($log->session_duration)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded border border-sky-200/80" title="User Session Duration at time of activity">
                                            <i data-lucide="timer" class="w-3 h-3 text-sky-500"></i>
                                            <span>{{ $log->session_duration }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- User & Seat -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>{{ $log->user_name ?? ($log->user->name ?? 'System/Guest') }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($log->user_pen)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                            PEN: {{ $log->user_pen }}
                                        </span>
                                    @endif
                                    @if($log->seat)
                                        <span class="text-[10px] text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded font-semibold border border-sky-200/60">
                                            {{ $log->seat->seat_name }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Action & Module Badges -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @php
                                        $actionColor = match($log->action) {
                                            'SEARCH' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'CREATE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'UPDATE' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'DELETE' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'READ'   => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'EXPORT' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            default  => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-lg border {{ $actionColor }}">
                                        {{ $log->action }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $log->module }}
                                    </span>
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="px-6 py-4 max-w-md">
                                <div class="text-xs font-semibold text-slate-800 line-clamp-2" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </div>
                            </td>

                            <!-- IP & Method -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs font-mono text-slate-600">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700 uppercase">
                                        {{ $log->method ?? 'GET' }}
                                    </span>
                                    <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                            </td>

                            <!-- View Details Button -->
                            <td class="px-6 py-4 text-right">
                                <button type="button" 
                                        onclick="showAuditDetails({{ json_encode($log) }})"
                                        class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-xl transition-all"
                                        title="View Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No audit log records found for the selected criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal for Inspecting Audit Details (Clean Uniform Styling) -->
<div id="auditModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="relative overflow-hidden bg-gradient-to-r from-[#1e293b] to-[#0f172a] px-6 py-4 border-b border-slate-700/50 flex items-center justify-between">
            <div class="absolute top-0 right-0 -translate-y-8 translate-x-1/4 pointer-events-none">
                <div class="w-48 h-48 bg-indigo-500/15 rounded-full blur-[40px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 pointer-events-none">
                <div class="w-48 h-48 bg-teal-500/15 rounded-full blur-[40px]"></div>
            </div>
            
            <div class="relative z-10">
                <h3 class="font-black text-white text-base tracking-tight flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-sky-400"></i>
                    Audit Log Details (<span id="modalAuditId" class="text-sky-300"></span>)
                </h3>
                <p class="text-sky-400 text-xs font-medium mt-0.5">
                    Detailed metadata and submitted parameters.
                </p>
            </div>
            <button onclick="closeAuditModal()" class="relative z-10 text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-slate-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <!-- Summary Info Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-xs">
                <div>
                    <span class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">User</span>
                    <p id="modalUser" class="font-bold text-slate-900 mt-0.5 text-sm"></p>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">Timestamp</span>
                    <p id="modalTimestamp" class="font-bold text-slate-900 mt-0.5 text-sm"></p>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">Session Duration</span>
                    <p id="modalSessionDuration" class="font-bold text-sky-700 mt-0.5 text-sm flex items-center gap-1">
                        <i data-lucide="timer" class="w-3.5 h-3.5 text-sky-500"></i>
                        <span id="modalSessionDurationText"></span>
                    </p>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">Action & Module</span>
                    <p id="modalActionModule" class="font-bold text-sky-800 mt-0.5 text-sm"></p>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">IP & Method</span>
                    <p id="modalIp" class="font-mono text-slate-700 mt-0.5 text-xs"></p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block mb-1">Activity Description</span>
                <p id="modalDescription" class="text-sm font-semibold text-slate-800 bg-sky-50/60 p-3 rounded-2xl border border-sky-100"></p>
            </div>

            <!-- Human-Readable Parameters Table -->
            <div>
                <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block mb-2">Activity Data & Parameters</span>
                <div id="modalPropertiesContainer" class="rounded-2xl border border-slate-200 overflow-hidden shadow-sm bg-white">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                                <th class="px-4 py-2.5 font-bold w-1/3">Field / Parameter</th>
                                <th class="px-4 py-2.5 font-bold">Value</th>
                            </tr>
                        </thead>
                        <tbody id="modalPropertiesTableBody" class="divide-y divide-slate-100">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
                <div id="modalPropertiesEmpty" class="hidden p-4 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs italic bg-slate-50">
                    No extra parameters recorded for this operation.
                </div>
            </div>
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
            <button onclick="closeAuditModal()" class="px-5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 transition-colors shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function formatFieldKey(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/\b\w/g, char => char.toUpperCase());
    }

    function formatFieldValue(val) {
        if (val === null || val === undefined || val === '') {
            return '<span class="text-slate-400 italic">-</span>';
        }
        if (typeof val === 'boolean') {
            return val 
                ? '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700">Yes</span>' 
                : '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">No</span>';
        }
        if (Array.isArray(val)) {
            if (val.length === 0) return '<span class="text-slate-400 italic">None</span>';
            
            // If array of objects (e.g. complainants: [{name: 'fsdf'}])
            if (typeof val[0] === 'object' && val[0] !== null) {
                return val.map((item, idx) => {
                    const entries = Object.entries(item)
                        .filter(([k, v]) => v !== null && v !== '')
                        .map(([k, v]) => `<span class="font-semibold text-slate-700">${formatFieldKey(k)}:</span> ${v}`)
                        .join(' | ');
                    return `<div class="py-1">${entries || '-'}</div>`;
                }).join('');
            }
            return val.join(', ');
        }
        if (typeof val === 'object') {
            const rows = Object.entries(val)
                .map(([k, v]) => `<div><span class="font-semibold text-slate-700">${formatFieldKey(k)}:</span> ${formatFieldValue(v)}</div>`)
                .join('');
            return rows || '<span class="text-slate-400 italic">-</span>';
        }
        return String(val);
    }

    function showAuditDetails(log) {
        document.getElementById('modalAuditId').innerText = '#' + log.audit_id;
        document.getElementById('modalUser').innerText = (log.user_name || 'System') + (log.user_pen ? ' (PEN: ' + log.user_pen + ')' : '');
        document.getElementById('modalTimestamp').innerText = log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A';
        document.getElementById('modalSessionDurationText').innerText = log.session_duration || 'Active Session';
        document.getElementById('modalActionModule').innerText = log.action + ' on ' + log.module;
        document.getElementById('modalIp').innerText = (log.ip_address || 'N/A') + ' (' + (log.method || 'GET') + ')';
        document.getElementById('modalDescription').innerText = log.description || 'N/A';

        const tableBody = document.getElementById('modalPropertiesTableBody');
        const container = document.getElementById('modalPropertiesContainer');
        const emptyState = document.getElementById('modalPropertiesEmpty');
        
        tableBody.innerHTML = '';

        const properties = log.properties;
        if (properties && typeof properties === 'object' && Object.keys(properties).length > 0) {
            container.classList.remove('hidden');
            emptyState.classList.add('hidden');

            for (const [key, val] of Object.entries(properties)) {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50 transition-colors';
                
                const tdKey = document.createElement('td');
                tdKey.className = 'px-4 py-2.5 font-bold text-slate-700 bg-slate-50/50';
                tdKey.innerText = formatFieldKey(key);
                
                const tdVal = document.createElement('td');
                tdVal.className = 'px-4 py-2.5 text-slate-800 break-words';
                tdVal.innerHTML = formatFieldValue(val);

                tr.appendChild(tdKey);
                tr.appendChild(tdVal);
                tableBody.appendChild(tr);
            }
        } else {
            container.classList.add('hidden');
            emptyState.classList.remove('hidden');
        }

        const modal = document.getElementById('auditModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAuditModal() {
        const modal = document.getElementById('auditModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close on clicking outside modal
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('auditModal');
        if (e.target === modal) {
            closeAuditModal();
        }
    });
</script>
@endsection
