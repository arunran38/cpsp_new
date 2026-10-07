@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-6" x-data="{ showLinkModal: false, showReturnModal: false, returnPetitionId: {{ $petition->petition_id }}, returnReceiptNo: '{{ $petition->receipt_no }}' }">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <i data-lucide="file-search" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Petition Details:
                        {{ $petition->receipt_no }}
                    </h1>
                    <p class="text-sm font-medium text-gray-600 mt-0.5 flex flex-wrap items-center gap-2">
                        <span>Submitted on {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                        @if($petition->user)
                            <span class="inline-block w-1 h-1 rounded-full bg-slate-300 hidden sm:inline-block"></span>
                            <span>Created By: <span class="font-bold text-slate-800">{{ $petition->user->name }}</span></span>
                        @endif
                    </p>
                    @if($petition->file_no)
                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 rounded-md">
                        <i data-lucide="folder-open" class="w-4 h-4 text-amber-600"></i>
                        <span class="text-sm font-bold text-amber-800">File No: {{ $petition->file_no }}</span>
                        @if($petition->file_created_date)
                            <span class="text-xs text-amber-600 font-medium ml-2 border-l border-amber-200 pl-2">Created on {{ \Carbon\Carbon::parse($petition->file_created_date)->format('d M, Y') }}</span>
                        @endif
                    </div>
                    @endif

                    @if($petition->isFromInward())
                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold {{ $petition->cpsp_opened_at ? 'bg-indigo-50 border-indigo-200 text-indigo-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                            <i data-lucide="{{ $petition->cpsp_opened_at ? 'eye' : 'clock' }}" class="w-4 h-4 {{ $petition->cpsp_opened_at ? 'text-indigo-600' : 'text-amber-600' }}"></i>
                            <span>
                                @if($petition->cpsp_opened_at)
                                    First Opened by CPSP: <strong class="font-bold">{{ $petition->cpspOpenedBySeat->seat_name ?? 'CPSP Seat' }}</strong> ({{ $petition->cpspOpenedByUser->name ?? 'User' }}) on {{ $petition->cpsp_opened_at->format('d-m-Y h:i A') }}
                                @else
                                    Status: Not Opened by CPSP Seat Yet
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(Auth::user()->canAccess('update petitions') && (Auth::user()->canAccess('access admin dashboard') || !in_array($petition->status, ['Closed', 'Sent_to_Govt'])))
                    <a href="{{ route('petitions.edit', $petition->petition_id) }}"
                        class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                        <i data-lucide="edit" class="w-4 h-4"></i> Edit Details
                    </a>
                    @if(!$petition->linked_petition_id && !in_array($petition->status, ['Closed', 'Sent_to_Govt', 'Duplicate']))
                        <button type="button" @click="showLinkModal = true"
                            class="px-4 py-2.5 text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-all shadow-sm flex items-center gap-2">
                            <i data-lucide="link" class="w-4 h-4"></i> Link Petition
                        </button>
                    @endif
                    @if(!$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $petition->isFromInward())
                        <button type="button" @click="showReturnModal = true"
                            class="px-4 py-2.5 text-sm font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-all shadow-sm flex items-center gap-2">
                            <i data-lucide="corner-up-left" class="w-4 h-4 text-rose-600"></i> Return to Inward
                        </button>
                    @endif
                    @if($petition->linked_petition_id)
                        <form action="{{ route('petitions.unlinkDuplicate', $petition->petition_id) }}" method="POST" class="inline unlink-form" data-message="Are you sure you want to unlink this petition?">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2.5 text-sm font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-all shadow-sm flex items-center gap-2">
                                <i data-lucide="unlink" class="w-4 h-4"></i> Unlink Petition
                            </button>
                        </form>
                    @endif
                @endif
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
                </a>
            </div>
        </div>

        @if($petition->is_returned_to_inward)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <div>
                    <span class="font-bold text-rose-900 text-sm">Returned to Inward</span>
                    <p class="text-rose-700 mt-0.5">
                        Returned on {{ $petition->returned_at?->format('d-m-Y h:i A') }} by {{ $petition->returnedByUser?->name ?? 'CPSP User' }}. 
                        Reason: <strong class="font-semibold">{{ $petition->return_reason }}</strong>
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 uppercase tracking-wider shrink-0">Returned to Inward</span>
        </div>
        @endif


        <!-- Data Layout -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-sm">
            <div class="p-8 space-y-10">
                <!-- Section 1: Petition Variables -->
                <div>
                    <h3
                        class="text-base font-bold text-gray-900 border-b-2 border-slate-200 pb-2 mb-5 flex items-center gap-2 w-max">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-600"></i> General Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <span
                                class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-1">Status</span>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-widest {{ ($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                @if($petition->decision)
                                    Final : {{ \App\Models\Decision::getDecisionLabel($petition->decision->final_decision) }}
                                @else
                                    {{ $petition->status }}
                                @endif
                            </span>
                        </div>
                        <div><span class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-1.5">Nature
                                of Petition</span> <span
                                class="font-semibold text-gray-900">{{ $petition->nature_of_petition }}</span></div>
                        <div>
                            <span class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-1.5">Mode
                                Received</span>
                            <span class="font-semibold text-gray-900">
                                {{ $petition->mode_of_petition_received === 'others'
        ? 'Others (' . $petition->mode_of_petition_received_others . ')'
        : $petition->mode_of_petition_received }}
                            </span>
                        </div>
                        <div class="lg:col-span-4 bg-slate-50/80 p-5 rounded-xl border border-slate-200 mt-2">
                            <span class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Detailed
                                Description</span>
                            <p class="text-base sm:text-[16.5px] font-normal text-slate-800 leading-relaxed sm:leading-loose whitespace-pre-line tracking-normal" style="text-align: justify;">
                                @php
                                    $showDesc = trim(str_ireplace(['Inward petition entry', 'Inward petition'], '', $petition->description ?? ''));
                                @endphp
                                {{ $showDesc ?: 'No description provided.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Associated Parties -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Complainants -->
                    <div>
                        <h3
                            class="text-base font-bold text-gray-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                            <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Complainants
                        </h3>
                        <div class="space-y-4">
                            @php
                                $targetPetition = $petition->originalPetition ?? $petition;
                                $complainants = $targetPetition->addresses->where('person_type', 'Complainant')->groupBy('person_name');
                            @endphp

                            @forelse($complainants as $name => $addresses)
                                @php $first = $addresses->first(); @endphp
                                <div class="bg-white border border-slate-200 rounded-lg p-5">
                                    <h4 class="font-bold text-gray-900 mb-4">{{ $name }}</h4>
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        <div class="text-xs"><span class="text-gray-600 block mb-0.5">Phone:</span> <span
                                                class="font-semibold text-gray-900">{{ $first->phone ?? 'N/A' }}</span></div>
                                        <div class="text-xs"><span class="text-gray-600 block mb-0.5">Email:</span> <span
                                                class="font-semibold text-gray-900">{{ $first->email ?? 'N/A' }}</span></div>
                                    </div>

                                    <div class="space-y-3 pt-3 border-t border-slate-100">
                                        <h5 class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Addresses</h5>
                                        @foreach($addresses as $addr)
                                            <div
                                                class="flex items-start gap-3 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                                <span
                                                    class="bg-blue-100 text-blue-800 text-[9px] font-bold uppercase px-2 py-0.5 rounded tracking-widest w-20 text-center shrink-0">{{ $addr->address_type }}</span>
                                                <span class="text-xs font-medium text-gray-800 leading-snug">
                                                    {{ $addr->full_address }}
                                                    @if(!empty($addr->district->district_name))
                                                        , {{ $addr->district->district_name }}
                                                    @endif
                                                    @if(!empty($addr->pincode))
                                                        - {{ $addr->pincode }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-600 italic text-sm p-4 bg-slate-50 rounded-lg">No Complainants recorded.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Accused -->
                    <div>
                        <h3
                            class="text-base font-bold text-gray-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                            <i data-lucide="user-x" class="w-5 h-5 text-rose-600"></i> Suspects (Accused)
                        </h3>
                        <div class="space-y-4">
                            @php
                                $accused = $targetPetition->addresses->where('person_type', 'Accused')->groupBy('person_name');
                            @endphp

                            @forelse($accused as $name => $addresses)
                                @php $first = $addresses->first(); @endphp
                                <div class="bg-white border border-slate-200 rounded-lg p-5">
                                    @if(isset($first->entity_type) && $first->entity_type === 'Firm')
                                        <div class="mb-4">
                                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold tracking-widest uppercase rounded mb-1">Firm / Project</span>
                                            <h4 class="font-bold text-gray-900">{{ $name }}</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
                                            @if($first->designation)
                                                <div class="text-xs"><span class="text-gray-600 block mb-0.5">Designation:</span> <span
                                                        class="font-semibold text-gray-900">{{ $first->designation->designation_name }}</span></div>
                                            @endif
                                            @if($first->department)
                                                <div class="text-xs"><span class="text-gray-600 block mb-0.5">Department:</span> <span
                                                        class="font-semibold text-gray-900">{{ $first->department->department_name }}</span></div>
                                            @endif
                                            <div class="text-xs"><span class="text-gray-600 block mb-0.5">Phone:</span> <span
                                                    class="font-semibold text-gray-900">{{ $first->phone ?? 'N/A' }}</span></div>
                                        </div>
                                    @else
                                        <div class="mb-4">
                                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold tracking-widest uppercase rounded mb-1">Person</span>
                                            <h4 class="font-bold text-gray-900">{{ $name }}</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
                                            @if($first->designation)
                                                <div class="text-xs"><span class="text-gray-600 block mb-0.5">Designation:</span> <span
                                                        class="font-semibold text-gray-900">{{ $first->designation->designation_name }}</span></div>
                                            @endif
                                            @if($first->department)
                                                <div class="text-xs"><span class="text-gray-600 block mb-0.5">Department:</span> <span
                                                        class="font-semibold text-gray-900">{{ $first->department->department_name }}</span></div>
                                            @endif
                                            <div class="text-xs"><span class="text-gray-600 block mb-0.5">Phone:</span> <span
                                                    class="font-semibold text-gray-900">{{ $first->phone ?? 'N/A' }}</span></div>
                                            @if(!empty($first->pen_number))
                                                <div class="text-xs"><span class="text-gray-600 block mb-0.5">PEN Number:</span> <span
                                                        class="font-semibold text-gray-900">{{ $first->pen_number }}</span></div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="space-y-3 pt-3 border-t border-slate-100">
                                        <h5 class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Addresses</h5>
                                        @foreach($addresses as $addr)
                                            <div
                                                class="flex items-start gap-3 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                                <span
                                                    class="bg-rose-100 text-rose-800 text-[9px] font-bold uppercase px-2 py-0.5 rounded tracking-widest w-20 text-center shrink-0">{{ $addr->address_type }}</span>
                                                <span class="text-xs font-medium text-gray-800 leading-snug">
                                                    {{ $addr->full_address }}
                                                    @if(!empty($addr->district->district_name))
                                                        , {{ $addr->district->district_name }}
                                                    @endif
                                                    @if(!empty($addr->pincode))
                                                        - {{ $addr->pincode }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-600 italic text-sm p-4 bg-slate-50 rounded-lg">No Suspects recorded.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Section 3: Proposed Action & Documents -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h3
                            class="text-base font-bold text-gray-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                            <i data-lucide="clipboard-check" class="w-5 h-5 text-emerald-600"></i> Proposed Action
                        </h3>
                        <div class="bg-emerald-50/50 p-5 rounded-xl border border-emerald-100 shadow-sm">
                            @if($petition->proposed_action)
                                <p class="font-medium text-gray-800 leading-relaxed">{{ $petition->proposed_action }}</p>
                            @else
                                <p class="italic text-gray-500">No proposed action recorded.</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3
                            class="text-base font-bold text-gray-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                            <i data-lucide="paperclip" class="w-5 h-5 text-indigo-600"></i> Attached Evidence
                        </h3>
                        <div class="space-y-3">
                            @forelse($petition->uploads as $upload)
                                <a href="{{ route('petitions.download', $upload->upload_id) }}" target="_blank"
                                    class="flex items-center gap-3 p-3 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors rounded-xl shadow-sm text-sm group">
                                    <div
                                        class="bg-slate-100 text-gray-600 p-2 rounded-lg group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shrink-0">
                                        <i data-lucide="file" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ $upload->original_filename }}</p>
                                        <p class="text-[10px] uppercase font-bold text-gray-500 mt-0.5 tracking-wider">
                                            {{ $upload->category }}
                                        </p>
                                    </div>
                                    <i data-lucide="download"
                                        class="w-4 h-4 text-gray-500 group-hover:text-indigo-500 mx-2"></i>
                                </a>
                            @empty
                                <div class="p-6 bg-slate-50 rounded-xl text-center border border-slate-200 border-dashed">
                                    <i data-lucide="file-x-2" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                    <span class="text-gray-600 text-sm font-medium">No files uploaded.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Section 4: Petition Workflow Actions -->
                <div class="mt-8 border-t border-slate-200 pt-8">
                    <h3 class="text-base font-bold text-gray-900 pb-2 mb-4 flex items-center gap-2 w-max">
                        <i data-lucide="git-merge" class="w-5 h-5 text-indigo-600"></i> Petition Workflow Actions
                    </h3>

                    @if($petition->status === 'Received' && Auth::user()->canAccess('update petitions'))
                            <!-- Director Forwarding Form -->
                            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-indigo-900 mb-4">Initial Decision / Forwarding</h4>
                                <form action="{{ route('forwardings.store') }}" method="POST" enctype="multipart/form-data"
                                    class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="petition_id" value="{{ $petition->petition_id }}">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">File Number <span class="text-red-500">*</span></label>
                                            <input type="text" name="file_no" required
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-gray-900"
                                                placeholder="Enter File Number">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">File Date</label>
                                            <input type="date" name="file_created_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                                style="color: #1e40af !important;">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                                        <div class="relative flex items-center">
                                            <select name="action"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none pr-10 py-2 text-sm text-gray-900"
                                                style="background-image: none !important;" required
                                                onchange="document.getElementById('unit-select-div').style.display = this.value === 'Forward_To_Unit' ? 'block' : 'none'; document.getElementById('final-doc-div-fwd').style.display = (this.value === 'Close' || this.value === 'Sent_to_Govt') ? 'block' : 'none'; document.getElementById('fwd_order_number').required = (this.value === 'Close' || this.value === 'Sent_to_Govt');">
                                                <option value="">Select Action...</option>
                                                <option value="Forward_To_Unit">Forward to Unit</option>
                                                <option value="Sent_to_Govt">Send to Govt</option>
                                                <option value="Close">Close Petition</option>
                                            </select>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Action Date</label>
                                        <input type="date" name="forwarded_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                            style="color: #1e40af !important;" required>
                                    </div>

                                    <div id="unit-select-div" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Unit</label>
                                        <div class="relative flex items-center">
                                            <select name="to_unit_id"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none pr-10 py-2 text-sm text-gray-900"
                                                style="background-image: none !important;">
                                                <option value="">Select Unit...</option>
                                                @foreach(\App\Models\Unit::all() as $unit)
                                                    <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                                                @endforeach
                                            </select>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div id="final-doc-div-fwd" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Directorate Order Number</label>
                                        <input type="text" name="directorate_order_number" id="fwd_order_number"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 mb-3"
                                            placeholder="Enter Order Number">
                                            
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Final Order Document</label>
                                        <input type="file" name="final_order_file" accept=".pdf,.jpg,.jpeg,.png"
                                            class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                        <textarea name="director_remarks" rows="3"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-gray-900"
                                            required></textarea>
                                    </div>
                                    <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-sm">Submit
                                        Decision</button>
                                </form>
                            </div>
                        @endif

                        <!-- Show Previous Workflow Details in a Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8 items-start">
                            <!-- Show Previous Forwarding Details if exists -->
                            @if($petition->latestForwarding)
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-6">
                                    <h4 class="font-bold text-gray-800 mb-4 border-b border-slate-200 pb-2">Forwarding History</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600 font-semibold">Forwarded To:</span>
                                            <span
                                                class="text-gray-900 block mt-1">{{ $petition->latestForwarding->toUnit->unit_name ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-semibold">Date:</span>
                                            <span
                                                class="text-gray-900 block mt-1">{{ \Carbon\Carbon::parse($petition->latestForwarding->forwarded_date)->format('d M, Y') }}</span>
                                        </div>
                                        @if($petition->latestForwarding->processedBy)
                                            <div>
                                                <span class="text-gray-600 font-semibold">Forwarded By:</span>
                                                <span
                                                    class="text-indigo-700 font-bold uppercase text-[11px] block mt-1">{{ $petition->latestForwarding->processedBy->name }}</span>
                                            </div>
                                        @endif
                                        <div class="md:col-span-2">
                                            <span class="text-gray-600 font-semibold">Remarks:</span>
                                            <span
                                                class="text-gray-800 block mt-1">{{ $petition->latestForwarding->director_remarks }}</span>
                                        </div>
                                        @if(!$petition->latestForwarding->vr_ref_no && (auth()->user()->canAccess('access admin dashboard') || !$petition->decision))
                                            <div class="md:col-span-2 pt-2">
                                                <form action="{{ route('forwardings.pullback', $petition->latestForwarding->petition_forwarding_id) }}" method="POST" class="inline pullback-form" data-message="Are you sure you want to pull back this forwarding?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 rounded-lg hover:bg-rose-100 transition-all flex items-center gap-1.5 shadow-sm">
                                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Pull Back Forwarding
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Verification Report Details (Added) -->
                            @if($petition->latestForwarding && $petition->latestForwarding->vr_ref_no)
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                                    <h4
                                        class="font-bold text-amber-900 mb-4 border-b border-amber-200 pb-2 flex items-center gap-2">
                                        <i data-lucide="file-check" class="w-5 h-5 text-amber-600"></i> Verification Report Details
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600 font-semibold">VR Reference No:</span>
                                            <span
                                                class="text-gray-900 block mt-1">{{ $petition->latestForwarding->vr_ref_no }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-semibold">VR Date:</span>
                                            <span
                                                class="text-gray-900 block mt-1">{{ \Carbon\Carbon::parse($petition->latestForwarding->vr_date)->format('d M, Y') }}</span>
                                        </div>
                                        @if($petition->latestForwarding->vr_received_at_cpsp_date)
                                            <div>
                                                <span class="text-gray-600 font-semibold">Received at CPSP:</span>
                                                <span
                                                    class="font-bold text-blue-700 block mt-1">{{ \Carbon\Carbon::parse($petition->latestForwarding->vr_received_at_cpsp_date)->format('d M, Y') }}</span>
                                            </div>
                                        @endif
                                        @if($petition->latestForwarding->processedBy)
                                            <div>
                                                <span class="text-gray-600 font-semibold">VR Updated By:</span>
                                                <span
                                                    class="text-amber-700 font-bold uppercase text-[11px] block mt-1">{{ $petition->latestForwarding->processedBy->name }}</span>
                                            </div>
                                        @endif
                                        <div class="md:col-span-2">
                                            <span class="text-gray-600 font-semibold">VR Recommendation:</span>
                                            <span
                                                class="text-gray-800 block mt-1">{{ \App\Models\Decision::getDecisionLabel($petition->latestForwarding->vr_remarks) ?? $petition->latestForwarding->vr_remarks }}</span>
                                        </div>

                                        @php
                                            $vrFile = $petition->uploads->where('category', 'Verification Report')->last();
                                        @endphp
                                        @if($vrFile)
                                            <div class="md:col-span-2 pt-2">
                                                <span class="text-gray-600 font-semibold block mb-2">Attached VR Document:</span>
                                                <a href="{{ route('petitions.download', $vrFile->upload_id) }}" target="_blank"
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-amber-200 rounded-lg text-amber-800 hover:bg-amber-100 transition-colors shadow-sm">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                    <span class="font-medium italic text-xs">{{ $vrFile->original_filename }}</span>
                                                </a>
                                            </div>
                                        @endif

                                        @if(!$petition->decision || auth()->user()->canAccess('access admin dashboard'))
                                            <div class="md:col-span-2 pt-4 border-t border-amber-200/50">
                                                <form action="{{ route('forwardings.pullbackVr', $petition->latestForwarding->petition_forwarding_id) }}" method="POST" class="inline pullback-form" data-message="Are you sure you want to pull back this VR Report?">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-white border border-rose-100 rounded-lg hover:bg-rose-50 transition-all flex items-center gap-1.5 shadow-sm">
                                                        <i data-lucide="undo-2" class="w-3.5 h-3.5"></i> Pull Back VR Report
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Unit VR Submission (Only if Forwarded) -->
                            @if($petition->status === 'Forwarded' && Auth::user()->canAccess('update petitions'))
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-6 mb-6">
                                    <h4 class="font-bold text-amber-900 mb-4">Submit Verification Report (Unit Action)</h4>
                                    <form
                                        action="{{ route('forwardings.updateVr', $petition->latestForwarding->petition_forwarding_id) }}"
                                        method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">VR Reference No</label>
                                                <input type="text" name="vr_ref_no"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-gray-900"
                                                    required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">VR Date</label>
                                                <input type="date" placeholder="DD-MM-YYYY" name="vr_date"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                                    style="color: #1e40af !important;"
                                                    required
                                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">VR Recommendation</label>
                                            <div class="relative flex items-center">
                                                <select name="vr_remarks"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none pr-10 py-2 text-sm text-gray-900"
                                                    style="background-image: none !important;" required>
                                                    <option value="">Select Recommendation...</option>
                                                    <option value="VC">Vigilance Case (VC)</option>
                                                    <option value="VE">Vigilance Enquiry (VE)</option>
                                                    <option value="PE">Preliminary Enquiry (PE)</option>
                                                    <option value="SC">Surprise Check (SC)</option>
                                                    <option value="CV">Confidential Verification (CV)</option>
                                                    <option value="IV">Internal Vigilance (IV)</option>
                                                    <option value="ICell">Intelligence Cell (I Cell)</option>
                                                    <option value="Closed">Closed</option>
                                                    <option value="Sent to Govt">Sent to Govt</option>
                                                </select>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none">
                                                    <path d="m6 9 6 6 6-6" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="space-y-5">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload verification
                                                    report</label>
                                                <input type="file" name="vr_file" accept=".pdf,.jpg,.jpeg,.png"
                                                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition-all">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1 mt-2 text-blue-700 font-bold tracking-wider text-[11px]">VR
                                                    received in CPSP</label>
                                                <input type="date" placeholder="DD-MM-YYYY" name="vr_received_at_cpsp_date"
                                                    class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-800 font-semibold bg-blue-50/20"
                                                    style="color: #1e40af !important;"
                                                    max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <button type="submit"
                                            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold shadow-sm">Submit
                                            VR</button>
                                    </form>
                                </div>
                            @endif

                            <!-- CPSP Receive VR -->
                            @if($petition->status === 'VR_Received' && $petition->latestForwarding && !$petition->latestForwarding->vr_received_at_cpsp_date && Auth::user()->canAccess('update petitions'))
                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 mb-6">
                                    <h4 class="font-bold text-blue-900 mb-4">Acknowledge VR Receipt (CPSP Action)</h4>
                                    <div class="mb-4 text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-gray-600 font-semibold">VR Ref:</span>
                                            <span
                                                class="text-gray-900 block mt-1">{{ $petition->latestForwarding->vr_ref_no }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-semibold">VR Date:</span>
                                            <span class="text-gray-900 block mt-1">{{ $petition->latestForwarding->vr_date }}</span>
                                        </div>
                                    </div>
                                    <form
                                        action="{{ $petition->latestForwarding ? route('forwardings.receiveVr', $petition->latestForwarding->petition_forwarding_id) : '#' }}"
                                        method="POST" class="space-y-4">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Verification report received
                                                in
                                                CPSP date</label>
                                            <input type="date" placeholder="DD-MM-YYYY" name="vr_received_at_cpsp_date"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-800 font-semibold"
                                                style="color: #1e40af !important;"
                                                value="{{ date('Y-m-d') }}" required
                                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                                        </div>
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow-sm w-full sm:w-auto">Acknowledge
                                            & Mark as Received</button>
                                    </form>
                                </div>
                            @endif

                            <!-- Final Decision -->
                            @if($petition->status === 'VR_Received' && $petition->latestForwarding && $petition->latestForwarding->vr_received_at_cpsp_date && Auth::user()->canAccess('update petitions'))
                                <div class="bg-rose-50 border border-rose-100 rounded-xl p-6 mb-6">
                                    <h4 class="font-bold text-rose-900 mb-4">Final Decision</h4>
                                    <form action="{{ route('decisions.store') }}" method="POST" enctype="multipart/form-data"
                                        class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="petition_id" value="{{ $petition->petition_id }}">

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Final Decision</label>
                                            <div class="relative flex items-center">
                                                <select name="final_decision"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 appearance-none pr-10 py-2 text-sm text-gray-900"
                                                    style="background-image: none !important;" required>
                                                    <option value="">Select...</option>
                                                    <option value="VC">Vigilance Case (VC)</option>
                                                    <option value="VE">Vigilance Enquiry (VE)</option>
                                                    <option value="PE">Preliminary Enquiry (PE)</option>
                                                    <option value="SC">Surprise Check (SC)</option>
                                                    <option value="CV">Confidential Verification (CV)</option>
                                                    <option value="IV">Internal Vigilance (IV)</option>
                                                    <option value="ICell">Intelligence Cell (I Cell)</option>
                                                    <option value="Closed">Closed</option>
                                                    <option value="Sent to Govt">Sent to Govt</option>
                                                </select>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none">
                                                    <path d="m6 9 6 6 6-6" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Decision Date</label>
                                            <input type="date" name="decision_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm py-[9px] text-slate-800 font-semibold"
                                                style="color: #1e40af !important;" required>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Directorate Order Number</label>
                                            <input type="text" name="directorate_order_number" placeholder="Enter Order Number"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm py-[9px] text-slate-800"
                                                required>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Final Remarks</label>
                                            <textarea name="final_remarks" rows="3"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-gray-900"></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Final Order
                                                Document</label>
                                            <input type="file" name="final_order_file" accept=".pdf,.jpg,.jpeg,.png"
                                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                                        </div>

                                        <button type="submit"
                                            class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 font-semibold shadow-sm">Submit
                                            Final Decision</button>
                                    </form>
                                </div>
                            @endif

                            @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                                @if($petition->decision)
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 lg:col-span-2">
                                        <h4 class="font-bold text-gray-800 mb-4 border-b border-slate-200 pb-2">Decision Details</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600 font-semibold">Final Decision:</span>
                                                <span
                                                    class="text-gray-900 block mt-1">{{ \App\Models\Decision::getDecisionLabel($petition->decision->final_decision) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 font-semibold">Decided On:</span>
                                                <span
                                                    class="text-gray-900 block mt-1">{{ \Carbon\Carbon::parse($petition->decision->decision_date)->format('d M, Y') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 font-semibold">Directorate Order Number:</span>
                                                <span
                                                    class="text-gray-900 block mt-1 font-mono text-xs">{{ $petition->decision->directorate_order_number ?? 'N/A' }}</span>
                                            </div>
                                            @if($petition->decision->processedBy)
                                                <div>
                                                    <span class="text-gray-600 font-semibold">Decision Taken By:</span>
                                                    <span
                                                        class="text-rose-700 font-bold uppercase text-[11px] block mt-1">{{ $petition->decision->processedBy->name }}</span>
                                                </div>
                                            @endif
                                            <div class="md:col-span-2">
                                                <span class="text-gray-600 font-semibold">Final Remarks:</span>
                                                <span
                                                    class="text-gray-800 block mt-1">{{ $petition->decision->final_remarks ?? 'N/A' }}</span>
                                            </div>

                                            @php
                                                $finalOrderFile = $petition->uploads->where('category', \App\Models\Upload::CATEGORY_FINAL_ORDER)->last();
                                            @endphp
                                            @if($finalOrderFile)
                                                <div class="md:col-span-2 pt-2">
                                                    <span class="text-gray-600 font-semibold block mb-2">Attached Final Order:</span>
                                                    <a href="{{ route('petitions.download', $finalOrderFile->upload_id) }}" target="_blank"
                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-gray-800 hover:bg-slate-50 transition-colors shadow-sm">
                                                        <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i>
                                                        <span
                                                            class="font-medium italic text-xs">{{ $finalOrderFile->original_filename }}</span>
                                                    </a>
                                                </div>
                                            @endif

                                            @if(auth()->user()->canAccess('access admin dashboard'))
                                            <div class="md:col-span-2 pt-4 border-t border-slate-200/50">
                                                <form action="{{ route('decisions.pullback', $petition->decision->decision_id) }}" method="POST" class="inline pullback-form" data-message="Are you sure you want to pull back this Final Decision?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-white border border-rose-100 rounded-lg hover:bg-rose-50 transition-all flex items-center gap-1.5 shadow-sm">
                                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Pull Back Final Decision
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($petition->compliance)
                                    <div class="mt-4 bg-emerald-50/50 border border-emerald-100 rounded-xl p-5 lg:col-span-2">
                                        <h4 class="font-bold text-emerald-900 mb-3 flex items-center gap-2">
                                            <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i> Compliance Details
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-emerald-700/80 font-semibold block text-xs uppercase tracking-wider">Action Taken</span>
                                                <span class="text-slate-900 font-bold mt-1 block">{{ $petition->compliance->action_number }}</span>
                                            </div>
                                            <div>
                                                <span class="text-emerald-700/80 font-semibold block text-xs uppercase tracking-wider">Date</span>
                                                <span class="text-slate-900 font-medium mt-1 block">{{ \Carbon\Carbon::parse($petition->compliance->action_date)->format('d M, Y') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-emerald-700/80 font-semibold block text-xs uppercase tracking-wider">Unit</span>
                                                <span class="text-slate-900 font-medium mt-1 block">{{ $petition->compliance->unit->unit_name ?? 'N/A' }}</span>
                                            </div>
                                            @if($petition->compliance->remarks)
                                            <div class="sm:col-span-3 pt-2">
                                                <span class="text-emerald-700/80 font-semibold block text-xs uppercase tracking-wider mb-1">Remarks</span>
                                                <span class="text-slate-700 italic">{{ $petition->compliance->remarks }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            @endif
                        </div> <!-- End of workflow details grid -->
                </div>

            </div>
        </div>
        
        @if($petition->originalPetition || ($petition->duplicates && $petition->duplicates->count() > 0))
        <!-- Linked Records Section -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6">
            <div class="px-8 py-6 border-b border-slate-200 bg-slate-50 flex items-center gap-3">
                <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                    <i data-lucide="link" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Linked Records</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Petitions that are linked to this entry.</p>
                </div>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-bold">
                            <th class="px-6 py-4">Petition No</th>
                            <th class="px-6 py-4">Received On</th>
                            <th class="px-6 py-4">Mode</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @if($petition->originalPetition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold">
                                <a href="{{ route('petitions.show', $petition->originalPetition->petition_id) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                    {{ $petition->originalPetition->receipt_no }}
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ \Carbon\Carbon::parse($petition->originalPetition->date_of_petition_received)->format('d M, Y') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $petition->originalPetition->mode_of_petition_received === 'others' ? $petition->originalPetition->mode_of_petition_received_others : $petition->originalPetition->mode_of_petition_received }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-widest {{ ($petition->originalPetition->status === 'Closed' || $petition->originalPetition->status === 'Sent_to_Govt') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    @if($petition->originalPetition->decision)
                                        Final : {{ \App\Models\Decision::getDecisionLabel($petition->originalPetition->decision->final_decision) }}
                                    @else
                                        {{ $petition->originalPetition->status }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('petitions.unlinkDuplicate', $petition->petition_id) }}" method="POST" class="inline-block unlink-form" data-message="Are you sure you want to unlink this petition?">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 font-medium text-xs rounded-md transition-colors border border-rose-100">
                                        <i data-lucide="unlink" class="w-3.5 h-3.5"></i> Unlink
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endif

                        @if($petition->duplicates)
                        @foreach($petition->duplicates as $dup)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold">
                                <a href="{{ route('petitions.show', $dup->petition_id) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                    {{ $dup->receipt_no }}
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ \Carbon\Carbon::parse($dup->date_of_petition_received)->format('d M, Y') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $dup->mode_of_petition_received === 'others' ? $dup->mode_of_petition_received_others : $dup->mode_of_petition_received }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-widest {{ ($dup->status === 'Closed' || $dup->status === 'Sent_to_Govt') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    @if($dup->decision)
                                        Final : {{ \App\Models\Decision::getDecisionLabel($dup->decision->final_decision) }}
                                    @else
                                        {{ $dup->status }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('petitions.unlinkDuplicate', $dup->petition_id) }}" method="POST" class="inline-block unlink-form" data-message="Are you sure you want to unlink this duplicate petition?">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 font-medium text-xs rounded-md transition-colors border border-rose-100">
                                        <i data-lucide="unlink" class="w-3.5 h-3.5"></i> Unlink
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <!-- Link Petition Modal (Redesigned) -->
        <div x-show="showLinkModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-md" 
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            <!-- Modal panel -->
            <div x-show="showLinkModal" @click.stop
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" 
                class="bg-white/95 backdrop-blur-xl border border-white/20 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.3)] w-full max-w-lg rounded-3xl overflow-visible flex flex-col relative">
                
                <div x-data="{
                        searchQuery: '',
                        results: [],
                        isOpen: false,
                        selectedPetition: '',
                        isLoading: false,
                        errorMessage: '',
                        fetchResults() {
                            if (this.searchQuery.length < 2) {
                                this.results = [];
                                this.isOpen = false;
                                return;
                            }
                            
                            this.isLoading = true;
                            this.errorMessage = '';
                            
                            fetch(`{{ route('petitions.searchDuplicates') }}?q=${encodeURIComponent(this.searchQuery)}&exclude={{ $petition->petition_id }}`)
                                .then(response => {
                                    if (!response.ok) throw new Error('Network error');
                                    return response.json();
                                })
                                .then(data => {
                                    this.results = data || [];
                                    this.isOpen = true;
                                })
                                .catch(error => {
                                    console.error('Error fetching duplicates:', error);
                                    this.errorMessage = 'An error occurred while searching.';
                                    this.results = [];
                                    this.isOpen = true;
                                })
                                .finally(() => {
                                    this.isLoading = false;
                                });
                        },
                        selectResult(result) {
                            this.selectedPetition = result.id;
                            this.searchQuery = result.text;
                            this.isOpen = false;
                        },
                        resetSearch() {
                            this.selectedPetition = '';
                            this.searchQuery = '';
                            this.results = [];
                            this.isOpen = false;
                        }
                    }" 
                    @click.away="isOpen = false"
                    class="relative w-full text-left"
                >
                    <form action="{{ route('petitions.linkDuplicate', $petition->petition_id) }}" method="POST">
                        @csrf
                        
                        <!-- Premium Header -->
                        <div class="relative overflow-hidden bg-gradient-to-r from-teal-500 via-emerald-500 to-teal-600 px-8 py-8 rounded-t-3xl text-center">
                            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white/10 blur-2xl rounded-full pointer-events-none"></div>
                            <div class="absolute bottom-0 left-10 w-32 h-32 bg-emerald-400/20 blur-2xl rounded-full pointer-events-none"></div>
                            
                            <div class="relative z-10 mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-white/20 backdrop-blur-md shadow-inner border border-white/20 mb-4">
                                <i data-lucide="link" class="h-8 w-8 text-white"></i>
                            </div>
                            
                            <h3 class="relative z-10 text-2xl font-black text-white tracking-tight" id="modal-title">
                                Link to Existing Petition
                            </h3>
                            <p class="relative z-10 text-teal-100 text-sm mt-1 font-medium">Search and select the original petition to merge.</p>
                            
                            <!-- Close Button -->
                            <button type="button" @click="showLinkModal = false; resetSearch()" class="absolute top-4 right-4 p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-full transition-all focus:outline-none">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        
                        <div class="bg-white px-8 py-8 text-center">
                            <style>
                                .petition-input::placeholder {
                                    color: #94a3b8 !important;
                                    font-weight: 500;
                                }
                            </style>
                            <div class="max-w-sm mx-auto relative group">
                                <label for="original_receipt_no" class="block text-sm font-bold text-slate-700 mb-3 text-left">Search Petition</label>
                                <input type="hidden" name="original_receipt_no" x-model="selectedPetition">
                                
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="search" class="w-5 h-5 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
                                    </div>
                                    <input type="text" x-model="searchQuery" 
                                        @input.debounce.300ms="selectedPetition = ''; fetchResults()" 
                                        @focus="if(searchQuery.length >= 2 && !selectedPetition) fetchResults()" 
                                        required autocomplete="off"
                                        class="pl-11 focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 block w-full sm:text-base border-slate-300 rounded-xl p-3.5 transition-all text-slate-900 font-semibold petition-input bg-slate-50 hover:bg-white focus:bg-white shadow-sm"
                                        placeholder="Enter Petition No or Name...">
                                    
                                    <!-- Loading Spinner -->
                                    <div x-show="isLoading" class="absolute right-4 top-3.5">
                                        <svg class="animate-spin h-5 w-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Clear Button -->
                                    <button type="button" x-show="selectedPetition" @click="resetSearch()" class="absolute right-4 top-3.5 text-slate-400 hover:text-rose-500 transition-colors bg-white rounded-full">
                                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                    
                                <!-- Dropdown Results -->
                                <div x-show="isOpen && !isLoading" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute z-50 w-full bg-white border border-slate-200 rounded-xl mt-2 shadow-2xl max-h-64 overflow-y-auto custom-scrollbar text-left" 
                                    style="display: none;">
                                    
                                    <ul x-show="results.length > 0" class="divide-y divide-slate-100 p-1">
                                        <template x-for="result in results" :key="result.id">
                                            <li @click="selectResult(result)" class="px-4 py-3 hover:bg-teal-50 rounded-lg cursor-pointer transition-colors group/item flex items-start gap-3 border-b border-slate-50 last:border-0">
                                                <div class="bg-slate-100 group-hover/item:bg-teal-100 p-1.5 rounded-md text-slate-500 group-hover/item:text-teal-600 transition-colors shrink-0 mt-0.5">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-sm font-bold text-slate-800 group-hover/item:text-teal-900" x-text="result.receipt_no"></span>
                                                        <span class="text-xs font-medium text-slate-500 bg-slate-100 group-hover/item:bg-teal-100/50 px-2 py-0.5 rounded border border-slate-200/60 group-hover/item:border-teal-200/60" x-text="result.date"></span>
                                                    </div>
                                                    <div class="text-[13px] text-slate-600 flex flex-col gap-1 mt-1.5">
                                                        <div class="flex items-start gap-2 truncate">
                                                            <span class="font-bold text-indigo-700/70 text-[11px] uppercase tracking-wider w-24 shrink-0 mt-0.5">Complainant</span>
                                                            <span class="font-medium text-slate-700 truncate" x-text="result.complainant"></span>
                                                        </div>
                                                        <div class="flex items-start gap-2 truncate">
                                                            <span class="font-bold text-rose-700/70 text-[11px] uppercase tracking-wider w-24 shrink-0 mt-0.5">Accused</span>
                                                            <span class="font-medium text-slate-700 truncate" x-text="result.accused"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                    
                                    <div x-show="results.length === 0 && !errorMessage" class="p-6 text-sm text-slate-500 text-center flex flex-col items-center gap-2">
                                        <i data-lucide="search-x" class="w-6 h-6 text-slate-300"></i>
                                        No petitions found matching your search.
                                    </div>
                                    
                                    <div x-show="errorMessage" class="p-6 text-sm text-rose-500 text-center font-medium bg-rose-50">
                                        <span x-text="errorMessage"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50/80 px-8 py-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-slate-100 rounded-b-3xl">
                            <button type="button" @click="showLinkModal = false; resetSearch()" class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-slate-200 shadow-sm px-6 py-2.5 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200 transition-all">
                                Cancel
                            </button>
                            <button type="submit" :disabled="!selectedPetition" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl border border-transparent shadow-md px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 focus:outline-none focus:ring-4 focus:ring-teal-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 hover:-translate-y-0.5 active:translate-y-0">
                                <i data-lucide="link" class="w-4 h-4"></i> Link Petition
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @include('user.partials.return_to_inward_modal')
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pullbackForms = document.querySelectorAll('.pullback-form');
        
        pullbackForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const message = this.getAttribute('data-message');
                
                Swal.fire({
                    title: 'Confirm Pull Back',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48', // rose-600
                    cancelButtonColor: '#64748b', // slate-500
                    confirmButtonText: 'Yes, Pull Back',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    background: '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border-none shadow-2xl',
                        title: 'text-xl font-bold text-gray-900',
                        htmlContainer: 'text-gray-600',
                        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-bold order-2',
                        cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-bold order-1'
                    },
                    buttonsStyling: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInUp animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutDown animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        const unlinkForms = document.querySelectorAll('.unlink-form');
        
        unlinkForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const message = this.getAttribute('data-message');
                
                Swal.fire({
                    title: 'Confirm Unlink',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48', // rose-600
                    cancelButtonColor: '#64748b', // slate-500
                    confirmButtonText: 'Yes, Unlink',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    background: '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl border-none shadow-2xl',
                        title: 'text-xl font-bold text-gray-900',
                        htmlContainer: 'text-gray-600',
                        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-bold order-2',
                        cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-bold order-1'
                    },
                    buttonsStyling: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInUp animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutDown animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    });
</script>
@endsection

