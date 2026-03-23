@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Update Unit</h1>
       
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900">
        <form method="POST" action="{{ route('admin.units.update', $unit->unit_id) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Unit Name -->
                <div class="space-y-2">
                    <label for="unit_name" class="text-sm font-bold text-slate-700">Unit Name</label>
                    <input type="text" name="unit_name" id="unit_name" value="{{ old('unit_name', $unit->unit_name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                           placeholder="e.g. Unit A">
                    @error('unit_name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit Code -->
                <div class="space-y-2">
                    <label for="unit_code" class="text-sm font-bold text-slate-700">Unit Code</label>
                    <input type="text" name="unit_code" id="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                           placeholder="e.g. UA-001">
                    @error('unit_code')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Districts (Multiple Selection Dropdown) -->
                @php
                    $keralaDistricts = [
                        'Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 
                        'Kottayam', 'Idukki', 'Ernakulam', 'Thrissur', 'Palakkad', 
                        'Malappuram', 'Kozhikode', 'Wayanad', 'Kannur', 'Kasaragod'
                    ];
                    $selectedDistricts = old('district', $unit->district ?? []);
                    if (!is_array($selectedDistricts)) {
                        $selectedDistricts = (array) $selectedDistricts;
                    }
                @endphp
                <div class="space-y-2 md:col-span-2" x-data="{ open: false, selected: {{ json_encode($selectedDistricts) }} }">
                    <label class="text-sm font-bold text-slate-700">Select Districts</label>
                    <div class="relative">
                        <!-- Trigger -->
                        <button type="button" @click="open = !open" @click.away="open = false"
                                class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-left">
                            <span class="block truncate text-slate-700 text-sm" x-text="selected.length === 0 ? 'Select Districts...' : (selected.length <= 3 ? selected.join(', ') : selected.slice(0, 3).join(', ') + ' + ' + (selected.length - 3) + ' more')"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <!-- Dropdown Options -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto custom-scrollbar py-2">
                            @foreach($keralaDistricts as $dist)
                            <label class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 cursor-pointer transition-colors group">
                                <input type="checkbox" name="district[]" value="{{ $dist }}" 
                                       x-model="selected"
                                       class="w-4 h-4 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 disabled:opacity-50 transition-all cursor-pointer">
                                <span class="text-sm font-medium text-slate-700 group-hover:text-primary transition-colors">
                                    {{ $dist }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @error('district')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.units.index') }}" 
                   class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-sm font-bold text-black bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
                    Update Unit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
