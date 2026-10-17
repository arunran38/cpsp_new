@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Premium Header Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left z-10">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                    Seats Management
                </h1>
                <p class="text-slate-300 font-medium max-w-2xl text-sm">
                    Manage primary occupants and additional charges across units.
                </p>
            </div>
            
            @if(Auth::user()->canAccess('create seats'))
            <div class="shrink-0 z-10">
                <a href="{{ route('admin.seats.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 transition-all text-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Add New Seat</span>
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Stunning Table Container -->
    <div class="bg-white border border-slate-200 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative">
        <!-- Subtle background glow -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-50/50 rounded-full blur-[100px] pointer-events-none -z-10"></div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest w-16 text-center">#</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest text-center">Seat Details</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest">Jurisdiction</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest">Primary Occupant</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest">Additional Charges</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-700 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($seats as $seat)
                        <tr class="group hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-slate-400 group-hover:text-indigo-600 transition-colors duration-300">
                                    {{ $seats->firstItem() + $loop->index }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center justify-center gap-1.5 text-center">
                                    <span class="text-base font-black text-slate-800 group-hover:text-sky-700 transition-colors">{{ $seat->seat_name }}</span>
                                    
                                    <div class="flex items-center justify-center gap-2 mt-0.5">
                                        @if(!$seat->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-100 text-rose-700 uppercase tracking-widest">Disabled</span>
                                        @endif
                                        
                                        @if($seat->roles->isNotEmpty())
                                            @foreach($seat->roles as $role)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-widest">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-widest">
                                                <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                                                NO ROLE
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($seat->units as $unit)
                                        @php
                                            $colors = [
                                                'bg-indigo-50 text-slate-800 border-indigo-300',
                                                'bg-emerald-50 text-slate-800 border-emerald-300',
                                                'bg-amber-50 text-slate-800 border-amber-300',
                                                'bg-rose-50 text-slate-800 border-rose-300',
                                                'bg-violet-50 text-slate-800 border-violet-300',
                                                'bg-sky-50 text-slate-800 border-sky-300',
                                                'bg-fuchsia-50 text-slate-800 border-fuchsia-300',
                                            ];
                                            $style = $colors[$unit->unit_id % count($colors)];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black {{ $style }} border uppercase tracking-widest shadow-sm transition-transform hover:scale-105" title="{{ $unit->unit_name }}">
                                            {{ $unit->unit_code }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $primary = $seat->activeAssignments->where('is_additional', false)->first();
                                @endphp
                                @if($primary)
                                    <div class="flex items-center gap-3 p-2 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors group/occupant shadow-sm">
                                        <div class="relative">
                                            @if($primary->user && $primary->user->profilePhoto)
                                                <img class="w-10 h-10 rounded-xl object-cover ring-2 ring-white shadow-sm" 
                                                     src="{{ asset('storage/' . $primary->user->profilePhoto->file_path) }}" alt="">
                                            @else
                                                <img class="w-10 h-10 rounded-xl ring-2 ring-white shadow-sm" 
                                                     src="https://ui-avatars.com/api/?name={{ urlencode($primary->user->name ?? 'Deleted') }}&background=e2e8f0&color=334155&bold=true" alt="">
                                            @endif
                                            <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-black text-slate-800 truncate">{{ $primary->user->name ?? 'Deleted User' }}</p>
                                            <p class="text-[10px] text-slate-600 uppercase font-bold tracking-widest mt-0.5">{{ $primary->user->pen ?? 'NO PEN' }}</p>
                                        </div>
                                        @if(Auth::user()->canAccess('update seats'))
                                        <form method="POST" action="{{ route('admin.seatuser.destroy', $primary->seat_user_id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 text-rose-500 hover:text-white hover:bg-rose-500 rounded-lg transition-colors"
                                                    onclick="return confirm('Revoke primary assignment for {{ $primary->user->name ?? 'this user' }}?')"
                                                    title="Revoke Assignment">
                                                <i class="fa-solid fa-xmark text-sm"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                @else
                                    @if(Auth::user()->canAccess('update seats'))
                                    <a href="{{ route('admin.seatuser.create', ['seat_id' => $seat->seat_id]) }}" 
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition-colors shadow-sm">
                                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                                        Assign User
                                    </a>
                                    @else
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 shadow-sm">
                                        <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                        VACANT
                                    </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $additionals = $seat->activeAssignments->where('is_additional', true);
                                @endphp
                                @if($additionals->isNotEmpty())
                                    <div class="flex flex-col items-start gap-2">
                                        @foreach($additionals as $additional)
                                            <div class="inline-flex items-center gap-2 p-1.5 pr-1.5 bg-gradient-to-r from-amber-50 to-orange-50/50 border border-amber-200/50 rounded-full shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
                                                @if($additional->user && $additional->user->profilePhoto)
                                                    <img class="w-7 h-7 rounded-full object-cover shadow-sm ring-2 ring-white" 
                                                         src="{{ asset('storage/' . $additional->user->profilePhoto->file_path) }}" alt="">
                                                @else
                                                    <img class="w-7 h-7 rounded-full shadow-sm ring-2 ring-white" 
                                                         src="https://ui-avatars.com/api/?name={{ urlencode($additional->user->name ?? 'X') }}&background=fef3c7&color=b45309&bold=true" alt="">
                                                @endif
                                                <div class="flex items-center gap-2">
                                                    <p class="text-[11px] font-black text-amber-900 ml-0.5">
                                                        {{ $additional->user->name ?? 'Deleted' }}
                                                        <span class="text-[9px] font-bold text-amber-700/80 ml-0.5 tracking-wide">({{ $additional->user->pen ?? 'N/A' }})</span>
                                                    </p>
                                                    @if(Auth::user()->canAccess('update seats'))
                                                    <form method="POST" action="{{ route('admin.seatuser.destroy', $additional->seat_user_id) }}" class="flex shrink-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="flex items-center justify-center w-6 h-6 rounded-full text-amber-500 hover:text-white hover:bg-rose-500 transition-colors shadow-sm"
                                                                onclick="return confirm('Revoke additional charge?')"
                                                                title="Revoke Charge">
                                                            <i class="fa-solid fa-xmark text-xs"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500 font-bold italic">No additional charges</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.seats.history', $seat->seat_id) }}" 
                                       class="flex items-center justify-center w-9 h-9 rounded-xl text-slate-700 bg-sky-50 hover:text-sky-900 hover:bg-sky-100 hover:shadow-md transition-all hover:-translate-y-0.5 shadow-sm border border-sky-200"
                                       title="Assignment History">
                                        <i data-lucide="history" class="w-4 h-4"></i>
                                    </a>

                                    @if(Auth::user()->canAccess('update seats') && $seat->activeAssignments->isNotEmpty())
                                        <a href="{{ route('admin.seatuser.create', ['seat_id' => $seat->seat_id, 'is_additional' => 1]) }}" 
                                           class="flex items-center justify-center w-9 h-9 rounded-xl text-slate-700 bg-emerald-50 hover:text-emerald-900 hover:bg-emerald-100 hover:shadow-md transition-all hover:-translate-y-0.5 shadow-sm border border-emerald-200"
                                           title="Add Additional Charge">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if(Auth::user()->canAccess('update seats'))
                                    <a href="{{ route('admin.seats.edit', $seat->seat_id) }}" 
                                       class="flex items-center justify-center w-9 h-9 rounded-xl text-slate-700 bg-violet-50 hover:text-violet-900 hover:bg-violet-100 hover:shadow-md transition-all hover:-translate-y-0.5 shadow-sm border border-violet-200"
                                       title="Edit Seat Details">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    @endif
                                    
                                    @if(Auth::user()->canAccess('delete seats'))
                                    <form method="POST" action="{{ route('admin.seats.destroy', $seat->seat_id) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="flex items-center justify-center w-9 h-9 rounded-xl text-rose-600 bg-rose-50 border border-rose-100 hover:text-rose-700 hover:bg-rose-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all"
                                                onclick="return confirm('Delete this seat?')" title="Delete Seat">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mb-5 rotate-3 hover:rotate-6 transition-transform">
                                        <i data-lucide="armchair" class="w-10 h-10 text-indigo-500"></i>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-800 mb-1">No Seats Configured</h3>
                                    <p class="text-sm text-slate-500 mb-6">Create seats to start assigning users and building your organizational structure.</p>
                                    @if(Auth::user()->canAccess('create seats'))
                                    <a href="{{ route('admin.seats.create') }}" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/20">
                                        Add First Seat
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($seats->hasPages())
            <div class="px-6 py-5 border-t border-slate-200/80 bg-slate-50/30">
                {{ $seats->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
