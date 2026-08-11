<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-4 font-bold w-12 text-center">#</th>
                <th class="px-6 py-4 font-bold">Petition No & Received Date</th>
                <th class="px-6 py-4 font-bold">Petitioner Name</th>
                <th class="px-6 py-4 font-bold">Suspect Name <br> Nature of petition</th>
                <th class="px-6 py-4 font-bold">Mode of Receipt</th>
                <th class="px-6 py-4 font-bold">Brief of the Petition</th>
                <th class="px-6 py-4 font-bold text-center">Status</th>
                <th class="px-6 py-4 font-bold text-center">Processing</th>
                @if(auth()->user()->role === 'admin')
                    <th class="px-6 py-4 font-bold text-center w-32">Seat & User</th>
                @endif
                <th class="px-6 py-4 font-bold text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($petitions as $petition)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-center text-sm font-semibold text-slate-500">
                        {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-800">{{ $petition->petition_no }}</span>
                        @if($petition->originalPetition)
                            <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-orange-100 text-orange-700 border border-orange-200">
                                <i data-lucide="link" class="w-3 h-3"></i> Linked
                            </span>
                        @endif
                        <span
                            class="block text-xs text-slate-500 mt-1">{{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600">
                        @php
                            $targetPetition = $petition->originalPetition ?? $petition;
                            $complainants = $targetPetition->addresses->where('person_type', 'Complainant')->unique('person_name');
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
                    <td class="px-6 py-4 max-w-xs text-slate-700">
                        @php
                            $accused = $targetPetition->addresses->where('person_type', 'Accused')->unique('person_name');
                        @endphp
                        @if($accused->count() > 0)
                            @foreach($accused as $accuse)
                                <span
                                    class="block font-semibold {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $accuse->person_name }}</span>
                            @endforeach
                        @else
                            <span class="text-slate-400 italic">N/A</span>
                        @endif
                        <span class="block text-xs text-indigo-500 mt-2 truncate w-full"
                            title="{{ $targetPetition->nature_of_petition }}">{{ $targetPetition->nature_of_petition }}</span>

                    </td>
                    <td class="px-6 py-4 text-slate-600">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                            {{ $targetPetition->mode_of_petition_received }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 max-w-[150px] truncate"
                            title="{{ $targetPetition->description }}">
                            {{ $targetPetition->description }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($targetPetition->status === 'Closed' || $targetPetition->status === 'Sent_to_Govt')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                Final : {{ $targetPetition->decision->decision_remarks ?? $targetPetition->status }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                                {{ $targetPetition->status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center justify-center gap-2">
                            @php $role = auth()->user()->role; @endphp
                            
                            @if($role === 'admin' || $role === 'user')
                                @if(!$petition->linked_petition_id)
                                    @if($petition->status === 'Received')
                                        <button @click="showForwardModal = true; activePetitionId = {{ $petition->petition_id }}"
                                            class="px-3 py-1 text-xs font-bold text-white bg-amber-500 rounded hover:bg-amber-600 transition-colors shadow-sm">
                                            Forward
                                        </button>
                                    @endif
                                    
                                    @if($petition->status === 'Forwarded' && $petition->latestForwarding)
                                        <button
                                            @click="showVrModal = true; activeForwardingId = {{ $petition->latestForwarding->petition_forwarding_id }}"
                                            class="px-3 py-1 text-xs font-bold text-white bg-blue-500 rounded hover:bg-blue-600 transition-colors shadow-sm">
                                            Update VR
                                        </button>
                                    @endif
                                    
                                    @if($petition->status === 'VR_Received')
                                        <button @click="showDecisionModal = true; activePetitionId = {{ $petition->petition_id }}"
                                            class="px-3 py-1 text-xs font-bold text-white bg-rose-500 rounded hover:bg-rose-600 transition-colors shadow-sm">
                                            Decision
                                        </button>
                                    @endif
                                    
                                    @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                                        <a href="{{ route('petitions.show', $petition->petition_id) }}#decision"
                                            class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 underline flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> Finalized
                                        </a>
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 italic flex items-center gap-1">
                                        <i class="fa-solid fa-link"></i> Linked
                                    </span>
                                @endif
                            @endif

                            @if($role === 'admin')
                                @php 
                                    $procBy = null;
                                    if($petition->status === 'Forwarded' || $petition->status === 'VR_Received') {
                                        $procBy = $petition->latestForwarding?->processedBy?->name;
                                    } elseif($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt') {
                                        $procBy = $petition->decision?->processedBy?->name;
                                    }
                                @endphp
                                @if($procBy)
                                    <span class="text-[9px] text-slate-400 italic">By: {{ $procBy }}</span>
                                @endif
                            @endif
                        </div>
                    </td>
                    @if(auth()->user()->role === 'admin')
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="font-bold text-slate-800 text-xs">{{ $petition->seat->seat_name ?? 'N/A' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $petition->user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    @endif
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('petitions.show', $petition->petition_id) }}"
                                class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                title="View Details">
                                <i class="fa-solid fa-eye text-indigo-500"></i>
                            </a>
                            @if(auth()->user()->role === 'user')
                                @if(!in_array($petition->status, ['Closed', 'Sent_to_Govt']))
                                    <a href="{{ route('petitions.edit', $petition->petition_id) }}"
                                        class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                        title="Edit Record">
                                        <i class="fa-solid fa-pen-to-square text-emerald-500"></i>
                                    </a>
                                    <form action="{{ route('petitions.destroy', $petition->petition_id) }}" method="POST"
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
                    <td colspan="{{ auth()->user()->role === 'admin' ? '10' : '9' }}" class="px-4 py-8 text-center text-slate-500">
                        No petitions found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="px-4 py-3 border-t border-slate-200">
    {{ $petitions->links() }}
</div>