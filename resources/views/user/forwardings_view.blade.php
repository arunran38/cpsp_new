@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-amber-500"></div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 border border-amber-100">
                <i data-lucide="send" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Forwarded Petitions</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Petitions currently under investigation by Units</p>
            </div>
        </div>
    </div>

        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <form id="searchForm" method="GET" action="{{ route('petitions.reports.forwarded') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Search Petitions</label>
                    <div class="relative flex items-center">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-9 pr-3 py-[9px] rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black" placeholder="Receipt No, Petitioner Name, or Accused Name...">
                    </div>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                    <input type="date" placeholder="DD-MM-YYYY" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold" style="color: #1e40af !important;">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                    <input type="date" placeholder="DD-MM-YYYY" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold" style="color: #1e40af !important;">
                </div>
                <div class="flex-none">
                    <label class="block text-xs font-medium text-transparent mb-1">&nbsp;</label>
                    <a href="{{ route('petitions.reports.forwarded') }}" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold px-4 py-[9px] rounded-lg text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap" title="Clear all filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold w-12 text-center">#</th>
                        <th class="px-6 py-4 font-bold">Receipt No</th>
                        <th class="px-6 py-4 font-bold">Petitioner</th>
                        <th class="px-6 py-4 font-bold">Suspect</th>
                        <th class="px-6 py-4 font-bold">Forwarded To</th>
                        <th class="px-6 py-4 font-bold">Director Remarks</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($petitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-slate-500">
                                {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800">{{ $petition->receipt_no }}</span>
                                <span class="block text-[10px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @php
                                    $complainants = $petition->addresses->where('person_type', 'Complainant')->unique('person_name');
                                @endphp
                                @if($complainants->count() > 0)
                                    @foreach($complainants as $complainant)
                                        <span class="block font-semibold {{ !$loop->first ? 'mt-1 text-xs' : '' }}">{{ $complainant->person_name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 italic">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @php
                                    $accused = $petition->addresses->where('person_type', 'Accused')->unique('person_name');
                                @endphp
                                @if($accused->count() > 0)
                                    @foreach($accused as $accuse)
                                        <span class="block font-semibold {{ !$loop->first ? 'mt-1 text-xs' : '' }}">{{ $accuse->person_name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 italic">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                        <i data-lucide="building-2" class="w-4 h-4"></i>
                                    </div>
                                    <span class="font-medium text-slate-700">{{ $petition->latestForwarding->toUnit->unit_name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-xs text-slate-600 italic line-clamp-2" title="{{ $petition->latestForwarding->director_remarks ?? '' }}">
                                    "{{ $petition->latestForwarding->director_remarks ?? 'No remarks provided' }}"
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($petition->latestForwarding && $petition->latestForwarding->vr_ref_no)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                                        VR RECEIVED
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200">
                                        PENDING VR
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('petitions.show', $petition->petition_id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="View Details">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3 text-slate-200"></i>
                                    <p class="text-lg font-medium text-slate-500">No forwarded petitions found</p>
                                    <p class="text-sm">Petitions will appear here once they are forwarded to units.</p>
                                </div>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        if (!searchForm) return;
        
        let timer = null;
        let abortController = null;

        const performSearch = () => {
            const formData = new FormData(searchForm);
            const searchParams = new URLSearchParams(formData);
            const url = `${searchForm.action}?${searchParams.toString()}`;

            window.history.pushState({}, '', url);

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: abortController.signal
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const currentTable = document.querySelector('.overflow-x-auto');
                const newTable = doc.querySelector('.overflow-x-auto');
                if (currentTable && newTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }

                const currentPagination = document.querySelector('.px-6.py-4.border-t.border-slate-200');
                const newPagination = doc.querySelector('.px-6.py-4.border-t.border-slate-200');
                if (currentPagination && newPagination) {
                    currentPagination.innerHTML = newPagination.innerHTML;
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Error fetching search results:', error);
                }
            });
        };

        const textInputs = searchForm.querySelectorAll('input[type="text"]');
        textInputs.forEach(input => {
            input.addEventListener('keyup', (e) => {
                clearTimeout(timer);
                timer = setTimeout(performSearch, 500);
            });
        });

        const changeInputs = searchForm.querySelectorAll('input[type="date"], select');
        changeInputs.forEach(input => {
            input.addEventListener('change', performSearch);
        });
        
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    });
</script>
@endsection

