@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Seat</h1>
     </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">
        <form method="POST" action="{{ route('admin.seats.update', $seat->seat_id) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Seat Name -->
                <div class="space-y-2">
                    <label for="seat_name" class="text-sm font-bold text-slate-700">Seat Name</label>
                    <input type="text" name="seat_name" id="seat_name" value="{{ old('seat_name', $seat->seat_name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                           placeholder="e.g. Desk 01">
                    @error('seat_name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="space-y-2">
                    <label for="is_active" class="text-sm font-bold text-slate-700">Status</label>
                    <select name="is_active" id="is_active" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        <option value="1" {{ old('is_active', $seat->is_active) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $seat->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Unit Assignment (Searchable Multiselect) -->
            <div class="space-y-4">
                <x-searchable-multiselect 
                    label="Assign to Units" 
                    name="unit_ids" 
                    :options="$units->mapWithKeys(fn($unit) => [$unit->unit_id => $unit->unit_name . ' (' . $unit->unit_code . ')'])->toArray()"
                    :selected="old('unit_ids', $seat->units->pluck('unit_id')->toArray())"
                    placeholder="Search and select units..."
                />
                @error('unit_ids')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-sm font-bold text-slate-600 bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
                    Update Seat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
