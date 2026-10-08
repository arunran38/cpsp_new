@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    @php
        $seatsList = $seats ?? \App\Models\Seat::role('CPSP')->where('is_active', true)->with(['activeAssignment.user'])->get()->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
        $unitsList = $units ?? \App\Models\Unit::orderBy('unit_name')->get();
        $tab = 'inward';
    @endphp

    <div class="space-y-6" x-data="inwardPetitionForm()" @keydown.ctrl.enter.prevent="openConfirmationModal()"
        @keydown.cmd.enter.prevent="openConfirmationModal()">



        @if((Auth::user()->canAccess('create_inward_petitions') || Auth::user()->canAccess('create inward petitions')) && !Auth::user()->hasRole('super admin') && !Auth::user()->hasRole('admin'))
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-2">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        Inward Petition Registration
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Minimal inward petition entry and seat transfer.
                    </p>
                </div>

            </div>
        </div>

        <!-- Alert / Validation Feedback -->

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center gap-3 text-rose-800 text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 space-y-1">
                <div class="flex items-center gap-2 font-semibold text-rose-800 text-sm">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    Please check the form for errors:
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Inward Form (CLEAN SINGLE CARD) -->
        <form method="POST" action="{{ route('petitions.store') }}" id="inwardPetitionForm"
            @submit.prevent="openConfirmationModal()">
            @csrf
            <input type="hidden" name="is_inward_entry" value="1">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Inward & Receipt Details</h2>
                            <p class="text-xs text-slate-500">Register inward petition and transfer to concerned seat</p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Receipt Number -->
                            <div class="space-y-1.5">
                                <label for="receipt_no" class="block text-sm font-semibold text-slate-700">
                                    Receipt No <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="receipt_no" id="receipt_no" x-model="receiptNo"
                                        @blur="checkReceiptUniqueness()" @keydown.enter.prevent="focusNextField($event)"
                                        required placeholder="Ex. 12/DVACB/2026-CPSP"
                                        class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all placeholder:text-slate-400"
                                        :class="receiptError ? 'border-rose-500 ring-2 ring-rose-500/20' : (receiptAvailable ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200')">

                                    <div x-show="receiptChecking" class="absolute right-3 top-3" style="display: none;">
                                        <i data-lucide="loader-2" class="w-4 h-4 text-indigo-600 animate-spin"></i>
                                    </div>
                                </div>
                                <template x-if="receiptError">
                                    <p class="text-xs font-medium text-rose-500 flex items-center gap-1 mt-1">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        <span x-text="receiptError"></span>
                                    </p>
                                </template>
                                <template x-if="receiptAvailable">
                                    <p class="text-xs font-medium text-emerald-600 flex items-center gap-1 mt-1">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        Receipt number available
                                    </p>
                                </template>
                            </div>

                            <!-- Date of Receipt -->
                            <div class="space-y-1.5">
                                <label for="date_of_petition_received" class="block text-sm font-semibold text-slate-700">
                                    Date of Receipt <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="date_of_petition_received" id="date_of_petition_received"
                                    x-model="dateOfReceipt"
                                    value="{{ old('date_of_petition_received', now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d')) }}"
                                    required @keydown.enter.prevent="focusNextField($event)"
                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                    class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                            </div>

                            <!-- Mode of Receipt -->
                            <div>
                                <x-select label="Mode of Receipt *" name="mode_of_petition_received"
                                    x-model="modeOfPetition" required :options="[
                                                  '' => 'Select Mode', 
                                                  'Tapal' => 'Tapal', 
                                                  'Email' => 'Email', 
                                                  'Direct' => 'Direct', 
                                                  'Whatsapp' => 'Whatsapp', 
                                                  'Tollfree' => 'Tollfree', 
                                                  'Unit' => 'Unit', 
                                                  'iaps' => 'iAPS', 
                                                  'others' => 'Others'
                                              ]" />
                            </div>

                            <!-- NESTED SECTION: Appears ONLY if Mode of Receipt is "Unit" -->
                            <div x-show="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" style="display: none;"
                                class="md:col-span-2 lg:col-span-3 bg-indigo-50/60 p-4 rounded-xl border border-indigo-100 grid grid-cols-1 md:grid-cols-2 gap-4">

                                <!-- Select Concerned Unit -->
                                <div class="space-y-1.5">
                                    <label for="unit_id"
                                        class="block text-sm font-semibold text-indigo-900 flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i>
                                        Select Concerned Unit <span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <select name="unit_id" id="unit_id" x-model="selectedUnitId"
                                        x-bind:required="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                        class="block w-full px-4 py-2.5 text-sm font-medium bg-white border border-indigo-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                        <option value="">-- Select Unit --</option>
                                        @foreach($unitsList as $unitItem)
                                            <option value="{{ $unitItem->unit_id }}" {{ old('unit_id') == $unitItem->unit_id ? 'selected' : '' }}>
                                                {{ $unitItem->unit_name }} ({{ $unitItem->unit_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date of Petition Received at Unit -->
                                <div class="space-y-1.5">
                                    <label for="date_of_petition_received_at_unit"
                                        class="block text-sm font-semibold text-indigo-900 flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                                        Date of Petition Received at Unit<span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" name="date_of_petition_received_at_unit"
                                        id="date_of_petition_received_at_unit" x-model="dateOfPetitionReceivedAtUnit"
                                        value="{{ old('date_of_petition_received_at_unit') }}"
                                        x-bind:required="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                        max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                        class="block w-full px-4 py-2.5 text-sm bg-white border border-indigo-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                </div>
                            </div>

                            <!-- Mode Others Conditional Input -->
                            <div x-show="modeOfPetition === 'others'" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0" style="display: none;"
                                class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                <x-input label="Specify Mode of Receipt *" name="mode_others"
                                    placeholder="Enter custom mode of receipt..."
                                    x-bind:required="modeOfPetition === 'others'" />
                            </div>

                            <!-- Complainant Name -->
                            <div class="space-y-1.5">
                                <label for="complainant_name" class="block text-sm font-semibold text-slate-700">
                                    Complainant Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="complainants[0][name]" id="complainant_name"
                                    x-model="complainantName" value="{{ old('complainants.0.name') }}" required
                                    @keydown.enter.prevent="focusNextField($event)"
                                    placeholder="Enter full name of complainant..."
                                    class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all placeholder:text-slate-400">
                            </div>

                            <!-- Target Concerned Seat Dropdown -->
                            <div class="space-y-1.5"
                                :class="(modeOfPetition && (modeOfPetition.toLowerCase() === 'unit' || modeOfPetition === 'others')) ? 'col-span-1' : 'col-span-1'">
                                <label for="seat_id" class="block text-sm font-semibold text-slate-700">
                                    Concerned Seat <span class="text-rose-500">*</span>
                                </label>
                                <select name="seat_id" id="seat_id" x-model="selectedSeatId" required
                                    class="block w-full px-4 py-2.5 text-sm font-medium bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                    <option value="">-- Select Concerned Seat --</option>
                                    @foreach($seatsList as $seatItem)
                                        @php
                                            $occupantName = $seatItem->activeAssignment?->user?->name;
                                        @endphp
                                        <option value="{{ $seatItem->seat_id }}" {{ old('seat_id') == $seatItem->seat_id ? 'selected' : '' }}>
                                            {{ $seatItem->seat_name }} {{ $occupantName ? "($occupantName)" : '(Vacant)' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Card Action Footer -->
                    <div
                        class="px-6 py-4 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-end gap-4">

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <button type="reset"
                                @click="receiptNo = ''; complainantName = ''; selectedSeatId = ''; selectedUnitId = ''; dateOfPetitionReceivedAtUnit = ''; receiptError = null; receiptAvailable = false"
                                class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all flex items-center gap-2">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset
                            </button>

                            <button type="button" @click="openConfirmationModal()"
                                :disabled="receiptError !== null || receiptChecking"
                                class="px-7 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Submit & Transfer Petition
                            </button>
                        </div>
                    </div>
                </div>
        </form>
        @endif




        <!-- Premium Header Section for View Inward Petitions -->
        <div id="view-inward-petitions" class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-4 mt-8 scroll-mt-24">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-5 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        View Inward Petitions
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-xs sm:text-sm">
                        Track and manage petitions registered through the inward section.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        @php
            $currentUser = Auth::user();
            $canFilterTransfer = $currentUser && (
                $currentUser->canAccess('filter_transfer') ||
                $currentUser->canAccess('filter transfer') ||
                $currentUser->canAccess('access admin dashboard')
            );

            $filterButtons = [
                'all' => 'All Petitions',
                'transferred' => 'Transferred',
                'returned' => 'Returned from CPSP',
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mt-6">
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                @if($canFilterTransfer)
                    <!-- Filter Pills: All Petitions, Transferred, Returned from CPSP -->
                    <nav class="flex flex-nowrap items-center gap-2 overflow-x-auto hide-scrollbar" aria-label="Filter inward transactions">
                        @foreach($filterButtons as $filterKey => $filterLabel)
                            <a href="{{ route('inward.enter_petition', array_merge(request()->except(['page', 'filter']), ['filter' => $filterKey])) }}"
                                @class([
                                    'h-10 px-4 inline-flex items-center gap-2 text-sm font-bold rounded-xl border transition-all shadow-sm',
                                    'bg-teal-600 text-white border-teal-600 shadow-sm' => $filter === $filterKey,
                                    'bg-indigo-50/50 text-indigo-700 border-indigo-200 hover:bg-indigo-100' => $filter !== $filterKey,
                                ])>
                                <span>{{ $filterLabel }}</span>
                                @if(isset($counts))
                                    <span id="badge-{{ $filterKey }}" @class([
                                        'inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors',
                                        'bg-white text-teal-700' => $filter === $filterKey,
                                        'bg-rose-600 text-white animate-pulse shadow-[0_0_8px_rgba(225,29,72,0.5)]' => $filter !== $filterKey && $filterKey === 'returned' && $counts['returned'] > 0,
                                        'bg-indigo-600 text-white' => $filter !== $filterKey && !($filterKey === 'returned' && $counts['returned'] > 0),
                                    ])>
                                        {{ $counts[$filterKey] ?? 0 }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                @else
                    <div></div>
                @endif

                <!-- Search and Transfer-Date Filters -->
                <form id="search-form" method="GET" action="{{ route('inward.enter_petition') }}"
                    class="flex flex-nowrap items-center gap-2 overflow-x-auto hide-scrollbar" x-data="{ searchTimer: null }">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="relative min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            @input="clearTimeout(searchTimer); searchTimer = setTimeout(() => performLiveSearch(), 500)"
                            placeholder="Receipt no, Complainant ..."
                            class="h-10 w-full xl:w-64 pl-9 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white focus:border-indigo-600 outline-none transition-all">
                        <i data-lucide="search"
                            class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 h-10">
                        <span class="text-xs font-semibold text-slate-500">From</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            @change="performLiveSearch()"
                            class="bg-transparent text-sm font-medium text-slate-900 outline-none border-none p-0 focus:ring-0">
                    </div>
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 h-10">
                        <span class="text-xs font-semibold text-slate-500">To</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            @change="performLiveSearch()"
                            class="bg-transparent text-sm font-medium text-slate-900 outline-none border-none p-0 focus:ring-0">
                    </div>
                    <button type="button" @click="performLiveSearch()"
                        class="h-10 px-5 text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition-all inline-flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filter
                    </button>
                    <button type="submit" formaction="{{ route('inward.export') }}"
                        class="h-10 px-5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-xl shadow-sm transition-all inline-flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        Export
                    </button>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <a href="{{ route('inward.enter_petition', ['filter' => $filter]) }}"
                            class="h-10 w-10 inline-flex items-center justify-center text-rose-500 hover:text-rose-700 hover:bg-rose-50 bg-slate-50 border border-slate-200 rounded-xl transition-colors"
                            title="Clear search and dates">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Inward Transactions Table (Keep each transaction in the same page) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6">

            <div id="inward-table-container">
                @include('inward.partials.inward_table')
            </div>
        </div>

        <!-- SUGGESTION 3: CONFIRMATION MODAL (കൺഫർമേഷൻ പോപ്പ്-അപ്പ്) -->
        <div x-show="showConfirmModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">

            <div
                class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden p-6 sm:p-8 space-y-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                        <i data-lucide="help-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Confirm Petition Transfer</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Please verify inward details before submitting</p>
                    </div>
                </div>

                <!-- Confirmation Summary Box -->
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-3.5 text-sm">
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-3">
                        <span class="text-slate-500 font-medium">Receipt No:</span>
                        <span class="font-bold text-slate-900" x-text="receiptNo || 'Not specified'"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-3">
                        <span class="text-slate-500 font-medium">Date of Receipt:</span>
                        <span class="font-bold text-slate-900" x-text="formatDate(dateOfReceipt)"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-3">
                        <span class="text-slate-500 font-medium">Complainant:</span>
                        <span class="font-bold text-slate-900" x-text="complainantName || 'Not specified'"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-3">
                        <span class="text-slate-500 font-medium">Mode of Receipt:</span>
                        <span
                            class="font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100"
                            x-text="modeOfPetition && modeOfPetition.toLowerCase() === 'unit' && getSelectedUnitName() ? 'Unit (' + getSelectedUnitName() + ')' : (modeOfPetition || 'Direct')"></span>
                    </div>
                    <template
                        x-if="modeOfPetition && modeOfPetition.toLowerCase() === 'unit' && dateOfPetitionReceivedAtUnit">
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-3">
                            <span class="text-slate-500 font-medium">Received at Unit Date:</span>
                            <span class="font-bold text-slate-900" x-text="formatDate(dateOfPetitionReceivedAtUnit)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-slate-500 font-medium">Concerned Seat:</span>
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200"
                            x-text="getSelectedSeatName()"></span>
                    </div>
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" @click="showConfirmModal = false"
                        class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">
                        Cancel & Edit
                    </button>
                    <button type="button" @click="submitFormNow()"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Yes, Submit & Transfer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function inwardPetitionForm() {
            return {
                receiptNo: '{{ old('receipt_no') }}',
                dateOfReceipt: '{{ old('date_of_petition_received', now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d')) }}',
                complainantName: '{{ old('complainants.0.name') }}',
                receiptError: null,
                receiptAvailable: false,
                receiptChecking: false,
                modeOfPetition: '{{ old('mode_of_petition_received', '') }}',
                selectedSeatId: '{{ old('seat_id', '') }}',
                selectedUnitId: '{{ old('unit_id', '') }}',
                dateOfPetitionReceivedAtUnit: '{{ old('date_of_petition_received_at_unit', '') }}',
                showConfirmModal: false,

                formatDate(val) {
                    if (!val) return 'Not specified';
                    try {
                        const parts = val.split('-');
                        if (parts.length === 3) {
                            return `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                        return val;
                    } catch (e) {
                        return val;
                    }
                },

                getSelectedSeatName() {
                    // Reading this.selectedSeatId registers reactivity dependency in Alpine
                    const id = this.selectedSeatId;
                    const select = document.getElementById('seat_id');
                    if (select && select.selectedIndex >= 0) {
                        const selectedOpt = select.options[select.selectedIndex];
                        if (selectedOpt && selectedOpt.value !== "") {
                            return selectedOpt.text;
                        }
                    }
                    return 'Not specified';
                },

                getSelectedUnitName() {
                    // Reading this.selectedUnitId registers reactivity dependency in Alpine
                    const id = this.selectedUnitId;
                    const select = document.getElementById('unit_id');
                    if (select && select.selectedIndex >= 0) {
                        const selectedOpt = select.options[select.selectedIndex];
                        if (selectedOpt && selectedOpt.value !== "") {
                            return selectedOpt.text;
                        }
                    }
                    return '';
                },

                openConfirmationModal() {
                    const form = document.getElementById('inwardPetitionForm');
                    if (form && form.checkValidity()) {
                        this.showConfirmModal = true;
                    } else if (form) {
                        form.reportValidity();
                    }
                },

                submitFormNow() {
                    const form = document.getElementById('inwardPetitionForm');
                    if (form) {
                        form.submit();
                    }
                },

                focusNextField(event) {
                    const formElements = Array.from(document.querySelectorAll('#inwardPetitionForm input, #inwardPetitionForm select, #inwardPetitionForm button'));
                    const currentIndex = formElements.indexOf(event.target);
                    if (currentIndex >= 0 && currentIndex < formElements.length - 1) {
                        formElements[currentIndex + 1].focus();
                    }
                },

                checkReceiptUniqueness() {
                    if (!this.receiptNo || this.receiptNo.trim() === '') {
                        this.receiptError = null;
                        this.receiptAvailable = false;
                        return;
                    }

                    this.receiptChecking = true;
                    this.receiptError = null;
                    this.receiptAvailable = false;

                    fetch('{{ route('petitions.checkPetitionNo') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ receipt_no: this.receiptNo.trim() })
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.receiptChecking = false;
                            if (data.exists) {
                                this.receiptError = 'Receipt number already exists in database!';
                                this.receiptAvailable = false;
                            } else {
                                this.receiptError = null;
                                this.receiptAvailable = true;
                            }
                        })
                        .catch(err => {
                            this.receiptChecking = false;
                        });
                }
            };
        }
    </script>
    <script>
        function performLiveSearch() {
            const form = document.getElementById('search-form');
            const url = new URL(form.action);
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            url.search = params.toString();
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('inward-table-container').innerHTML = html;
                    lucide.createIcons();
                    
                    const countsData = document.getElementById('counts-data');
                    if (countsData) {
                        const counts = {
                            all: parseInt(countsData.getAttribute('data-all')),
                            transferred: parseInt(countsData.getAttribute('data-transferred')),
                            returned: parseInt(countsData.getAttribute('data-returned'))
                        };
                        
                        ['all', 'transferred', 'returned'].forEach(key => {
                            const badge = document.getElementById('badge-' + key);
                            if (badge) {
                                badge.innerText = counts[key];
                                
                                // Special styling for 'returned'
                                if (key === 'returned') {
                                    const isSelected = badge.closest('a').classList.contains('bg-teal-600');
                                    if (!isSelected) {
                                        if (counts[key] > 0) {
                                            badge.classList.add('bg-rose-600', 'animate-pulse', 'shadow-[0_0_8px_rgba(225,29,72,0.5)]');
                                            badge.classList.remove('bg-indigo-600', 'bg-white', 'text-teal-700');
                                            badge.classList.add('text-white');
                                        } else {
                                            badge.classList.remove('bg-rose-600', 'animate-pulse', 'shadow-[0_0_8px_rgba(225,29,72,0.5)]');
                                            badge.classList.add('bg-indigo-600', 'text-white');
                                            badge.classList.remove('bg-white', 'text-teal-700');
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
        }
    </script>

    <!-- Reassign to CPSP Modal -->
    <div x-data="{ open: false, petitionId: null, receiptNo: '' }"
         @open-reassign-modal.window="open = true; petitionId = $event.detail.id; receiptNo = $event.detail.receipt"
         class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak x-show="open">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="open = false"
                    class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-[0_20px_60px_-15px_rgba(0,0,0,0.2)] transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    
                    <form :action="'{{ url('inward/petitions') }}/' + petitionId + '/reassign'" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between rounded-t-2xl relative overflow-hidden bg-white">
                            <div class="flex items-center gap-4 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-rose-100 text-rose-600 flex items-center justify-center">
                                    <i data-lucide="rotate-ccw" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-indigo-900 tracking-tight" id="modal-title">
                                        Re-transfer Petition to CPSP
                                    </h3>
                                    <p class="text-[13px] text-blue-600 font-medium mt-0.5">
                                        Select a new seat to re-transfer petition <strong x-text="receiptNo" class="text-teal-600 font-bold ml-0.5 font-mono"></strong>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="open = false"
                                class="text-blue-400 hover:text-blue-600 p-2 rounded-xl hover:bg-blue-50 transition-all relative z-10 focus:outline-none">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        
                        <div class="p-6 space-y-6 bg-white">
                            <div class="space-y-2.5">
                                <label class="block text-[15px] font-bold text-indigo-900 mb-1">
                                    Select Seat <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="seat_id" required class="appearance-none block w-full px-4 py-3 text-sm bg-blue-50 border border-blue-200 rounded-xl text-blue-900 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
                                        <option value="">Select a seat...</option>
                                        @foreach($seats as $seat)
                                            <option value="{{ $seat->seat_id }}">{{ $seat->seat_name }} {{ $seat->activeAssignment && $seat->activeAssignment->user ? '('.$seat->activeAssignment->user->name.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-blue-500">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-5 bg-slate-50/80 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center gap-3 rounded-b-2xl">
                            <button type="submit" class="w-full sm:w-auto px-7 py-2.5 text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-lg shadow-teal-600/20 transition-all flex items-center justify-center gap-2 focus:ring-4 focus:ring-teal-500/20 outline-none hover:-translate-y-0.5 active:translate-y-0">
                                Confirm Transfer
                            </button>
                            <button type="button" @click="open = false" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-blue-600 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all focus:ring-4 focus:ring-blue-100 outline-none">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection