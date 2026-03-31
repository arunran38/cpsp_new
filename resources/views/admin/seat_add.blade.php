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
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Seat</h1>
            <p class="text-sm text-slate-500">Create a new organizational seat assignment.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4 text-primary"></i>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">New Seat</span>
        </div>

        <form method="POST" action="{{ route('admin.seats.store') }}" class="p-8 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Seat Name -->
                <x-input 
                    label="Seat Name" 
                    name="seat_name" 
                    placeholder="Enter Seat Name" 
                    required 
                    :value="old('seat_name')"
                />

                <!-- Active Status -->
                <x-select 
                    label="Status" 
                    name="is_active" 
                    :options="['1' => 'Active', '0' => 'Inactive']" 
                    :selected="old('is_active', '1')"
                />
            </div>

            <!-- Unit Assignment -->
            <div class="space-y-4 pt-6 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-sm font-bold text-slate-700">Assign to Units</span>
                </div>
                <x-searchable-multiselect 
                    name="unit_ids" 
                    :options="$units->mapWithKeys(fn($unit) => [$unit->unit_id => $unit->unit_name . ' (' . $unit->unit_code . ')'])->toArray()"
                    :selected="old('unit_ids', [])"
                    placeholder="Search and select units for this seat..."
                />
                @error('unit_ids')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-6 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Back to List
                </a>
                <x-button variant="primary" type="submit" icon="plus">
                    Create New Seat
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection

