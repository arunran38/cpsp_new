@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Units Details</h1>
     
        </div>
        <a href="{{ route('admin.units.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-black bg-primary hover:bg-primary-hover rounded-xl shadow-lg shadow-primary/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add New Unit
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-medium">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Name</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Code</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($units as $unit)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-900">{{ $unit->unit_name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded bg-slate-100 text-[10px] font-bold text-slate-600 uppercase">
                                    {{ $unit->unit_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.units.edit', $unit->unit_id) }}" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Edit Unit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.units.destroy', $unit->unit_id) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                onclick="return confirm('Are you sure you want to delete this unit?')"
                                                title="Delete Unit">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="building" class="w-12 h-12 text-slate-200 mb-4"></i>
                                    <p class="text-base font-semibold text-slate-900">No units found</p>
                                    <p class="text-sm mt-1">Start by adding your first organizational unit.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
