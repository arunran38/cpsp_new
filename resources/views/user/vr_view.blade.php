@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-500"></div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="file-check" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Verification Reports</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Petitions with uploaded Verification Reports ready for Decision</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Petition No</th>
                        <th class="px-6 py-4 font-bold">Unit</th>
                        <th class="px-6 py-4 font-bold">VR Reference NO</th>
                        <th class="px-6 py-4 font-bold">VR Date</th>
                        <th class="px-6 py-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($petitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $petition->petition_no }}</td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $petition->latestForwarding->toUnit->unit_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $petition->latestForwarding->vr_ref_no ?? 'Pending' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $petition->latestForwarding->vr_date ? \Carbon\Carbon::parse($petition->latestForwarding->vr_date)->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('petitions.show', $petition->petition_id) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                                    Review VR
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                No verification reports available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
