@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-12">
        <!-- Add Unit Form Section -->
        <div class="space-y-6">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-white border-b-2 border-primary w-fit pb-1">Unit
                    Management</h1>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900" x-data="adminFormValidation()">
                <form method="POST" action="{{ route('admin.units.store') }}" class="p-8" @submit.prevent="submitForm($event)">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-input label="Unit Name" name="unit_name" placeholder="Full Name of Unit" required />

                        <x-input label="Unit Code" name="unit_code" placeholder="Short Code of Unit" required />

                        <div class="flex flex-col">
                            <label class="block text-sm font-bold text-transparent mb-1.5 select-none"
                                aria-hidden="true">&nbsp;</label>
                            <x-button icon="save" class="w-md">
                                Save Unit
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Units List Section -->
        <div class="space-y-6">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-bold tracking-tight text-white">Unit Details</h2>
            </div>



            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Unit Name
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Unit Code
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($units as $unit)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">{{ $units->firstItem() + $loop->index }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900">{{ $unit->unit_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-3 py-1 rounded-lg bg-indigo-50 text-[11px] font-bold text-indigo-600 border border-indigo-100 uppercase tracking-wider">
                                            {{ $unit->unit_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.units.edit', $unit->unit_id) }}"
                                                class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                                title="Edit Unit">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.units.destroy', $unit->unit_id) }}"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                                    onclick="return confirm('Are you sure you want to delete this unit? All associated data will be affected.')"
                                                    title="Delete Unit">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <i data-lucide="building" class="w-8 h-8 text-slate-300"></i>
                                            </div>
                                            <p class="text-base font-bold text-slate-900">No units found</p>
                                            <p class="text-sm mt-1 text-slate-500">Start by adding your first organizational
                                                unit above.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($units->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/30">
                        {{ $units->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @section('scripts')
        @include('components.admin-form-validation-script')
    @endsection
@endsection