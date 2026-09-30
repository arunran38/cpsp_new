@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Premium Header Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
            <div class="w-72 h-72 bg-emerald-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center gap-4 z-10">
            <a href="{{ route('admin.seats.index') }}" 
               class="flex items-center justify-center w-10 h-10 text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 shrink-0">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white drop-shadow-md mb-1">Create New Seat</h1>
                <p class="text-slate-300 font-medium text-sm">Add a new position to your organizational structure</p>
            </div>
        </div>
    </div>

    <!-- Stunning Form Container -->
    <div class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.08)] relative border border-slate-100">
        <!-- Subtle gradient background -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50 rounded-full blur-[100px] pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-50 rounded-full blur-[100px] pointer-events-none -z-10"></div>

        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                <i data-lucide="armchair" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Seat Information</h2>
        </div>

        <form method="POST" action="{{ route('admin.seats.store') }}" class="p-8 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Seat Name -->
                <div class="space-y-2">
                    <label for="seat_name" class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                        Seat Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-500 transition-colors">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="seat_name" name="seat_name" required value="{{ old('seat_name') }}"
                               placeholder="e.g. CPSP 1"
                               class="w-full pl-11 pr-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-semibold shadow-sm">
                    </div>
                    @error('seat_name')
                        <p class="text-xs font-bold text-rose-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="space-y-2">
                    <label for="is_active" class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                        Status <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-emerald-500 transition-colors z-10 pointer-events-none">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <select id="is_active" name="is_active"
                                class="w-full pl-11 pr-10 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-semibold shadow-sm appearance-none cursor-pointer">
                            <option value="1" @if(old('is_active', '1') == '1') selected @endif>Active</option>
                            <option value="0" @if(old('is_active') == '0') selected @endif>Inactive</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Role Assignment -->
                <div class="space-y-2 md:col-span-2">
                    <label for="role" class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                        Assign Role <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-violet-500 transition-colors z-10 pointer-events-none">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                        </div>
                        <select id="role" name="role" required
                                class="w-full pl-11 pr-10 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all font-semibold shadow-sm appearance-none cursor-pointer">
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @if(old('role') == $role->name) selected @endif>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unit Assignment -->
            <div class="space-y-4 pt-8 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600 shadow-sm">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Assign to Units</h2>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 shadow-sm">
                    <x-searchable-multiselect 
                        name="unit_ids" 
                        :options="$units->mapWithKeys(fn($unit) => [$unit->unit_id => $unit->unit_name . ' (' . $unit->unit_code . ')'])->toArray()"
                        :selected="old('unit_ids', [])"
                        placeholder="Search and select units for this seat..."
                    />
                </div>
            </div>

            <div class="pt-8 mt-8 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="w-full sm:w-auto px-6 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:text-slate-800 hover:bg-slate-200 rounded-xl transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3 text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-lg shadow-slate-900/20 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Create Seat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

