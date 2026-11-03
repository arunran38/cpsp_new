@extends('layouts.admin')

@section('content')
@php
    $selectedDesignation = old('designation', $user->designation);
@endphp
<div class="max-w-4xl mx-auto" x-data="adminFormValidation({ designation: @json($selectedDesignation), userId: @json(encrypt($user->user_id)) })">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Edit User Profile</h1>

        </div>
        <x-button variant="light" size="sm" icon="arrow-left" onclick="window.location='{{ route('users.index') }}'">
            Back to List
        </x-button>
    </div>

    <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm">
        <form method="POST" action="{{ route('users.update', encrypt($user->user_id)) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                

                <!-- Name -->
                <div class="sm:col-span-2">
                    <x-input label="Full Name" name="name" :value="$user->name" required icon="user" />
                </div>

                <!-- PEN & Mobile -->
                <x-input label="PEN" name="pen" :value="$user->pen" required icon="hash" maxlength="7" minlength="6" />
                <x-input label="Mobile Number" name="mobile_number" :value="$user->mobile_number" required icon="smartphone" maxlength="10" />

                <!-- Email -->
                <div class="sm:col-span-2">
                    <x-input label="Email Address" name="email" type="email" :value="$user->email" required icon="mail" />
                </div>

                <!-- Password -->
                <div class="sm:col-span-2">
                    <x-input label="Change Password" name="password" type="password" placeholder="Leave blank to keep current" minlength="8" icon="lock" />
                </div>

                <!-- Current Photo -->
                @if($user->profilePhoto)
                <div id="current-photo-container" class="sm:col-span-2 flex items-center gap-6 p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="relative shrink-0">
                        <img id="photo-preview" src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" 
                             alt="Current Profile" 
                             class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm">
                        
                        <input type="hidden" name="remove_photo" id="remove_photo_input" value="0">
                        <button type="button" 
                                onclick="document.getElementById('remove_photo_input').value='1'; document.getElementById('current-photo-container').style.display='none';" 
                                class="absolute top-0 right-0 z-10 flex items-center justify-center w-6 h-6 bg-rose-500 text-white rounded-full shadow-lg hover:bg-rose-600 hover:scale-110 transition-all border-2 border-white focus:outline-none transform translate-x-1/3 -translate-y-1/3 cursor-pointer"
                                title="Remove photo">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Current Photo</p>
                        <p class="text-xs text-slate-500">Keep as is or upload a new one below.</p>
                    </div>
                </div>
                @endif

                <!-- Upload New Photo -->
                <div class="sm:col-span-2">
                    <x-input label="Update Photo" name="user_photo" type="file" icon="image" onchange="previewImage(this)" accept=".jpg,.jpeg,.png" />
                </div>

                <script>
                    function previewImage(input) {
                        const preview = document.getElementById('photo-preview');
                        if (input.files && input.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                if (preview) {
                                    preview.src = e.target.result;
                                } else {
                                    // Handle cases where preview element might not exist initially
                                }
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                </script>



                <!-- Status -->
                <x-select 
                    label="Account Status" 
                    name="status" 
                    :options="['Active' => 'Active', 'Transferred' => 'Transferred']" 
                    :selected="$user->status" 
                    required
                />

                <!-- Designation -->
                <x-select 
                    label="Designation" 
                    name="designation" 
                    x-model="designation"
                    required
                    :selected="$selectedDesignation"
                    :options="['' => 'Select Designation', 'CPO' => 'CPO', 'SCPO' => 'SCPO', 'ASI' => 'ASI', 'SI' => 'SI', 'IP' => 'IP', 'Others' => 'Others']"
                />


                <!-- Other Designation -->
                <div class="sm:col-span-2" x-show="designation === 'Others'" x-cloak x-transition>
                    <x-input label="Other Designation Details" name="other_designation" :value="$user->other_designation" icon="briefcase" />
                </div>
            </div>

            @if(isset($allRoles) && Auth::user()->canAccess('update roles'))
            <!-- Roles Assignment -->
            <div class="pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Assigned Roles</h3>
                        <p class="text-xs text-slate-500">Assign system roles directly to this user.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @php
                        $userRoles = $user->roles->pluck('name')->toArray();
                    @endphp
                    @foreach($allRoles as $role)
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                               {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-800">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($allPermissions) && Auth::user()->canAccess('update permissions'))
            <!-- Granular Permissions Assignment -->
            <div class="pt-6 border-t border-slate-100" x-data="{ permSearch: '' }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Direct Permissions</h3>
                        <p class="text-xs text-slate-500">Grant or revoke individual granular permissions for this user.</p>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="permSearch" placeholder="Filter permissions..."
                               class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-3 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    </div>
                </div>

                @php
                    $userPerms = $user->getDirectPermissions()->pluck('name')->toArray();
                    $categories = \App\Services\PermissionCatalog::categories();
                    $grouped = [];
                    foreach ($allPermissions as $perm) {
                        $cat = \App\Services\PermissionCatalog::categoryFor($perm->name);
                        $grouped[$cat][] = $perm;
                    }
                @endphp

                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    @foreach($grouped as $catKey => $perms)
                    @php
                        $catMeta = $categories[$catKey] ?? ['title' => ucwords($catKey), 'badge' => 'bg-slate-50 text-slate-700 border-slate-200'];
                    @endphp
                    <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">{{ $catMeta['title'] }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full border {{ $catMeta['badge'] }}">{{ count($perms) }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($perms as $perm)
                            <label class="flex items-start gap-2 p-2 rounded-lg bg-white border border-slate-200 hover:border-indigo-300 cursor-pointer transition text-xs"
                                   x-show="!permSearch || '{{ strtolower($perm->name) }}'.includes(permSearch.toLowerCase())">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                       class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ in_array($perm->name, old('permissions', $userPerms)) ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <span class="font-medium text-slate-800 block">{{ \App\Services\PermissionCatalog::label($perm->name) }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <x-button variant="primary" icon="refresh-cw" size="lg" type="submit">
                    Update Profile
                </x-button>
                <a href="{{ route('users.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition-all bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 text-slate-600">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
    @include('components.admin-form-validation-script')
@endsection
