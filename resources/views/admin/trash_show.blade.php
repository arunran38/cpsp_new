@extends('layouts.admin')
@section('container_width', 'max-w-6xl')

@section('title', 'Deleted Petition - ' . ($petition->receipt_no ?? 'ID ' . $petition->petition_id))

@section('content')
<div class="space-y-6">
    <!-- Top Action / Back Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.trash.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs w-max">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i> Back to Recycle Bin
        </a>

        <div class="flex items-center gap-2.5">
            @if(Auth::user()->canAccess('restore recycle bin'))
                <form action="{{ route('admin.trash.restore', $petition->petition_id) }}" method="POST"
                      data-confirm="Are you sure you want to restore petition {{ $petition->receipt_no ?? ('ID ' . $petition->petition_id) }}?">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Restore Petition
                    </button>
                </form>
            @endif

            @if(Auth::user()->canAccess('delete recycle bin'))
                <form action="{{ route('admin.trash.forceDelete', $petition->petition_id) }}" method="POST"
                      data-confirm="CRITICAL: Permanently purge {{ $petition->receipt_no ?? ('ID ' . $petition->petition_id) }} and its files? This cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Permanently Purge
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Soft Delete Notice Banner -->
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3.5 shadow-2xs">
        <div class="w-9 h-9 rounded-xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        </div>
        <div class="text-sm">
            <div class="font-bold text-rose-900">Soft-Deleted Record</div>
            <div class="text-rose-700 font-medium text-xs mt-0.5">
                This petition was deleted on <span class="font-bold">{{ \Carbon\Carbon::parse($petition->deleted_at)->format('d M, Y \a\t h:i A') }}</span> ({{ \Carbon\Carbon::parse($petition->deleted_at)->diffForHumans() }}). It is currently inactive and hidden from normal petition dashboards.
            </div>
        </div>
    </div>

    <!-- Main Petition Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-black tracking-tight text-white">
                        Petition {{ $petition->receipt_no ?? ('ID #' . $petition->petition_id) }}
                    </h2>
                    @if($petition->file_no)
                        <span class="px-2 py-0.5 bg-slate-800 border border-slate-700 text-slate-300 rounded text-xs font-mono">
                            File: {{ $petition->file_no }}
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-300 font-medium mt-1 flex flex-wrap items-center gap-3">
                    <span>Nature: <strong>{{ $petition->nature_of_petition ?? 'General' }}</strong></span>
                    <span>&bull;</span>
                    <span>Mode: <strong>{{ $petition->mode_of_petition_received ?? 'Direct' }}</strong></span>
                    <span>&bull;</span>
                    <span>Received: <strong>{{ $petition->date_of_petition_received ? \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') : 'N/A' }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-800 border border-slate-700 text-slate-300">
                    Desk: {{ $petition->seat->seat_name ?? 'N/A' }}
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-800 border border-slate-700 text-slate-300">
                    Unit: {{ $petition->unit->unit_name ?? 'N/A' }}
                </span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Summary / Description -->
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                    <i data-lucide="align-left" class="w-3.5 h-3.5"></i> Petition Summary / Description
                </h3>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm whitespace-pre-line leading-relaxed font-normal">
                    {{ $petition->description ?: 'No detailed summary provided.' }}
                </div>
            </div>

            @if($petition->proposed_action)
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                        <i data-lucide="compass" class="w-3.5 h-3.5"></i> Proposed Action
                    </h3>
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl text-amber-900 text-xs font-medium">
                        {{ $petition->proposed_action }}
                    </div>
                </div>
            @endif

            <!-- Parties Grid -->
            @php
                $complainants = $petition->addresses->where('person_type', 'Complainant');
                $accused = $petition->addresses->where('person_type', 'Accused');
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                <!-- Complainants -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> Complainants ({{ $complainants->count() }})
                        </span>
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($complainants as $comp)
                            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-2xs space-y-1">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5 text-sm">
                                    <i data-lucide="user-check" class="w-4 h-4 text-indigo-600"></i>
                                    {{ $comp->person_name ?: 'Unnamed' }}
                                </div>
                                @if($comp->phone)
                                    <div class="text-xs text-slate-600 font-mono">{{ $comp->phone }}</div>
                                @endif
                                @if($comp->district)
                                    <div class="text-xs text-slate-500">District: {{ $comp->district->district_name }}</div>
                                @endif
                                @if($comp->full_address)
                                    <div class="text-xs text-slate-400 italic">{{ $comp->full_address }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs text-center italic">
                                No complainant details attached.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Accused -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-500"></i> Accused / Respondents ({{ $accused->count() }})
                        </span>
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($accused as $acc)
                            <div class="p-4 rounded-xl border border-amber-200/80 bg-amber-50/20 shadow-2xs space-y-1">
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $acc->person_name ?: 'Unnamed' }}
                                </div>
                                @if($acc->designation)
                                    <div class="text-xs font-medium text-slate-700">{{ $acc->designation->designation_name }}</div>
                                @endif
                                @if($acc->department)
                                    <div class="text-xs text-slate-500">{{ $acc->department->department_name }}</div>
                                @endif
                                @if($acc->district)
                                    <div class="text-xs text-slate-500">District: {{ $acc->district->district_name }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs text-center italic">
                                No accused/respondents recorded.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Attached Files -->
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Evidence Files ({{ $petition->uploads->count() }})
                    </span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($petition->uploads as $upload)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div class="min-w-0 pr-3">
                                <div class="text-xs font-bold text-slate-800 truncate" title="{{ $upload->original_filename ?: basename($upload->file_path) }}">
                                    {{ $upload->original_filename ?: basename($upload->file_path) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                                    {{ $upload->file_category ?? 'Document' }}
                                </div>
                            </div>
                            <a href="{{ route('admin.trash.download', $upload->upload_id) }}" 
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-indigo-50 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 text-xs font-bold flex items-center gap-1.5 transition-colors shrink-0 shadow-2xs">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                            </a>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs text-center italic col-span-2">
                            No files attached to this petition.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection

