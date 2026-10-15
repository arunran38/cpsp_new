@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
@php
    $totalPermissionsCount = \Spatie\Permission\Models\Permission::count();

    // 7 High-Level System Modules matching Create/Edit Role
    $modules = [
        'users' => [
            'title' => 'User & Access',
            'icon' => 'users',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            'filter' => fn($p) => str_contains($p, 'user') || str_contains($p, 'role') || str_contains($p, 'permission') || str_contains($p, 'dashboard')
        ],
        'organization' => [
            'title' => 'Organization',
            'icon' => 'building-2',
            'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'filter' => fn($p) => str_contains($p, 'unit') || str_contains($p, 'department') || str_contains($p, 'designation') || (str_contains($p, 'seat') && !str_contains($p, 'diagnostic') && !str_contains($p, 'chart'))
        ],
        'petitions' => [
            'title' => 'Petitions',
            'icon' => 'file-text',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'filter' => fn($p) => (str_contains($p, 'petition') && !str_contains($p, 'inward') && !str_contains($p, 'chart')) || str_contains($p, 'link') || str_contains($p, 'duplicate')
        ],
        'inward' => [
            'title' => 'Inward Desk',
            'icon' => 'inbox',
            'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
            'filter' => fn($p) => (str_contains($p, 'inward') && !str_contains($p, 'stat')) || str_contains($p, 'transfer')
        ],
        'compliances' => [
            'title' => 'Petition Compliances',
            'icon' => 'clipboard-check',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'filter' => fn($p) => str_contains($p, 'compliance')
        ],
        'analytics' => [
            'title' => 'Reports & Charts',
            'icon' => 'pie-chart',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
            'filter' => fn($p) => str_contains($p, 'stat') || str_contains($p, 'chart') || str_contains($p, 'export') || str_contains($p, 'master report')
        ],
        'system' => [
            'title' => 'Maintenance',
            'icon' => 'activity',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            'filter' => fn($p) => str_contains($p, 'diagnostic') || str_contains($p, 'recycle')
        ]
    ];
@endphp

<div class="space-y-6" x-data="{
    search: '',
    inspectModal: false,
    selectedRole: '',
    selectedRoleId: null,
    selectedModules: {},
    openInspect(roleName, roleId, moduleData) {
        this.selectedRole = roleName;
        this.selectedRoleId = roleId;
        this.selectedModules = moduleData;
        this.inspectModal = true;
    },
    matchesSearch(name, permsString) {
        if (!this.search || this.search.trim() === '') return true;
        const q = this.search.toLowerCase().trim();
        return name.toLowerCase().includes(q) || permsString.toLowerCase().includes(q);
    }
}">
    <!-- Premium Header Section matching unit_add design -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-4 text-center sm:text-left z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                        System Roles
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm mt-1">
                        Manage user roles and assigned capabilities organized module-by-module.
                    </p>
                </div>
            </div>
            <div class="shrink-0 z-10">
                <a href="{{ route('admin.roles.create') }}" 
                   class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add New Role
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-900 px-5 py-3.5 rounded-2xl border border-emerald-200 flex items-center gap-3 shadow-xs font-semibold text-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Table Card with Rainbow Accent Bar -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">
        <!-- Rainbow Accent Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 via-indigo-500 via-purple-500 via-pink-500 via-rose-500 via-amber-500 to-emerald-500"></div>

        <!-- Search & Quick Stats Toolbar -->
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative w-full md:w-88">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" x-model="search" 
                       placeholder="Filter roles or capabilities..."
                       class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all shadow-2xs">
                <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Stats Pills -->
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap text-xs font-bold">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>{{ $roles->total() }} Roles</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="key" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>{{ $totalPermissionsCount }} System Capabilities</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-purple-500"></i>
                    <span>7 Modules</span>
                </span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 text-center w-16">#</th>
                        <th class="px-6 py-4 w-64 whitespace-nowrap">Role Details</th>
                        <th class="px-6 py-4">Assigned Modules & Capabilities</th>
                        <th class="px-6 py-4 text-center w-36 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($roles as $role)
                        @php
                            $rolePermNames = $role->permissions->pluck('name')->toArray();
                            $permCount = count($rolePermNames);
                            $isDev = strtolower($role->name) === 'developer';
                            $isSuper = strtolower($role->name) === 'super admin';
                            $isAdmin = strtolower($role->name) === 'admin';
                            $isProtected = $isDev || $isSuper || $isAdmin;

                            // Calculate module permissions breakdown
                            $roleModuleData = [];
                            foreach ($modules as $mKey => $mConfig) {
                                $matchedPerms = array_values(array_filter($rolePermNames, $mConfig['filter']));
                                if (count($matchedPerms) > 0) {
                                    $roleModuleData[$mKey] = [
                                        'title' => $mConfig['title'],
                                        'icon' => $mConfig['icon'],
                                        'badge' => $mConfig['badge'],
                                        'count' => count($matchedPerms),
                                        'permissions' => $matchedPerms,
                                    ];
                                }
                            }
                            $searchHaystack = strtolower($role->name . ' ' . implode(' ', $rolePermNames));
                        @endphp

                        <tr class="hover:bg-indigo-50/20 transition-colors"
                            x-show="matchesSearch('{{ addslashes($role->name) }}', '{{ addslashes($searchHaystack) }}')">
                            <!-- Index -->
                            <td class="px-6 py-5 text-center align-top">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center shadow-2xs">
                                    {{ $loop->iteration }}
                                </span>
                            </td>

                            <!-- Role Info -->
                            <td class="px-6 py-5 align-top">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 text-sm capitalize">{{ $role->name }}</span>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        @if($isDev)
                                            <span class="inline-flex items-center text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                                                Developer Role
                                            </span>
                                        @elseif($isProtected)
                                            <span class="inline-flex items-center text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200">
                                                Core Role
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                                Operational Role
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Module Capabilities & Coverage -->
                            <td class="px-6 py-5 align-top">
                                @if($permCount > 0)
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <!-- Module Badges matching Edit Role style -->
                                        @foreach($roleModuleData as $mKey => $mData)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border shadow-2xs {{ $mData['badge'] }}"
                                                  title="{{ $mData['count'] }} permissions in {{ $mData['title'] }}">
                                                <i data-lucide="{{ $mData['icon'] }}" class="w-3.5 h-3.5"></i>
                                                <span>{{ $mData['title'] }}</span>
                                                <span class="px-1.5 py-0.2 rounded-full bg-white/90 font-black text-[10px] text-slate-800 shadow-2xs">
                                                    {{ $mData['count'] }}
                                                </span>
                                            </span>
                                        @endforeach

                                        <button type="button" 
                                                @click="openInspect('{{ addslashes($role->name) }}', {{ $role->id }}, {{ json_encode($roleModuleData) }})"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline px-2 py-1 ml-1"
                                                title="View detailed permissions">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View Details
                                        </button>
                                    </div>
                                @else
                                    <!-- 0 Permissions Empty State -->
                                    <div class="flex items-center gap-3 py-1">
                                        <span class="text-xs font-medium text-slate-400 italic flex items-center gap-1.5">
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-slate-300"></i> No permissions assigned yet
                                        </span>
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-lg border border-indigo-200 transition-colors">
                                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Configure in Edit Role →
                                        </a>
                                    </div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-5 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-transparent transition-all shadow-2xs group"
                                       title="Edit Role & Permissions">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-indigo-500 group-hover:text-white transition-colors"></i> Edit
                                    </a>

                                    @if($isProtected)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed"
                                              title="Core system roles cannot be deleted">
                                            <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i> Locked
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete role {{ addslashes($role->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 hover:border-transparent transition-all shadow-2xs group"
                                                    title="Delete Role">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500 group-hover:text-white transition-colors"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                        <i data-lucide="shield-off" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-base font-bold text-slate-800">No roles found</p>
                                    <p class="text-xs text-slate-400 mt-1">Click "Add New Role" above to create one.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    <!-- ------------------------------------------------------------- -->
    <!-- Inspect Capabilities Modal (Module-by-Module like Edit Role)    -->
    <!-- ------------------------------------------------------------- -->
    <div x-show="inspectModal" x-cloak
         @click="inspectModal = false"
         @keydown.escape.window="inspectModal = false"
         class="fixed inset-0 z-50 flex items-start justify-center p-3 sm:p-6 pt-6 sm:pt-10 bg-slate-900/70 backdrop-blur-sm overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative bg-white border border-slate-200 shadow-2xl w-full max-w-4xl rounded-2xl overflow-hidden flex flex-col"
             style="height: 85vh; max-height: calc(100vh - 5rem);"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Modal Header (Always pinned at top, 100% visible) -->
            <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 capitalize" x-text="selectedRole"></h3>
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200">Capabilities Breakdown</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Assigned module permissions and capabilities</p>
                    </div>
                </div>
                <button type="button" @click="inspectModal = false"
                        class="w-9 h-9 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 hover:text-slate-900 flex items-center justify-center transition-all shadow-xs"
                        title="Close Modal (Esc)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Smoothly scrollable, flex-1 min-h-0) -->
            <div class="p-6 overflow-y-auto flex-1 min-h-0 space-y-3.5"
                 style="scrollbar-width: thin; scrollbar-color: #94a3b8 #f1f5f9;">
                <template x-for="(mod, modKey) in selectedModules" :key="modKey">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                <span x-text="mod.title"></span>
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 shadow-2xs"
                                  x-text="mod.count + ' Granted'"></span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-0.5">
                            <template x-for="perm in mod.permissions" :key="perm">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white border border-slate-200 text-slate-800 shadow-2xs hover:border-slate-300 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="perm"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer (Always pinned at bottom, shrink-0) -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <span class="text-xs text-slate-500 font-medium">To modify permissions, open role in editor.</span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="inspectModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition-colors">
                        Close
                    </button>
                    <a :href="'/admin/roles/' + selectedRoleId + '/edit'"
                       class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Role
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
