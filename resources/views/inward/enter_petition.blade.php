@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    @php
        $seatsList = $seats ?? \App\Models\Seat::eligibleForInward()->with(['activeAssignment.user'])->get()->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
        $unitsList = $units ?? \App\Models\Unit::orderBy('unit_name')->get();
        $tab = 'inward';

        $currentUser = Auth::user();
        $hasInwardRole = $currentUser && (
            $currentUser->hasRole(['inward', 'Inward']) || 
            ($currentUser->currentSeatUser()?->seat && (
                $currentUser->currentSeatUser()->seat->hasRole(['inward', 'Inward']) || 
                str_contains(strtolower($currentUser->currentSeatUser()->seat->seat_name), 'inward')
            ))
        );

        $canRegisterInward = $currentUser && (
            $hasInwardRole ||
            $currentUser->canAccess('create inward petitions') ||
            $currentUser->canAccess('create_inward_petitions') ||
            $currentUser->canAccess('inward form') ||
            $currentUser->canAccess('inward form') ||
            $currentUser->hasRole(['inward', 'Inward', 'admin', 'Admin', 'super admin', 'Super Admin', 'developer', 'Developer'])
        );

        $shouldHideForm = false;
        if (!$errors->any() && !session('success')) {
            if (request()->has('show_form')) {
                $shouldHideForm = !request()->boolean('show_form');
            } elseif (request()->has('hide_form')) {
                $shouldHideForm = request()->boolean('hide_form');
            } elseif (request()->cookie('inward_form_hidden') === '1') {
                $shouldHideForm = true;
            }
        }
        $hideRegistrationForm = $shouldHideForm;
    @endphp

    <div class="space-y-6" x-data="inwardPetitionForm()" @keydown.ctrl.enter.prevent="openConfirmationModal()"
        @keydown.cmd.enter.prevent="openConfirmationModal()">

        @if($canRegisterInward)
        <div id="inward-registration-section"
             x-show="showRegistrationForm"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="{{ $hideRegistrationForm ? 'display: none;' : '' }}"
             class="space-y-6">

            <!-- Premium Header Section -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-2">
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
                                Inward Petition Registration
                            </h1>
                            <p class="text-slate-300 font-medium max-w-2xl text-sm">
                                Minimal inward petition entry and seat transfer.
                            </p>
                        </div>
                    </div>
                    <div class="z-10">
                        <button type="button" @click="setFormVisibility(false)"
                            class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/50 rounded-xl transition-all flex items-center gap-2 shadow-sm backdrop-blur-sm cursor-pointer">
                            <i data-lucide="eye-off" class="w-4 h-4"></i> Hide Form
                        </button>
                    </div>
                </div>
            </div>

        <!-- Alert / Validation Feedback -->


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
                                        @blur="handleReceiptBlur()" @keydown.enter.prevent="focusNextField($event)"
                                        required placeholder="Ex. 123456/{{ now()->year }}"
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
                                        Concerned Unit Name <span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <select name="unit_id" id="unit_id" x-model="selectedUnitId"
                                        x-bind:required="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                        class="block w-full px-4 py-2.5 text-sm font-medium bg-white border border-indigo-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                        <option value="">-- Select Concerned Unit --</option>
                                        @foreach($unitsList as $unitItem)
                                            <option value="{{ $unitItem->unit_id }}" {{ old('unit_id') == $unitItem->unit_id ? 'selected' : '' }}>
                                                {{ $unitItem->unit_name }} ({{ $unitItem->unit_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <p class="text-xs font-semibold text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date of Petition Received at Unit -->
                                <div class="space-y-1.5">
                                    <label for="date_of_petition_received_at_unit"
                                        class="block text-sm font-semibold text-indigo-900 flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                                        Date of Petition Received at Unit <span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" name="date_of_petition_received_at_unit"
                                        id="date_of_petition_received_at_unit" x-model="dateOfPetitionReceivedAtUnit"
                                        value="{{ old('date_of_petition_received_at_unit') }}"
                                        x-bind:required="modeOfPetition && modeOfPetition.toLowerCase() === 'unit'"
                                        max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                                        class="block w-full px-4 py-2.5 text-sm bg-white border border-indigo-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                    @error('date_of_petition_received_at_unit')
                                        <p class="text-xs font-semibold text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
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
        </div>
        @endif

        <!-- Premium Header Section for View Inward Petitions -->
        <div id="view-inward-petitions" class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6 mt-2 scroll-mt-24">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                            View Inward Petitions
                        </h1>
                        @if($hasInwardRole && !($currentUser->canAccess('view all petitions') && !session('is_impersonating_seat')))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                <i data-lucide="user-check" class="w-3.5 h-3.5"></i> My Registrations
                            </span>
                        @elseif(isset($isSeatLocked) && $isSeatLocked && isset($currentSeatName))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                <i data-lucide="shield" class="w-3.5 h-3.5"></i> {{ $currentSeatName }}
                            </span>
                        @endif
                    </div>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Track and manage petitions registered through the inward section.
                    </p>
                </div>
                @if($canRegisterInward)
                    <div class="z-10">
                        <button type="button" @click="setFormVisibility(!showRegistrationForm)"
                            class="px-5 py-2.5 text-xs font-bold rounded-xl transition-all flex items-center gap-2 shadow-md cursor-pointer"
                            :class="showRegistrationForm ? 'text-slate-300 bg-slate-800/80 hover:bg-slate-700 border border-slate-700/50' : 'text-white bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/30'">
                            <span x-show="!showRegistrationForm" class="flex items-center gap-1.5">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Register Inward Petition
                            </span>
                            <span x-show="showRegistrationForm" class="flex items-center gap-1.5" style="display: none;">
                                <i data-lucide="chevron-up" class="w-4 h-4"></i> Hide Registration Form
                            </span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Unified Tabs (Visible to users who can view all petitions) -->
        @if(!$isSuperAdmin && (Auth::user()->canAccess('view petitions') || Auth::user()->canAccess('view all petitions')))
            @php $tab = 'inward'; @endphp
            <div
                class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 p-1.5 rounded-2xl mb-6 w-full bg-slate-900/60 backdrop-blur-xl border border-slate-700/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                <a href="{{ route('inward.enter_petition') }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border border-indigo-400/30">
                    <span>Inward Petitions</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white text-indigo-700">{{ $unifiedCounts['inward'] ?? 0 }}</span>
                </a>
                <a href="{{ route('petitions.index', ['tab' => 'all']) }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent">
                    <span>All Petitions</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white/20 text-white">{{ $unifiedCounts['all'] ?? 0 }}</span>
                </a>
                <a href="{{ route('petitions.index', ['tab' => 'received']) }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent">
                    <span>Petitions Processed</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white/20 text-white">{{ $unifiedCounts['received'] ?? 0 }}</span>
                </a>
                <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent">
                    <span>Forwarded Petitions</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white/20 text-white">{{ $unifiedCounts['forwarded'] ?? 0 }}</span>
                </a>
                <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent">
                    <span>Verification Reports Received</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white/20 text-white">{{ $unifiedCounts['vrs'] ?? 0 }}</span>
                </a>
                <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}"
                    class="flex-1 text-center flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-transparent">
                    <span>Final Decisions</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] rounded-full text-[11px] font-black px-1.5 transition-colors bg-white/20 text-white">{{ $unifiedCounts['decisions'] ?? 0 }}</span>
                </a>
            </div>
        @endif

        <!-- Filter Section -->
        @php
            $currentUser = Auth::user();
            $canFilterTransfer = $currentUser && (
                $isSuperAdmin ||
                (
                    $currentUser->canAccess('filter transfer') &&
                    !$currentUser->canAccess('access admin dashboard')
                )
            );

            $filterButtons = [
                'all' => 'All Petitions',
                'transferred' => 'Transferred',
                'returned' => 'Returned from CPSP',
            ];
        @endphp

        <!-- Filter and Data Table Section -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden mt-6">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col gap-4">
                    @if($canFilterTransfer)
                        <!-- Filter Pills: All Petitions, Transferred, Returned from CPSP -->
                        <nav id="inward-filter-nav" class="flex flex-nowrap items-center gap-2 overflow-x-auto hide-scrollbar mb-2" aria-label="Filter inward transactions">
                            @foreach($filterButtons as $filterKey => $filterLabel)
                                @php
                                    $linkParams = array_merge(request()->except(['page', 'filter']), ['filter' => $filterKey]);
                                    if ($hideRegistrationForm) {
                                        $linkParams['hide_form'] = 1;
                                        unset($linkParams['show_form']);
                                    }
                                @endphp
                                <a href="{{ route('inward.enter_petition', $linkParams) }}#view-inward-petitions"
                                    data-filter-key="{{ $filterKey }}"
                                    @click.prevent="switchFilterTab('{{ $filterKey }}')"
                                    @class([
                                        'filter-pill px-4 py-1.5 inline-flex items-center gap-2 text-xs font-bold rounded-full border transition-all shadow-sm cursor-pointer',
                                        'bg-teal-600 text-white border-teal-600 shadow-sm' => $filter === $filterKey,
                                        'bg-indigo-50/50 text-indigo-700 border-indigo-200 hover:bg-indigo-100' => $filter !== $filterKey,
                                    ])>
                                    <span>{{ $filterLabel }}</span>
                                    @if(isset($counts))
                                        <span id="badge-{{ $filterKey }}" @class([
                                            'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors',
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
                    @endif

                    <form id="search-form" method="GET" action="{{ route('inward.enter_petition') }}"
                        class="flex flex-wrap gap-4 items-end" x-data="{ searchTimer: null }">
                        <input type="hidden" name="filter" id="filter-input" value="{{ $filter }}">
                        <input type="hidden" name="hide_form" id="hide-form-input" value="{{ $hideRegistrationForm ? '1' : '0' }}">
                        
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Search Petitions</label>
                            <div class="relative flex items-center">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3 text-slate-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    @input="clearTimeout(searchTimer); searchTimer = setTimeout(() => performLiveSearch(), 500)"
                                    placeholder="Receipt no, Complainant ..."
                                    class="w-full pl-9 pr-3 py-[9px] rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black">
                            </div>
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                @change="performLiveSearch()" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                style="color: #1e40af !important;">
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                @change="performLiveSearch()" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                style="color: #1e40af !important;">
                            <p id="dateRangeError" class="text-xs text-rose-600 mt-1 hidden font-semibold"></p>
                        </div>

                        @if(isset($seats) && $seats->count() > 0)
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-xs font-medium text-slate-700 mb-1">
                                    Concerned Seat
                                    @if(isset($isSeatLocked) && $isSeatLocked)
                                        <span class="text-[10px] font-normal text-slate-500">(Assigned)</span>
                                    @endif
                                </label>
                                @if(isset($isSeatLocked) && $isSeatLocked)
                                    <select disabled
                                        class="w-full rounded-lg border-slate-300 bg-slate-100 text-slate-600 shadow-sm text-sm py-[9px] font-semibold cursor-not-allowed">
                                        @foreach($seats as $seat)
                                            <option value="{{ $seat->seat_id }}" {{ (string)($defaultSeatId ?? '') === (string)$seat->seat_id ? 'selected' : '' }}>
                                                {{ $seat->seat_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($defaultSeatId))
                                        <input type="hidden" name="seat_id" value="{{ $defaultSeatId }}">
                                    @endif
                                @else
                                    <select name="seat_id" @change="performLiveSearch()"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold">
                                        <option value="">All Concerned Seats</option>
                                        @foreach($seats as $seat)
                                            <option value="{{ $seat->seat_id }}" {{ (string)request('seat_id') === (string)$seat->seat_id ? 'selected' : '' }}>
                                                {{ $seat->seat_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endif

                        <div class="flex-none flex items-center gap-2">
                            <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                            @if(request()->hasAny(['search', 'date_from', 'date_to']) || (!isset($isSeatLocked) || !$isSeatLocked) && request()->has('seat_id'))
                                @php
                                    $resetParams = ['filter' => $filter];
                                    if ($hideRegistrationForm) {
                                        $resetParams['hide_form'] = 1;
                                    }
                                    if (isset($isSeatLocked) && $isSeatLocked && isset($defaultSeatId)) {
                                        $resetParams['seat_id'] = $defaultSeatId;
                                    }
                                @endphp
                                <a href="{{ route('inward.enter_petition', $resetParams) }}#view-inward-petitions"
                                    class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap"
                                    title="Clear all filters">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    Reset
                                </a>
                            @endif
                            
                            <button type="button" @click="performLiveSearch()"
                                class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                                Filter
                            </button>
                            
                            <button type="submit" formaction="{{ route('inward.export') }}"
                                class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                Export
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Inward Transactions Table -->
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
            let initialHidden = {{ $hideRegistrationForm ? 'true' : 'false' }};
            @if(!$errors->any() && !session('success'))
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('show_form')) {
                        initialHidden = urlParams.get('show_form') !== '1';
                        if (!initialHidden) {
                            try { localStorage.setItem('inward_form_hidden', '0'); } catch (e) {}
                            document.cookie = "inward_form_hidden=0; path=/; max-age=31536000; SameSite=Lax";
                        }
                    } else if (urlParams.has('hide_form')) {
                        initialHidden = urlParams.get('hide_form') === '1';
                        if (initialHidden) {
                            try { localStorage.setItem('inward_form_hidden', '1'); } catch (e) {}
                            document.cookie = "inward_form_hidden=1; path=/; max-age=31536000; SameSite=Lax";
                        }
                    } else {
                        const stored = localStorage.getItem('inward_form_hidden');
                        if (stored !== null) {
                            initialHidden = stored === '1';
                        }
                    }
                } catch (e) {}
            @endif

            return {
                showRegistrationForm: !initialHidden,
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

                init() {
                    window.inwardFormState = this;
                    syncFilterLinks(!initialHidden);
                },

                setFormVisibility(visible) {
                    this.showRegistrationForm = visible;
                    const hiddenVal = visible ? '0' : '1';
                    try {
                        localStorage.setItem('inward_form_hidden', hiddenVal);
                    } catch (e) {}
                    document.cookie = "inward_form_hidden=" + hiddenVal + "; path=/; max-age=31536000; SameSite=Lax";

                    const hideInput = document.getElementById('hide-form-input');
                    if (hideInput) {
                        hideInput.value = hiddenVal;
                    }

                    try {
                        const currentUrl = new URL(window.location.href);
                        if (visible) {
                            currentUrl.searchParams.set('show_form', '1');
                            currentUrl.searchParams.delete('hide_form');
                        } else {
                            currentUrl.searchParams.set('hide_form', '1');
                            currentUrl.searchParams.delete('show_form');
                        }
                        window.history.replaceState({}, '', currentUrl.toString());
                    } catch (e) {}

                    syncFilterLinks(visible);

                    this.$nextTick(() => {
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    });
                },

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
                    this.formatReceiptNo();
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

                formatReceiptNo() {
                    if (!this.receiptNo || typeof this.receiptNo !== 'string') return;
                    let val = this.receiptNo.trim();
                    if (!val) return;

                    const currentYear = new Date().getFullYear();

                    // If no slash is present, automatically append the current year
                    if (!val.includes('/')) {
                        this.receiptNo = `${val}/${currentYear}`;
                    } else if (val.endsWith('/')) {
                        this.receiptNo = `${val}${currentYear}`;
                    }
                },

                handleReceiptBlur() {
                    this.formatReceiptNo();
                    this.checkReceiptUniqueness();
                },

                focusNextField(event) {
                    if (event.target && event.target.id === 'receipt_no') {
                        this.formatReceiptNo();
                        this.checkReceiptUniqueness();
                    }
                    const formElements = Array.from(document.querySelectorAll('#inwardPetitionForm input, #inwardPetitionForm select, #inwardPetitionForm button'));
                    const currentIndex = formElements.indexOf(event.target);
                    if (currentIndex >= 0 && currentIndex < formElements.length - 1) {
                        formElements[currentIndex + 1].focus();
                    }
                },

                checkReceiptUniqueness() {
                    this.formatReceiptNo();

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
        function syncFilterLinks(isFormVisible) {
            const pills = document.querySelectorAll('#inward-filter-nav [data-filter-key]');
            pills.forEach(pill => {
                try {
                    const url = new URL(pill.href, window.location.origin);
                    if (isFormVisible) {
                        url.searchParams.set('show_form', '1');
                        url.searchParams.delete('hide_form');
                    } else {
                        url.searchParams.set('hide_form', '1');
                        url.searchParams.delete('show_form');
                    }
                    pill.href = url.toString();
                } catch (e) {}
            });

            const resetLink = document.querySelector('a[title="Clear all filters"]');
            if (resetLink) {
                try {
                    const url = new URL(resetLink.href, window.location.origin);
                    if (isFormVisible) {
                        url.searchParams.set('show_form', '1');
                        url.searchParams.delete('hide_form');
                    } else {
                        url.searchParams.set('hide_form', '1');
                        url.searchParams.delete('show_form');
                    }
                    resetLink.href = url.toString();
                } catch (e) {}
            }
        }

        function switchFilterTab(filterKey) {
            const filterInput = document.getElementById('filter-input');
            if (filterInput) {
                filterInput.value = filterKey;
            }

            updateFilterPillsUI(filterKey);

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('filter', filterKey);
                url.searchParams.delete('page');

                const isFormHidden = (window.inwardFormState && !window.inwardFormState.showRegistrationForm) ||
                    localStorage.getItem('inward_form_hidden') === '1' ||
                    url.searchParams.get('hide_form') === '1';

                if (isFormHidden) {
                    url.searchParams.set('hide_form', '1');
                    url.searchParams.delete('show_form');
                } else {
                    url.searchParams.set('show_form', '1');
                    url.searchParams.delete('hide_form');
                }
                url.hash = 'view-inward-petitions';
                window.history.pushState({ filter: filterKey }, '', url.toString());
            } catch (e) {}

            performLiveSearch();
        }

        function updateFilterPillsUI(activeKey) {
            const pills = document.querySelectorAll('#inward-filter-nav [data-filter-key]');
            pills.forEach(pill => {
                const key = pill.getAttribute('data-filter-key');
                const badge = pill.querySelector('#badge-' + key);
                if (key === activeKey) {
                    pill.className = 'filter-pill px-4 py-1.5 inline-flex items-center gap-2 text-xs font-bold rounded-full border transition-all shadow-sm cursor-pointer bg-teal-600 text-white border-teal-600 shadow-sm';
                    if (badge) {
                        badge.className = 'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors bg-white text-teal-700';
                    }
                } else {
                    pill.className = 'filter-pill px-4 py-1.5 inline-flex items-center gap-2 text-xs font-bold rounded-full border transition-all shadow-sm cursor-pointer bg-indigo-50/50 text-indigo-700 border-indigo-200 hover:bg-indigo-100';
                    if (badge) {
                        const count = parseInt(badge.innerText.trim()) || 0;
                        if (key === 'returned' && count > 0) {
                            badge.className = 'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors bg-rose-600 text-white animate-pulse shadow-[0_0_8px_rgba(225,29,72,0.5)]';
                        } else {
                            badge.className = 'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors bg-indigo-600 text-white';
                        }
                    }
                }
            });
        }

        function performLiveSearch(customUrl) {
            const form = document.getElementById('search-form');
            if (!form) return;
            const fromEl = form.querySelector('[name="date_from"]');
            const toEl = form.querySelector('[name="date_to"]');
            if (fromEl && toEl && fromEl.value && toEl.value && fromEl.value > toEl.value) {
                return;
            }
            let url;
            if (customUrl) {
                url = new URL(customUrl, window.location.origin);
            } else {
                url = new URL(form.action);
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                url.search = params.toString();

                try {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.search = params.toString();
                    currentUrl.hash = 'view-inward-petitions';
                    window.history.replaceState({}, '', currentUrl.toString());
                } catch (e) {}
            }

            const tableContainer = document.getElementById('inward-table-container');
            if (tableContainer) {
                tableContainer.style.opacity = '0.6';
            }

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    if (tableContainer) {
                        tableContainer.innerHTML = html;
                        tableContainer.style.opacity = '1';
                    }
                    if (window.lucide) {
                        lucide.createIcons();
                    }

                    const countsData = document.getElementById('counts-data');
                    if (countsData) {
                        const counts = {
                            all: parseInt(countsData.getAttribute('data-all')) || 0,
                            transferred: parseInt(countsData.getAttribute('data-transferred')) || 0,
                            returned: parseInt(countsData.getAttribute('data-returned')) || 0
                        };

                        const currentFilter = document.getElementById('filter-input')?.value || 'all';

                        ['all', 'transferred', 'returned'].forEach(key => {
                            const badge = document.getElementById('badge-' + key);
                            if (badge) {
                                badge.innerText = counts[key];
                                if (key !== currentFilter) {
                                    if (key === 'returned' && counts[key] > 0) {
                                        badge.className = 'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors bg-rose-600 text-white animate-pulse shadow-[0_0_8px_rgba(225,29,72,0.5)]';
                                    } else {
                                        badge.className = 'inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-black px-1.5 transition-colors bg-indigo-600 text-white';
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(() => {
                    if (tableContainer) tableContainer.style.opacity = '1';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tableContainer = document.getElementById('inward-table-container');
            if (tableContainer) {
                tableContainer.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && link.closest('.pagination, nav[role="navigation"]')) {
                        e.preventDefault();
                        try {
                            const pageUrl = new URL(link.href, window.location.origin);
                            const newUrl = new URL(window.location.href);
                            if (pageUrl.searchParams.has('page')) {
                                newUrl.searchParams.set('page', pageUrl.searchParams.get('page'));
                            }
                            window.history.pushState({}, '', newUrl.toString());
                        } catch (e) {}
                        performLiveSearch(link.href);
                    }
                });
            }

            window.addEventListener('popstate', function() {
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    const filter = urlParams.get('filter') || 'all';
                    const filterInput = document.getElementById('filter-input');
                    if (filterInput) filterInput.value = filter;
                    updateFilterPillsUI(filter);
                    performLiveSearch();
                } catch (e) {}
            });
        });
    </script>

    <!-- Reassign to CPSP Modal -->
    <div x-data="{ open: false, petitionId: null, receiptNo: '', returnReason: '', returnedBy: '', returnedAt: '', returnedSeat: '' }"
         @open-reassign-modal.window="open = true; petitionId = $event.detail.id; receiptNo = $event.detail.receipt; returnReason = $event.detail.reason || ''; returnedBy = $event.detail.by || ''; returnedAt = $event.detail.at || ''; returnedSeat = $event.detail.seat || ''; $nextTick(() => { if (window.lucide) { lucide.createIcons(); } });"
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
                            <!-- Reason for Return Banner -->
                            <div x-show="returnReason" class="rounded-2xl bg-rose-50 border border-rose-200/80 p-4 space-y-2.5">
                                <div class="flex items-center gap-2 text-rose-800 text-xs font-bold uppercase tracking-wider">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                                    <span>Reason for Return</span>
                                </div>
                                <div class="bg-white/90 rounded-xl p-3 border border-rose-100 shadow-sm">
                                    <p class="text-sm text-rose-950 font-semibold leading-relaxed break-words" x-text="returnReason"></p>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-rose-700/80 pt-0.5">
                                    <span x-show="returnedBy" class="font-medium text-slate-700 flex items-center gap-1">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-rose-400"></i>
                                        <span>Returned by: <strong x-text="returnedBy"></strong></span>
                                        <span x-show="returnedSeat" class="text-slate-500 font-normal">(<span x-text="returnedSeat"></span>)</span>
                                    </span>
                                    <span x-show="returnedAt" class="text-slate-400 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        <span x-text="returnedAt"></span>
                                    </span>
                                </div>
                            </div>

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

    <!-- Delete Returned Petition Confirmation Modal -->
    <div x-data="{ open: false, petitionId: null, receiptNo: '', returnReason: '' }"
         @open-delete-modal.window="open = true; petitionId = $event.detail.id; receiptNo = $event.detail.receipt; returnReason = $event.detail.reason || ''; $nextTick(() => { if (window.lucide) { lucide.createIcons(); } });"
         class="relative z-[100]" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true" x-cloak x-show="open">
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
                    class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-[0_20px_60px_-15px_rgba(0,0,0,0.2)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                    
                    <form :action="'{{ url('inward/petitions') }}/' + petitionId" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between rounded-t-2xl relative overflow-hidden bg-white">
                            <div class="flex items-center gap-4 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 shadow-sm border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="delete-modal-title">
                                        Delete Petition
                                    </h3>
                                    <p class="text-[13px] text-slate-500 font-medium mt-0.5">
                                        Receipt No: <strong x-text="receiptNo" class="text-rose-600 font-bold ml-0.5 font-mono"></strong>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="open = false"
                                class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-50 transition-all relative z-10 focus:outline-none">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-4 bg-white">
                            <!-- Reason for Return Banner -->
                            <div x-show="returnReason" class="rounded-2xl bg-rose-50 border border-rose-200/80 p-4 space-y-2">
                                <div class="flex items-center gap-2 text-rose-800 text-xs font-bold uppercase tracking-wider">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                                    <span>Desk Return Reason</span>
                                </div>
                                <div class="bg-white/90 rounded-xl p-3 border border-rose-100 shadow-sm">
                                    <p class="text-sm text-rose-950 font-semibold leading-relaxed break-words" x-text="returnReason"></p>
                                </div>
                            </div>

                            <div class="rounded-xl bg-amber-50/80 border border-amber-200 p-3.5 flex items-start gap-3">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                                <p class="text-xs text-amber-900 leading-relaxed font-medium">
                                    This petition does not belong to the integrated sections. Deleting it will remove it from active Inward lists and move it to the <strong>Recycle Bin</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="px-6 py-5 bg-slate-50/80 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="open = false" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-700 transition-all focus:ring-4 focus:ring-slate-100 outline-none">
                                Cancel
                            </button>
                            <button type="submit" class="w-full sm:w-auto px-7 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-lg shadow-rose-600/20 transition-all flex items-center justify-center gap-2 focus:ring-4 focus:ring-rose-500/20 outline-none hover:-translate-y-0.5 active:translate-y-0">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span>Confirm Delete</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection