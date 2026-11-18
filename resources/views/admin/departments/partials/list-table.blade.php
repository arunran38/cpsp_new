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
                        <span class="text-sm font-bold text-slate-400 group-hover:text-primary transition-colors">
                            {{ $departments->firstItem() + $loop->index }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors shrink-0">
                                <i data-lucide="building" class="w-4 h-4"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-primary transition-colors">
                                {{ $department->department_name }}
                            </span>
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
                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400">
                                <i data-lucide="{{ !empty($search) ? 'search-x' : 'building' }}" class="w-7 h-7"></i>
                            </div>
                            <p class="text-base font-bold text-slate-800">No departments found</p>
                            @if(!empty($search))
                                <p class="text-xs text-slate-500 mt-1">No departments match &ldquo;<span class="font-semibold text-slate-700">{{ $search }}</span>&rdquo;. Check spelling or add it above.</p>
                            @else
                                <p class="text-xs text-slate-500 mt-1">Start by adding your first organizational department above.</p>
                            @endif
                        </div>
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
