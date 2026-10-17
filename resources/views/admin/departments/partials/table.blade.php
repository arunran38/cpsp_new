    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">Sl No</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Department Name</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Total Petitions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($analysis as $index => $data)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-6 text-sm text-slate-500">
                            {{ $analysis->firstItem() + $index }}
                        </td>
                        <td class="py-4 px-6 text-sm font-semibold">
                            <a href="{{ route('petitions.index', ['department_id' => $data->department_id]) }}" class="text-slate-900 hover:text-indigo-600 transition-colors" title="View Petitions for {{ $data->department_name }}">
                                {{ $data->department_name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <a href="{{ route('petitions.index', ['department_id' => $data->department_id]) }}" class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-1 text-xs font-bold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 hover:text-indigo-800 transition-colors rounded-full cursor-pointer" title="View Petitions">
                                {{ $data->total_petitions }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="bar-chart-2" class="w-12 h-12 text-slate-300 mb-3"></i>
                                <p class="text-base font-medium">No departmental data available</p>
                                <p class="text-sm text-slate-400 mt-1">There are no petitions assigned to any department yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($analysis->count() > 0)
            <tfoot class="bg-slate-100 border-t-2 border-slate-200">
                <tr>
                    <td colspan="2" class="py-4 px-6 text-sm font-bold text-right text-slate-700 uppercase">
                        Total Petitions:
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1.5 text-sm font-extrabold bg-slate-800 text-white rounded-full shadow-sm">
                            {{ $total_sum }}
                        </span>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    
    @if($analysis->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $analysis->links() }}
        </div>
    @endif
