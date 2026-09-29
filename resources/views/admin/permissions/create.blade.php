@extends('layouts.admin')
@section('container_width', 'max-w-4xl')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Add New Permission</h1>
            </div>
            <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-8">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Permission Name</label>
                    <input type="text" name="name" id="name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="e.g. edit posts" required>
                    @error('name')
                        <p class="text-rose-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-4 mt-8">
                    <a href="{{ route('admin.permissions.index') }}" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 font-bold text-white rounded-xl bg-primary hover:bg-primary/90 shadow-sm transition-all">Create Permission</button>
                </div>
            </form>
        </div>
    </div>
@endsection
