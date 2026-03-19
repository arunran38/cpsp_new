@extends('layouts.user')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
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
            <a href="{{ route('petitions.edit', $petition->petition_id) }}" class="px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="edit" class="w-4 h-4"></i> Edit Details
            </a>
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
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                            {{ $petition->status }}
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

        </div>
    </div>
</div>
@endsection
