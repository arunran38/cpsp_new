@php
    $districtBadgeStyles = [
        'TVM' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'KLM' => 'bg-sky-50 text-sky-700 border-sky-200',
        'PTA' => 'bg-amber-50 text-amber-800 border-amber-200',
        'ALP' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'KTM' => 'bg-rose-50 text-rose-700 border-rose-200',
        'IDK' => 'bg-teal-50 text-teal-700 border-teal-200',
        'EKM' => 'bg-blue-50 text-blue-700 border-blue-200',
        'TSR' => 'bg-purple-50 text-purple-700 border-purple-200',
        'PKD' => 'bg-orange-50 text-orange-800 border-orange-200',
        'MPM' => 'bg-green-50 text-green-700 border-green-200',
        'KKD' => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
        'WYD' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
        'KNR' => 'bg-red-50 text-red-700 border-red-200',
        'KSD' => 'bg-violet-50 text-violet-700 border-violet-200',
        'OTH' => 'bg-slate-100 text-slate-700 border-slate-300',
    ];
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider w-16 text-center">Sl No</th>
                <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">District Name</th>
                <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-48">Total Petitions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($districtStats as $index => $stat)
                @php
                    $isNumericDistrict = is_numeric($stat['district_id']);
                    $drillDownParams = array_filter([
                        'concerned_district_id' => $isNumericDistrict ? $stat['district_id'] : null,
                        'date_from' => $filters['date_from'] ?? null,
                        'date_to' => $filters['date_to'] ?? null,
                        'status' => $filters['status'] ?? null,
                        'nature_of_petition' => $filters['nature_of_petition'] ?? null,
                        'mode_of_petition' => $filters['mode_of_petition'] ?? null,
                    ]);
                    $drillDownUrl = route('petitions.reports', $drillDownParams);
                    $code = $stat['district_code'] ?? '';
                    $badgeStyle = $districtBadgeStyles[$code] ?? 'bg-blue-50 text-blue-700 border-blue-200';
                @endphp
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 text-sm text-slate-500 text-center">
                        {{ $index + 1 }}
                    </td>
                    <td class="py-4 px-6 text-sm font-semibold">
                        <div class="flex items-center gap-2.5">
                            <span class="text-slate-900 font-semibold">{{ $stat['district_name'] }}</span>
                            @if(!empty($code))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md font-mono text-xs font-bold border tracking-wider {{ $badgeStyle }}">
                                    {{ $code }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($isNumericDistrict)
                            <a href="{{ $drillDownUrl }}"
                                class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 text-xs font-bold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 hover:text-indigo-800 transition-colors rounded-full cursor-pointer"
                                title="View Petitions for {{ $stat['district_name'] }}">
                                {{ number_format($stat['petition_count']) }}
                            </a>
                        @else
                            <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded-full">
                                {{ number_format($stat['petition_count']) }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="bar-chart-2" class="w-12 h-12 text-slate-300 mb-3"></i>
                            <p class="text-base font-medium">No district data available</p>
                            <p class="text-sm text-slate-400 mt-1">There are no petitions found for the selected filter criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($districtStats) > 0)
            <tfoot class="bg-slate-100 border-t-2 border-slate-200">
                <tr>
                    <td colspan="2" class="py-4 px-6 text-sm font-bold text-right text-slate-700 uppercase">
                        Total Petitions:
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1.5 text-sm font-extrabold bg-slate-800 text-white rounded-full shadow-sm">
                            {{ number_format($grandTotal) }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
