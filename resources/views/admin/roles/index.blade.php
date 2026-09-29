@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-8">
        <!-- Page Header -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Roles List</h1>
            </div>
            <x-button variant="primary" size="md" icon="shield" onclick="window.location='{{ route('admin.roles.create') }}'">
                Add New Role
            </x-button>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl mb-4 border border-emerald-200 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-4 font-bold text-center">#</th>
                            <th class="px-6 py-4 font-bold">Role Name</th>
                            <th class="px-6 py-4 font-bold w-1/2">Assigned Permissions</th>
                            <th class="px-6 py-4 font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($roles as $role)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-500 text-center">
                                    {{ $role->id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                            <i data-lucide="shield" class="w-5 h-5"></i>
                                        </div>
                                        <span class="font-black text-slate-900 text-sm leading-tight capitalize">{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($role->permissions as $permission)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $permission->name }}
                                            </span>
                                        @empty
                                            <span class="text-slate-400 text-xs italic">No permissions assigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Edit Role">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                onclick="return confirm('Are you sure you want to remove this role?')"
                                                title="Remove Role">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center">
                                        <i data-lucide="shield-off" class="w-12 h-12 text-slate-200 mb-4"></i>
                                        <p class="text-base font-semibold text-slate-900">No roles found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($roles->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
