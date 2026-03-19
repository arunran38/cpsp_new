@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Seat</h1>
        <p class="text-sm text-slate-500">Create a seat and assign it to one or more organizational units.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('admin.seats.store') }}" class="p-8 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Seat Name -->
                <div class="space-y-2">
                    <label for="seat_name" class="text-sm font-bold text-slate-700">Seat Name</label>
                    <input type="text" name="seat_name" id="seat_name" value="{{ old('seat_name') }}" required
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
                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Unit Assignment (Checkboxes) -->
            <div class="space-y-4">
                <label class="text-sm font-bold text-slate-700">Assign to Units</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($units as $unit)
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors group">
                            <input type="checkbox" name="unit_ids[]" value="{{ $unit->unit_id }}" 
                                   {{ is_array(old('unit_ids')) && in_array($unit->unit_id, old('unit_ids')) ? 'checked' : '' }}
                                   class="w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary/20">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-slate-900 group-hover:text-primary transition-colors">{{ $unit->unit_name }}</span>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">{{ $unit->unit_code }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
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
                        class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
                    Save Seat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
