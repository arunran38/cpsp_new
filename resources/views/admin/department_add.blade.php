@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-12">
        @if(Auth::user()->canAccess('create departments'))
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        Department Management
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Add and manage organizational departments.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900" x-data="adminFormValidation()">
            <form method="POST" action="{{ route('admin.departments.store') }}" class="p-8" @submit.prevent="submitForm($event)">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <x-input label="Department Name" name="department_name" placeholder="Name of Department" required />
                    </div>

                    <div class="flex flex-col justify-end md:col-span-1">
                        <label class="block text-sm font-bold text-transparent mb-1.5 select-none" aria-hidden="true">&nbsp;</label>
                        <x-button icon="save" class="w-full">
                            Save Department
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <div class="space-y-6">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-bold tracking-tight text-white">Department Details</h2>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Department Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($departments as $department)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">{{ $departments->firstItem() + $loop->index }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900">{{ $department->department_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            @if(Auth::user()->canAccess('update departments'))
                                                <a href="{{ route('admin.departments.edit', $department->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit Department">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                            @if(Auth::user()->canAccess('delete departments'))
                                                <form method="POST" action="{{ route('admin.departments.destroy', $department->id) }}" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" onclick="return confirm('Are you sure you want to delete this department?')" title="Delete Department">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-16 text-center text-slate-500">
                                        <p class="text-base font-bold text-slate-900">No departments found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($departments->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/30">
                        {{ $departments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
