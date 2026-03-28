@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Seats Management</h1>
           
        </div>
        <a href="{{ route('admin.seats.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-600 bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add New Seat
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
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Sl No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Seat Name</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Current Occupant</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($seats as $seat)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-slate-400">{{ $seats->firstItem() + $loop->index }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-900">{{ $seat->seat_name }}</span>
                                @if(!$seat->is_active)
                                    <span class="block text-[10px] text-rose-500 font-bold uppercase mt-0.5 italic">Disabled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($seat->units as $unit)
                                        @php
                                            $colors = [
                                                ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100'],
                                                ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100'],
                                                ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100'],
                                                ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100'],
                                                ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-100'],
                                                ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-100'],
                                            ];
                                            $color = $colors[$unit->unit_id % count($colors)];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $color['bg'] }} {{ $color['text'] }} border {{ $color['border'] }} uppercase tracking-wider shadow-sm" title="{{ $unit->unit_name }}">
                                            {{ $unit->unit_code }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($seat->activeAssignment)
                                    <div class="flex items-center gap-3">
                                        @if($seat->activeAssignment->user && $seat->activeAssignment->user->profilePhoto)
                                            <img class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-100" 
                                                 src="{{ asset('storage/' . $seat->activeAssignment->user->profilePhoto->file_path) }}" alt="">
                                        @else
                                            <img class="w-8 h-8 rounded-full ring-2 ring-slate-100" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($seat->activeAssignment->user->name ?? 'Deleted') }}&background=f8fafc&color=4361ee" alt="">
                                        @endif
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $seat->activeAssignment->user->name ?? 'Deleted User' }}</p>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">{{ $seat->activeAssignment->user->pen ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('admin.seatuser.create', ['seat_id' => $seat->seat_id]) }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-primary transition-colors italic">
                                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                        Vacant - Assign Now
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($seat->activeAssignment)
                                    @if($seat->activeAssignment->is_additional)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase">
                                            Additional Charge
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-100 uppercase">
                                            Primary
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[10px] text-slate-300 italic">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.seats.history', $seat->seat_id) }}" 
                                       class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100"
                                       title="Assignment History">
                                        <i class="fa-solid fa-history"></i>
                                    </a>
                                    
                                    @if($seat->activeAssignment)
                                        <form method="POST" action="{{ route('admin.seats.revoke', $seat->seat_id) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-transparent hover:border-amber-100"
                                                    onclick="return confirm('Are you sure you want to revoke the current assignment?')"
                                                    title="Revoke Assignment">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.seats.edit', $seat->seat_id) }}" 
                                       class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100"
                                       title="Edit Seat">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.seats.destroy', $seat->seat_id) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-slate-400 {{ $seat->activeAssignment ? 'opacity-30 cursor-not-allowed' : 'hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100' }} rounded-lg transition-colors"
                                                @if($seat->activeAssignment) disabled title="Cannot delete occupied seat" @else onclick="return confirm('Are you sure you want to delete this seat?')" title="Delete Seat" @endif>
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="layout" class="w-12 h-12 text-slate-200 mb-4"></i>
                                    <p class="text-base font-semibold text-slate-900">No seats found</p>
                                    <p class="text-sm mt-1">Start by adding your first seat arrangement.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($seats->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $seats->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
