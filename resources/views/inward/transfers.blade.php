@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-6" x-data="{ showReassignModal: false, reassignPetitionId: null, reassignReceiptNo: '', currentSeatName: '' }">
    <!-- Page Header -->
    <div class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-600 via-teal-500 to-indigo-600"></div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                <i data-lucide="arrow-right-left" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inward File Transfers</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        view_file_transfer
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Table of inward petition registrations and transfers to target concerned seats.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()->canAccess('create petitions'))
            <a href="{{ route('inward.enter_petition') }}" 
               class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                New Inward Entry
            </a>
            @endif
        </div>
    </div>

    <!-- Unified Tabs -->
    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl mb-4 w-fit border border-slate-200 shadow-sm">
        <a href="{{ route('inward.transfers') }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all bg-white text-indigo-600 shadow-sm">
            Inward Petition
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'all']) }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700 hover:bg-white/50">
            All Petitions
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'received']) }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700 hover:bg-white/50">
            New Petitions
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'forwarded']) }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700 hover:bg-white/50">
            Forwarded Petitions
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'vrs']) }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700 hover:bg-white/50">
            Verification Reports Received
        </a>
        <a href="{{ route('petitions.index', ['tab' => 'decisions']) }}"
            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700 hover:bg-white/50">
            Final Decisions
        </a>
    </div>

    <!-- Filter Pills: All / Active / Returned to Inward -->
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('inward.transfers') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ !request('filter') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Transferred
        </a>
        <a href="{{ route('inward.transfers', array_merge(request()->query(), ['filter' => 'active'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request('filter') === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Active in CPSP
        </a>
        @php
            $returnedCount = \App\Models\Petition::where('is_returned_to_inward', true)->count();
        @endphp
        <a href="{{ route('inward.transfers', array_merge(request()->query(), ['filter' => 'returned'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request('filter') === 'returned' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
            <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
            Returned from CPSP (തിരികെ വന്നവ)
            @if($returnedCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request('filter') === 'returned' ? 'bg-white text-rose-700' : 'bg-rose-600 text-white' }}">{{ $returnedCount }}</span>
            @endif
        </a>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('inward.transfers') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Keyword -->
            <div class="md:col-span-1">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}" 
                           placeholder="Receipt No, Complainant, Seat..." 
                           class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-600 outline-none transition-all">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
                <input type="date" 
                       name="date_from" 
                       id="date_from" 
                       value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-600 outline-none transition-all">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
                <input type="date" 
                       name="date_to" 
                       id="date_to" 
                       value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-600 outline-none transition-all">
            </div>

            <!-- Filter Action Buttons -->
            <div class="md:col-span-3 flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 py-2 px-4 text-xs font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filter
                </button>

                @if(request()->anyFilled(['search', 'date_from', 'date_to', 'status']))
                <a href="{{ route('inward.transfers') }}" 
                   class="py-2 px-4 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center gap-1">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                <h2 class="text-sm font-bold text-slate-800">Transferred Petitions List</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">Total: {{ $petitions->total() }} records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-5 py-3 text-center w-12">#</th>
                        <th class="px-5 py-3">Receipt / Inward No</th>
                        <th class="px-5 py-3">Date of Receipt</th>
                        <th class="px-5 py-3">Complainant</th>
                        <th class="px-5 py-3">Mode of Receipt</th>
                        <th class="px-5 py-3">Concerned Seat</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($petitions as $index => $petition)
                        @php
                            $complainant = $petition->addresses->firstWhere('person_type', 'Complainant');
                            $seatUser = $petition->seat?->activeAssignment?->user;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 text-center font-medium text-slate-400">
                                {{ $petitions->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-indigo-900 block">{{ $petition->receipt_no }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">ID: #{{ $petition->petition_id }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d-m-Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $complainant?->person_name ?? 'N/A' }}</span>
                                @if($complainant?->phone)
                                    <span class="text-[10px] text-slate-500">{{ $complainant->phone }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $petition->mode_of_petition_received }}
                                </span>
                                @if(strtolower($petition->mode_of_petition_received) === 'unit' && ($petition->unit || $petition->date_of_petition_received_at_unit))
                                    <div class="mt-1 text-[11px] text-slate-600">
                                        @if($petition->unit)
                                            <span class="font-semibold text-indigo-700 block">{{ $petition->unit->unit_name }}</span>
                                        @endif
                                        @if($petition->date_of_petition_received_at_unit)
                                            <span class="text-[10px] text-slate-500 block">
                                                Recd: {{ \Carbon\Carbon::parse($petition->date_of_petition_received_at_unit)->format('d-m-Y') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($petition->seat)
                                    <span class="font-semibold text-indigo-700 block">{{ $petition->seat->seat_name }}</span>
                                    <span class="text-[10px] text-slate-500 block">
                                        Occupant: {{ $seatUser ? $seatUser->name : '(Vacant)' }}
                                    </span>
                                    @if($petition->is_returned_to_inward)
                                        <div class="mt-1.5 p-2 rounded-xl bg-rose-50 border border-rose-200 text-left">
                                            <div class="flex items-center gap-1 text-[11px] font-bold text-rose-700">
                                                <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
                                                Returned from CPSP
                                            </div>
                                            <p class="text-[11px] text-rose-800 font-medium mt-0.5" title="{{ $petition->return_reason }}">
                                                <span class="font-bold text-rose-900">Reason:</span> {{ \Illuminate\Support\Str::limit($petition->return_reason, 50) }}
                                            </p>
                                            <span class="text-[9px] text-slate-500 block mt-0.5">
                                                By {{ $petition->returnedByUser?->name ?? 'CPSP User' }} on {{ $petition->returned_at?->format('d-m-Y h:i A') }}
                                            </span>
                                        </div>
                                    @elseif($petition->cpsp_opened_at)
                                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100" title="Opened by {{ $petition->cpspOpenedBySeat?->seat_name ?? 'CPSP' }} ({{ $petition->cpspOpenedByUser?->name ?? '' }})">
                                            <i data-lucide="eye" class="w-3 h-3 text-indigo-500"></i>
                                            Opened: {{ $petition->cpsp_opened_at->format('d-m-Y h:i A') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100">
                                            <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i>
                                            Not Opened Yet
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Not Assigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($petition->is_returned_to_inward)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Returned
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $petition->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right flex items-center justify-end gap-2 flex-wrap">
                                @if($petition->is_returned_to_inward)
                                    <button type="button" 
                                            @click="reassignPetitionId = {{ $petition->petition_id }}; reassignReceiptNo = '{{ $petition->receipt_no }}'; currentSeatName = '{{ $petition->seat?->seat_name ?? '' }}'; showReassignModal = true"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-all whitespace-nowrap"
                                            title="Reassign to another Concerned Seat">
                                        <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                                        Reassign Seat
                                    </button>
                                @endif
                                <a href="{{ route('petitions.show', $petition->petition_id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-100 transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    View
                                </a>
                                @can('update', $petition)
                                <a href="{{ route('petitions.edit', $petition->petition_id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition-all">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    Edit
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-semibold text-slate-500">No transferred inward petitions found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($petitions->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $petitions->links() }}
        </div>
        @endif
    </div>

    <!-- Reassign Modal -->
    <div x-show="showReassignModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showReassignModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showReassignModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showReassignModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                
                <form :action="'{{ url('inward/petitions') }}/' + reassignPetitionId + '/reassign'" method="POST">
                    @csrf
                    <div class="bg-indigo-50/70 px-6 py-4 border-b border-indigo-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                <i data-lucide="arrow-right-left" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Reassign Concerned Seat</h3>
                                <p class="text-xs text-indigo-700 font-medium">പുതിയ സീറ്റിലേക്ക് മാറ്റുക</p>
                            </div>
                        </div>
                        <button type="button" @click="showReassignModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-white/80 transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Receipt No:</span>
                                <span class="font-bold text-indigo-900 text-sm font-mono" x-text="reassignReceiptNo"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Previously Assigned To:</span>
                                <span class="font-semibold text-rose-700" x-text="currentSeatName || 'Not Assigned'"></span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="target_seat_id" class="block text-sm font-bold text-slate-700">
                                Select New Concerned Seat (പുതിയ ബന്ധപ്പെട്ട സീറ്റ്) <span class="text-rose-500">*</span>
                            </label>
                            <select name="seat_id" 
                                    id="target_seat_id" 
                                    required 
                                    class="block w-full px-4 py-2.5 text-sm font-medium bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all">
                                <option value="">-- Select Concerned Seat --</option>
                                @foreach($seatsList as $seatItem)
                                    @php
                                        $occupantName = $seatItem->activeAssignment?->user?->name;
                                    @endphp
                                    <option value="{{ $seatItem->seat_id }}">
                                        {{ $seatItem->seat_name }} {{ $occupantName ? "($occupantName)" : '(Vacant)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showReassignModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Confirm Reassign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
