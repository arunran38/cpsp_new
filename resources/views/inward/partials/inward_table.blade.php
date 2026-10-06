@if(isset($counts))
    <div id="counts-data" data-all="{{ $counts['all'] ?? 0 }}" data-transferred="{{ $counts['transferred'] ?? 0 }}" data-returned="{{ $counts['returned'] ?? 0 }}" style="display:none;"></div>
@endif
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr
                class="bg-slate-100/70 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                <th class="px-5 py-3 text-center w-12">#</th>
                <th class="px-5 py-3">Receipt No & Date</th>
                <th class="px-5 py-3">Complainant</th>
                <th class="px-5 py-3">Mode of Receipt</th>
                <th class="px-5 py-3">Concerned Seat</th>
                <th class="px-5 py-3">Transferred On</th>
                <th class="px-5 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-sm">
            @forelse($transfers ?? [] as $index => $item)
                @php
                    $complainant = $item->addresses->firstWhere('person_type', 'Complainant');
                    $seatUser = $item->seat?->activeAssignment?->user;
                @endphp
                <tr
                    class="hover:bg-slate-50/80 transition-colors {{ $loop->first && session('success') ? 'bg-indigo-50/40' : '' }}">
                    <td class="px-5 py-3.5 text-center font-medium text-slate-400">
                        {{ method_exists($transfers, 'firstItem') ? ($transfers->firstItem() + $index) : ($index + 1) }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-col">
                            <span class="font-bold text-indigo-900 block font-mono">{{ $item->receipt_no }}</span>
                            <span class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                {{ \Carbon\Carbon::parse($item->date_of_petition_received)->format('d-m-Y') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span
                            class="font-semibold text-slate-900 block">{{ $complainant?->person_name ?? 'N/A' }}</span>
                        @if($complainant?->phone)
                            <span class="text-xs text-slate-500">{{ $complainant->phone }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $item->mode_of_petition_received }}
                        </span>
                        @if(strtolower($item->mode_of_petition_received) === 'unit' && $item->unit)
                            <span
                                class="block text-xs text-indigo-600 font-semibold mt-0.5">{{ $item->unit->unit_name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($item->seat)
                            <span class="font-semibold text-indigo-700 block">{{ $item->seat->seat_name }}</span>
                            <span class="text-xs text-slate-500 block">
                                Occupant: {{ $seatUser ? $seatUser->name : '(Vacant)' }}
                            </span>
                        @else
                            <span class="text-slate-400 italic">Not Assigned</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                        {{ $item->created_at?->format('d-m-Y') }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($item->is_returned_to_inward)
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Returned to Inward
                                </span>
                                <button type="button" @click="$dispatch('open-reassign-modal', { id: {{ $item->petition_id }}, receipt: '{{ $item->receipt_no }}' })"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-600 hover:text-rose-700 hover:bg-rose-100 rounded-lg transition-colors shadow-sm text-[10px] font-bold border border-rose-100 uppercase tracking-wider" title="Re-transfer to CPSP">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Re-transfer</span>
                                </button>
                            </div>
                        @elseif($item->is_cpsp_processed)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Processed by CPSP
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
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
