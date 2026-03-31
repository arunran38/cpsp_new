@extends('layouts.admin')

@section('title', 'Recycle Bin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-rose-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-rose-500"></div>
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-rose-100 rounded-full blur-2xl opacity-50 pointer-events-none"></div>

        <div class="flex items-center gap-4 z-10">
            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600 border border-rose-100">
                <i data-lucide="trash-2" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Recycle Bin</h1>
                <p class="text-sm text-slate-500 mt-1">Manage deleted petitions and securely restore them if needed.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold w-12 text-center">#</th>
                        <th class="px-6 py-4 font-bold">Petition No</th>
                        <th class="px-6 py-4 font-bold">Deleted Object Info</th>
                        <th class="px-6 py-4 font-bold">Deleted At</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($trashedPetitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-slate-500">
                                {{ ($trashedPetitions->currentPage() - 1) * $trashedPetitions->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800">{{ $petition->petition_no }}</span>
                                <span class="block text-xs text-slate-500 mt-1 w-max px-2 py-0.5 rounded bg-slate-100 border border-slate-200">{{ $petition->nature_of_petition }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-800 font-medium">User: {{ $petition->user->name ?? 'Unknown' }}</span>
                                <span class="block text-xs text-slate-500 mt-1">Seat: {{ $petition->seat->seat_name ?? 'N/A' }}</span>
                                <span class="block text-xs text-slate-500 mt-1">Submitted: {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4 text-rose-400"></i>
                                    <span class="text-slate-700 font-medium">{{ \Carbon\Carbon::parse($petition->deleted_at)->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.trash.restore', $petition->petition_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors flex items-center gap-1.5" title="Restore Petition">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Restore
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.trash.forceDelete', $petition->petition_id) }}" method="POST" onsubmit="return confirm('WARNING: This will permanently delete the petition and all associated physical files from the server. This cannot be undone. Area you absolutely sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors flex items-center gap-1.5" title="Permanently Delete">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Delete Forever
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 border border-slate-100">
                                    <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-400"></i>
                                </div>
                                <span class="text-slate-500 font-medium text-sm">The recycle bin is completely empty.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trashedPetitions->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $trashedPetitions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
