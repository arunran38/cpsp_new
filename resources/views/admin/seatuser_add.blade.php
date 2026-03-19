@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Assign Seat to User</h1>
        <p class="text-sm text-slate-500">Create a new seat assignment, optionally as an additional charge.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('admin.seatuser.store') }}" class="p-8 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Selection -->
                <div class="space-y-2">
                    <label for="user_id" class="text-sm font-bold text-slate-700">Select Officer</label>
                    <select name="user_id" id="user_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        <option value="">Select an Officer</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}" {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->pen }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Seat Selection -->
                <div class="space-y-2">
                    <label for="seat_id" class="text-sm font-bold text-slate-700">Select Seat</label>
                    <select name="seat_id" id="seat_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        <option value="">Select a Seat</option>
                        @foreach($seats as $seat)
                            <option value="{{ $seat->seat_id }}" {{ old('seat_id') == $seat->seat_id ? 'selected' : '' }}>
                                {{ $seat->seat_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('seat_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Charge Option -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                <label class="relative flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_additional" value="1" 
                           {{ old('is_additional') ? 'checked' : '' }}
                           class="w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary/20">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-slate-900 group-hover:text-primary transition-colors">Additional Charge</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Allow this user to hold this seat in addition to their current assignments. If unchecked, existing active assignments will be revoked.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seatuser.index') }}" 
                   class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
                    Assign Seat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
