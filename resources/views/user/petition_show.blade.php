@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="file-search" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Petition Details: {{ $petition->petition_no }}</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Submitted on {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->role === 'user' || auth()->user()->role === 'admin')
                <a href="{{ route('petitions.edit', $petition->petition_id) }}" class="px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="edit" class="w-4 h-4"></i> Edit Details
                </a>
            @endif
            <a href="{{ route('petitions.index') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Data Layout -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-sm">
        <div class="p-8 space-y-10">
            <!-- Section 1: Petition Variables -->
            <div>
                <h3 class="text-base font-bold text-slate-900 border-b-2 border-slate-200 pb-2 mb-5 flex items-center gap-2 w-max">
                    <i data-lucide="info" class="w-5 h-5 text-indigo-600"></i> General Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">Status</span> 
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-widest {{ ($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            @if($petition->decision)
                                Final : {{ $petition->decision->decision_remarks }}
                            @else
                                {{ $petition->status }}
                            @endif
                        </span>
                    </div>
                    <div><span class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Nature of Petition</span> <span class="font-semibold text-slate-900">{{ $petition->nature_of_petition }}</span></div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Mode Received</span> 
                        <span class="font-semibold text-slate-900">
                            {{ $petition->mode_of_petition_received === 'others' 
                                ? 'Others (' . $petition->mode_of_petition_received_others . ')' 
                                : $petition->mode_of_petition_received }}
                        </span>
                    </div>
                    <div class="lg:col-span-4 bg-slate-50 p-5 rounded-xl border border-slate-200 mt-2">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Detailed Description</span> 
                        <p class="font-medium text-slate-700 leading-relaxed">{{ $petition->description ?: 'No description provided.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Associated Parties -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Complainants -->
                <div>
                    <h3 class="text-base font-bold text-slate-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                        <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Complainants
                    </h3>
                    <div class="space-y-4">
                        @php
                            $complainants = $petition->addresses->where('person_type', 'Complainant')->groupBy('person_name');
                        @endphp
                        
                        @forelse($complainants as $name => $addresses)
                            @php $first = $addresses->first(); @endphp
                            <div class="bg-white border border-slate-200 rounded-lg p-5">
                                <h4 class="font-bold text-slate-800 mb-4">{{ $name }}</h4>
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <div class="text-xs"><span class="text-slate-500 block mb-0.5">Phone:</span> <span class="font-semibold text-slate-800">{{ $first->phone ?? 'N/A' }}</span></div>
                                    <div class="text-xs"><span class="text-slate-500 block mb-0.5">Aadhar:</span> <span class="font-semibold text-slate-800">{{ $first->Aadhar_number ?? 'N/A' }}</span></div>
                                </div>
                                
                                <div class="space-y-3 pt-3 border-t border-slate-100">
                                    <h5 class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Addresses</h5>
                                    @foreach($addresses as $addr)
                                        <div class="flex items-start gap-3 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                            <span class="bg-blue-100 text-blue-700 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded tracking-widest w-16 text-center shrink-0">{{ $addr->address_type }}</span>
                                            <span class="text-xs font-medium text-slate-700 leading-snug">{{ $addr->full_address }}, {{ $addr->district }} - {{ $addr->pincode }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 italic text-sm p-4 bg-slate-50 rounded-lg">No Complainants recorded.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Accused -->
                <div>
                    <h3 class="text-base font-bold text-slate-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                        <i data-lucide="user-x" class="w-5 h-5 text-rose-600"></i> Respondents (Accused)
                    </h3>
                    <div class="space-y-4">
                        @php
                            $accused = $petition->addresses->where('person_type', 'Accused')->groupBy('person_name');
                        @endphp
                        
                        @forelse($accused as $name => $addresses)
                            @php $first = $addresses->first(); @endphp
                            <div class="bg-white border border-slate-200 rounded-lg p-5">
                                <h4 class="font-bold text-slate-800 mb-4">{{ $name }}</h4>
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <div class="text-xs"><span class="text-slate-500 block mb-0.5">Phone:</span> <span class="font-semibold text-slate-800">{{ $first->phone ?? 'N/A' }}</span></div>
                                    <div class="text-xs"><span class="text-slate-500 block mb-0.5">Aadhar:</span> <span class="font-semibold text-slate-800">{{ $first->Aadhar_number ?? 'N/A' }}</span></div>
                                </div>
                                
                                <div class="space-y-3 pt-3 border-t border-slate-100">
                                    <h5 class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Addresses</h5>
                                    @foreach($addresses as $addr)
                                        <div class="flex items-start gap-3 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                            <span class="bg-rose-100 text-rose-700 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded tracking-widest w-16 text-center shrink-0">{{ $addr->address_type }}</span>
                                            <span class="text-xs font-medium text-slate-700 leading-snug">{{ $addr->full_address }}, {{ $addr->district }} - {{ $addr->pincode }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 italic text-sm p-4 bg-slate-50 rounded-lg">No Respondents recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section 3: Proposed Action & Documents -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-base font-bold text-slate-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                        <i data-lucide="clipboard-check" class="w-5 h-5 text-emerald-600"></i> Initial Assessment / Action
                    </h3>
                    <div class="bg-emerald-50/50 p-5 rounded-xl border border-emerald-100 shadow-sm text-slate-700">
                        @if($petition->proposed_action)
                            <p class="font-medium leading-relaxed">{{ $petition->proposed_action }}</p>
                        @else
                            <p class="italic text-slate-500">No initial action proposed recorded.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 border-b-2 border-slate-200 pb-2 mb-4 flex items-center gap-2 w-max">
                        <i data-lucide="paperclip" class="w-5 h-5 text-indigo-600"></i> Attached Evidence
                    </h3>
                    <div class="space-y-3">
                        @forelse($petition->uploads as $upload)
                            <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="flex items-center gap-3 p-3 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors rounded-xl shadow-sm text-sm group">
                                <div class="bg-slate-100 text-slate-500 p-2 rounded-lg group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shrink-0">
                                    <i data-lucide="file" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-800 truncate">{{ $upload->original_filename }}</p>
                                    <p class="text-[10px] uppercase font-bold text-slate-400 mt-0.5 tracking-wider">{{ $upload->category }}</p>
                                </div>
                                <i data-lucide="download" class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 mx-2"></i>
                            </a>
                        @empty
                            <div class="p-6 bg-slate-50 rounded-xl text-center border border-slate-200 border-dashed">
                                <i data-lucide="file-x-2" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                                <span class="text-slate-500 text-sm font-medium">No files uploaded.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section 4: Petition Workflow Actions -->
            <div class="mt-8 border-t border-slate-200 pt-8">
                <h3 class="text-base font-bold text-slate-900 pb-2 mb-4 flex items-center gap-2 w-max">
                    <i data-lucide="git-merge" class="w-5 h-5 text-indigo-600"></i> Petition Workflow Actions
                </h3>

                @if(auth()->user()->role === 'user' || auth()->user()->role === 'admin')
                    @if($petition->status === 'Received')
                        <!-- Director Forwarding Form -->
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-6">
                            <h4 class="font-bold text-indigo-900 mb-4">Initial Decision / Forwarding</h4>
                            <form action="{{ route('forwardings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="petition_id" value="{{ $petition->petition_id }}">
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Action</label>
                                    <select name="action" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required onchange="document.getElementById('unit-select-div').style.display = this.value === 'Forward_To_Unit' ? 'block' : 'none'; document.getElementById('final-doc-div-fwd').style.display = (this.value === 'Close' || this.value === 'Sent_to_Govt') ? 'block' : 'none';">
                                        <option value="">Select Action...</option>
                                        <option value="Forward_To_Unit">Forward to Unit</option>
                                        <option value="Sent_to_Govt">Send to Govt</option>
                                        <option value="Close">Close Petition</option>
                                    </select>
                                </div>

                                <div id="final-doc-div-fwd" style="display: none;">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Upload Final Order Document</label>
                                    <input type="file" name="final_order_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <div id="unit-select-div" style="display: none;">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Select Unit</label>
                                    <select name="to_unit_id" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Unit...</option>
                                        @foreach(\App\Models\Unit::all() as $unit)
                                            <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Remarks</label>
                                    <textarea name="director_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                </div>

                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-sm">Submit Decision</button>
                            </form>
                        </div>
                    @endif

                    <!-- Show Previous Forwarding Details if exists -->
                    @if($petition->latestForwarding)
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 mb-6">
                            <h4 class="font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Forwarding History</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="text-slate-500 font-semibold">Forwarded To:</span> {{ $petition->latestForwarding->toUnit->unit_name ?? 'N/A' }}</div>
                                <div><span class="text-slate-500 font-semibold">Date:</span> {{ \Carbon\Carbon::parse($petition->latestForwarding->forwarded_date)->format('d M, Y') }}</div>
                                <div class="col-span-2"><span class="text-slate-500 font-semibold">Remarks:</span> {{ $petition->latestForwarding->director_remarks }}</div>
                            </div>
                        </div>

                        <!-- Verification Report Details (Added) -->
                        @if($petition->latestForwarding->vr_ref_no)
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-amber-800 mb-4 border-b border-amber-200 pb-2 flex items-center gap-2">
                                    <i data-lucide="file-check" class="w-5 h-5 text-amber-600"></i> Verification Report Details
                                </h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><span class="text-slate-500 font-semibold">VR Reference No:</span> {{ $petition->latestForwarding->vr_ref_no }}</div>
                                    <div><span class="text-slate-500 font-semibold">VR Date:</span> {{ \Carbon\Carbon::parse($petition->latestForwarding->vr_date)->format('d M, Y') }}</div>
                                    @if($petition->latestForwarding->vr_received_at_cpsp_date)
                                        <div class="col-span-2"><span class="text-slate-500 font-semibold text-blue-600">Received at CPSP:</span> <span class="font-bold text-blue-700">{{ \Carbon\Carbon::parse($petition->latestForwarding->vr_received_at_cpsp_date)->format('d M, Y') }}</span></div>
                                    @endif
                                    <div class="col-span-2"><span class="text-slate-500 font-semibold">VR Remarks/Findings:</span> {{ $petition->latestForwarding->vr_remarks }}</div>
                                    
                                    @php
                                        $vrFile = $petition->uploads->where('category', 'Verification Report')->last();
                                    @endphp
                                    @if($vrFile)
                                        <div class="col-span-2 pt-2">
                                            <span class="text-slate-500 font-semibold block mb-2">Attached VR Document:</span>
                                            <a href="{{ asset('storage/' . $vrFile->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-amber-200 rounded-lg text-amber-700 hover:bg-amber-100 transition-colors shadow-sm">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                <span class="font-medium italic text-xs">{{ $vrFile->original_filename }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Unit VR Submission (Only if Forwarded) -->
                        @if($petition->status === 'Forwarded')
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-amber-900 mb-4">Submit Verification Report (Unit Action)</h4>
                                <form action="{{ route('forwardings.updateVr', $petition->latestForwarding->petition_forwarding_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Reference No</label>
                                            <input type="text" name="vr_ref_no" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Date</label>
                                            <input type="date" name="vr_date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Remarks</label>
                                        <textarea name="vr_remarks" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Findings or remarks..." required></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload verification report</label>
                                            <input type="file" name="vr_file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1 text-blue-600 font-bold">VR received in CPSP date</label>
                                            <input type="date" name="vr_received_at_cpsp_date" class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        </div>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold shadow-sm">Submit VR</button>
                                </form>
                            </div>
                        @endif

                        <!-- CPSP Receive VR -->
                        @if($petition->status === 'VR_Received' && !$petition->latestForwarding->vr_received_at_cpsp_date)
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-blue-900 mb-4">Acknowledge VR Receipt (CPSP Action)</h4>
                                <div class="mb-4 text-sm">
                                    <p><strong>VR Ref:</strong> {{ $petition->latestForwarding->vr_ref_no }}</p>
                                    <p><strong>VR Date:</strong> {{ $petition->latestForwarding->vr_date }}</p>
                                </div>
                                <form action="{{ route('forwardings.receiveVr', $petition->latestForwarding->petition_forwarding_id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Verification report received in CPSP date</label>
                                        <input type="date" name="vr_received_at_cpsp_date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow-sm w-full sm:w-auto">Acknowledge & Mark as Received</button>
                                </form>
                            </div>
                        @endif

                        <!-- Final Decision -->
                        @if($petition->status === 'VR_Received' && $petition->latestForwarding->vr_received_at_cpsp_date)
                            <div class="bg-rose-50 border border-rose-100 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-rose-900 mb-4">Final Decision</h4>
                                <form action="{{ route('decisions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="petition_id" value="{{ $petition->petition_id }}">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Decision Remarks (Code)</label>
                                        <select name="decision_remarks" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500" required>
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
                                        <textarea name="final_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload Final Order Document</label>
                                        <input type="file" name="final_order_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                                    </div>

                                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 font-semibold shadow-sm">Submit Final Decision</button>
                                </form>
                            </div>
                        @endif
                    @endif
                    
                    @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                        @if($petition->decision)
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 mb-6">
                                <h4 class="font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Decision Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><span class="text-slate-500 font-semibold">Decision Code:</span> {{ $petition->decision->decision_remarks }}</div>
                                    <div><span class="text-slate-500 font-semibold">Decided On:</span> {{ \Carbon\Carbon::parse($petition->decision->decision_date)->format('d M, Y') }}</div>
                                    <div class="col-span-2"><span class="text-slate-500 font-semibold">Final Remarks:</span> {{ $petition->decision->final_remarks ?? 'N/A' }}</div>
                                    
                                    @php
                                        $finalOrderFile = $petition->uploads->where('category', \App\Models\Upload::CATEGORY_FINAL_ORDER)->last();
                                    @endphp
                                    @if($finalOrderFile)
                                        <div class="col-span-2 pt-2">
                                            <span class="text-slate-500 font-semibold block mb-2">Attached Final Order:</span>
                                            <a href="{{ asset('storage/' . $finalOrderFile->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                                                <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i>
                                                <span class="font-medium italic text-xs">{{ $finalOrderFile->original_filename }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
