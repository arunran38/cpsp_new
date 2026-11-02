@if(isset($counts))
    <div id="counts-data" data-all="{{ $counts['all'] ?? 0 }}" data-transferred="{{ $counts['transferred'] ?? 0 }}" data-returned="{{ $counts['returned'] ?? 0 }}" style="display:none;"></div>
@endif
@php
    $showEnteredBy = Auth::user()->canAccess('access admin dashboard') && !$isSuperAdmin;
@endphp
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr
                class="bg-blue-50/50 border-b border-blue-100 text-xs font-bold text-blue-600 uppercase tracking-wider">
                <th class="px-6 py-4 text-center w-12">#</th>
                <th class="px-6 py-4">Receipt No & Date</th>
                <th class="px-6 py-4">Complainant</th>
                <th class="px-6 py-4">Mode of Receipt</th>
                <th class="px-6 py-4">Concerned Seat</th>
                <th class="px-6 py-4">Transferred On</th>
                @if($showEnteredBy)
                    <th class="px-6 py-4">Entered By (Inward)</th>
                @endif
                @if(($filter ?? request('filter')) === 'returned')
                    <th class="px-6 py-4">Reason for Return</th>
                @endif
                <th class="px-6 py-4 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-sm">
            @forelse($transfers ?? [] as $index => $item)
                @php
                    $complainant = $item->displayAddresses()->firstWhere('person_type', 'Complainant');
                    $seatUser = $item->seat?->activeAssignment?->user;
                @endphp
            <tr class="hover:bg-blue-50/30 transition-colors {{ $loop->first && session('success') ? 'bg-indigo-50/40' : '' }}">
                    <td class="px-5 py-3.5 text-center font-bold text-blue-600">
                        {{ method_exists($transfers, 'firstItem') ? ($transfers->firstItem() + $index) : ($index + 1) }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col">
                            <a href="{{ route('petitions.show', $item->hashid) }}" 
                               class="font-bold text-blue-800 hover:text-indigo-600 hover:underline flex items-center gap-1 group/link"
                               title="View Petition Details">
                                <span>{{ $item->receipt_no }}</span>
                                <i data-lucide="external-link" class="w-3 h-3 text-blue-400 group-hover/link:text-indigo-600 transition-colors"></i>
                            </a>
                            <span class="text-[11px] font-medium text-blue-500 flex items-center gap-1 mt-0.5">
                                {{ \Carbon\Carbon::parse($item->date_of_petition_received)->format('d M, Y') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-bold text-blue-700 block">{{ $complainant?->person_name ?? 'N/A' }}</span>
                        @if($complainant?->phone)
                            <span class="text-[11px] font-medium text-blue-500">{{ $complainant->phone }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-100/80 text-blue-700 border border-blue-200/50">
                            {{ $item->mode_of_petition_received }}
                        </span>
                        @if(strtolower($item->mode_of_petition_received) === 'unit')
                            @if($item->unit)
                                <span class="block text-[11px] text-indigo-600 font-semibold mt-1">{{ $item->unit->unit_name }}</span>
                            @endif
                            @if($item->date_of_petition_received_at_unit)
                                <span class="block text-[10px] text-slate-500 font-medium">Recd: {{ \Carbon\Carbon::parse($item->date_of_petition_received_at_unit)->format('d/m/Y') }}</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($item->seat)
                            <span class="font-bold text-blue-700 block">{{ $item->seat->seat_name }}</span>
                            <span class="text-[11px] font-medium text-blue-500 block">
                                {{ $seatUser ? $seatUser->name : 'Vacant' }}
                            </span>
                        @else
                            <span class="text-blue-400 italic">Not Assigned</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 font-medium text-blue-600 whitespace-nowrap">
                        {{ $item->created_at?->format('d M, Y') }}
                    </td>
                    @if($showEnteredBy)
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col">
                            <span class="font-bold text-blue-700">{{ $item->user?->name ?? 'Unknown' }}</span>
                            @if($item->user?->seatUsers?->isNotEmpty())
                                <span class="text-[11px] font-medium text-blue-500">{{ $item->user->seatUsers->first()->seat?->seat_name ?? '' }}</span>
                            @endif
                        </div>
                    </td>
                    @endif
                    @if(($filter ?? request('filter')) === 'returned')
                    <td class="px-5 py-3.5">
                        @if($item->is_returned_to_inward)
                            <div class="max-w-xs space-y-1">
                                <div class="bg-rose-50 border border-rose-200/80 rounded-xl px-3 py-2 text-xs shadow-sm">
                                    <div class="flex items-start gap-1.5">
                                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-rose-500 mt-0.5 shrink-0"></i>
                                        <span class="font-semibold text-rose-900 leading-snug break-words">
                                            {{ $item->return_reason ?? 'No remarks provided' }}
                                        </span>
                                    </div>
                                </div>
                                @if($item->returnedByUser || $item->returned_at)
                                    <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5 px-1">
                                        @if($item->returnedByUser)
                                            <span class="font-medium text-slate-600">
                                                By: {{ $item->returnedByUser->name }}
                                            </span>
                                        @endif
                                        @if($item->returned_at)
                                            <span class="text-slate-400">
                                                {{ \Carbon\Carbon::parse($item->returned_at)->format('d M, Y h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-slate-300 font-bold">—</span>
                        @endif
                    </td>
                    @endif
                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                        @if($item->is_returned_to_inward)
                            <div class="inline-flex items-center justify-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider shrink-0">
                                    Returned
                                </span>
                                <button type="button" @click="$dispatch('open-reassign-modal', { 
                                        id: '{{ $item->hashid }}', 
                                        receipt: {{ json_encode($item->receipt_no) }},
                                        reason: {{ json_encode($item->return_reason ?? '') }},
                                        by: {{ json_encode($item->returnedByUser?->name ?? '') }},
                                        at: {{ json_encode($item->returned_at ? \Carbon\Carbon::parse($item->returned_at)->format('d M, Y h:i A') : '') }},
                                        seat: {{ json_encode($item->seat?->seat_name ?? '') }}
                                    })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 hover:text-amber-800 hover:bg-amber-100 rounded-lg transition-colors shadow-sm text-[10px] font-bold border border-amber-200 uppercase tracking-wider shrink-0" title="Re-transfer to another seat">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Re-transfer</span>
                                </button>
                                <button type="button" @click="$dispatch('open-delete-modal', { 
                                        id: '{{ $item->hashid }}', 
                                        receipt: {{ json_encode($item->receipt_no) }},
                                        reason: {{ json_encode($item->return_reason ?? '') }}
                                    })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-600 hover:text-rose-700 hover:bg-rose-100 rounded-lg transition-colors shadow-sm text-[10px] font-bold border border-rose-200 uppercase tracking-wider shrink-0" title="Delete this returned petition">
                                    <i data-lucide="trash-2" class="w-3 h-3 text-rose-500"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        @elseif($item->is_cpsp_processed)
                            @if(in_array($item->status, ['Closed', 'Sent_to_Govt', 'Internal_Vigilance']))
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Finalized
                                </span>
                            @elseif($item->status === 'VR_Received')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    VR Received
                                </span>
                            @elseif($item->status === 'Forwarded')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Forwarded
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Processed
                                </span>
                            @endif
                            @if($item->processedBySeat || $item->processedByUser)
                                <span class="block text-[10px] text-emerald-700 font-semibold mt-1">
                                    {{ $item->processedBySeat->seat_name ?? '' }} ({{ $item->processedByUser->name ?? '' }})
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Transferred
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    @php
                        $colspan = 7;
                        if ($showEnteredBy) $colspan++;
                        if (($filter ?? request('filter')) === 'returned') $colspan++;
                    @endphp
                    <td colspan="{{ $colspan }}" class="px-6 py-10 text-center text-slate-400">
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
