@if(($tab ?? '') === 'inward')
    {{-- Old Inward Table & Headers --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-blue-50/50 border-b border-blue-100 text-xs font-bold text-blue-600 uppercase tracking-wider">
                    <th class="px-3 py-4 text-center w-10">#</th>
                    <th class="px-4 py-4 font-bold">Receipt No & Date</th>
                    <th class="px-4 py-4 font-bold">Complainant</th>
                    <th class="px-4 py-4 font-bold min-w-[185px] w-[18%]">Mode of Receipt</th>
                    <th class="px-4 py-4 font-bold">Concerned Seat</th>
                    <th class="px-4 py-4 font-bold">Transferred On</th>
                    @if(Auth::user()->canAccess('access admin dashboard'))
                        <th class="px-4 py-4 font-bold">Entered By (Inward)</th>
                    @endif
                    <th class="px-3 py-4 font-bold text-center">Status</th>
                    <th class="px-3 py-4 font-bold text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($petitions as $petition)
                    @php
                        $targetPetition = $petition->originalPetition ?? $petition;
                        $displayAddresses = $petition->displayAddresses();
                        $complainants = $displayAddresses->where('person_type', 'Complainant')->unique('person_name');
                        $seatUser = $petition->seat?->activeAssignment?->user;
                        $regDate = $petition->date_of_petition_received ? \Carbon\Carbon::parse($petition->date_of_petition_received)->format('Y-m-d') : ($petition->created_at ? $petition->created_at->format('Y-m-d') : '');
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-3 py-4 text-center font-bold text-blue-600 text-sm">
                            {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-blue-800">{{ $petition->receipt_no }}</span>
                                    @if($petition->isTSection())
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 border border-indigo-200" title="T-Section Confidential Petition">
                                            <i class="fa-solid fa-shield-halved text-[9px]"></i> T-Section
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200" title="CPSP Reference Petition">
                                            <i class="fa-solid fa-clipboard-list text-[9px]"></i> CPSP
                                        </span>
                                    @endif
                                </div>
                                @if($petition->file_no)
                                    <span class="block text-[11px] font-bold text-amber-600 mt-0.5" title="File No">
                                        <i class="fa-regular fa-folder-open text-[10px]"></i> {{ $petition->file_no }}
                                    </span>
                                @endif
                                @if($petition->originalPetition && (!$petition->originalPetition->isTSection() || auth()->user()->canAccessTSection() || auth()->user()->canAccess('access admin dashboard')))
                                    <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-orange-100 text-orange-700 border border-orange-200" title="Linked to {{ $petition->originalPetition->receipt_no }}">
                                        <i data-lucide="link" class="w-3 h-3"></i> Linked to {{ $petition->originalPetition->receipt_no }}
                                    </span>
                                @endif
                                @if($petition->duplicates && $petition->duplicates->count() > 0)
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($petition->duplicates as $dup)
                                            @if($dup->isTSection() && !auth()->user()->canAccessTSection() && !auth()->user()->canAccess('access admin dashboard'))
                                                @continue
                                            @endif
                                            <a href="{{ route('petitions.show', $dup->hashid) }}" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors" title="Linked Duplicate Petition">
                                                <i data-lucide="copy" class="w-2.5 h-2.5"></i> {{ $dup->receipt_no }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                <span class="text-[11px] font-medium text-blue-500 flex items-center gap-1 mt-0.5">
                                    {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($complainants->count() > 0)
                                @foreach($complainants as $complainant)
                                    <span class="block font-bold text-blue-700 {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $complainant->person_name }}</span>
                                    @if($complainant->phone)
                                        <span class="text-[11px] font-medium text-blue-500">{{ $complainant->phone }}</span>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-slate-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 min-w-[185px]">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-100/80 text-blue-700 border border-blue-200/50">
                                {{ $targetPetition->mode_of_petition_received }}
                            </span>
                            @if(strtolower($targetPetition->mode_of_petition_received) === 'unit')
                                @if($targetPetition->unit)
                                    <span class="block text-[11px] text-indigo-600 font-semibold mt-1 leading-snug">{{ $targetPetition->unit->unit_name }}</span>
                                @endif
                                @if($targetPetition->petition_number_at_unit)
                                    <span class="block text-[10px] text-slate-600 font-medium whitespace-nowrap">No: {{ $targetPetition->petition_number_at_unit }}</span>
                                @endif
                                @if($targetPetition->date_of_petition_received_at_unit)
                                    <span class="block text-[10px] text-slate-500 font-medium whitespace-nowrap">Recd: {{ \Carbon\Carbon::parse($targetPetition->date_of_petition_received_at_unit)->format('d/m/Y') }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($petition->seat)
                                <span class="font-bold text-blue-700 block">{{ $petition->seat->seat_name }}</span>
                                <span class="text-[11px] font-medium text-blue-500 block">
                                    {{ $seatUser ? $seatUser->name : 'Vacant' }}
                                </span>
                            @else
                                <span class="text-blue-400 italic">Not Assigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-medium text-blue-600 whitespace-nowrap text-xs">
                            {{ $petition->created_at?->format('d M, Y') }}
                        </td>
                        @if(Auth::user()->canAccess('access admin dashboard'))
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-blue-700">{{ $petition->user?->name ?? 'Unknown' }}</span>
                                    @if($petition->user?->seatUsers?->isNotEmpty())
                                        <span class="text-[11px] font-medium text-blue-500">{{ $petition->user->seatUsers->first()->seat?->seat_name ?? '' }}</span>
                                    @endif
                                </div>
                            </td>
                        @endif
                        <td class="px-3 py-4 text-center">
                            @if($petition->is_returned_to_inward)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Returned
                                </span>
                            @elseif($petition->is_cpsp_processed)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Processed
                                </span>
                                @if($petition->processedBySeat || $petition->processedByUser)
                                    <span class="block text-[10px] text-emerald-700 font-semibold mt-0.5">
                                        {{ $petition->processedBySeat->seat_name ?? '' }} ({{ $petition->processedByUser->name ?? '' }})
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-cyan-100 text-cyan-800 border border-cyan-200">
                                    Inward Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @php
                                    $canModifyPetition = Auth::user()->can('update', $petition);
                                @endphp

                                @if($canModifyPetition && !$petition->is_cpsp_processed && !$petition->is_returned_to_inward)
                                    <a href="{{ route('petitions.edit', $petition->hashid) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all hover:scale-105 whitespace-nowrap"
                                        title="Open Petition to Add Further Details">
                                        <i class="fa-solid fa-file-pen text-xs"></i>
                                        <span>Add Details</span>
                                    </a>
                                @endif

                                <a href="{{ route('petitions.show', $petition->hashid) }}"
                                    class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="View Details">
                                    <i class="fa-solid fa-eye text-indigo-500"></i>
                                </a>

                                @if($canModifyPetition && !$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $petition->isFromInward())
                                    <button type="button"
                                        onclick="window.openReturnModal('{{ $petition->hashid }}', '{{ addslashes($petition->receipt_no) }}')"
                                        @click="showReturnModal = true; returnPetitionId = '{{ $petition->hashid }}'; returnReceiptNo = '{{ addslashes($petition->receipt_no) }}'; $dispatch('open-return-modal', { id: '{{ $petition->hashid }}', receipt: '{{ addslashes($petition->receipt_no) }}' })"
                                        class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="Return to Inward">
                                        <i class="fa-solid fa-arrow-rotate-left text-rose-500"></i>
                                    </button>
                                @endif

                                @if(Auth::user()->can('delete', $petition))
                                    @if(!in_array($petition->status, ['Closed', 'Sent_to_Govt', 'Sent_to_SPC', 'Internal_Vigilance']) && (!$petition->isFromInward() || Auth::user()->canAccess('access admin dashboard')))
                                        <form action="{{ route('petitions.destroy', $petition->hashid) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Are you sure you want to delete this petition?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Delete Record">
                                                <i class="fa-solid fa-trash-can text-rose-500"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canAccess('access admin dashboard') ? '9' : '8' }}" class="px-6 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="inbox" class="w-7 h-7 text-slate-300"></i>
                                <p class="font-medium text-slate-500">No inward petitions found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    {{-- Standard Reports Table & Headers for All Other Tabs --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-3 py-4 font-bold w-10 text-center">#</th>
                    <th class="px-4 py-4 font-bold min-w-[145px] w-[13%]">Receipt No & File No</th>
                    <th class="px-4 py-4 font-bold min-w-[105px] w-[10%]">Petitioner Name</th>
                    <th class="px-4 py-4 font-bold min-w-[135px] max-w-[180px] w-[12%]">Suspect Name <br> Nature of petition</th>
                    <th class="px-4 py-4 font-bold min-w-[190px] w-[16%]">Mode of Receipt</th>
                    <th class="px-4 py-4 font-bold min-w-[230px] w-[22%]">Brief of the Petition</th>
                    <th class="px-3 py-4 font-bold text-center min-w-[120px] w-[10%]">Status</th>
                    <th class="px-3 py-4 font-bold text-center min-w-[95px] w-[8%]">Processing</th>
                    @if(auth()->user()->canAccess('access admin dashboard'))
                        <th class="px-3 py-4 font-bold text-center min-w-[95px] w-[8%]">Seat & User</th>
                    @endif
                    <th class="px-3 py-4 font-bold text-center w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($petitions as $petition)
                    @php
                        $regDate = $petition->date_of_petition_received ? \Carbon\Carbon::parse($petition->date_of_petition_received)->format('Y-m-d') : ($petition->created_at ? $petition->created_at->format('Y-m-d') : '');
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-3 py-4 text-center text-sm font-semibold text-slate-500">
                            {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-800">{{ $petition->receipt_no }}</span>
                                @if($petition->isTSection())
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 border border-indigo-200" title="T-Section Confidential Petition">
                                        <i class="fa-solid fa-shield-halved text-[9px]"></i> T-Section
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200" title="CPSP Reference Petition">
                                        <i class="fa-solid fa-clipboard-list text-[9px]"></i> CPSP
                                    </span>
                                @endif
                            </div>
                            @if($petition->file_no)
                                <span class="block text-[11px] font-bold text-amber-600 mt-0.5" title="File No">
                                    <i class="fa-regular fa-folder-open text-[10px]"></i> {{ $petition->file_no }}
                                </span>
                            @endif
                            @if($petition->originalPetition && (!$petition->originalPetition->isTSection() || auth()->user()->canAccessTSection() || auth()->user()->canAccess('access admin dashboard')))
                                <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-orange-100 text-orange-700 border border-orange-200" title="Linked to {{ $petition->originalPetition->receipt_no }}">
                                    <i data-lucide="link" class="w-3 h-3"></i> Linked to {{ $petition->originalPetition->receipt_no }}
                                </span>
                            @endif
                            @if($petition->duplicates && $petition->duplicates->count() > 0)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($petition->duplicates as $dup)
                                        @if($dup->isTSection() && !auth()->user()->canAccessTSection() && !auth()->user()->canAccess('access admin dashboard'))
                                            @continue
                                        @endif
                                        <a href="{{ route('petitions.show', $dup->hashid) }}" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors" title="Linked Duplicate Petition">
                                            <i data-lucide="copy" class="w-2.5 h-2.5"></i> {{ $dup->receipt_no }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <span
                                class="block text-xs text-slate-500 mt-1" title="Received Date">Rec: {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                            @if($petition->processed_at)
                                <span class="block text-[11px] text-teal-600 font-medium mt-0.5" title="Processed Date">Proc: {{ \Carbon\Carbon::parse($petition->processed_at)->format('d M, Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-600">
                            @php
                                $targetPetition = $petition->originalPetition ?? $petition;
                                $displayAddresses = $petition->displayAddresses();
                                $complainants = $displayAddresses->where('person_type', 'Complainant')->unique('person_name');
                            @endphp
                            @if($complainants->count() > 0)
                                @foreach($complainants as $complainant)
                                    <span
                                        class="block font-semibold {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $complainant->person_name }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 max-w-[180px] text-slate-700">
                            @php
                                $accused = $displayAddresses->where('person_type', 'Accused')->unique('person_name');
                            @endphp
                            @if($accused->count() > 0)
                                @foreach($accused as $accuse)
                                    <span
                                        class="block font-semibold {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $accuse->person_name }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-400 italic">N/A</span>
                            @endif
                            <span class="block text-xs text-indigo-500 mt-1.5 truncate w-full"
                                title="{{ $targetPetition->nature_of_petition }}">{{ $targetPetition->nature_of_petition }}</span>

                        </td>
                        <td class="px-4 py-4 min-w-[190px] text-slate-600">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $targetPetition->mode_of_petition_received }}
                            </span>
                            @if(strtolower($targetPetition->mode_of_petition_received) === 'unit')
                                @if($targetPetition->unit)
                                    <span class="block text-[11px] text-indigo-600 font-semibold mt-1 leading-snug">{{ $targetPetition->unit->unit_name }}</span>
                                @endif
                                @if($targetPetition->petition_number_at_unit)
                                    <span class="block text-[10px] text-slate-600 font-medium whitespace-nowrap">No: {{ $targetPetition->petition_number_at_unit }}</span>
                                @endif
                                @if($targetPetition->date_of_petition_received_at_unit)
                                    <span class="block text-[10px] text-slate-500 font-medium whitespace-nowrap">Recd: {{ \Carbon\Carbon::parse($targetPetition->date_of_petition_received_at_unit)->format('d/m/Y') }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-4 min-w-[230px] text-slate-600">
                            @php
                                $cleanDesc = trim(str_ireplace(['Inward petition entry', 'Inward petition'], '', $targetPetition->description ?? ''));
                            @endphp
                            <div
                                class="inline-block max-w-full px-2.5 py-1.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700"
                                title="{{ $cleanDesc ?: '-' }}">
                                <span class="line-clamp-2 leading-relaxed break-words">{{ $cleanDesc ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4 text-center">
                            @php
                                $wf = $petition->workflow_status;
                                $isDuplicatePetition = (bool) $petition->linked_petition_id || $petition->status === \App\Models\Petition::STATUS_DUPLICATE;
                                $canViewWf = $petition->canViewWorkflowDetails();
                            @endphp
                            @if(!$canViewWf)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-700 border border-slate-200" title="CPSP Registered / Processed">
                                    CPSP Processed
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest {{ $wf['pill_class'] }}"
                                    title="{{ $wf['description'] ?? $wf['label'] }}">
                                    @if($isDuplicatePetition)
                                        Duplicate
                                    @elseif($petition->decision)
                                        Final: {{ \App\Models\Decision::getDecisionLabel($petition->decision->final_decision) }}
                                    @else
                                        {{ $wf['label'] }}
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-center">
                            <div class="flex flex-col items-center justify-center gap-2">
                                @if(!$canViewWf)
                                    <span class="text-[11px] text-slate-400 font-semibold italic flex items-center gap-1">
                                        <i class="fa-solid fa-lock text-[10px] text-slate-300"></i> Reference Only
                                    </span>
                                @else
                                    @php $role = auth()->user()->getRoleNames()->first(); @endphp
                                    
                                    @if(Auth::user()->can('update', $petition))
                                        @if(!$petition->linked_petition_id)
                                            @if($petition->status === 'Received' && $petition->is_cpsp_processed)
                                                <button @click="showForwardModal = true; activePetitionId = {{ $petition->petition_id }}; activePetitionDate = '{{ $regDate }}'"
                                                    class="px-3 py-1 text-xs font-bold text-white bg-amber-500 rounded hover:bg-amber-600 transition-colors shadow-sm">
                                                    Forward
                                                </button>
                                            @elseif($petition->status === 'Received' && !$petition->is_cpsp_processed)
                                                <span class="text-[11px] font-semibold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                                                    Awaiting CPSP Details
                                                </span>
                                            @endif
                                            
                                            @if($petition->status === 'Forwarded' && $petition->latestForwarding)
                                                <button
                                                    @click="showVrModal = true; activeForwardingId = {{ $petition->latestForwarding->petition_forwarding_id }}"
                                                    class="px-3 py-1 text-xs font-bold text-white bg-blue-500 rounded hover:bg-blue-600 transition-colors shadow-sm">
                                                    Update VR
                                                </button>
                                            @endif
                                            
                                            @if($petition->status === 'VR_Received')
                                                <button @click="showDecisionModal = true; activePetitionId = {{ $petition->petition_id }}; activePetitionDate = '{{ $regDate }}'"
                                                    class="px-3 py-1 text-xs font-bold text-white bg-rose-500 rounded hover:bg-rose-600 transition-colors shadow-sm">
                                                    Decision
                                                </button>
                                            @endif
                                            
                                            @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt' || $petition->status === 'Sent_to_SPC' || $petition->status === 'Internal_Vigilance')
                                                <a href="{{ route('petitions.show', $petition->hashid) }}#decision"
                                                    class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 underline flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-check"></i> Finalized
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400 italic flex items-center gap-1">
                                                <i class="fa-solid fa-link"></i> Linked
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 font-medium italic">-</span>
                                    @endif

                                    @if($role === 'admin')
                                        @php 
                                            $procBy = null;
                                            if($petition->status === 'Forwarded' || $petition->status === 'VR_Received') {
                                                $procBy = $petition->latestForwarding?->processedBy?->name;
                                            } elseif($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt' || $petition->status === 'Sent_to_SPC' || $petition->status === 'Internal_Vigilance') {
                                                $procBy = $petition->decision?->processedBy?->name;
                                            }
                                        @endphp
                                        @if($procBy)
                                            <span class="text-[9px] text-slate-400 italic">By: {{ $procBy }}</span>
                                        @endif
                                    @endif
                                @endif
                            </div>
                         </td>
                        @if(auth()->user()->canAccess('access admin dashboard'))
                        <td class="px-3 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-bold text-slate-800 text-xs">{{ $petition->processedBySeat->seat_name ?? $petition->seat->seat_name ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-500">{{ $petition->processedByUser->name ?? $petition->user->name ?? 'N/A' }}</span>
                                @if($petition->isFromInward() && $petition->user && $petition->processedByUser && $petition->processed_by_user_id !== $petition->user_id)
                                    <span class="text-[9px] text-slate-400">Inward: {{ $petition->user->name }}</span>
                                @endif
                            </div>
                        </td>
                        @endif
                        <td class="px-3 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                @if(!$canViewWf)
                                    <a href="{{ route('petitions.show', $petition->hashid) }}"
                                        class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="View Details">
                                        <i class="fa-solid fa-eye text-indigo-500"></i>
                                    </a>
                                @else
                                    @php
                                        $canModifyPetition = Auth::user()->can('update', $petition);
                                    @endphp

                                    @if($canModifyPetition && !$petition->is_cpsp_processed && !$petition->is_returned_to_inward)
                                        <a href="{{ route('petitions.edit', $petition->hashid) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all hover:scale-105 whitespace-nowrap"
                                            title="Open Petition to Add Further Details">
                                            <i class="fa-solid fa-file-pen text-xs"></i>
                                            <span>Add Details</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('petitions.show', $petition->hashid) }}"
                                        class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="View Details">
                                        <i class="fa-solid fa-eye text-indigo-500"></i>
                                    </a>
                                    @if($canModifyPetition)
                                        @if(!in_array($petition->status, ['Closed', 'Sent_to_Govt', 'Sent_to_SPC', 'Internal_Vigilance']) && ($petition->is_cpsp_processed || $petition->is_returned_to_inward))
                                            <a href="{{ route('petitions.edit', $petition->hashid) }}"
                                                class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                                title="Edit Record">
                                                <i class="fa-solid fa-pen-to-square text-emerald-500"></i>
                                            </a>
                                        @endif
                                    @endif
                                    @if($canModifyPetition && !$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $petition->isFromInward())
                                        <button type="button"
                                            onclick="window.openReturnModal('{{ $petition->hashid }}', '{{ addslashes($petition->receipt_no) }}')"
                                            @click="showReturnModal = true; returnPetitionId = '{{ $petition->hashid }}'; returnReceiptNo = '{{ addslashes($petition->receipt_no) }}'; $dispatch('open-return-modal', { id: '{{ $petition->hashid }}', receipt: '{{ addslashes($petition->receipt_no) }}' })"
                                            class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Return to Inward">
                                            <i class="fa-solid fa-arrow-rotate-left text-rose-500"></i>
                                        </button>
                                    @endif
                                    @if(Auth::user()->can('delete', $petition))
                                        @if(!in_array($petition->status, ['Closed', 'Sent_to_Govt', 'Sent_to_SPC', 'Internal_Vigilance']) && (!$petition->isFromInward() || Auth::user()->canAccess('access admin dashboard')))
                                            <form action="{{ route('petitions.destroy', $petition->hashid) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Are you sure you want to delete this petition?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                    title="Delete Record">
                                                    <i class="fa-solid fa-trash-can text-rose-500"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canAccess('access admin dashboard') ? '10' : '9' }}" class="px-4 py-8 text-center text-slate-500">
                            No petitions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
<div class="px-4 py-3 border-t border-slate-200">
    {{ $petitions->links() }}
</div>
