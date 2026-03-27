@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Seat Assignments</h1>
            <p class="text-sm text-slate-500">Manage officer seat assignments and additional charges.</p>
        </div>
        <a href="{{ route('admin.seatuser.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
            <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
            Assign New Seat
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-medium">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Seat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Officer</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ !$assignment->is_active ? 'opacity-60 grayscale-[0.5]' : '' }}">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">{{ $assignment->seat->seat_name }}</p>
                                <p class="text-[10px] text-slate-400">Assigned: {{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img class="w-8 h-8 rounded-full ring-2 ring-slate-100" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($assignment->user->name ?? 'Deleted User') }}&background=f8fafc&color=4361ee" alt="">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $assignment->user->name ?? 'Deleted User' }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">{{ $assignment->user->pen ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($assignment->is_additional)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                        ADDITIONAL CHARGE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-100">
                                        PRIMARY
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($assignment->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        ACTIVE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        REVOKED
                                    </span>
                                    @if($assignment->revoked_at)
                                        <p class="text-[9px] text-slate-400 mt-1 italic">{{ $assignment->revoked_at->format('M d, Y') }}</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($assignment->is_active)
                                    <form method="POST" action="{{ route('admin.seatuser.destroy', $assignment->seat_user_id) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                onclick="return confirm('Are you sure you want to revoke this seat assignment?')"
                                                title="Revoke Assignment">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="user-x" class="w-12 h-12 text-slate-200 mb-4"></i>
                                    <p class="text-base font-semibold text-slate-900">No seat assignments found</p>
                                    <p class="text-sm mt-1">Start by assigning a seat to an officer.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
