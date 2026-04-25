@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Edit User Profile</h1>

        </div>
        <x-button variant="light" size="sm" icon="arrow-left" onclick="window.location='{{ route('users.index') }}'">
            Back to List
        </x-button>
    </div>

    <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm" x-data="{ designation: '{{ $user->designation }}' }">
        <form method="POST" action="{{ route('users.update', encrypt($user->user_id)) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                

                <!-- Name -->
                <div class="sm:col-span-2">
                    <x-input label="Full Name" name="name" :value="$user->name" required icon="user" />
                </div>

                <!-- PEN & Mobile -->
                <x-input label="PEN" name="pen" :value="$user->pen" required icon="hash" />
                <x-input label="Mobile Number" name="mobile_number" :value="$user->mobile_number" required icon="smartphone" />

                <!-- Email -->
                <div class="sm:col-span-2">
                    <x-input label="Email Address" name="email" type="email" :value="$user->email" required icon="mail" />
                </div>

                <!-- Password -->
                <div class="sm:col-span-2">
                    <x-input label="Change Password" name="password" type="password" placeholder="Leave blank to keep current" icon="lock" />
                </div>

                <!-- Current Photo -->
                @if($user->profilePhoto)
                <div class="sm:col-span-2 flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                    <img id="photo-preview" src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" 
                         alt="Current Profile" 
                         class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Current Photo</p>
                        <p class="text-xs text-slate-500">Keep as is or upload a new one below.</p>
                    </div>
                </div>
                @endif

                <!-- Upload New Photo -->
                <div class="sm:col-span-2">
                    <x-input label="Update Photo" name="user_photo" type="file" icon="image" onchange="previewImage(this)" />
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

                <!-- Role -->
                <x-select 
                    label="Access Role" 
                    name="role" 
                    :options="['user' => 'Standard User', 'admin' => 'Administrator']" 
                    :selected="$user->role" 
                    required
                />

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
                    :selected="$user->designation"
                    :options="['' => 'Select Designation', 'CPO' => 'CPO', 'SCPO' => 'SCPO', 'ASI' => 'ASI', 'SI' => 'SI', 'IP' => 'IP', 'Others' => 'Others']"
                />


                <!-- Other Designation -->
                <div class="sm:col-span-2" x-show="designation === 'Others'" x-cloak x-transition>
                    <x-input label="Other Designation Details" name="other_designation" :value="$user->other_designation" icon="briefcase" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <x-button variant="primary" icon="refresh-cw" size="lg">
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
