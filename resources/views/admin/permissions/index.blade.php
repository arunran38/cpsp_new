@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
@php
    $modules = \App\Services\PermissionCatalog::categories();

    $getModuleInfo = function($permName) use ($modules) {
        $key = \App\Services\PermissionCatalog::categoryFor($permName);
        return ['key' => $key] + $modules[$key];
    };

    // Core protected permissions
    $protectedPerms = array_merge([
        'view users', 'create users', 'update users', 'delete users',
        'view roles', 'create roles', 'update roles', 'delete roles',
        'view permissions', 'create permissions', 'update permissions', 'delete permissions',
        'access admin dashboard'
    ], \App\Services\TSectionPermissionRegistry::all());
@endphp

<div class="space-y-6" x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    activeModule: 'all',
    visibleCount: 0,
    setActiveModule(modKey) {
        this.activeModule = this.activeModule === modKey ? 'all' : modKey;
        this.recalculateCount();
    },
    matchesFilter(name, moduleKey, rolesString) {
        if (this.activeModule !== 'all' && this.activeModule !== moduleKey) {
            return false;
        }
        if (!this.search || this.search.trim() === '') {
            return true;
        }
        const q = this.search.toLowerCase().trim();
        return name.toLowerCase().includes(q) ||
               moduleKey.toLowerCase().includes(q) ||
               rolesString.toLowerCase().includes(q);
    },
    recalculateCount() {
        this.$nextTick(() => {
            const rows = document.querySelectorAll('tr[data-perm-row]');
            let visible = 0;
            rows.forEach(r => {
                if (r.offsetParent !== null) visible++;
            });
            this.visibleCount = visible;
        });
    },
    init() {
        this.recalculateCount();
        this.$watch('search', () => this.recalculateCount());
    }
}">
    <!-- Premium Ambient Header Banner matching unit_add / roles design -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 pointer-events-none">
            <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
        </div>
        
        <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 z-10">
            <div class="flex items-center gap-4 text-center sm:text-left z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 text-white flex items-center justify-center shadow-md shadow-amber-500/25 shrink-0">
                    <i data-lucide="key" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                        Permissions Directory
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm mt-1">
                        Manage system permissions and access levels across {{ count($modules) }} functional modules.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2.5 shrink-0 z-10">
                <a href="{{ route('admin.roles.index') }}" 
                   class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-200 hover:text-white bg-slate-800/90 hover:bg-slate-800 border border-slate-700 transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-indigo-400"></i>
                    <span>Roles List</span>
                </a>
                <a href="{{ route('admin.permissions.create') }}" 
                   class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Add New Permission</span>
                </a>
            </div>
        </div>
    </div>


    <!-- Main Table Card with Rainbow Accent -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">
        <!-- Rainbow Accent Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 via-indigo-500 via-purple-500 via-pink-500 via-rose-500 via-amber-500 to-emerald-500"></div>

        <!-- Toolbar Section: Search & Quick KPI Badges -->
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex flex-col lg:flex-row items-center justify-between gap-4">
            <!-- Search Form / Live Search -->
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="relative w-full lg:w-96" @submit.prevent>
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" x-model="search" 
                       placeholder="Filter by permission, module, or role..."
                       class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all shadow-2xs">
                <button type="button" x-show="search.length > 0" @click="search = ''; recalculateCount()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </form>

            <!-- Quick Metrics & View Controls -->
            <div class="flex items-center gap-2.5 flex-wrap text-xs font-bold w-full lg:w-auto justify-start lg:justify-end">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="key" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>{{ $totalPermissionsCount }} System Permissions</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>{{ count($modules) }} Modules</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-purple-500"></i>
                    <span>{{ $totalRolesCount }} Roles</span>
                </span>

                <!-- Per-Page View Selector -->
                <div class="inline-flex items-center rounded-xl bg-slate-200/60 p-0.5 border border-slate-200">
                    <a href="{{ route('admin.permissions.index', ['per_page' => 25, 'search' => request('search')]) }}" 
                       class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ request('per_page', 25) == 25 ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        25 / page
                    </a>
                    <a href="{{ route('admin.permissions.index', ['per_page' => 'all', 'search' => request('search')]) }}" 
                       class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ request('per_page') === 'all' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        View All
                    </a>
                </div>
            </div>
        </div>

        <!-- Module Filter Pills Bar -->
        <div class="px-6 py-3 bg-white border-b border-slate-200 overflow-x-auto">
            <div class="flex items-center gap-2 min-w-max">
                <span class="text-xs font-black uppercase text-slate-400 tracking-wider mr-1 flex items-center gap-1">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Modules:
                </span>
                
                <!-- All Filter Pill -->
                <button type="button" @click="setActiveModule('all')"
                        :class="activeModule === 'all' 
                            ? 'bg-slate-900 text-white shadow-xs' 
                            : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                    <span>All Modules</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                          :class="activeModule === 'all' ? 'bg-white/20 text-white' : 'bg-white text-slate-800 shadow-2xs'">
                        {{ $totalPermissionsCount }}
                    </span>
                </button>

                <!-- Individual Module Filter Pills -->
                @foreach($modules as $mKey => $mConfig)
                    <button type="button" @click="setActiveModule('{{ $mKey }}')"
                            :class="activeModule === '{{ $mKey }}'
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                        <i data-lucide="{{ $mConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $mConfig['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 text-center w-16">#</th>
                        <th class="px-6 py-4 w-80 whitespace-nowrap">Permission Name</th>
                        <th class="px-6 py-4 w-56 whitespace-nowrap">Module</th>
                        <th class="px-6 py-4">Assigned Roles</th>
                        <th class="px-6 py-4 text-center w-32 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($permissions as $permission)
                        @php
                            $modInfo = $getModuleInfo($permission->name);
                            $rolesList = $permission->roles;
                            $roleNames = $rolesList->pluck('name')->toArray();
                            $rolesString = implode(' ', $roleNames);
                            $isProtected = in_array(strtolower($permission->name), $protectedPerms);
                            
                            $formattedTitle = \App\Services\PermissionCatalog::label($permission->name);
                            $permissionDescription = \App\Services\PermissionCatalog::description($permission->name);
                        @endphp

                        <tr class="hover:bg-indigo-50/20 transition-colors"
                            data-perm-row
                            x-show="matchesFilter('{{ addslashes($permission->name) }}', '{{ $modInfo['key'] }}', '{{ addslashes($rolesString) }}')">
                            <!-- Index -->
                            <td class="px-6 py-4 text-center align-middle">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center shadow-2xs">
                                    {{ $permissions->firstItem() ? ($permissions->firstItem() + $loop->index) : ($loop->iteration) }}
                                </span>
                            </td>

                            <!-- Permission Name -->
                            <td class="px-6 py-4 align-middle">
                                <span class="font-bold text-slate-900 text-sm leading-snug">
                                    {{ $formattedTitle }}
                                </span>
                                <p class="mt-1 text-xs leading-snug text-slate-600">{{ $permissionDescription }}</p>
                            </td>

                            <!-- Module Badge -->
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border shadow-2xs {{ $modInfo['badge'] }}">
                                    <i data-lucide="{{ $modInfo['icon'] }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $modInfo['title'] }}</span>
                                </span>
                            </td>

                            <!-- Assigned Roles -->
                            <td class="px-6 py-4 align-middle">
                                @if($rolesList->count() > 0)
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($rolesList as $role)
                                            @php
                                                $rName = strtolower($role->name);
                                                if ($rName === 'developer') {
                                                    $rBadge = 'bg-purple-50 text-purple-700 border-purple-200';
                                                } elseif ($rName === 'super admin') {
                                                    $rBadge = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                                } elseif ($rName === 'admin') {
                                                    $rBadge = 'bg-blue-50 text-blue-700 border-blue-200';
                                                } else {
                                                    $rBadge = 'bg-slate-100 text-slate-700 border-slate-200';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border shadow-2xs capitalize {{ $rBadge }}">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold text-slate-400 bg-slate-50 border border-slate-200">
                                        <i data-lucide="alert-circle" class="w-3 h-3 text-slate-300"></i> Unassigned
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                       class="p-2 rounded-xl text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-transparent transition-all shadow-2xs group"
                                       title="Edit Permission">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>

                                    @if($isProtected)
                                        <button type="button" 
                                                disabled
                                                class="p-2 rounded-xl text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed opacity-60"
                                                title="Core permission is protected from deletion">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission->id) }}"
                                              class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete permission \'{{ addslashes($permission->name) }}\'? This may affect assigned roles.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 hover:border-transparent transition-all shadow-2xs group"
                                                    title="Delete Permission">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 shadow-inner">
                                        <i data-lucide="key" class="w-7 h-7"></i>
                                    </div>
                                    <p class="text-base font-bold text-slate-800">No Permissions Found</p>
                                    <p class="text-xs text-slate-400 mt-1">Get started by creating your first system capability.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- No Filter Results State (Handled by Alpine) -->
        <div x-show="visibleCount === 0 && search.length > 0" 
             class="px-6 py-12 text-center border-t border-slate-100" style="display: none;">
            <div class="flex flex-col items-center max-w-sm mx-auto">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <i data-lucide="search-x" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-black text-slate-800">No matching permissions found</h3>
                <p class="text-xs text-slate-500 mt-1">No capability matched "<span class="font-bold text-indigo-600" x-text="search"></span>" under selected filter.</p>
                <button type="button" @click="search = ''; activeModule = 'all'; recalculateCount()"
                        class="mt-3 px-4 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all shadow-xs">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Pagination & Footer Stats -->
        @if($permissions->hasPages() || $permissions->total() > 0)
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold text-slate-600">
                <div>
                    @if($permissions->total() > 0)
                        Showing <span class="font-bold text-slate-900">{{ $permissions->firstItem() ?? 1 }}</span> to <span class="font-bold text-slate-900">{{ $permissions->lastItem() ?? $permissions->count() }}</span> of <span class="font-bold text-slate-900">{{ $permissions->total() }}</span> total permissions
                    @endif
                </div>

                @if($permissions->hasPages())
                    <div>
                        {{ $permissions->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
