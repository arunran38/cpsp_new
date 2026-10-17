@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-10">
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        Designation Management
                    </h1>
                    <p class="text-sky-400 font-medium max-w-2xl text-xs sm:text-sm">
                        Add and manage organizational designations.
                    </p>
                </div>
            </div>
        </div>

        @if(Auth::user()->canAccess('create designations'))
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900" x-data="adminFormValidation()">
            <form method="POST" action="{{ route('admin.designations.store') }}" class="p-8" @submit.prevent="submitForm($event)">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <x-input label="Designation Name" name="designation_name" placeholder="Name of Designation" required />
                    </div>

                    <div class="flex flex-col justify-end md:col-span-1">
                        <label class="block text-sm font-bold text-transparent mb-1.5 select-none" aria-hidden="true">&nbsp;</label>
                        <x-button icon="save" class="w-full">
                            Save Designation
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <div class="space-y-6 pt-6">
            <!-- Designation Details Header Section -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-md">
                <div class="absolute top-0 right-0 -translate-y-8 translate-x-1/4 pointer-events-none">
                    <div class="w-48 h-48 bg-indigo-500/15 rounded-full blur-[40px]"></div>
                </div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 pointer-events-none">
                    <div class="w-48 h-48 bg-teal-500/15 rounded-full blur-[40px]"></div>
                </div>
                
                <div class="relative px-6 py-3.5 sm:px-8 flex items-center justify-between z-10">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-white tracking-tight drop-shadow-sm mb-0.5">
                            Designation Details
                        </h2>
                        <p class="text-sky-400 font-medium text-xs">
                            Add and manage organizational designations.
                        </p>
                    </div>
                    @if(isset($designations) && $designations->total() > 0)
                        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800/80 border border-slate-700/60 text-xs font-bold text-slate-300">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5 text-sky-400"></i>
                            <span>{{ $designations->total() }} Total</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Designation Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($designations as $designation)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">{{ $designations->firstItem() + $loop->index }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900">{{ $designation->designation_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            @if(Auth::user()->canAccess('update designations'))
                                                <a href="{{ route('admin.designations.edit', $designation->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit Designation">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                            @if(Auth::user()->canAccess('delete designations'))
                                                <form method="POST" action="{{ route('admin.designations.destroy', $designation->id) }}" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" onclick="return confirm('Are you sure you want to delete this designation?')" title="Delete Designation">
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
                                        <p class="text-base font-bold text-slate-900">No designations found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($designations->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/30">
                        {{ $designations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@section('scripts')
    @include('components.admin-form-validation-script')
@endsection
@endsection
