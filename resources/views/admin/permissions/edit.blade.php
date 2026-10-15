@extends('layouts.admin')
@section('container_width', 'max-w-4xl')

@section('content')
<div class="space-y-6">
    <!-- Premium Header Section matching unit_add / roles design -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-4 text-center sm:text-left z-10">
                <a href="{{ route('admin.permissions.index') }}" 
                   class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 text-white flex items-center justify-center transition-all shadow-sm shrink-0"
                   title="Back to Permissions List">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                        Edit Permission
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm mt-1">
                        Modify permission identifier and examine role assignments for <span class="text-white font-bold">{{ $permission->name }}</span>.
                    </p>
                </div>
            </div>
            <div class="shrink-0 z-10">
                <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-200 hover:text-white bg-slate-800/90 hover:bg-slate-800 border border-slate-700 transition-all shadow-sm">
                    <i data-lucide="key" class="w-4 h-4 text-amber-400"></i>
                    <span>Permissions List</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 via-indigo-500 via-purple-500 via-pink-500 via-rose-500 via-amber-500 to-emerald-500"></div>

        <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST" class="p-6 sm:p-8">
            @csrf
            @method('PUT')
            
            <div class="max-w-2xl space-y-6">
                <div>
                    <label for="name" class="block text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider mb-2">
                        Permission Key / Name <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-500">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}"
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 transition-all text-base shadow-xs" 
                               required autofocus>
                    </div>
                    @error('name')
                        <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Roles currently holding this permission -->
                @if($permission->roles->count() > 0)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-4 h-4 text-purple-600"></i>
                            <span>Currently Granted to {{ $permission->roles->count() }} Roles:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($permission->roles as $role)
                                @php
                                    $rName = strtolower($role->name);
                                    if ($rName === 'developer') {
                                        $rBadge = 'bg-purple-50 text-purple-700 border-purple-200';
                                    } elseif ($rName === 'super admin') {
                                        $rBadge = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                    } elseif ($rName === 'admin') {
                                        $rBadge = 'bg-blue-50 text-blue-700 border-blue-200';
                                    } else {
                                        $rBadge = 'bg-slate-100 text-slate-700 border-slate-200';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border shadow-2xs capitalize {{ $rBadge }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>This permission is currently not assigned to any system role.</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.permissions.index') }}" 
                   class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-600/25 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
