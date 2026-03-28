@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Seat History: {{ $seat->seat_name }}</h1>
            <p class="text-sm text-slate-500">List of all officers assigned to this seat over time.</p>
        </div>
        <a href="{{ route('admin.seats.index') }}" class="px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            Back to List
        </a>
    </div>

    <!-- History List -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Officer</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($assignment->user && $assignment->user->profilePhoto)
                                        <img class="w-9 h-9 rounded-full object-cover border border-slate-200" 
                                             src="{{ asset('storage/' . $assignment->user->profilePhoto->file_path) }}" alt="">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 border border-slate-200">
                                            {{ substr($assignment->user->name ?? 'D', 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $assignment->user->name ?? 'Deleted User' }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">{{ $assignment->user->pen ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($assignment->is_additional)
                                    <span class="text-[9px] font-extrabold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 uppercase">Additional</span>
                                @else
                                    <span class="text-[9px] font-extrabold text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 uppercase">Primary</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-0.5">
                                    <p class="text-[11px] font-medium text-slate-700">From: {{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</p>
                                    <p class="text-[11px] text-slate-400">To: {{ $assignment->revoked_at ? $assignment->revoked_at->format('M d, Y') : ($assignment->is_active ? 'Present' : 'N/A') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($assignment->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        ACTIVE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-50 text-slate-500 border border-slate-200">
                                        PAST
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="history" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                                <p class="text-sm font-medium">No assignment history found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
