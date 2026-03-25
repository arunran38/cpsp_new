@extends('layouts.user')

@section('content')
<div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activeForwardingId: null }" class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="list" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Submitted Petitions</h1>
                <p class="text-sm text-slate-500 mt-1">Unified view of all petitions and workflow stages.</p>
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

    <!-- Unified Tabs -->
    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl mb-6 w-fit border border-slate-200 shadow-sm">
        <a href="{{ route('petitions.index', ['tab' => 'all']) }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
            All Petitions
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'received']) }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'received' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
            Received
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'forwarded' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
            Forwarded
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'vrs' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
            VR Received
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'decisions' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
            Final Decisions
        </a>
    </div>

    @if($tab === 'decisions')
    <!-- Sub-Tabs for Final Decisions -->
    <div class="flex flex-wrap items-center gap-2 mb-6 p-1 bg-slate-50/50 rounded-2xl border border-slate-200 animate-in fade-in slide-in-from-top-2 duration-300">
        @php
            $statuses = ['All' => null, 'PE' => 'PE', 'SC' => 'SC', 'QV' => 'QV', 'Closed' => 'Closed', 'Sent to Govt' => 'Sent to Govt', 'ICell' => 'ICell'];
        @endphp
        @foreach($statuses as $label => $value)
            <a href="{{ route('petitions.index', ['tab' => 'decisions', 'status' => $value]) }}" 
               class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all border {{ request('status') == $value ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    @endif

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl space-y-1">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-3 text-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                {{ $error }}
            </div>
        @endforeach
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <form id="searchForm" method="GET" action="{{ route('petitions.index') }}" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Search Petitions</label>
                    <div class="relative flex items-center">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-9 pr-3 py-[9px] rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Petition No, Petitioner Name, or Accused Name...">
                    </div>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px]">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px]">
                </div>
                <div class="flex-none">
                    <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                    <a href="{{ route('petitions.index', ['tab' => $tab, 'status' => request('status')]) }}" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap" title="Clear all filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Dynamic Table Container -->
        <div id="tableContainer">
            @include('user.partials.reports_table', ['petitions' => $petitions, 'tab' => $tab])
        </div>
    </div>

    <!-- Forward Modal (Alpine.js) -->
    <div x-show="showForwardModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
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

    <!-- VR Modal (Alpine.js) -->
    <div x-show="showVrModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
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
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload verification report</label>
                        <input type="file" name="vr_file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 text-blue-600 font-bold">VR received in CPSP date</label>
                        <input type="date" name="vr_received_at_cpsp_date" class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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

    <!-- Final Decision Modal (Alpine.js) -->
    <div x-show="showDecisionModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
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
                            <option value="Closed">Closed</option>
                            <option value="Sent to Govt">Sent to Govt</option>
                            <option value="ICell">ICell</option>
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

            // Abort previous request if still pending
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

        // Attach keyup event with debounce for text inputs
        const textInputs = searchForm.querySelectorAll('input[type="text"]');
        textInputs.forEach(input => {
            input.addEventListener('keyup', (e) => {
                clearTimeout(timer);
                timer = setTimeout(performSearch, 500);
            });
        });

        // Attach change event for date, select inputs
        const changeInputs = searchForm.querySelectorAll('input[type="date"], select');
        changeInputs.forEach(input => {
            input.addEventListener('change', performSearch);
        });
        
        // Prevent default form submission via enter key
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    });
</script>
@endsection
