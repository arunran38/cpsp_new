<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-4 py-3 font-bold w-12 text-center">#</th>
                <th class="px-4 py-3 font-bold">Petition No & Received Date</th>
                <th class="px-4 py-3 font-bold">Name of the Petitioner</th>
                <th class="px-4 py-3 font-bold">Name of the Respondent & nature of petition</th>
                <th class="px-4 py-3 font-bold">Mode of Receipt</th>
                <th class="px-4 py-3 font-bold text-center">Status</th>
                <th class="px-4 py-3 font-bold text-center">{{ auth()->user()->role === 'admin' ? 'Seat & User' : 'Processing' }}</th>
                <th class="px-4 py-3 font-bold text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($petitions as $petition)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3 text-center text-sm font-semibold text-slate-500">
                        {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-bold text-slate-800">{{ $petition->petition_no }}</span>
                        <span class="block text-xs text-slate-500 mt-1">{{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        @php
                            $complainants = $petition->addresses->where('person_type', 'Complainant')->unique('person_name');
                        @endphp
                        @if($complainants->count() > 0)
                            @foreach($complainants as $complainant)
                                <span class="block font-semibold {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $complainant->person_name }}</span>
                            @endforeach
                        @else
                            <span class="text-slate-400 italic">N/A</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 max-w-xs text-slate-700">
                        @php
                            $accused = $petition->addresses->where('person_type', 'Accused')->unique('person_name');
                        @endphp
                        @if($accused->count() > 0)
                            @foreach($accused as $accuse)
                                <span class="block font-semibold {{ !$loop->first ? 'mt-1 text-sm' : '' }}">{{ $accuse->person_name }}</span>
                            @endforeach
                        @else
                            <span class="text-slate-400 italic">N/A</span>
                        @endif
                        <span class="block text-xs text-indigo-500 mt-2 truncate w-full" title="{{ $petition->nature_of_petition }}">{{ $petition->nature_of_petition }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                            {{ $petition->mode_of_petition_received }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                Final : {{ $petition->decision->decision_remarks ?? $petition->status }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                                {{ $petition->status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if(auth()->user()->role === 'admin')
                                <div class="flex flex-col items-center gap-1">
                                    <span class="font-bold text-slate-800">{{ $petition->seat->seat_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-slate-500">{{ $petition->user->name ?? 'N/A' }}</span>
                                </div>
                            @elseif(auth()->user()->role === 'user')
                                @if($petition->status === 'Received')
                                    <button @click="showForwardModal = true; activePetitionId = {{ $petition->petition_id }}" class="px-3 py-1 text-xs font-bold text-white bg-amber-500 rounded hover:bg-amber-600 transition-colors">
                                        Forward
                                    </button>
                                @endif
                                @if($petition->status === 'Forwarded' && $petition->latestForwarding)
                                    <button @click="showVrModal = true; activeForwardingId = {{ $petition->latestForwarding->petition_forwarding_id }}" class="px-3 py-1 text-xs font-bold text-white bg-blue-500 rounded hover:bg-blue-600 transition-colors">
                                        Update VR
                                    </button>
                                @endif
                                @if($petition->status === 'VR_Received')
                                    <button @click="showDecisionModal = true; activePetitionId = {{ $petition->petition_id }}" class="px-3 py-1 text-xs font-bold text-white bg-rose-500 rounded hover:bg-rose-600 transition-colors">
                                        Decision
                                    </button>
                                @endif
                                @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                                    <a href="{{ route('petitions.show', $petition->petition_id) }}#decision" class="text-xs font-bold text-slate-500 hover:text-slate-800 underline">
                                        Final Decision
                                    </a>
                                @endif
                            @else
                                <span class="text-xs text-slate-400 italic">View Only</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('petitions.show', $petition->petition_id) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Details">
                                <i class="fa-solid fa-eye text-indigo-500"></i>
                            </a>
                            @if(auth()->user()->role === 'user')
                                @if(!in_array($petition->status, ['Closed', 'Sent_to_Govt']))
                                    <a href="{{ route('petitions.edit', $petition->petition_id) }}" class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit Record">
                                        <i class="fa-solid fa-pen-to-square text-emerald-500"></i>
                                    </a>
                                    <form action="{{ route('petitions.destroy', $petition->petition_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this petition?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Record">
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
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">
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
