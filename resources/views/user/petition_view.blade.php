@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activeForwardingId: null }"
        class="space-y-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <i data-lucide="list" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Submitted Petitions</h1>
                    <p class="text-sm text-slate-500 mt-1">Unified view of all petitions and workflow stages.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->role === 'user')
                    <a href="{{ route('petitions.create') }}"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
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
                New Petitions
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}"
                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'forwarded' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
                Forwarded Petitions
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}"
                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'vrs' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
                Verification Reports Received
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}"
                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'decisions' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' }}">
                Final Decisions
            </a>
        </div>

        @if($tab === 'decisions')
            <!-- Sub-Tabs for Final Decisions -->
            <div
                class="flex flex-wrap items-center gap-2 mb-6 p-1 bg-slate-50/50 rounded-2xl border border-slate-200 animate-in fade-in slide-in-from-top-2 duration-300">
                @php
                    $statuses = [
                        'All' => null,
                        'VC' => 'VC',
                        'VE' => 'VE',
                        'PE' => 'PE',
                        'SC' => 'SC',
                        'CV' => 'CV',
                        'ICell' => 'ICell',
                        'Closed' => 'Closed',
                        'Sent to Govt' => 'Sent to Govt'
                    ];
                @endphp
                @foreach($statuses as $label => $value)
                    <a href="{{ route('petitions.index', ['tab' => 'decisions', 'status' => $value]) }}"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all border {{ request('status') == $value ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
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
                <form id="searchForm" method="GET" action="{{ route('petitions.index') }}"
                    class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Search Petitions</label>
                        <div class="relative flex items-center">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 text-slate-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full pl-9 pr-3 py-[9px] rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                placeholder="Petition No, Petitioner, Accused, Firm/Project, Phone, or PEN...">
                        </div>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                        <input type="date" id="date_from" placeholder="DD-MM-YYYY" name="date_from"
                            value="{{ request('date_from') }}" max="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                        <input type="date" id="date_to" placeholder="DD-MM-YYYY" name="date_to"
                            value="{{ request('date_to') }}" max="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                        <p id="dateRangeError" class="mt-1 text-xs text-rose-600 hidden" aria-live="polite"></p>
                    </div>
                    <div class="flex-none">
                        <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                        <a href="{{ route('petitions.index', ['tab' => $tab, 'status' => request('status')]) }}"
                            class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap"
                            title="Clear all filters">
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
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showForwardModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="send"
                            class="w-5 h-5 text-indigo-600"></i> Petition Decision</h3>
                    <button type="button" @click="showForwardModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('forwardings.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="petition_id" :value="activePetitionId">

                    <div x-data="{ 
                                action: '', 
                                fileNo: '', 
                                fileNoError: '',
                                checkEmpty() {
                                    if (!this.fileNo.trim()) {
                                        this.fileNoError = 'this field is required';
                                    } else {
                                        this.fileNoError = '';
                                    }
                                },
                                async checkFileNoUniqueness() {
                                    if (!this.fileNo.trim()) {
                                        this.fileNoError = 'this field is required';
                                        return;
                                    }
                                    try {
                                        const response = await fetch('/petitions/check-file-no', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                            },
                                            body: JSON.stringify({ file_no: this.fileNo, petition_id: activePetitionId })
                                        });
                                        const data = await response.json();
                                        if (data.exists) {
                                            this.fileNoError = 'File number already exists';
                                        } else {
                                            this.fileNoError = '';
                                        }
                                    } catch (error) {
                                        console.error(error);
                                    }
                                }
                            }" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Number <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="file_no" required x-model="fileNo" @blur="checkFileNoUniqueness"
                                    @input="fileNoError = ''"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-gray-900"
                                    placeholder="Enter File Number">
                                <p x-show="fileNoError" x-text="fileNoError" x-cloak class="mt-1 text-sm text-red-600"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Date</label>
                                <input type="date" name="file_created_date" max="{{ date('Y-m-d') }}"
                                    placeholder="DD-MM-YYYY"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        @php
                            $actionOptions = [
                                'Forward_To_Unit' => 'Forward to Unit',
                                'Sent_to_Govt' => 'Send to Govt (Decision)',
                                'Close' => 'Close Petition (Decision)',
                            ];
                        @endphp
                        <x-select label="Action" name="action" :options="$actionOptions" x-model="action"
                            placeholder="Select Action..." required />


                        <div x-show="action === 'Forward_To_Unit'" x-cloak class="pt-2">
                            @php
                                $unitOptions = \App\Models\Unit::pluck('unit_name', 'unit_id')->toArray();
                            @endphp
                            <x-select label="Select Unit" name="to_unit_id" :options="$unitOptions"
                                placeholder="Select Unit..." x-bind:required="action === 'Forward_To_Unit'" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Action Date</label>
                            <input type="date" name="forwarded_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required>
                        </div>

                        <div x-show="action === 'Close' || action === 'Sent_to_Govt'" x-cloak class="pt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload Final Order</label>
                            <input type="file" name="final_order_file" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="pt-2 border-t border-slate-100 mt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Director Remarks</label>
                            <textarea name="director_remarks" rows="3"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                placeholder="Enter instructions or remarks..." required></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-6">
                            <button type="button" @click="showForwardModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                            <button type="submit" :disabled="fileNoError !== ''"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                Submit Action <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- VR Modal (Alpine.js) -->
        <div x-show="showVrModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showVrModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="file-check"
                            class="w-5 h-5 text-blue-600"></i> Update Verification Report</h3>
                    <button type="button" @click="showVrModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('forwardings') }}/' + activeForwardingId + '/vr'" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Ref No</label>
                            <input type="text" name="vr_ref_no"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Date</label>
                            <input type="date" placeholder="DD-MM-YYYY" name="vr_date"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required
                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div>
                        @php
                            $vrRecommendationOptions = [
                                'VC' => 'Vigilance Case (VC)',
                                'VE' => 'Vigilance Enquiry (VE)',
                                'PE' => 'Preliminary Enquiry (PE)',
                                'SC' => 'Surprise Check (SC)',
                                'CV' => 'Confidential Verification (CV)',
                                'ICell' => 'Intelligence Cell (I Cell)',
                                'Closed' => 'Closed',
                                'Sent to Govt' => 'Sent to Govt'
                            ];
                        @endphp
                        <x-select label="VR Recommendation" name="vr_remarks" :options="$vrRecommendationOptions"
                            placeholder="Select Recommendation..." required />
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload verification report</label>
                            <input type="file" name="vr_file" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-blue-600 mb-2 mt-2 tracking-wider text-[11px] font-bold">VR
                                received in CPSP</label>
                            <input type="date" placeholder="DD-MM-YYYY" name="vr_received_at_cpsp_date"
                                class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-800 font-semibold bg-blue-50/20"
                                style="color: #1e40af !important;"
                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showVrModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit VR <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Final Decision Modal (Alpine.js) -->
        <div x-show="showDecisionModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showDecisionModal = false"
                class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="check-square"
                            class="w-5 h-5 text-rose-600"></i> Final Decision</h3>
                    <button type="button" @click="showDecisionModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('decisions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="petition_id" :value="activePetitionId">

                    <div class="space-y-4">
                        @php
                            $decisionOptions = [
                                'VC' => 'Vigilance Case (VC)',
                                'VE' => 'Vigilance Enquiry (VE)',
                                'PE' => 'Preliminary Enquiry (PE)',
                                'SC' => 'Surprise Check (SC)',
                                'CV' => 'Confidential Verification (CV)',
                                'ICell' => 'Intelligence Cell (I Cell)',
                                'Closed' => 'Closed',
                                'Sent to Govt' => 'Sent to Govt'
                            ];
                        @endphp
                        <x-select label="Final Decision" name="decision_remarks" :options="$decisionOptions"
                            placeholder="Select..." required />

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Decision Date</label>
                            <input type="date" name="decision_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Final Remarks</label>
                        <textarea name="final_remarks" rows="3"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-black"
                            placeholder="Director's final remarks..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload Final Order Document</label>
                        <input type="file" name="final_order_file" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showDecisionModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
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
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('searchForm');
            if (!searchForm) return;

            let timer = null;
            let abortController = null;

            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');
            const dateRangeError = document.getElementById('dateRangeError');

            const validateDateRange = () => {
                if (!dateFromInput || !dateToInput || !dateRangeError) {
                    return true;
                }

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

            const performSearch = () => {
                if (!validateDateRange()) {
                    return;
                }

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
                input.addEventListener('change', () => {
                    if (validateDateRange()) {
                        performSearch();
                    }
                });
            });

            if (dateFromInput) {
                dateFromInput.addEventListener('change', validateDateRange);
            }

            if (dateToInput) {
                dateToInput.addEventListener('change', validateDateRange);
            }

            // Prevent default form submission via enter key
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                performSearch();
            });
        });
    </script>
@endsection