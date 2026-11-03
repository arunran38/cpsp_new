@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('title', 'Recycle Bin - Deleted Petitions')

@section('content')
<div class="space-y-6" x-data="recycleBinManager()">
    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-rose-500/15 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/15 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-5 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-4 text-center sm:text-left z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center text-rose-400 shadow-inner shrink-0">
                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 justify-center sm:justify-start">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                            Recycle Bin
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            {{ $totalTrashed }} {{ Str::plural('Record', $totalTrashed) }}
                        </span>
                    </div>
                    <p class="text-slate-300 font-medium max-w-2xl text-xs sm:text-sm mt-0.5">
                        Inspect soft-deleted petitions, restore active records, or permanently purge files.
                    </p>
                </div>
            </div>

            @if($totalTrashed > 0)
                <div class="flex items-center gap-2 z-10 text-xs font-bold text-slate-300 shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/60 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>{{ $trashedThisMonth }} this month</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/60 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>{{ $trashedWithFiles }} with files</span>
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Data Card: Filters & Table Unified -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <!-- Compact Filter Toolbar -->
        <div class="p-4 sm:p-5 bg-slate-50/70 border-b border-slate-200">
            <form method="GET" action="{{ route('admin.trash.index') }}" class="flex flex-wrap lg:flex-nowrap items-end gap-3">
                <!-- Search Input -->
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Search Petitions</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Receipt No, File No, Petitioner, Accused..."
                               class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-all text-slate-800 placeholder-slate-400">
                    </div>
                </div>

                <!-- Nature Dropdown -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nature</label>
                    <div class="relative">
                        <select name="nature"
                                class="w-full pl-3 pr-8 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-all text-slate-800 appearance-none cursor-pointer">
                            <option value="">All Natures</option>
                            @foreach($availableNatures as $nat)
                                <option value="{{ $nat }}" {{ ($nature ?? '') === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div class="w-full sm:w-36">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deleted From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-all text-slate-800">
                </div>

                <!-- Date To -->
                <div class="w-full sm:w-36">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deleted To</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-all text-slate-800">
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-xs flex items-center gap-1.5 h-[38px] cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span>Filter</span>
                    </button>

                    @php
                        $hasActiveFilters = !empty($search) || !empty($nature) || !empty($dateFrom) || !empty($dateTo);
                    @endphp
                    @if($hasActiveFilters)
                        <a href="{{ route('admin.trash.index') }}"
                           class="px-3.5 py-2 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition-all flex items-center gap-1.5 h-[38px]"
                           title="Clear Filters">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-5 py-3.5 w-12 text-center">#</th>
                        <th class="px-5 py-3.5">Receipt & Nature</th>
                        <th class="px-5 py-3.5">Petitioner (Complainant)</th>
                        <th class="px-5 py-3.5">Accused / Subject</th>
                        <th class="px-5 py-3.5">Desk & User</th>
                        <th class="px-5 py-3.5">Deleted Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($trashedPetitions as $petition)
                        @php
                            $complainants = $petition->addresses->where('person_type', 'Complainant');
                            $firstComplainant = $complainants->first();
                            $accused = $petition->addresses->where('person_type', 'Accused');
                            $firstAccused = $accused->first();
                            $hasUploads = $petition->uploads->count() > 0;

                            // Prepared data payload for fast Alpine modal preview
                            $previewPayload = [
                                'id' => $petition->petition_id,
                                'receipt_no' => $petition->receipt_no ?? ('ID #' . $petition->petition_id),
                                'file_no' => $petition->file_no,
                                'nature' => $petition->nature_of_petition,
                                'mode' => $petition->mode_of_petition_received,
                                'status' => $petition->status,
                                'date_received' => $petition->date_of_petition_received ? \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') : 'N/A',
                                'deleted_at' => \Carbon\Carbon::parse($petition->deleted_at)->format('d M, Y h:i A'),
                                'deleted_at_human' => \Carbon\Carbon::parse($petition->deleted_at)->diffForHumans(),
                                'creator' => $petition->user->name ?? 'Unknown',
                                'seat' => $petition->seat->seat_name ?? 'N/A',
                                'unit' => $petition->unit->unit_name ?? 'N/A',
                                'description' => $petition->description ?: 'No detailed summary provided.',
                                'proposed_action' => $petition->proposed_action ?: 'None',
                                'complainants' => $complainants->values()->map(fn($c) => [
                                    'name' => $c->person_name ?: 'N/A',
                                    'phone' => $c->phone ?: null,
                                    'email' => $c->email ?: null,
                                    'aadhar' => $c->Aadhar_number ?: null,
                                    'district' => $c->district->district_name ?? null,
                                    'address' => $c->full_address ?: null,
                                    'pincode' => $c->pincode ?: null,
                                ]),
                                'accused' => $accused->values()->map(fn($a) => [
                                    'name' => $a->person_name ?: 'N/A',
                                    'designation' => $a->designation->designation_name ?? null,
                                    'department' => $a->department->department_name ?? null,
                                    'phone' => $a->phone ?: null,
                                    'district' => $a->district->district_name ?? null,
                                    'address' => $a->full_address ?: null,
                                ]),
                                'uploads' => $petition->uploads->map(fn($u) => [
                                    'id' => $u->upload_id,
                                    'name' => $u->original_filename ?: basename($u->file_path),
                                    'category' => $u->file_category ?? 'Document',
                                    'download_url' => route('admin.trash.download', $u->upload_id),
                                ]),
                                'restore_url' => route('admin.trash.restore', $petition->petition_id),
                                'force_delete_url' => route('admin.trash.forceDelete', $petition->petition_id),
                                'can_restore' => Auth::user()->canAccess('restore recycle bin'),
                                'can_delete' => Auth::user()->canAccess('delete recycle bin'),
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- # SL -->
                            <td class="px-5 py-4 text-center font-semibold text-slate-400 text-xs">
                                {{ ($trashedPetitions->currentPage() - 1) * $trashedPetitions->perPage() + $loop->iteration }}
                            </td>

                            <!-- Receipt No & Nature -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">
                                        {{ $petition->receipt_no ?? ('ID #' . $petition->petition_id) }}
                                    </span>
                                    @if($hasUploads)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded px-1.5 py-0.5" title="Has file attachments">
                                            <i data-lucide="paperclip" class="w-3 h-3"></i> {{ $petition->uploads->count() }}
                                        </span>
                                    @endif
                                </div>
                                @if($petition->file_no)
                                    <span class="block text-xs text-slate-500 font-medium mt-0.5">
                                        File: {{ $petition->file_no }}
                                    </span>
                                @endif
                                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                    @if($petition->nature_of_petition)
                                        <span class="inline-block text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded px-2 py-0.5">
                                            {{ $petition->nature_of_petition }}
                                        </span>
                                    @endif
                                    @if($petition->mode_of_petition_received)
                                        <span class="inline-block text-[10px] font-medium text-slate-500 bg-slate-50 border border-slate-200/80 rounded px-1.5 py-0.5">
                                            {{ $petition->mode_of_petition_received }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Petitioner (Complainant) -->
                            <td class="px-5 py-4">
                                @if($firstComplainant)
                                    <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span>{{ $firstComplainant->person_name ?: 'Unnamed Petitioner' }}</span>
                                        @if($complainants->count() > 1)
                                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded px-1.5 py-0.2">
                                                +{{ $complainants->count() - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5 space-y-0.5">
                                        @if($firstComplainant->district)
                                            <div>{{ $firstComplainant->district->district_name }}</div>
                                        @endif
                                        @if($firstComplainant->phone)
                                            <div class="text-slate-400 font-mono text-[11px]">{{ $firstComplainant->phone }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No petitioner recorded</span>
                                @endif
                            </td>

                            <!-- Accused / Subject -->
                            <td class="px-5 py-4">
                                @if($firstAccused)
                                    <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                                        <span>{{ $firstAccused->person_name ?: 'Unnamed Accused' }}</span>
                                        @if($accused->count() > 1)
                                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.2">
                                                +{{ $accused->count() - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5">
                                        @if($firstAccused->designation)
                                            <span>{{ $firstAccused->designation->designation_name }}</span>
                                        @endif
                                        @if($firstAccused->department)
                                            <span class="text-slate-400 block text-[11px] truncate max-w-[200px]" title="{{ $firstAccused->department->department_name }}">
                                                {{ $firstAccused->department->department_name }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">None specified</span>
                                @endif
                            </td>

                            <!-- Desk & Creator -->
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-800 block text-xs">
                                    {{ $petition->seat->seat_name ?? 'No Seat' }}
                                </span>
                                <span class="text-xs text-slate-500 block mt-0.5">
                                    By: {{ $petition->user->name ?? 'Unknown' }}
                                </span>
                                @if($petition->unit)
                                    <span class="text-[11px] text-slate-400 block mt-0.5 truncate max-w-[160px]">
                                        Unit: {{ $petition->unit->unit_name }}
                                    </span>
                                @endif
                            </td>

                            <!-- Deleted Date -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 text-rose-600 font-semibold text-xs">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>{{ \Carbon\Carbon::parse($petition->deleted_at)->diffForHumans() }}</span>
                                </div>
                                <span class="block text-[11px] text-slate-400 font-medium mt-0.5">
                                    {{ \Carbon\Carbon::parse($petition->deleted_at)->format('d M, Y h:i A') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- View Details Button -->
                                    <button type="button" 
                                            @click="openPreview(@js($previewPayload))"
                                            class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg hover:bg-slate-200 hover:text-slate-900 transition-colors flex items-center gap-1.5"
                                            title="View Record Details">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-500"></i>
                                        <span>Details</span>
                                    </button>

                                    <!-- Restore Button -->
                                    @if(Auth::user()->canAccess('restore recycle bin'))
                                        <form action="{{ route('admin.trash.restore', $petition->petition_id) }}" 
                                              method="POST" 
                                              data-confirm="Are you sure you want to restore petition {{ $petition->receipt_no ?? ('ID ' . $petition->petition_id) }} back to active processing?">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors flex items-center gap-1.5"
                                                    title="Restore Petition">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Restore</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Permanent Delete Button -->
                                    @if(Auth::user()->canAccess('delete recycle bin'))
                                        <form action="{{ route('admin.trash.forceDelete', $petition->petition_id) }}"
                                              method="POST" 
                                              data-confirm="CRITICAL: This will PERMANENTLY purge petition {{ $petition->receipt_no ?? ('ID ' . $petition->petition_id) }} and delete all attached physical documents. This action CANNOT be reversed. Do you wish to proceed?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors flex items-center gap-1.5"
                                                    title="Permanently Purge Petition">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Purge</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400">
                                        <i data-lucide="trash-2" class="w-8 h-8"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800">
                                        @if(!empty($search) || !empty($nature) || !empty($dateFrom) || !empty($dateTo))
                                            No Matching Trashed Records
                                        @else
                                            Recycle Bin is Empty
                                        @endif
                                    </h3>
                                    <p class="text-slate-500 text-xs sm:text-sm max-w-sm mx-auto mt-1">
                                        @if(!empty($search) || !empty($nature) || !empty($dateFrom) || !empty($dateTo))
                                            No deleted petitions matched your search criteria. Try resetting the filters.
                                        @else
                                            There are currently no soft-deleted petition records in the system.
                                        @endif
                                    </p>
                                    @if(!empty($search) || !empty($nature) || !empty($dateFrom) || !empty($dateTo))
                                        <div class="mt-4">
                                            <a href="{{ route('admin.trash.index') }}" 
                                               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Clear Filters
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trashedPetitions->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/70">
                {{ $trashedPetitions->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Preview Modal Dialog -->
    <div x-show="previewModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="previewModal = false">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
             @click="previewModal = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Content Container -->
        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] z-10"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            <!-- Modal Header -->
            <div class="px-6 py-4.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center text-rose-400">
                        <i data-lucide="file-text" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-white" x-text="selectedItem.receipt_no"></h3>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/30 text-rose-200 border border-rose-500/40">
                                Soft-Deleted
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 font-medium">
                            <span x-text="selectedItem.nature"></span> &bull; 
                            Deleted <span x-text="selectedItem.deleted_at_human"></span> (<span x-text="selectedItem.deleted_at"></span>)
                        </p>
                    </div>
                </div>

                <button type="button" 
                        @click="previewModal = false"
                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-slate-700 text-sm" style="scrollbar-width: thin;">
                <!-- Key Metadata Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">File No</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="selectedItem.file_no || 'None'"></span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Receipt Date</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="selectedItem.date_received || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Received Mode</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="selectedItem.mode || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Lifecycle Status</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="selectedItem.status || 'Received'"></span>
                    </div>
                    <div class="col-span-2 pt-2 border-t border-slate-200/60">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Desk / Unit</span>
                        <span class="font-bold text-slate-800 text-xs">
                            <span x-text="selectedItem.seat"></span> &bull; <span x-text="selectedItem.unit"></span>
                        </span>
                    </div>
                    <div class="col-span-2 pt-2 border-t border-slate-200/60">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Registered By</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="selectedItem.creator"></span>
                    </div>
                </div>

                <!-- Description & Proposed Action -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="align-left" class="w-3.5 h-3.5"></i> Petition Summary / Description
                    </h4>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm whitespace-pre-line leading-relaxed font-normal"
                         x-text="selectedItem.description"></div>

                    <template x-if="selectedItem.proposed_action && selectedItem.proposed_action !== 'None'">
                        <div class="mt-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5 mb-1.5">
                                <i data-lucide="compass" class="w-3.5 h-3.5"></i> Proposed Action
                            </h4>
                            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-amber-900 text-xs font-medium"
                                 x-text="selectedItem.proposed_action"></div>
                        </div>
                    </template>
                </div>

                <!-- Complainant(s) Section -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> Complainant / Petitioner(s)
                        </span>
                        <span class="text-[11px] text-slate-500 font-bold" x-text="(selectedItem.complainants || []).length + ' Recorded'"></span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="(comp, idx) in selectedItem.complainants" :key="idx">
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-2xs space-y-1.5">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span x-text="comp.name"></span>
                                </div>
                                <div class="text-xs text-slate-500 space-y-0.5">
                                    <div x-show="comp.phone" class="flex items-center gap-1 text-slate-600 font-mono text-[11px]">
                                        <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>
                                        <span x-text="comp.phone"></span>
                                    </div>
                                    <div x-show="comp.district" class="text-slate-600">
                                        District: <span class="font-semibold" x-text="comp.district"></span>
                                    </div>
                                    <div x-show="comp.address" class="text-slate-500 italic text-[11px]" x-text="comp.address"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedItem.complainants || selectedItem.complainants.length === 0">
                            <div class="p-3 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs italic col-span-2 text-center">
                                No complainant details attached.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Accused Section -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-500"></i> Accused / Respondent(s)
                        </span>
                        <span class="text-[11px] text-slate-500 font-bold" x-text="(selectedItem.accused || []).length + ' Recorded'"></span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="(acc, idx) in selectedItem.accused" :key="idx">
                            <div class="p-3.5 rounded-xl border border-amber-200/80 bg-amber-50/30 shadow-2xs space-y-1.5">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span x-text="acc.name"></span>
                                </div>
                                <div class="text-xs text-slate-600 space-y-0.5">
                                    <div x-show="acc.designation" class="font-medium text-slate-700" x-text="acc.designation"></div>
                                    <div x-show="acc.department" class="text-slate-500 text-[11px]" x-text="acc.department"></div>
                                    <div x-show="acc.district" class="text-slate-500 text-[11px]">District: <span x-text="acc.district"></span></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedItem.accused || selectedItem.accused.length === 0">
                            <div class="p-3 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs italic col-span-2 text-center">
                                No respondents or accused officers specified.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Attachments Section -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Evidence / Documents
                        </span>
                        <span class="text-[11px] text-slate-500 font-bold" x-text="(selectedItem.uploads || []).length + ' Files'"></span>
                    </h4>

                    <div class="space-y-2">
                        <template x-for="(file, idx) in selectedItem.uploads" :key="idx">
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <i data-lucide="file" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 truncate" x-text="file.name"></div>
                                        <div class="text-[10px] text-slate-500" x-text="file.category"></div>
                                    </div>
                                </div>
                                <a :href="file.download_url" 
                                   target="_blank"
                                   class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-indigo-50 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 text-xs font-bold flex items-center gap-1.5 transition-colors shrink-0 shadow-2xs">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                                </a>
                            </div>
                        </template>
                        <template x-if="!selectedItem.uploads || selectedItem.uploads.length === 0">
                            <div class="p-3 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs italic text-center">
                                No document attachments associated with this petition.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <span class="text-xs text-slate-500 font-medium hidden sm:inline">
                    Changes here directly impact audit trails.
                </span>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" 
                            @click="previewModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition-colors">
                        Close
                    </button>

                    <!-- Restore Trigger -->
                    <template x-if="selectedItem.can_restore">
                        <form :action="selectedItem.restore_url" method="POST"
                              :data-confirm="'Are you sure you want to restore petition ' + selectedItem.receipt_no + '?'">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow-xs">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                Restore Petition
                            </button>
                        </form>
                    </template>

                    <!-- Force Delete Trigger -->
                    <template x-if="selectedItem.can_delete">
                        <form :action="selectedItem.force_delete_url" method="POST"
                              :data-confirm="'CRITICAL: Permanently purge ' + selectedItem.receipt_no + ' and its files? This cannot be undone.'">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors flex items-center gap-1.5 shadow-xs">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                Permanently Purge
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function recycleBinManager() {
        return {
            previewModal: false,
            selectedItem: {},
            openPreview(item) {
                this.selectedItem = item;
                this.previewModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
