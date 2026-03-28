@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ designation: '' }">
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900"> User Registration</h1>
       
    </div>

    <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-8" enctype="multipart/form-data" >
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <x-input label="Full Name" name="name" placeholder="Enter Full Name" required icon="user" />
                </div>

                <!-- PEN & Mobile -->
                <x-input label="PEN" name="pen" placeholder="Enter PEN" required icon="hash" />
                <x-input label="Mobile Number" name="mobile_number" placeholder="Enter Mobile Number" required icon="smartphone" />

                <!-- Email -->
                <div class="sm:col-span-2">
                    <x-input label="Email Address" name="email" type="email" placeholder="Enter Email Address" required icon="mail" />
                </div>

                <!-- Password -->
                <div class="sm:col-span-2">
                    <x-input label="Temporary Password" name="password" type="password" placeholder="Enter Password" required icon="lock" />
                </div>

                <!--Upload Photo-->
                <div class="sm:col-span-2">
                    <x-input label="Upload Photo" name="user_photo" type="file" required icon="image" />
                </div> 

                <!-- Role -->
                <x-select 
                    label="Role" 
                    name="role" 
                    :options="['user' => 'User', 'admin' => 'Admin']" 
                    selected="user" 
                    required
                />

                <!-- Designation -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Designation</label>
                    <div class="relative flex items-center group">
                        <select name="designation" x-model="designation" 
                                class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 transition-all duration-200 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none appearance-none group-hover:border-slate-300 cursor-pointer" 
                                required>
                            <option value="">Select Designation</option>
                            <option value="CPO">CPO</option>
                            <option value="SCPO">SCPO</option>
                            <option value="ASI">ASI</option>
                            <option value="SI">SI</option>
                            <option value="IP">IP</option>
                            <option value="Others">Others</option>
                        </select>
                        <div class="absolute right-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Other Designation -->
                <div class="sm:col-span-2" x-show="designation === 'Others'" x-cloak x-transition>
                    <x-input label="Other Designation Details" name="other_designation" placeholder="Specify designation" icon="briefcase" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <x-button variant="primary" icon="user-plus" size="lg">
                    Register
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