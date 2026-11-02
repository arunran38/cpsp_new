@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activePetitionDate: '', activeForwardingId: null, showReturnModal: false, returnPetitionId: null, returnReceiptNo: '' }"
        @open-return-modal.window="returnPetitionId = $event.detail.id; returnReceiptNo = $event.detail.receipt; showReturnModal = true"
        class="space-y-6">
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="flex items-center gap-4 z-10">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-400/20 text-teal-400 shadow-inner shrink-0">
                        <i data-lucide="scroll-text" class="w-6 h-6"></i>
                    </div>
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                            Submitted Petitions
                        </h1>
                        <p class="text-slate-300 font-medium max-w-2xl text-sm">
                            Unified view of all petitions and workflow stages.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 z-10">
                    @php
                        $user = Auth::user();
                        $currentSeat = $user?->currentSeatUser()?->seat;
                        $isInward = $user && ($user->hasRole(['inward', 'Inward']) || ($currentSeat && ($currentSeat->hasRole(['inward', 'Inward']) || stripos($currentSeat->seat_name, 'Inward') !== false)));
                    @endphp
                    @if($isInward)
                        <a href="{{ route('inward.enter_petition', ['show_form' => 1]) }}"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                            <i data-lucide="file-plus-2" class="w-4 h-4"></i> New Registration
                        </a>
                    @elseif(Auth::user()->canAccess('create petitions') || Auth::user()->canAccess('create petition') || Auth::user()->canAccess('create inward petitions') || Auth::user()->canAccess('create_inward_petitions'))
                        <a href="{{ route('petitions.create') }}"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                            <i data-lucide="file-plus-2" class="w-4 h-4"></i> New Petition
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Unified Tabs -->
        <div
            class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 p-1.5 rounded-2xl mb-6 w-full bg-slate-900/60 backdrop-blur-xl border border-slate-700/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
            <a href="{{ route('petitions.index', ['tab' => 'inward']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'inward' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>Inward Petitions</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'inward' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['inward'] ?? 0 }}</span>
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'all']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'all' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>All Petitions</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'all' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'received']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'received' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>Petitions Processed</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'received' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['received'] ?? 0 }}</span>
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'forwarded' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>Forwarded Petitions</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'forwarded' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['forwarded'] ?? 0 }}</span>
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'vrs' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>Verification Reports Received</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'vrs' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['vrs'] ?? 0 }}</span>
            </a>
            <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}"
                class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 {{ $tab === 'decisions' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] border border-emerald-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                <span>Final Decisions</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors {{ $tab === 'decisions' ? 'bg-white text-emerald-700' : 'bg-white/20 text-white' }}">{{ $unifiedCounts['decisions'] ?? 0 }}</span>
            </a>
        </div>

        @if($tab === 'decisions')
            <!-- Sub-Tabs for Final Decisions -->
            <div
                class="flex flex-wrap items-center justify-center gap-2 {{ request('status') === 'Sent to Govt' ? 'mb-3' : 'mb-6' }} p-1.5 bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] animate-in fade-in slide-in-from-top-2 duration-300">
                @php
                    $statuses = [
                        'All' => null,
                        'VC' => 'VC',
                        'VE' => 'VE',
                        'PE' => 'PE',
                        'SC' => 'SC',
                        'CV' => 'CV',
                        'Internal Vigilance' => 'IV',
                        'ICell' => 'ICell',
                        'Closed' => 'Closed',
                        'Sent to Govt' => 'Sent to Govt',
                        'Sent to SPC' => 'Sent to SPC'
                    ];
                @endphp
                @foreach($statuses as $label => $value)
                    <a href="{{ route('petitions.index', ['tab' => 'decisions', 'status' => $value]) }}"
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-300 {{ request('status') == $value ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-[0_0_15px_rgba(16,185,129,0.3)] border border-emerald-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                        <span>{{ $label }}</span>
                        <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[10px] font-black px-1.5 transition-colors {{ request('status') == $value ? 'bg-white text-emerald-700' : 'bg-white/20 text-white' }}">{{ $decisionCounts[$label] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            @if(request('status') === 'Sent to Govt')
                <!-- Sub-Navigation for Recommendation Type under Sent to Govt -->
                <div
                    class="flex flex-wrap items-center justify-center gap-2 mb-6 p-1.5 bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] animate-in fade-in slide-in-from-top-2 duration-300">
                    @php
                        $recTypes = [
                            'All' => null,
                            '17 A Sanction' => '17 A Sanction',
                            'Report with Recommendations' => 'Report with Recommendations',
                            'No Recommendations' => 'No Recommendations',
                        ];
                    @endphp
                    @foreach($recTypes as $label => $value)
                        @php
                            $isSelected = $value === null
                                ? empty(request('recommendation_type'))
                                : request('recommendation_type') === $value;
                            $recUrl = route('petitions.index', array_filter([
                                'tab' => 'decisions',
                                'status' => 'Sent to Govt',
                                'recommendation_type' => ($value === null || $isSelected) ? null : $value,
                                'search' => request('search'),
                                'date_from' => request('date_from'),
                                'date_to' => request('date_to')
                            ]));
                        @endphp
                        <a href="{{ $recUrl }}"
                            class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-300 {{ $isSelected ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-[0_0_15px_rgba(16,185,129,0.3)] border border-emerald-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent' }}">
                            <span>{{ $label }}</span>
                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[10px] font-black px-1.5 transition-colors {{ $isSelected ? 'bg-white text-emerald-700' : 'bg-white/20 text-white' }}">{{ $govtRecCounts[$label] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
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
                    @if(request('recommendation_type'))
                        <input type="hidden" name="recommendation_type" value="{{ request('recommendation_type') }}">
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
                            value="{{ request('date_from') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                        <input type="date" id="date_to" placeholder="DD-MM-YYYY" name="date_to"
                            value="{{ request('date_to') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                        <p id="dateRangeError" class="mt-1 text-xs text-rose-600 hidden" aria-live="polite"></p>
                    </div>
                    <div class="flex-none">
                        <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                        <a href="{{ route('petitions.index', array_filter(['tab' => $tab, 'status' => request('status'), 'recommendation_type' => request('recommendation_type')])) }}"
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

                <form action="{{ route('forwardings.store') }}" method="POST" enctype="multipart/form-data"
                    x-data="{ 
                        action: '', 
                        recommendationType: '',
                        recommendationTypeError: '',
                        fileNo: '', 
                        fileNoError: '',
                        fileCreatedDate: '',
                        forwardedDate: '',
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
                        },
                        validateForm(e) {
                            this.recommendationTypeError = '';
                            if (this.action === 'Sent_to_Govt' && !this.recommendationType) {
                                this.recommendationTypeError = 'Recommendation Type is required.';
                                if (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                return false;
                            }
                            if (this.fileNoError) {
                                if (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                return false;
                            }
                            return true;
                        }
                    }"
                    x-init="
                        $watch('action', val => { if (val !== 'Sent_to_Govt') { recommendationType = ''; recommendationTypeError = ''; } });
                        $watch('recommendationType', val => { if (val) recommendationTypeError = ''; });
                        $watch('fileCreatedDate', val => { if (val && forwardedDate && forwardedDate < val) { forwardedDate = val; } });
                    "
                    @submit="if (!validateForm($event)) return false;">
                    @csrf
                    <input type="hidden" name="petition_id" :value="activePetitionId">

                    <div class="space-y-4">
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
                                <input type="date" name="file_created_date" x-model="fileCreatedDate"
                                    :min="activePetitionDate || null" max="{{ date('Y-m-d') }}"
                                    placeholder="DD-MM-YYYY"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                    style="color: #1e40af !important;">
                            </div>
                        </div>

                        @php
                            $actionOptions = [
                                'Forward_To_Unit' => 'Forward to Unit',
                                'Sent_to_Govt' => 'Send to Govt (Decision)',
                                'Sent_to_SPC' => 'Send to SPC (Decision)',
                                'Internal_Vigilance' => 'Send to Internal Vigilance',
                                'Close' => 'Close Petition (Decision)',
                            ];
                        @endphp
                        <x-select label="Action" name="action" :options="$actionOptions" x-model="action"
                            placeholder="Select Action..." required />

                        <div x-show="action === 'Sent_to_Govt'" x-cloak class="pt-2">
                            @php
                                $govtRecOptions = [
                                    '17 A Sanction' => '17 A Sanction',
                                    'Report with Recommendations' => 'Report with Recommendations',
                                    'No Recommendations' => 'No Recommendations',
                                ];
                            @endphp
                            <x-select label="Recommendation Type" name="govt_recommendation_type" :options="$govtRecOptions"
                                x-model="recommendationType"
                                placeholder="Select Recommendation Type..." />
                            <p x-show="recommendationTypeError" x-text="recommendationTypeError" x-cloak class="mt-1 text-xs font-semibold text-rose-600"></p>
                        </div>

                        <div x-show="action === 'Forward_To_Unit'" x-cloak class="pt-2">
                            @php
                                $unitOptions = \App\Models\Unit::pluck('unit_name', 'unit_id')->toArray();
                            @endphp
                            <x-select label="Select Unit" name="to_unit_id" :options="$unitOptions"
                                placeholder="Select Unit..." x-bind:required="action === 'Forward_To_Unit'" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Action Date</label>
                            <input type="date" name="forwarded_date" x-model="forwardedDate"
                                :min="fileCreatedDate || activePetitionDate || null" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required>
                        </div>

                        <div x-show="action === 'Close' || action === 'Sent_to_Govt' || action === 'Sent_to_SPC' || action === 'Internal_Vigilance'" x-cloak class="pt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Directorate Order Number</label>
                            <input type="text" name="directorate_order_number"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 mb-3"
                                placeholder="Order Number"
                                x-bind:required="action === 'Close' || action === 'Sent_to_Govt' || action === 'Sent_to_SPC' || action === 'Internal_Vigilance'">

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
                            <button type="submit" @click="if (!validateForm($event)) return false;" :disabled="fileNoError !== ''"
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
                                'IV' => 'Internal Vigilance (IV)',
                                'ICell' => 'Intelligence Cell (I Cell)',
                                'Closed' => 'Closed',
                                'Sent to Govt' => 'Sent to Govt',
                                'Sent to SPC' => 'Sent to SPC'
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

                <form action="{{ route('decisions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                    x-data="{ 
                        finalDecision: '', 
                        recommendationType: '',
                        recommendationTypeError: '',
                        validateDecision(e) {
                            this.recommendationTypeError = '';
                            if (this.finalDecision === 'Sent to Govt' && !this.recommendationType) {
                                this.recommendationTypeError = 'Recommendation Type is required.';
                                if (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                return false;
                            }
                            return true;
                        }
                    }"
                    x-init="
                        $watch('finalDecision', val => { if (val !== 'Sent to Govt') { recommendationType = ''; recommendationTypeError = ''; } });
                        $watch('recommendationType', val => { if (val) recommendationTypeError = ''; });
                    "
                    @submit="if (!validateDecision($event)) return false;">
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
                                'IV' => 'Internal Vigilance (IV)',
                                'ICell' => 'Intelligence Cell (I Cell)',
                                'Closed' => 'Closed',
                                'Sent to Govt' => 'Sent to Govt',
                                'Sent to SPC' => 'Sent to SPC'
                            ];
                        @endphp
                        <x-select label="Final Decision" name="final_decision" :options="$decisionOptions"
                            x-model="finalDecision"
                            placeholder="Select..." required />

                        <div x-show="finalDecision === 'Sent to Govt'" x-cloak class="pt-2">
                            @php
                                $govtRecOptions = [
                                    '17 A Sanction' => '17 A Sanction',
                                    'Report with Recommendations' => 'Report with Recommendations',
                                    'No Recommendations' => 'No Recommendations',
                                ];
                            @endphp
                            <x-select label="Recommendation Type" name="govt_recommendation_type" :options="$govtRecOptions"
                                x-model="recommendationType"
                                placeholder="Select Recommendation Type..." />
                            <p x-show="recommendationTypeError" x-text="recommendationTypeError" x-cloak class="mt-1 text-xs font-semibold text-rose-600"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Decision Date</label>
                            <input type="date" name="decision_date" :min="activePetitionDate || null" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Directorate Order Number</label>
                            <input type="text" name="directorate_order_number"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-slate-800"
                                placeholder="Order Number" required>
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
                        <button type="submit" @click="if (!validateDecision($event)) return false;"
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit Decision <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Return to Inward Modal -->
        @include('user.partials.return_to_inward_modal')
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
                            if (window.Alpine) {
                                window.Alpine.initTree(currentTable);
                            }
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