@extends('layouts.user')

@section('content')
<div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activeForwardingId: null }" class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="list" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Submitted Petitions</h1>
            
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->role === 'user')
                <a href="{{ route('petitions.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Petition
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl space-y-1">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-3 text-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                {{ $error }}
            </div>
        @endforeach
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Advanced Search</h3>
            </div>
            
            <form id="searchForm" method="GET" action="{{ route('petitions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Petition No</label>
                    <input type="text" name="petition_no" value="{{ request('petition_no') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Search...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Complainant Name</label>
                    <input type="text" name="complainant_name" value="{{ request('complainant_name') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Search name...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Respondent Name</label>
                    <input type="text" name="respondent_name" value="{{ request('respondent_name') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Search name...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nature of Petition</label>
                    <input type="text" name="nature_of_petition" value="{{ request('nature_of_petition') }}" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Nature...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Mode of Receipt</label>
                    <select name="mode_of_petition" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Modes</option>
                        <option value="Direct" {{ request('mode_of_petition') == 'Direct' ? 'selected' : '' }}>Direct</option>
                        <option value="Post" {{ request('mode_of_petition') == 'Post' ? 'selected' : '' }}>Post</option>
                        <option value="Email" {{ request('mode_of_petition') == 'Email' ? 'selected' : '' }}>Email</option>
                        <option value="Whatsapp" {{ request('mode_of_petition') == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                        <option value="Tollfree" {{ request('mode_of_petition') == 'Tollfree' ? 'selected' : '' }}>Tollfree</option>
                        <option value="others" {{ request('mode_of_petition') == 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-[9px] rounded-lg text-sm shadow-sm transition-colors flex justify-center items-center gap-1">
                        <i data-lucide="search" class="w-4 h-4"></i> Search
                    </button>
                    <a href="{{ route('petitions.index') }}" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-[9px] rounded-lg text-sm text-center shadow-sm transition-colors block">
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
                        <th class="px-6 py-4 font-bold">Record No</th>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold">Nature / Action</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Processing</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($petitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-slate-500">
                                {{ ($petitions->currentPage() - 1) * $petitions->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800">{{ $petition->petition_no }}</span>
                                <span class="block text-xs text-slate-500 mt-1">{{ count($petition->addresses) }} Parties Linked</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}
                                <span class="block text-xs text-slate-400 mt-1">{{ $petition->mode_of_petition_received }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-slate-700">
                                <span class="font-semibold">{{ $petition->nature_of_petition }}</span>
                                <span class="block text-xs text-slate-500 truncate mt-1" title="{{ $petition->description }}">{{ $petition->description }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($petition->status === 'Closed' || $petition->status === 'Sent_to_Govt')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                        Final Recommendation: {{ $petition->decision->decision_remarks ?? $petition->status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                                        {{ $petition->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->role === 'user')
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
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('petitions.show', $petition->petition_id) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->role === 'user')
                                        <a href="{{ route('petitions.edit', $petition->petition_id) }}" class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit Record">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('petitions.destroy', $petition->petition_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this petition?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Record">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3 text-slate-300"></i>
                                    <p class="text-lg font-medium text-slate-500">No petitions found</p>
                                    @if(auth()->user()->role === 'user')
                                        <p class="text-sm">Create a new petition to get started</p>
                                        <a href="{{ route('petitions.create') }}" class="mt-4 px-4 py-2 text-sm font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-all">
                                            Add Petition
                                        </a>
                                    @endif
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

    <!-- Forward Modal (Alpine.js) -->
    <div x-show="showForwardModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showForwardModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="send" class="w-5 h-5 text-indigo-600"></i> Petition Decision</h3>
                <button type="button" @click="showForwardModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('forwardings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="petition_id" :value="activePetitionId">
                
                <div x-data="{ action: '' }" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Action</label>
                        <select name="action" x-model="action" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">Select Action...</option>
                            <option value="Forward_To_Unit">Forward to Unit</option>
                            <option value="Sent_to_Govt">Send to Govt (Decision)</option>
                            <option value="Close">Close Petition (Decision)</option>
                        </select>
                    </div>

                    <div x-show="action === 'Forward_To_Unit'" x-cloak class="pt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Unit</label>
                        <select name="to_unit_id" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" :required="action === 'Forward_To_Unit'">
                            <option value="">Select Unit...</option>
                            @foreach(\App\Models\Unit::all() as $unit)
                                <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2 border-t border-slate-100 mt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Director Remarks</label>
                        <textarea name="director_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Enter instructions or remarks..." required></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showForwardModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit Action <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- VR Modal (Alpine.js) -->
    <div x-show="showVrModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showVrModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="file-check" class="w-5 h-5 text-blue-600"></i> Update Verification Report</h3>
                <button type="button" @click="showVrModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form :action="'{{ url('forwardings') }}/' + activeForwardingId + '/vr'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Ref No</label>
                        <input type="text" name="vr_ref_no" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Date</label>
                        <input type="date" name="vr_date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">VR Remarks</label>
                    <textarea name="vr_remarks" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Enter findings or notes..." required></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload verification report</label>
                        <input type="file" name="vr_file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 text-blue-600 font-bold">VR received in CPSP date</label>
                        <input type="date" name="vr_received_at_cpsp_date" class="w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" @click="showVrModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                        Submit VR <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Final Decision Modal (Alpine.js) -->
    <div x-show="showDecisionModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showDecisionModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="check-square" class="w-5 h-5 text-rose-600"></i> Final Decision</h3>
                <button type="button" @click="showDecisionModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('decisions.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="petition_id" :value="activePetitionId">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Decision Code</label>
                        <select name="decision_remarks" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm" required>
                            <option value="">Select...</option>
                            <option value="PE">PE</option>
                            <option value="SC">SC</option>
                            <option value="QV">QV</option>
                            <option value="Closed">Closed</option>
                            <option value="Sent to Govt">Sent to Govt</option>
                            <option value="ICell">ICell</option>
                        </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Final Remarks</label>
                    <textarea name="final_remarks" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm" placeholder="Director's final remarks..." required></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" @click="showDecisionModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
                        Submit Decision <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
