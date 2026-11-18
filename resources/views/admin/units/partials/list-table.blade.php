<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50/50">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Unit Name</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Unit Code</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($units as $unit)
                <tr class="hover:bg-slate-50/30 transition-colors group">
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">
                            {{ $units->firstItem() + $loop->index }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors shrink-0">
                                <i data-lucide="building-2" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-primary transition-colors">
                                {{ $unit->unit_name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-lg bg-indigo-50 text-[11px] font-bold text-indigo-600 border border-indigo-100 uppercase tracking-wider">
                            {{ $unit->unit_code }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if(Auth::user()->canAccess('update units'))
                                <a href="{{ route('admin.units.edit', $unit->unit_id) }}"
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                    title="Edit Unit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                            @endif
                            @if(Auth::user()->canAccess('delete units'))
                                <form method="POST" action="{{ route('admin.units.destroy', $unit->unit_id) }}" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                        onclick="return confirm('Are you sure you want to delete this unit? All associated data will be affected.')"
                                        title="Delete Unit">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400">
                                <i data-lucide="{{ !empty($search) ? 'search-x' : 'building-2' }}" class="w-7 h-7"></i>
                            </div>
                            <p class="text-base font-bold text-slate-800">No units found</p>
                            @if(!empty($search))
                                <p class="text-xs text-slate-500 mt-1">No units match &ldquo;<span class="font-semibold text-slate-700">{{ $search }}</span>&rdquo;. Check spelling or add it above.</p>
                            @else
                                <p class="text-xs text-slate-500 mt-1">Start by adding your first organizational unit above.</p>
                            @endif
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
