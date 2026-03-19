@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="list" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Submitted Petitions</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Manage and track all official complaints in the system</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('petitions.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> New Petition
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
        {{ session('success') }}
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Petition Directory</h3>
            <div class="relative w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" placeholder="Search records..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Record No</th>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold">Nature / Action</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($petitions as $petition)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800">{{ $petition->petition_no }}</span>
                                <span class="block text-xs text-slate-500 mt-1">{{ count($petition->addresses) }} Parties Linked</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d M, Y') }}
                                <span class="block text-xs text-slate-400 mt-1">{{ $petition->mode_of_petition_received }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-slate-700">
                                <span class="font-semibold">{{ $petition->nature_of_petition }}</span>
                                <span class="block text-xs text-slate-500 truncate mt-1" title="{{ $petition->description }}">{{ $petition->description }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700">
                                    {{ $petition->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('petitions.show', $petition->petition_id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors" title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('petitions.edit', $petition->petition_id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Record">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('petitions.destroy', $petition->petition_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this petition?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="Delete Record">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3 text-slate-300"></i>
                                    <p class="text-lg font-medium text-slate-500">No petitions found</p>
                                    <p class="text-sm">Create a new petition to get started</p>
                                    <a href="{{ route('petitions.create') }}" class="mt-4 px-4 py-2 text-sm font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-all">
                                        Add Petition
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
