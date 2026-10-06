@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
@php
    $seatsList = $seats ?? \App\Models\Seat::where('is_active', true)->with(['activeAssignment.user'])->get()->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
    $unitsList = $units ?? \App\Models\Unit::orderBy('unit_name')->get();
@endphp

<div class="space-y-6" 
     x-data="inwardPetitionForm()"
     @keydown.ctrl.enter.prevent="openConfirmationModal()"
     @keydown.cmd.enter.prevent="openConfirmationModal()">
    
    <!-- Page Header (CPSP Inward Registration) -->
    <div class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-600 via-indigo-500 to-teal-500"></div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                <i data-lucide="file-input" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inward Petition Registration</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        CPSP Role Desk
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Minimal inward petition entry and seat transfer.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('petitions.index') }}" 
               class="px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Petitions
            </a>
        </div>
    </div>

    <!-- Alert / Validation Feedback -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-3 text-emerald-800 text-sm shadow-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

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
    <form method="POST" action="{{ route('petitions.store') }}" id="inwardPetitionForm" @submit.prevent="openConfirmationModal()">
        @csrf
        <input type="hidden" name="is_inward_entry" value="1">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Card Header -->
            <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Inward & Receipt Details</h2>
                        <p class="text-xs text-slate-500">Register inward petition and transfer to concerned seat</p>
                    </div>
                </div>

                <!-- Keyboard Hint -->
                <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                    <i data-lucide="keyboard" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span>Press <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-slate-100 border border-slate-300 rounded text-slate-700">Ctrl + Enter</kbd> to quick submit</span>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Receipt Number -->
                    <div class="space-y-1.5">
                        <label for="receipt_no" class="block text-sm font-semibold text-slate-700">
                            Receipt / Inward No <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="receipt_no" 
                                   id="receipt_no"
                                   x-model="receiptNo"
                                   @blur="checkReceiptUniqueness()"
                                   @keydown.enter.prevent="focusNextField($event)"
                                   required 
                                   placeholder="Ex. 12/DVACB/2026-CPSP"
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
                        <input type="date" 
                               name="date_of_petition_received" 
                               id="date_of_petition_received"
                               x-model="dateOfReceipt"
                               value="{{ old('date_of_petition_received', now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d')) }}" 
                               required
                               @keydown.enter.prevent="focusNextField($event)"
                               max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                               class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                    </div>

                    <!-- Mode of Receipt -->
                    <div>
                        <x-select label="Mode of Receipt *" 
                                  name="mode_of_petition_received" 
                                  x-model="modeOfPetition"
                                  required
                                  :options="[
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
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="md:col-span-2 bg-indigo-50/60 p-4 rounded-xl border border-indigo-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Select Concerned Unit -->
                        <div class="space-y-1.5">
                            <label for="unit_id" class="block text-sm font-semibold text-indigo-900 flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i>
                                Select Concerned Unit (യൂണിറ്റ് സെലക്ട് ചെയ്യുക) <span class="text-rose-500">*</span>
                            </label>
                            <select name="unit_id" 
                                    id="unit_id" 
                                    x-model="selectedUnitId"
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
                            <label for="date_of_petition_received_at_unit" class="block text-sm font-semibold text-indigo-900 flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                                Date of Petition Received at Unit (യൂണിറ്റിൽ ലഭിച്ച തീയതി) <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date_of_petition_received_at_unit" 
                                   id="date_of_petition_received_at_unit"
                                   x-model="dateOfPetitionReceivedAtUnit"
                                   value="{{ old('date_of_petition_received_at_unit') }}"
                                   x-bind:required="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                   max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                   class="block w-full px-4 py-2.5 text-sm bg-white border border-indigo-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                        </div>
                    </div>

                    <!-- Mode Others Conditional Input -->
                    <div x-show="modeOfPetition === 'others'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;"
                         class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <x-input label="Specify Mode of Receipt *" 
                                 name="mode_others" 
                                 placeholder="Enter custom mode of receipt..." 
                                 x-bind:required="modeOfPetition === 'others'" />
                    </div>

                    <!-- Complainant Name -->
                    <div class="space-y-1.5">
                        <label for="complainant_name" class="block text-sm font-semibold text-slate-700">
                            Complainant Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="complainants[0][name]" 
                               id="complainant_name"
                               x-model="complainantName"
                               value="{{ old('complainants.0.name') }}"
                               required 
                               @keydown.enter.prevent="focusNextField($event)"
                               placeholder="Enter full name of complainant..."
                               class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Target Concerned Seat Dropdown -->
                    <div class="space-y-1.5" :class="(modeOfPetition && (modeOfPetition.toLowerCase() === 'unit' || modeOfPetition === 'others')) ? 'col-span-1' : 'md:col-span-1'">
                        <label for="seat_id" class="block text-sm font-semibold text-slate-700">
                            Target Concerned Seat (ബന്ധപ്പെട്ട സീറ്റ്) <span class="text-rose-500">*</span>
                        </label>
                        <select name="seat_id" 
                                id="seat_id" 
                                x-model="selectedSeatId" 
                                required 
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
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>Registers inward petition and assigns it directly to target seat.</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="reset" 
                            @click="receiptNo = ''; complainantName = ''; selectedSeatId = ''; selectedUnitId = ''; dateOfPetitionReceivedAtUnit = ''; receiptError = null; receiptAvailable = false"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset
                    </button>

                    <button type="button" 
                            @click="openConfirmationModal()"
                            :disabled="receiptError !== null || receiptChecking"
                            class="px-7 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Submit & Transfer Petition
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Inward Transactions Table (Keep each transaction in the same page) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80 space-y-4">
            <!-- Row 1: Title & Records Count -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="arrow-right-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">Inward Transactions</h3>
                        @if(isset($transfers) && method_exists($transfers, 'total'))
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                {{ $transfers->total() }} records
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">History of inward petitions registered and transferred to seats</p>
                </div>
            </div>

            <!-- Row 2: Three buttons on the left (below title) aligned with Search & Dates on the right -->
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
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 pt-1">
                @if($canFilterTransfer)
                <!-- Filter Pills: All Petitions, Transferred, Returned from CPSP -->
                <nav class="flex flex-wrap items-center gap-2" aria-label="Filter inward transactions">
                    @foreach($filterButtons as $filterKey => $filterLabel)
                        <a href="{{ route('inward.enter_petition', array_merge(request()->except(['page', 'filter']), ['filter' => $filterKey])) }}"
                           @class([
                               'h-9 px-3.5 inline-flex items-center text-xs font-semibold rounded-xl border transition-all shadow-sm',
                               'bg-teal-700 text-white border-teal-700 shadow-sm' => $filter === $filterKey,
                               'bg-white text-blue-600 border-blue-200 hover:bg-blue-50/60' => $filter !== $filterKey,
                           ])>
                            {{ $filterLabel }}
                        </a>
                    @endforeach
                </nav>
                @else
                <div></div>
                @endif

                <!-- Search and Transfer-Date Filters (Aligned with Buttons) -->
                <form method="GET" action="{{ route('inward.enter_petition') }}" class="flex flex-wrap items-end gap-2.5">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search receipt, complainant..."
                               class="h-9 w-56 sm:w-64 pl-8 pr-3 text-xs bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                    </div>
                    <label class="flex flex-col gap-1 text-[10px] font-semibold text-blue-600">
                        Transfer date from
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="h-9 px-2.5 text-xs font-normal bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none">
                    </label>
                    <label class="flex flex-col gap-1 text-[10px] font-semibold text-blue-600">
                        Transfer date to
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="h-9 px-2.5 text-xs font-normal bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none">
                    </label>
                    <button type="submit" class="h-9 px-4 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition-all inline-flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <a href="{{ route('inward.enter_petition', ['filter' => $filter]) }}" class="h-9 w-9 inline-flex items-center justify-center text-slate-500 hover:text-slate-700 bg-slate-100 rounded-xl text-xs transition-colors" title="Clear search and dates">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-5 py-3 text-center w-12">#</th>
                        <th class="px-5 py-3">Receipt / Inward No</th>
                        <th class="px-5 py-3">Date of Receipt</th>
                        <th class="px-5 py-3">Transferred On</th>
                        <th class="px-5 py-3">Complainant</th>
                        <th class="px-5 py-3">Mode of Receipt</th>
                        <th class="px-5 py-3">Target Concerned Seat</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($transfers ?? [] as $index => $item)
                        @php
                            $complainant = $item->addresses->firstWhere('person_type', 'Complainant');
                            $seatUser = $item->seat?->activeAssignment?->user;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $loop->first && session('success') ? 'bg-indigo-50/40' : '' }}">
                            <td class="px-5 py-3.5 text-center font-medium text-slate-400">
                                {{ method_exists($transfers, 'firstItem') ? ($transfers->firstItem() + $index) : ($index + 1) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-indigo-900 block font-mono">{{ $item->receipt_no }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">ID: #{{ $item->petition_id }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->date_of_petition_received)->format('d-m-Y') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                                {{ $item->created_at?->format('d-m-Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $complainant?->person_name ?? 'N/A' }}</span>
                                @if($complainant?->phone)
                                    <span class="text-[10px] text-slate-500">{{ $complainant->phone }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $item->mode_of_petition_received }}
                                </span>
                                @if(strtolower($item->mode_of_petition_received) === 'unit' && $item->unit)
                                    <span class="block text-[10px] text-indigo-600 font-semibold mt-0.5">{{ $item->unit->unit_name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->seat)
                                    <span class="font-semibold text-indigo-700 block">{{ $item->seat->seat_name }}</span>
                                    <span class="text-[10px] text-slate-500 block">
                                        Occupant: {{ $seatUser ? $seatUser->name : '(Vacant)' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Not Assigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($item->is_returned_to_inward)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Returned to Inward
                                    </span>
                                @elseif($item->is_cpsp_processed)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Processed by CPSP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Transferred to Seat
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('petitions.show', $item->petition_id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-100 transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-7 h-7 text-slate-300"></i>
                                    <p class="font-medium text-slate-500">No inward petition transactions recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($transfers) && method_exists($transfers, 'hasPages') && $transfers->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/50">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- SUGGESTION 3: CONFIRMATION MODAL (കൺഫർമേഷൻ പോപ്പ്-അപ്പ്) -->
    <div x-show="showConfirmModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
        
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden p-6 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Confirm Petition Transfer</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Please verify inward details before submitting</p>
                </div>
            </div>

            <!-- Confirmation Summary Box -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2.5 text-xs">
                <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Receipt No:</span>
                    <span class="font-bold text-slate-900" x-text="receiptNo || 'Not specified'"></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Date of Receipt:</span>
                    <span class="font-bold text-slate-900" x-text="formatDate(dateOfReceipt)"></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Complainant:</span>
                    <span class="font-bold text-slate-900" x-text="complainantName || 'Not specified'"></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Mode of Receipt:</span>
                    <span class="font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100" 
                          x-text="modeOfPetition && modeOfPetition.toLowerCase() === 'unit' && getSelectedUnitName() ? 'Unit (' + getSelectedUnitName() + ')' : (modeOfPetition || 'Direct')"></span>
                </div>
                <template x-if="modeOfPetition && modeOfPetition.toLowerCase() === 'unit' && dateOfPetitionReceivedAtUnit">
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                        <span class="text-slate-500 font-medium">Received at Unit Date:</span>
                        <span class="font-bold text-slate-900" x-text="formatDate(dateOfPetitionReceivedAtUnit)"></span>
                    </div>
                </template>
                <div class="flex justify-between items-center pt-0.5">
                    <span class="text-slate-500 font-medium">Target Concerned Seat:</span>
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" x-text="getSelectedSeatName()"></span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="showConfirmModal = false"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">
                    Cancel & Edit
                </button>
                <button type="button" 
                        @click="submitFormNow()"
                        class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all flex items-center gap-1.5">
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
                } catch(e) {
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
@endsection
