@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                <i data-lucide="check-square" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Final Decisions</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Petitions closed or sent to government</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold w-12 text-center">#</th>
                        <th class="px-6 py-4 font-bold">Petition No</th>
                        <th class="px-6 py-4 font-bold">Final Recommendation</th>
                        <th class="px-6 py-4 font-bold">Final Remarks</th>
                        <th class="px-6 py-4 font-bold">Date Decided</th>
                        <th class="px-6 py-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($petitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-slate-500">
                                {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $petition->petition_no }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                    {{ $petition->decision->decision_remarks ?? $petition->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-slate-600" title="{{ $petition->decision->final_remarks ?? '' }}">
                                {{ $petition->decision->final_remarks ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $petition->decision ? $petition->decision->created_at->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('petitions.show', $petition->petition_id) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No final decisions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $petitions->links() }}
        </div>
    </div>
</div>
@endsection
