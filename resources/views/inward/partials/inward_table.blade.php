@if(isset($counts))
    <div id="counts-data" data-all="{{ $counts['all'] ?? 0 }}" data-transferred="{{ $counts['transferred'] ?? 0 }}" data-returned="{{ $counts['returned'] ?? 0 }}" style="display:none;"></div>
@endif
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
                @if(Auth::user()->canAccess('access admin dashboard'))
                    <th class="px-6 py-4">Entered By (Inward)</th>
                @endif
                <th class="px-6 py-4 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-sm">
            @forelse($transfers ?? [] as $index => $item)
                @php
                    $complainant = $item->addresses->firstWhere('person_type', 'Complainant');
                    $seatUser = $item->seat?->activeAssignment?->user;
                @endphp
            <tr class="hover:bg-blue-50/30 transition-colors {{ $loop->first && session('success') ? 'bg-indigo-50/40' : '' }}">
                    <td class="px-5 py-3.5 text-center font-bold text-blue-600">
                        {{ method_exists($transfers, 'firstItem') ? ($transfers->firstItem() + $index) : ($index + 1) }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col">
                            <span class="font-bold text-blue-800 block">{{ $item->receipt_no }}</span>
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
                        @if(strtolower($item->mode_of_petition_received) === 'unit' && $item->unit)
                            <span class="block text-[11px] text-indigo-600 font-semibold mt-1">{{ $item->unit->unit_name }}</span>
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
                    @if(Auth::user()->canAccess('access admin dashboard'))
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col">
                            <span class="font-bold text-blue-700">{{ $item->user?->name ?? 'Unknown' }}</span>
                            @if($item->user?->seatUsers?->isNotEmpty())
                                <span class="text-[11px] font-medium text-blue-500">{{ $item->user->seatUsers->first()->seat?->seat_name ?? '' }}</span>
                            @endif
                        </div>
                    </td>
                    @endif
                    <td class="px-5 py-3.5 text-center">
                        @if($item->is_returned_to_inward)
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Returned
                                </span>
                                <button type="button" @click="$dispatch('open-reassign-modal', { id: {{ $item->petition_id }}, receipt: '{{ $item->receipt_no }}' })"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-600 hover:text-rose-700 hover:bg-rose-100 rounded-lg transition-colors shadow-sm text-[10px] font-bold border border-rose-100 uppercase tracking-wider" title="Re-transfer to CPSP">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Re-transfer</span>
                                </button>
                            </div>
                        @elseif($item->is_cpsp_processed)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Processed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Transferred
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
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
