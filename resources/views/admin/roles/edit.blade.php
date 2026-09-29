@extends('layouts.admin')
@section('container_width', 'max-w-4xl')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Edit Role</h1>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-8">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Role Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" required>
                    @error('name')
                        <p class="text-rose-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-700 mb-4">Assign Permissions</label>
                    
                    @php
                        $groupedPermissions = [];
                        foreach($permissions as $p) {
                            $parts = explode(' ', $p->name, 2);
                            $category = $parts[1] ?? 'other';
                            $groupedPermissions[$category][] = $p;
                        }
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($groupedPermissions as $category => $perms)
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">{{ $category }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-grow">
                                    @php
                                        $order = ['create' => 1, 'view' => 2, 'update' => 3, 'delete' => 4];
                                        usort($perms, function($a, $b) use ($order) {
                                            $actionA = strtolower(explode(' ', $a->name)[0]);
                                            $actionB = strtolower(explode(' ', $b->name)[0]);
                                            $valA = $order[$actionA] ?? 99;
                                            $valB = $order[$actionB] ?? 99;
                                            return $valA <=> $valB;
                                        });
                                    @endphp
                                    @foreach($perms as $permission)
                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white shadow-sm cursor-pointer hover:border-primary/30 hover:shadow-md transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/50">
                                            <span class="text-slate-700 text-sm font-semibold capitalize">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-8">
                    <a href="{{ route('admin.roles.index') }}" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 font-bold text-white rounded-xl bg-primary hover:bg-primary/90 shadow-sm transition-all">Update Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection
