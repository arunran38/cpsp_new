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
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Assign Seat to User</h1>
            <p class="text-sm text-slate-500">Create a new organizational seat assignment.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3 text-rose-800 text-sm font-medium shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2 rounded-t-3xl">
            <i data-lucide="user-plus" class="w-4 h-4 text-primary"></i>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Assignment Details</span>
        </div>

        <form method="POST" action="{{ route('admin.seatuser.store') }}" class="p-8 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Seat Selection -->
                <div class="space-y-2.5">
                    <label for="seat_id" class="text-sm font-bold text-slate-700">Target Seat</label>
                    <div class="relative">
                        <select name="seat_id" id="seat_id" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium text-slate-900 appearance-none">
                            <option value="">Select a Seat</option>
                            @foreach($seats as $seat)
                                <option value="{{ $seat->seat_id }}" {{ (old('seat_id') ?? ($selectedSeatId ?? '')) == $seat->seat_id ? 'selected' : '' }}>
                                    {{ $seat->seat_name }}
                                </option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                    @error('seat_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Selection -->
                <div class="space-y-2.5">
                    <label for="user_id" class="text-sm font-bold text-slate-700">Officer / User</label>
                    <div class="relative">
                        <select name="user_id" id="user_id" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-medium text-slate-900 appearance-none">
                            <option value="">Select an Officer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}" {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->pen }})
                                </option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                    @error('user_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Charge Option -->
            <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl group transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                <label class="relative flex items-start cursor-pointer">
                    <div class="relative flex items-center h-5 mt-1">
                        <input type="checkbox" name="is_additional" value="1" 
                               {{ old('is_additional') ? 'checked' : '' }}
                               class="w-5 h-5 text-primary border-slate-300 rounded focus:ring-4 focus:ring-primary/10 transition-all">
                    </div>
                    <div class="ml-4">
                        <span class="block text-sm font-bold text-slate-900 group-hover:text-primary transition-colors">Additional Charge Assignment</span>
                        <span class="block text-xs text-slate-500 mt-1 leading-relaxed">
                            <i data-lucide="info" class="w-3 h-3 inline mr-1 opacity-50"></i>
                            If checked, the user will hold this seat in addition to their current assignments. If unchecked (Primary Seat), any other active assignments for this user will be automatically revoked.
                        </span>
                    </div>
                </label>
            </div>

            <div class="pt-6 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.seats.index') }}" 
                   class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-100 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-xl shadow-primary/20 transition-all flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    Complete Assignment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
