@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')
@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions */

    // -------------------------------------------------------------
    // Define the 8 High-Level System Modules & Their Capabilities
    // -------------------------------------------------------------
    $modules = [
        'users' => [
            'key' => 'users',
            'title' => 'User & Access Management',
            'desc' => 'User accounts, role configurations, system permissions & admin panel access',
            'icon' => 'users',
            'gradient' => 'from-blue-500 to-indigo-600 shadow-blue-500/25',
            'accent' => 'blue',
            'crud' => [
                'users' => ['name' => 'Users', 'desc' => 'Accounts & credentials', 'icon' => 'user'],
                'roles' => ['name' => 'Roles', 'desc' => 'Role configurations', 'icon' => 'shield'],
                'permissions' => ['name' => 'Permissions', 'desc' => 'Granular capability items', 'icon' => 'key'],
            ],
            'special' => [
                'Admin Dashboard' => [
                    'desc' => 'Global administrative access',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'dashboard') && !\App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ]
            ]
        ],
        'organization' => [
            'key' => 'organization',
            'title' => 'Organization & Master Data',
            'desc' => 'Police stations, circles, departments, official ranks, and office seats',
            'icon' => 'building-2',
            'gradient' => 'from-cyan-500 to-blue-600 shadow-cyan-500/25',
            'accent' => 'cyan',
            'crud' => [
                'units' => ['name' => 'Units', 'desc' => 'Police stations & headquarters', 'icon' => 'building-2'],
                'departments' => ['name' => 'Departments', 'desc' => 'External govt bodies', 'icon' => 'landmark'],
                'designations' => ['name' => 'Designations', 'desc' => 'Official ranks & titles', 'icon' => 'award'],
                'seats' => ['name' => 'Seats', 'desc' => 'Desk allocations & assignments', 'icon' => 'briefcase'],
            ],
            'special' => []
        ],
        'petitions' => [
            'key' => 'petitions',
            'title' => 'Petitions & Grievance Lifecycle',
            'desc' => 'Petition processing, status workflows, and jurisdiction visibility scopes',
            'icon' => 'file-text',
            'gradient' => 'from-emerald-500 to-teal-600 shadow-emerald-500/25',
            'accent' => 'emerald',
            'crud' => [
                'petitions' => ['name' => 'Petitions', 'desc' => 'Grievances & petition records', 'icon' => 'file-text'],
            ],
            'special' => [
                'Visibility Scopes' => [
                    'desc' => 'Personal, recent, or department-wide visibility',
                    'filter' => fn($p) => (str_contains(strtolower($p->name), 'own') || str_contains(strtolower($p->name), 'all petitions') || str_contains(strtolower($p->name), 'recent')) && !\App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ],
                'Duplicate Management' => [
                    'desc' => 'Duplicate detection warnings, linking and unlinking records',
                    'filter' => fn($p) => (str_contains(strtolower($p->name), 'duplicate') || str_contains(strtolower($p->name), 'link')) && !\App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ]
            ]
        ],
        't_section' => [
            'key' => 't_section',
            'title' => 'T-Section',
            'desc' => 'Confidential & Intelligence Desk',
            'icon' => 'shield-alert',
            'gradient' => 'from-rose-600 to-red-700 shadow-rose-600/25',
            'accent' => 'rose',
            'crud' => [],
            'special' => [
                'Confidential Capabilities' => [
                    'desc' => 'Confidential desk operations and intelligence controls',
                    'filter' => fn($p) => \App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ]
            ]
        ],
        'inward' => [
            'key' => 'inward',
            'title' => 'Inward Desk & File Transfers',
            'desc' => 'Inward registration desk, petition intake, and inter-unit file movements',
            'icon' => 'inbox',
            'gradient' => 'from-teal-500 to-emerald-600 shadow-teal-500/25',
            'accent' => 'teal',
            'crud' => [],
            'special' => [
                'Inward Registration' => [
                    'desc' => 'Registration form & petition intake',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'inward') && !str_contains(strtolower($p->name), 'stat')
                ],
                'File Movements' => [
                    'desc' => 'File transfer visibility & filtering',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'transfer')
                ]
            ]
        ],
        'compliances' => [
            'key' => 'compliances',
            'title' => 'Petition Compliances',
            'desc' => 'Directives tracking, compliance reporting, and orders monitoring',
            'icon' => 'clipboard-check',
            'gradient' => 'from-indigo-600 to-purple-600 shadow-indigo-500/25',
            'accent' => 'indigo',
            'crud' => [
                'compliances' => ['name' => 'Compliances', 'desc' => 'Directives & compliance records', 'icon' => 'clipboard-check'],
            ],
            'special' => [
            ]
        ],
        'analytics' => [
            'key' => 'analytics',
            'title' => 'Reports, Analytics & Charts',
            'desc' => 'Master reports, operational statistics, data exports, and graphical charts',
            'icon' => 'pie-chart',
            'gradient' => 'from-purple-500 to-pink-600 shadow-purple-500/25',
            'accent' => 'purple',
            'crud' => [],
            'special' => [
                'Master Reports' => [
                    'desc' => 'Consolidated master reporting',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'master report')
                ],
                'Statistics, Diagnostics & Exports' => [
                    'desc' => 'Operational statistics, seat diagnostics, and data exports',
                    'filter' => fn($p) => (str_contains(strtolower($p->name), 'stat') || str_contains(strtolower($p->name), 'export') || str_contains(strtolower($p->name), 'diagnostic') || str_contains(strtolower($p->name), 'pendency')) && !str_contains(strtolower($p->name), 'chart') && !\App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ],
                'Visual Analytics Charts' => [
                    'desc' => 'Trends, nature, status & seat distribution charts',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'chart')
                ]
            ]
        ],
        'system' => [
            'key' => 'system',
            'title' => 'Deleted Record Recovery',
            'desc' => 'View, restore, and permanently remove deleted petition records',
            'icon' => 'activity',
            'gradient' => 'from-rose-500 to-orange-600 shadow-rose-500/25',
            'accent' => 'rose',
            'crud' => [],
            'special' => [
                'Health & Recovery' => [
                    'desc' => 'View, restore, or permanently delete soft-deleted petitions',
                    'filter' => fn($p) => str_contains(strtolower($p->name), 'recycle') && !\App\Services\TSectionPermissionRegistry::isTSectionPermission($p->name)
                ]
            ]
        ]
    ];

    $crudActions = ['view', 'create', 'update', 'delete'];
    $allocatedPermIds = [];
    $moduleData = [];

    foreach ($modules as $modKey => $mod) {
        $moduleData[$modKey] = [
            'info' => $mod,
            'crud' => [],
            'special' => [],
            'all_permissions' => []
        ];

        // 1. Populate CRUD entities
        foreach ($mod['crud'] as $entityKey => $entityMeta) {
            foreach ($crudActions as $act) {
                $targetName = "{$act} {$entityKey}";
                $found = $permissions->first(fn($p) => strtolower(trim($p->name)) === $targetName);
                if ($found) {
                    $moduleData[$modKey]['crud'][$entityKey][$act] = $found;
                    $moduleData[$modKey]['all_permissions'][] = $found;
                    $allocatedPermIds[] = $found->id;
                }
            }
        }

        // 2. Populate Special groups
        foreach ($mod['special'] as $groupName => $groupConfig) {
            $matched = $permissions->filter(function($p) use ($groupConfig, $allocatedPermIds) {
                if (in_array($p->id, $allocatedPermIds)) return false;
                return ($groupConfig['filter'])($p);
            });

            if ($matched->isNotEmpty()) {
                $moduleData[$modKey]['special'][$groupName] = [
                    'desc' => $groupConfig['desc'],
                    'permissions' => $matched
                ];
                foreach ($matched as $mp) {
                    $moduleData[$modKey]['all_permissions'][] = $mp;
                    $allocatedPermIds[] = $mp->id;
                }
            }
        }
    }

    // Catch any unallocated permissions
    $unallocated = $permissions->filter(fn($p) => !in_array($p->id, $allocatedPermIds));
    if ($unallocated->isNotEmpty()) {
        $moduleData['system']['special']['Additional Privileges'] = [
            'desc' => 'Other standalone system capabilities',
            'permissions' => $unallocated
        ];
        foreach ($unallocated as $up) {
            $moduleData['system']['all_permissions'][] = $up;
        }
    }

    $oldPerms = old('permissions', []);
@endphp

<div class="space-y-6" x-data="rolePermissionsManager()">
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
                <a href="{{ route('admin.roles.index') }}" 
                   class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 text-white flex items-center justify-center transition-all shadow-sm shrink-0"
                   title="Back to Roles List">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm">
                        Add New Role
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm mt-1">
                        Define a new role and grant capabilities structured module-by-module.
                    </p>
                </div>
            </div>
            <div class="shrink-0 z-10">
                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-200 hover:text-white bg-slate-800/90 hover:bg-slate-800 border border-slate-700 transition-all shadow-sm">
                    <i data-lucide="shield" class="w-4 h-4 text-indigo-400"></i> Roles List
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <!-- Role Details Card -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 sm:p-7">
                <div class="max-w-2xl">
                    <label for="name" class="block text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider mb-2">
                        Role Name <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-600">
                            <i data-lucide="shield" class="w-5 h-5"></i>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 transition-all text-base shadow-xs"
                               placeholder="e.g. Officer, Section Supervisor..." required>
                    </div>
                    @error('name')
                        <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Toolbar: Search + Module Quick Nav Pills + Quick Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col gap-3.5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full sm:w-auto min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" x-model="search" 
                               placeholder="Filter modules or permissions..."
                               class="w-full pl-10 pr-9 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all shadow-xs">
                        <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Batch Actions -->
                    <div class="flex items-center gap-2 sm:gap-2.5 w-auto justify-end flex-nowrap shrink-0">
                        <button type="button" @click="selectAll()" 
                                class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm shadow-emerald-600/20 hover:-translate-y-0.5 transition-all whitespace-nowrap shrink-0">
                            <i data-lucide="check-check" class="w-4 h-4"></i> Select All
                        </button>
                        <button type="button" @click="selectViewOnly()" 
                                class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-sm shadow-blue-600/20 hover:-translate-y-0.5 transition-all whitespace-nowrap shrink-0">
                            <i data-lucide="eye" class="w-4 h-4"></i> All View
                        </button>
                        <button type="button" @click="deselectAll()" 
                                class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-sm shadow-rose-600/20 hover:-translate-y-0.5 transition-all whitespace-nowrap shrink-0">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Module Quick Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs sm:text-sm font-bold">
                    <button type="button" @click="activeModule = 'all'"
                            :class="activeModule === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-800 hover:bg-slate-100 border border-slate-300'"
                            class="px-3.5 py-2 rounded-xl transition-all">
                        All Modules ({{ count($modules) }})
                    </button>
                    @foreach($modules as $modKey => $mod)
                        <button type="button" @click="activeModule = '{{ $modKey }}'"
                                :class="activeModule === '{{ $modKey }}' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-800 hover:bg-slate-100 border border-slate-300'"
                                class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5">
                            <span>{{ $mod['title'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------------------- -->
        <!-- Module-by-Module Cards Section                                -->
        <!-- ------------------------------------------------------------- -->
        <div class="space-y-6">
            @foreach($moduleData as $modKey => $data)
                @php 
                    $modInfo = $data['info']; 
                    $modTotal = count($data['all_permissions']);
                    $allPermNames = array_map(fn($p) => is_object($p) ? $p->name : (string) ($p['name'] ?? ''), $data['all_permissions']);
                    $searchData = strtolower($modInfo['title'] . ' ' . $modInfo['desc'] . ' ' . implode(' ', $allPermNames));
                @endphp

                <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden module-container"
                     x-show="(activeModule === 'all' || activeModule === '{{ $modKey }}') && matchesSearch('{{ addslashes($searchData) }}')"
                     data-module-card="{{ $modKey }}">
                    
                    <!-- Module Card Header with Vibrant Gradient Icon & Module Toggle -->
                    <div class="p-6 bg-slate-50/80 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $modInfo['gradient'] }} text-white flex items-center justify-center shrink-0 shadow-md">
                                <i data-lucide="{{ $modInfo['icon'] }}" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-tight">{{ $modInfo['title'] }}</h3>
                                <p class="text-xs sm:text-sm font-semibold text-slate-600 mt-0.5">{{ $modInfo['desc'] }}</p>
                            </div>
                        </div>

                        <!-- Module Master Toggle & Live Counter -->
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <span class="text-xs sm:text-sm font-black text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-slate-300 shadow-2xs">
                                <span class="font-black text-indigo-600" id="mod-counter-{{ $modKey }}">0</span> / {{ $modTotal }}
                            </span>
                            <label class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50/80 hover:bg-indigo-100 transition-all cursor-pointer">
                                <input type="checkbox" 
                                       id="mod-master-{{ $modKey }}"
                                       @change="toggleModuleGroup('{{ $modKey }}', $event.target.checked)"
                                       class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/40 cursor-pointer">
                                <span class="text-xs sm:text-sm font-black text-indigo-950 uppercase tracking-wide">Toggle Module</span>
                            </label>
                        </div>
                    </div>

                    <!-- Module Body: CRUD Grid & Structured Special Sub-sections -->
                    <div class="p-6 space-y-6">
                        
                        <!-- 1. CRUD Capabilities Table (if module has CRUD entities) -->
                        @if(!empty($data['crud']))
                            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-100 text-slate-800 text-xs sm:text-sm font-black uppercase tracking-wider border-b border-slate-200">
                                                <th class="px-5 py-3.5 text-slate-900">Resource</th>
                                                <th class="px-3 py-3.5 text-center w-24">
                                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xs">ALL</span>
                                                </th>
                                                <th class="px-3 py-3.5 text-center w-24">
                                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xs">VIEW</span>
                                                </th>
                                                <th class="px-3 py-3.5 text-center w-24">
                                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-xs">CREATE</span>
                                                </th>
                                                <th class="px-3 py-3.5 text-center w-24">
                                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-xs">UPDATE</span>
                                                </th>
                                                <th class="px-3 py-3.5 text-center w-24">
                                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-xs">DELETE</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-sm">
                                            @foreach($data['crud'] as $entityKey => $actions)
                                                @php
                                                    $entityMeta = $modInfo['crud'][$entityKey];
                                                    $rowSlug = Str::slug($entityKey);
                                                @endphp
                                                <tr class="hover:bg-indigo-50/30 transition-colors perm-module-row"
                                                    x-show="matchesSearch('{{ strtolower($entityMeta['name'] . ' ' . $entityKey . ' ' . $entityMeta['desc']) }}')"
                                                    data-module="{{ $rowSlug }}">
                                                    <td class="px-5 py-3.5">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                                                <i data-lucide="{{ $entityMeta['icon'] }}" class="w-4 h-4"></i>
                                                            </div>
                                                            <div>
                                                                <div class="font-black text-slate-900 text-sm sm:text-base leading-tight">{{ $entityMeta['name'] }}</div>
                                                                <div class="text-slate-600 text-xs sm:text-sm font-semibold mt-0.5">{{ $entityMeta['desc'] }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <!-- Row Master Toggle -->
                                                    <td class="px-3 py-3.5 text-center">
                                                        <input type="checkbox" 
                                                               id="row-master-{{ $rowSlug }}"
                                                               @change="toggleRow('{{ $rowSlug }}', $event.target.checked); syncModuleStats('{{ $modKey }}')"
                                                               class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/40 cursor-pointer">
                                                    </td>
                                                    <!-- View -->
                                                    <td class="px-3 py-3.5 text-center">
                                                        @if(isset($actions['view']))
                                                            @php $perm = $actions['view']; @endphp
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $perm->name }}"
                                                                   data-action="view"
                                                                   {{ in_array($perm->name, $oldPerms) ? 'checked' : '' }}
                                                                   @change="onCheckboxChange('{{ $rowSlug }}', '{{ $modKey }}')"
                                                                   title="{{ $perm->name }}"
                                                                   class="perm-checkbox perm-mod-{{ $modKey }} perm-row-{{ $rowSlug }} perm-act-view w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/40 cursor-pointer">
                                                        @endif
                                                    </td>
                                                    <!-- Create -->
                                                    <td class="px-3 py-3.5 text-center">
                                                        @if(isset($actions['create']))
                                                            @php $perm = $actions['create']; @endphp
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $perm->name }}"
                                                                   data-action="create"
                                                                   {{ in_array($perm->name, $oldPerms) ? 'checked' : '' }}
                                                                   @change="onCheckboxChange('{{ $rowSlug }}', '{{ $modKey }}')"
                                                                   title="{{ $perm->name }}"
                                                                   class="perm-checkbox perm-mod-{{ $modKey }} perm-row-{{ $rowSlug }} perm-act-create w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/40 cursor-pointer">
                                                        @endif
                                                    </td>
                                                    <!-- Update -->
                                                    <td class="px-3 py-3.5 text-center">
                                                        @if(isset($actions['update']))
                                                            @php $perm = $actions['update']; @endphp
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $perm->name }}"
                                                                   data-action="update"
                                                                   {{ in_array($perm->name, $oldPerms) ? 'checked' : '' }}
                                                                   @change="onCheckboxChange('{{ $rowSlug }}', '{{ $modKey }}')"
                                                                   title="{{ $perm->name }}"
                                                                   class="perm-checkbox perm-mod-{{ $modKey }} perm-row-{{ $rowSlug }} perm-act-update w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-amber-500/40 cursor-pointer">
                                                        @endif
                                                    </td>
                                                    <!-- Delete -->
                                                    <td class="px-3 py-3.5 text-center">
                                                        @if(isset($actions['delete']))
                                                            @php $perm = $actions['delete']; @endphp
                                                            <input type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $perm->name }}"
                                                                   data-action="delete"
                                                                   {{ in_array($perm->name, $oldPerms) ? 'checked' : '' }}
                                                                   @change="onCheckboxChange('{{ $rowSlug }}', '{{ $modKey }}')"
                                                                   title="{{ $perm->name }}"
                                                                   class="perm-checkbox perm-mod-{{ $modKey }} perm-row-{{ $rowSlug }} perm-act-delete w-5 h-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500/40 cursor-pointer">
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- 2. Structured Operational Sub-Sections -->
                        @if(!empty($data['special']))
                            <div class="space-y-4">
                                @foreach($data['special'] as $groupTitle => $groupInfo)
                                    @php $subSlug = Str::slug($groupTitle); @endphp
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5">
                                        <div class="flex items-center justify-between mb-3.5 border-b border-slate-200 pb-2.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                                <h4 class="text-xs sm:text-sm font-black text-slate-900 uppercase tracking-wider">{{ $groupTitle }}</h4>
                                                @if(!empty($groupInfo['desc']))
                                                    <span class="text-xs font-semibold text-slate-600">({{ $groupInfo['desc'] }})</span>
                                                @endif
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 bg-white px-2.5 py-1 rounded-md border border-slate-300 shadow-2xs">
                                                {{ count($groupInfo['permissions']) }} items
                                            </span>
                                        </div>

                                        <!-- Aligned Grid of Structured Toggle Chips -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                            @foreach($groupInfo['permissions'] as $perm)
                                                <label class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group shadow-2xs">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $perm->name }}"
                                                           {{ in_array($perm->name, $oldPerms) ? 'checked' : '' }}
                                                           @change="onCheckboxChange('{{ $subSlug }}', '{{ $modKey }}')"
                                                           class="perm-checkbox perm-mod-{{ $modKey }} perm-row-{{ $subSlug }} w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30 cursor-pointer shrink-0">
                                                    <span class="select-none leading-snug">
                                                        <span class="block text-xs sm:text-sm font-bold text-slate-900">
                                                            {{ \App\Services\PermissionCatalog::label($perm->name) }}
                                                        </span>
                                                        <span class="mt-0.5 block text-[11px] leading-snug text-slate-600">
                                                            {{ \App\Services\PermissionCatalog::description($perm->name) }}
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sticky Save Footer with Vibrant Gradient Action Button -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xl p-4 sm:px-6 flex items-center justify-between sticky bottom-4 z-20 mt-6 backdrop-blur-md">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-bold text-slate-800">
                    <span class="text-indigo-600 font-black text-sm sm:text-base" x-text="selectedCount">0</span> of <span class="font-black" x-text="totalCount">{{ $permissions->count() }}</span> capabilities assigned
                </span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.roles.index') }}" 
                   class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-7 py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:via-purple-500 hover:to-pink-500 shadow-md shadow-indigo-600/30 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Create Role</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function rolePermissionsManager() {
    return {
        activeModule: 'all',
        search: '',
        selectedCount: 0,
        totalCount: 0,
        getRoot() {
            return this.$root || document.querySelector('[x-data*="rolePermissionsManager"]') || document;
        },
        init() {
            this.updateCount();
            this.syncAllModules();
            this.$nextTick(() => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        },
        updateCount() {
            const root = this.getRoot();
            const allCheckboxes = root.querySelectorAll('input.perm-checkbox');
            const checkedCheckboxes = root.querySelectorAll('input.perm-checkbox:checked');
            this.totalCount = allCheckboxes.length;
            this.selectedCount = checkedCheckboxes.length;
        },
        toggleModuleGroup(modKey, isChecked) {
            const root = this.getRoot();
            const checkboxes = root.querySelectorAll('.perm-mod-' + modKey);
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });

            // Sync all row masters inside this module
            const container = root.querySelector('.module-container[data-module-card="' + modKey + '"]');
            if (container) {
                const rows = container.querySelectorAll('tr.perm-module-row');
                rows.forEach(r => {
                    const slug = r.getAttribute('data-module');
                    if (slug) this.syncRowMaster(slug);
                });
            }

            this.syncModuleStats(modKey);
            this.updateCount();
        },
        toggleRow(rowSlug, isChecked) {
            const root = this.getRoot();
            const checkboxes = root.querySelectorAll('.perm-row-' + rowSlug);
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            this.updateCount();
        },
        onCheckboxChange(rowSlug, modKey) {
            if (rowSlug) this.syncRowMaster(rowSlug);
            if (modKey) this.syncModuleStats(modKey);
            this.updateCount();
        },
        syncRowMaster(rowSlug) {
            const root = this.getRoot();
            const checkboxes = Array.from(root.querySelectorAll('.perm-row-' + rowSlug));
            const master = root.querySelector('#row-master-' + rowSlug);
            if (!master || checkboxes.length === 0) return;

            const checkedCount = checkboxes.filter(cb => cb.checked).length;
            master.checked = checkedCount === checkboxes.length;
            master.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        },
        syncModuleStats(modKey) {
            const root = this.getRoot();
            const checkboxes = Array.from(root.querySelectorAll('.perm-mod-' + modKey));
            const master = root.querySelector('#mod-master-' + modKey);
            const counter = root.querySelector('#mod-counter-' + modKey);
            
            if (checkboxes.length === 0) return;

            const checkedCount = checkboxes.filter(cb => cb.checked).length;
            if (counter) counter.innerText = checkedCount;

            if (master) {
                master.checked = checkedCount === checkboxes.length;
                master.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        },
        syncAllModules() {
            const root = this.getRoot();
            const containers = root.querySelectorAll('.module-container');
            containers.forEach(c => {
                const modKey = c.getAttribute('data-module-card');
                if (modKey) this.syncModuleStats(modKey);
            });

            const rows = root.querySelectorAll('tr.perm-module-row');
            rows.forEach(r => {
                const slug = r.getAttribute('data-module');
                if (slug) this.syncRowMaster(slug);
            });
        },
        selectAll() {
            const root = this.getRoot();
            root.querySelectorAll('input.perm-checkbox').forEach(cb => {
                cb.checked = true;
            });
            this.syncAllModules();
            this.updateCount();
        },
        deselectAll() {
            const root = this.getRoot();
            root.querySelectorAll('input.perm-checkbox').forEach(cb => {
                cb.checked = false;
            });
            this.syncAllModules();
            this.updateCount();
        },
        selectViewOnly() {
            const root = this.getRoot();
            root.querySelectorAll('input.perm-checkbox').forEach(cb => {
                cb.checked = cb.classList.contains('perm-act-view');
            });
            this.syncAllModules();
            this.updateCount();
        },
        matchesSearch(text) {
            if (!this.search || !this.search.trim()) return true;
            return text.toLowerCase().includes(this.search.toLowerCase().trim());
        }
    };
}
</script>
@endsection
