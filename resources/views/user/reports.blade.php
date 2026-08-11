@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div x-data="{ showForwardModal: false, showVrModal: false, showDecisionModal: false, activePetitionId: null, activeForwardingId: null }"
        class="space-y-4">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-4 py-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900"> Reports</h1>
                    <p class="text-sm text-slate-500 mt-1">Filter and analyze petition data</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('petitions.export', request()->query()) }}" id="exportButton"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-all font-semibold text-sm shadow-sm">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    Excel Export
                </a>
                @if(auth()->user()->role === 'user')
                    <a href="{{ route('petitions.create') }}"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> New Petition
                    </a>
                @endif
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-indigo-500"></i>
                    Filter Petitions
                </h3>
                <form id="searchForm" method="GET" action="{{ route('petitions.reports') }}"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Search</label>
                        <div class="flex gap-2 w-full" style="width: 100%;">
                            <div class="relative flex items-center" style="width: 30%; min-width: 120px; flex-shrink: 0;">
                                <select name="search_type"
                                    class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm font-semibold text-slate-700 py-2 transition-all bg-white appearance-none pr-8">
                                    <option value="" class="font-medium text-slate-800 bg-white py-1">All Petitions</option>
                                    <option value="complainant" {{ request('search_type') == 'complainant' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Complainant</option>
                                    <option value="suspect" {{ request('search_type') == 'suspect' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Suspect (Accused)</option>
                                    <option value="firm" {{ request('search_type') == 'firm' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Firm / Project</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-blue-600 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                            <div class="relative flex items-center" style="width: 70%; flex-grow: 1;">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3 text-blue-600"></i>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="w-full pl-9 pr-3 py-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm transition-all bg-white text-black font-semibold"
                                    placeholder="Petition No, Petitioner, Accused, Firm/Project, Phone, or PEN..."
                                    style="color: #000000 !important;">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Date
                            From</label>
                        <input type="date" placeholder="DD-MM-YYYY" name="date_from" value="{{ request('date_from') }}" max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                            class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2 transition-all bg-white text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Date
                            To</label>
                        <input type="date" placeholder="DD-MM-YYYY" name="date_to" value="{{ request('date_to') }}" max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}"
                            class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm py-2 transition-all bg-white text-slate-800 font-semibold"
                            style="color: #1e40af !important;">
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Status /
                            Timeline</label>
                        <div class="relative flex items-center">
                            <select name="status"
                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm font-medium text-slate-900 py-2 transition-all bg-white appearance-none pr-10">
                                <option value="" class="font-medium text-slate-800 bg-white py-1">All Petitions</option>
                                <optgroup label="Petition Timeline" class="font-bold text-slate-900 bg-slate-50">
                                    <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}
                                        class="font-medium text-slate-800 bg-white py-1">Received</option>
                                    <option value="Forwarded" {{ request('status') == 'Forwarded' ? 'selected' : '' }}
                                        class="font-medium text-slate-800 bg-white py-1">Forwarded</option>
                                    <option value="VR_Received" {{ request('status') == 'VR_Received' ? 'selected' : '' }}
                                        class="font-medium text-slate-800 bg-white py-1">Verification Report Received</option>
                                    <option value="VR_Received_at_cpsp_date" {{ request('status') == 'VR_Received_at_cpsp_date' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Verification Report
                                        Received at CPSP</option>
                                </optgroup>
                                <optgroup label="Final Decisions" class="font-bold text-slate-900 bg-slate-50">
                                    <option value="All_Final_Decisions" {{ request('status') == 'All_Final_Decisions' ? 'selected' : '' }} class="font-medium text-indigo-600 bg-indigo-50/50 py-1.5 font-bold">All Final Decisions</option>
                                    <option value="VC" {{ request('status') == 'VC' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Vigilance Case (VC)</option>
                                    <option value="VE" {{ request('status') == 'VE' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Vigilance Enquiry (VE)</option>
                                    <option value="PE" {{ request('status') == 'PE' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Preliminary Enquiry (PE)</option>
                                    <option value="SC" {{ request('status') == 'SC' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Surprise Check (SC)</option>
                                    <option value="CV" {{ request('status') == 'CV' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Confidential Verification (CV)</option>
                                    <option value="ICell" {{ request('status') == 'ICell' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Intelligence Cell (I Cell)</option>
                                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Closed</option>
                                    <option value="Sent to Govt" {{ request('status') == 'Sent to Govt' ? 'selected' : '' }} class="font-medium text-slate-800 bg-white py-1">Sent to Govt</option>
                                </optgroup>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Nature of
                            Petition</label>
                        <div class="relative flex items-center">
                            <select name="nature_of_petition"
                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm font-medium text-slate-900 py-2 transition-all bg-white appearance-none pr-10"
                                style="background-image: none !important;">
                                <option value="" class="font-medium text-slate-800 bg-white py-1">All Natures</option>
                                @foreach(['Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence', 'others'] as $nature)
                                    <option value="{{ $nature }}" {{ request('nature_of_petition') == $nature ? 'selected' : '' }}
                                        class="font-medium text-slate-800 bg-white py-1">{{ $nature }}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Mode of
                            Receipt</label>
                        <div class="relative flex items-center">
                            <select name="mode_of_petition"
                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm font-medium text-slate-900 py-2 transition-all bg-white appearance-none pr-10"
                                style="background-image: none !important;">
                                <option value="" class="font-medium text-slate-800 bg-white py-1">All Modes</option>
                                @foreach(['Direct', 'Email', 'Whatsapp', 'Tollfree', 'others'] as $mode)
                                    <option value="{{ $mode }}" {{ request('mode_of_petition') == $mode ? 'selected' : '' }}
                                        class="font-medium text-slate-800 bg-white py-1">{{ $mode }}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin' && isset($seats))
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Filter by
                                Seat</label>
                            <div class="relative flex items-center">
                                <select name="seat_id"
                                    class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/10 text-sm font-medium text-slate-900 py-2 transition-all bg-white appearance-none pr-10">
                                    <option value="" class="font-medium text-slate-800 bg-white py-1">All Seats</option>
                                    @foreach($seats as $seat)
                                        <option value="{{ $seat->seat_id }}" {{ request('seat_id') == $seat->seat_id ? 'selected' : '' }}
                                            class="font-medium text-slate-800 bg-white py-1">{{ $seat->seat_name }}</option>
                                    @endforeach
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    @endif

                    <div style="z-index: 10;">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Department</label>
                        <x-searchable-select name="department_id" :options="$departments"
                            :selected="request('department_id')" placeholder="All Departments" />
                    </div>

                    <div class="flex items-center gap-2 h-[38px]">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Search
                        </button>
                        <a href="{{ route('petitions.reports') }}"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-sm transition-all flex items-center gap-2 shadow-sm whitespace-nowrap"
                            title="Clear all filters">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Dynamic Table Container -->
            <div id="tableContainer">
                @include('user.partials.reports_table', ['petitions' => $petitions, 'tab' => $tab])
            </div>
        </div>

        <!-- Modals (Copied from petition_view for full functionality) -->
        <!-- Forward Modal -->
        <div x-show="showForwardModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
            <div @click.away="showForwardModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="send"
                            class="w-5 h-5 text-indigo-600"></i> Petition Decision</h3>
                    <button type="button" @click="showForwardModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('forwardings.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="petition_id" :value="activePetitionId">
                    <div x-data="{ action: '' }" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Action</label>
                            <div class="relative flex items-center">
                                <select name="action" x-model="action"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm appearance-none pr-10 text-black"
                                        style="background-image: none !important;"
                                        required>
                                    <option value="">Select Action...</option>
                                    <option value="Forward_To_Unit">Forward to Unit</option>
                                    <option value="Sent_to_Govt">Send to Govt (Decision)</option>
                                    <option value="Close">Close Petition (Decision)</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                        <div x-show="action === 'Forward_To_Unit'" x-cloak class="pt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Select Unit</label>
                            <div class="relative flex items-center">
                                <select name="to_unit_id"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm appearance-none pr-10 text-black"
                                    style="background-image: none !important;"
                                    :required="action === 'Forward_To_Unit'">
                                    <option value="">Select Unit...</option>
                                    @foreach(\App\Models\Unit::all() as $unit)
                                        <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                                    @endforeach
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Action Date</label>
                            <input type="date" name="forwarded_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-[9px] text-slate-800 font-semibold"
                                style="color: #1e40af !important;" required>
                        </div>

                        <div class="pt-2 border-t border-slate-100 mt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Director Remarks</label>
                            <textarea name="director_remarks" rows="3"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                placeholder="Enter instructions or remarks..." required></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-6">
                            <button type="button" @click="showForwardModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2">
                                Submit Action <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- VR Modal -->
        <div x-show="showVrModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
            <div @click.away="showVrModal = false" class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="file-check"
                            class="w-5 h-5 text-blue-600"></i> Update Verification Report</h3>
                    <button type="button" @click="showVrModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form :action="'{{ url('forwardings') }}/' + activeForwardingId + '/vr'" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Ref No</label>
                            <input type="text" name="vr_ref_no"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-black"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">VR Date</label>
                            <input type="date" placeholder="DD-MM-YYYY" name="vr_date"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                style="color: #1e40af !important;"
                                required max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">VR Recommendation</label>
                        <div class="relative flex items-center">
                            <select name="vr_remarks"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm appearance-none pr-10 text-black"
                                style="background-image: none !important;"
                                required>
                                <option value="">Select Recommendation...</option>
                                <option value="VC">Vigilance Case (VC)</option>
                                <option value="VE">Vigilance Enquiry (VE)</option>
                                <option value="PE">Preliminary Enquiry (PE)</option>
                                <option value="SC">Surprise Check (SC)</option>
                                <option value="CV">Confidential Verification (CV)</option>
                                <option value="ICell">Intelligence Cell (I Cell)</option>
                                <option value="Closed">Closed</option>
                                <option value="Sent to Govt">Sent to Govt</option>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload Report</label>
                            <input type="file" name="vr_file"
                                class="w-full text-sm text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-blue-600 mb-1 uppercase text-[11px] font-bold tracking-wider">Received
                                in CPSP Date</label>
                            <input type="date" placeholder="DD-MM-YYYY" name="vr_received_at_cpsp_date"
                                class="w-full rounded-lg border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-1.5 bg-blue-50/20 text-slate-800 font-semibold px-3"
                                style="color: #1e40af !important;"
                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showVrModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit VR <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Final Decision Modal -->
        <div x-show="showDecisionModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 backdrop-blur-sm">
            <div @click.away="showDecisionModal = false"
                class="relative w-full max-w-md p-6 bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i data-lucide="check-square"
                            class="w-5 h-5 text-rose-600"></i> Final Decision</h3>
                    <button type="button" @click="showDecisionModal = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form action="{{ route('decisions.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="petition_id" :value="activePetitionId">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Final Decision</label>
                        <select name="decision_remarks"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-black"
                            required>
                            <option value="">Select...</option>
                            <option value="VC">Vigilance Case (VC)</option>
                            <option value="VE">Vigilance Enquiry (VE)</option>
                            <option value="PE">Preliminary Enquiry (PE)</option>
                            <option value="SC">Surprise Check (SC)</option>
                            <option value="CV">Confidential Verification (CV)</option>
                            <option value="ICell">Intelligence Cell (I Cell)</option>
                            <option value="Closed">Closed</option>
                            <option value="Sent to Govt">Sent to Govt</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Decision Date</label>
                        <input type="date" name="decision_date" max="{{ date('Y-m-d') }}" placeholder="DD-MM-YYYY"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-slate-800 font-semibold"
                            style="color: #1e40af !important;" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Final Remarks</label>
                        <textarea name="final_remarks" rows="3"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm text-black"
                            placeholder="Director's final remarks..." required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showDecisionModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 border border-transparent rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
                            Submit Decision <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('searchForm');
            if (!searchForm) return;

            let timer = null;
            let abortController = null;

            const performSearch = () => {
                const formData = new FormData(searchForm);
                const searchParams = new URLSearchParams(formData);
                const url = `${searchForm.action}?${searchParams.toString()}`;

                // Update URL without reloading
                window.history.pushState({}, '', url);

                // Abort previous request
                if (abortController) abortController.abort();
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
                        // Replace table container content directly with the returned partial
                        const currentTable = document.getElementById('tableContainer');
                        if (currentTable) {
                            currentTable.innerHTML = html;

                            // Update Export Button URL
                            const exportBtn = document.getElementById('exportButton');
                            if (exportBtn) {
                                const exportUrl = new URL('{{ route('petitions.export') }}', window.location.origin);
                                // Append all current search params to the export URL
                                searchParams.forEach((value, key) => exportUrl.searchParams.set(key, value));
                                exportBtn.href = exportUrl.toString();
                            }

                            if (window.lucide) window.lucide.createIcons();
                        }
                    })
                    .catch(error => {
                        if (error.name !== 'AbortError') console.error('Error:', error);
                    });
            };

            // Debounce text inputs
            searchForm.querySelectorAll('input[type="text"]').forEach(input => {
                input.addEventListener('keyup', () => {
                    clearTimeout(timer);
                    timer = setTimeout(performSearch, 500);
                });
            });

            // Instant filter for select and date
            searchForm.querySelectorAll('select, input[type="date"]').forEach(input => {
                input.addEventListener('change', performSearch);
            });

            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                performSearch();
            });
        });
    </script>
@endsection
