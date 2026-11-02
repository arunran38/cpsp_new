@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.seats.index') }}" 
           class="p-2 text-slate-400 hover:text-slate-600 bg-white border border-slate-200 rounded-xl transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Edit Seat</h1>
            <p class="text-sm text-slate-500 italic">{{ $seat->seat_name }}</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2 rounded-t-3xl">
            <i data-lucide="layout" class="w-4 h-4 text-primary"></i>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Seat Configuration</span>
        </div>
        
        <form method="POST" action="{{ route('admin.seats.update', $seat->seat_id) }}" class="p-8 space-y-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Seat Name -->
                <div class="space-y-2.5">
                    <label for="seat_name" class="text-sm font-bold text-slate-700">Seat Name</label>
                    <input type="text" name="seat_name" id="seat_name" value="{{ old('seat_name', $seat->seat_name) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium text-slate-900"
                           placeholder="e.g. Desk 01">
                    @error('seat_name')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="space-y-2.5">
                    <label for="is_active" class="text-sm font-bold text-slate-700">Display Status</label>
                    <div class="relative">
                        <select name="is_active" id="is_active" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium text-slate-900 appearance-none !bg-none"
                                style="background-image: none !important;">
                            <option value="1" {{ old('is_active', $seat->is_active) == 1 ? 'selected' : '' }}>Active - Visible in lists</option>
                            <option value="0" {{ old('is_active', $seat->is_active) == 0 ? 'selected' : '' }}>Inactive - Hidden/Locked</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <!-- Multiple Roles Assignment -->
                <div class="space-y-3 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-bold text-slate-700">
                            Assign Roles (Multiple Selection) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs text-slate-400">Select one or more roles to define capabilities for this seat</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @php
                            $activeRoleNames = $seat->roles->pluck('name')->toArray();
                            $oldRoles = old('roles', old('role') ? [old('role')] : $activeRoleNames);
                        @endphp
                        @foreach($roles as $r)
                            @php
                                $isChecked = in_array($r->name, $oldRoles);
                                $isConfidential = str_contains(strtolower($r->name), 't section') || str_contains(strtolower($r->name), 't-section');
                            @endphp
                            <label class="relative flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/80 cursor-pointer transition-all duration-200 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 has-[:checked]:ring-2 has-[:checked]:ring-indigo-500/20 shadow-sm group select-none">
                                <input type="checkbox" name="roles[]" value="{{ $r->name }}" 
                                       {{ $isChecked ? 'checked' : '' }}
                                       class="mt-0.5 w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 focus:ring-2">
                                <div class="flex flex-col flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">
                                            {{ ucfirst($r->name) }}
                                        </span>
                                        @if($isConfidential)
                                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 border border-rose-200">
                                                Secret
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $r->permissions_count ?? $r->permissions->count() }} permissions
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Unit Assignment -->
            <div class="space-y-4 pt-6 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-sm font-bold text-slate-700">Unit Assignments</span>
                </div>
                <x-searchable-multiselect 
                    name="unit_ids" 
                    :options="$units->mapWithKeys(fn($unit) => [$unit->unit_id => $unit->unit_name . ' (' . $unit->unit_code . ')'])->toArray()"
                    :selected="old('unit_ids', $seat->units->pluck('unit_id')->toArray())"
                    placeholder="Search and select units..."
                />
                @error('unit_ids')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-6 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-100 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-xl shadow-primary/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update Seat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
