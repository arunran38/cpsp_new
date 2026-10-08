@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-white">Update Designation</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900">
        <form method="POST" action="{{ route('admin.designations.update', $designation->id) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-2">
                    <label for="designation_name" class="text-sm font-bold text-slate-700">Designation Name</label>
                    <input type="text" name="designation_name" id="designation_name" value="{{ old('designation_name', $designation->designation_name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                           placeholder="e.g. Manager">
                    @error('designation_name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.designations.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-black bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
                    Update Designation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
