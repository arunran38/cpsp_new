@extends('layouts.user')

@section('content')
<div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activeForwardingId: null }" class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Advanced Reports</h1>
                <p class="text-sm text-slate-500 mt-1">Filter and analyze petition data with advanced parameters.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('petitions.export', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-all font-semibold text-sm shadow-sm">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                Excel Export
            </a>
            @if(auth()->user()->role === 'user')
                <a href="{{ route('petitions.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Petition
                </a>
            @endif
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-indigo-500"></i>
                Filter Petitions
            </h3>
            <form id="searchForm" method="GET" action="{{ route('petitions.reports') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Search Text</label>
                    <div class="relative flex items-center">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-9 pr-3 py-2.5 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm transition-all bg-white" placeholder="Petition No, Petitioner, or Accused...">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2.5 transition-all bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2.5 transition-all bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Petition Status</label>
                    <select name="status" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2.5 transition-all bg-white">
                        <option value="">All Petitions</option>
                        <optgroup label="Workflow Progress">
                            <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                            <option value="Forwarded" {{ request('status') == 'Forwarded' ? 'selected' : '' }}>Forwarded</option>
                            <option value="VR_Received" {{ request('status') == 'VR_Received' ? 'selected' : '' }}>Verification Report Received</option>
                            <option value="VR_Received_at_cpsp_date" {{ request('status') == 'VR_Received_at_cpsp_date' ? 'selected' : '' }}>Verification Report Received at CPSP</option>    
                        </optgroup>
                        <optgroup label="Final Decisions">
                            <option value="PE" {{ request('status') == 'PE' ? 'selected' : '' }}>PE (Preliminary Enquiry)</option>
                            <option value="SC" {{ request('status') == 'SC' ? 'selected' : '' }}>SC (Show Cause)</option>
                            <option value="QV" {{ request('status') == 'QV' ? 'selected' : '' }}>QV (Quick Verification)</option>
                            <option value="ICell" {{ request('status') == 'ICell' ? 'selected' : '' }}>ICell</option>
                            <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                            <option value="Sent to Govt" {{ request('status') == 'Sent to Govt' ? 'selected' : '' }}>Sent to Govt</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nature of Petition</label>
                    <select name="nature_of_petition" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2.5 transition-all bg-white">
                        <option value="">All Natures</option>
                        @foreach(['Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence', 'others'] as $nature)
                            <option value="{{ $nature }}" {{ request('nature_of_petition') == $nature ? 'selected' : '' }}>{{ $nature }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Mode of Receipt</label>
                    <select name="mode_of_petition" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2.5 transition-all bg-white">
                        <option value="">All Modes</option>
                        @foreach(['Direct', 'Email', 'Whatsapp', 'Tollfree', 'others'] as $mode)
                            <option value="{{ $mode }}" {{ request('mode_of_petition') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 h-[42px]">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i> Search
                    </button>
                    <a href="{{ route('petitions.reports') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-sm transition-all flex items-center gap-2 shadow-sm whitespace-nowrap" title="Clear all filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Dynamic Table Container -->
        <div id="tableContainer">
            @include('user.partials.reports_table', ['petitions' => $petitions, 'tab' => $tab])
        </div>
    </div>

    <!-- Modals (Copied from petition_view for full functionality) -->
    <!-- Forward Modal -->
    <div x-show="showForwardModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showForwardModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="send" class="w-5 h-5 text-indigo-600"></i> Petition Decision</h3>
                <button type="button" @click="showForwardModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('forwardings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="petition_id" :value="activePetitionId">
                <div x-data="{ action: '' }" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Action</label>
                        <select name="action" x-model="action" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">Select Action...</option>
                            <option value="Forward_To_Unit">Forward to Unit</option>
                            <option value="Sent_to_Govt">Send to Govt (Decision)</option>
                            <option value="Close">Close Petition (Decision)</option>
                        </select>
                    </div>
                    <div x-show="action === 'Forward_To_Unit'" x-cloak class="pt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Unit</label>
                        <select name="to_unit_id" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" :required="action === 'Forward_To_Unit'">
                            <option value="">Select Unit...</option>
                            @foreach(\App\Models\Unit::all() as $unit)
                                <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pt-2 border-t border-slate-100 mt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Director Remarks</label>
                        <textarea name="director_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Enter instructions or remarks..." required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showForwardModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit Action <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- VR Modal -->
    <div x-show="showVrModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showVrModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="file-check" class="w-5 h-5 text-blue-600"></i> Update Verification Report</h3>
                <button type="button" @click="showVrModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form :action="'{{ url('forwardings') }}/' + activeForwardingId + '/vr'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Ref No</label>
                        <input type="text" name="vr_ref_no" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Date</label>
                        <input type="date" name="vr_date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">VR Remarks</label>
                    <textarea name="vr_remarks" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Enter findings or notes..." required></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload Report</label>
                        <input type="file" name="vr_file" class="w-full text-sm text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-600 mb-1 uppercase text-[10px] font-bold">Received in CPSP Date</label>
                        <input type="date" name="vr_received_at_cpsp_date" class="w-full rounded-lg border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-1.5 bg-blue-50/30">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" @click="showVrModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                        Submit VR <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Final Decision Modal -->
    <div x-show="showDecisionModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
        <div @click.away="showDecisionModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="check-square" class="w-5 h-5 text-rose-600"></i> Final Decision</h3>
                <button type="button" @click="showDecisionModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('decisions.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="petition_id" :value="activePetitionId">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Decision Code</label>
                    <select name="decision_remarks" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm" required>
                        <option value="">Select...</option>
                        <option value="PE">PE</option>
                        <option value="SC">SC</option>
                        <option value="QV">QV</option>
                        <option value="ICell">ICell</option>
                        <option value="Closed">Closed</option>
                        <option value="Sent to Govt">Sent to Govt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Final Remarks</label>
                    <textarea name="final_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm" placeholder="Director's final remarks..." required></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" @click="showDecisionModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
                        Submit Decision <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        if (!searchForm) return;
        
        let timer = null;
        let abortController = null;

        const performSearch = () => {
            const formData = new FormData(searchForm);
            const searchParams = new URLSearchParams(formData);
            const url = `${searchForm.action}?${searchParams.toString()}`;

            // Update URL without reloading
            window.history.pushState({}, '', url);

            // Abort previous request
            if (abortController) abortController.abort();
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
                // Replace table container content directly with the returned partial
                const currentTable = document.getElementById('tableContainer');
                if (currentTable) {
                    currentTable.innerHTML = html;
                    if (window.lucide) window.lucide.createIcons();
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') console.error('Error:', error);
            });
        };

        // Debounce text inputs
        searchForm.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('keyup', () => {
                clearTimeout(timer);
                timer = setTimeout(performSearch, 500);
            });
        });

        // Instant filter for select and date
        searchForm.querySelectorAll('select, input[type="date"]').forEach(input => {
            input.addEventListener('change', performSearch);
        });
        
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    });
</script>
@endsection