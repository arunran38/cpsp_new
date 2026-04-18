@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto" x-data="adminFormValidation()">
        <div class="mb-8">
            <h1
                class="text-3xl font-bold tracking-tight text-white bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                User Registration</h1>
            <p class="mt-2 text-sm text-slate-500">Create a new user account with the required information</p>
        </div>

        <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-lg shadow-indigo-100/20">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-8" enctype="multipart/form-data"
                @submit.prevent="submitForm($event)">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <x-input label="Full Name" name="name" placeholder="Enter Full Name" required icon="user" />
                    </div>

                    <!-- PEN -->
                    <div class="pr-2">
                        <x-input label="PEN" name="pen" placeholder="Enter PEN" required icon="hash" />
                    </div>

                    <!-- Mobile Number -->
                    <div class="pl-2">
                        <x-input label="Mobile Number" name="mobile_number" placeholder="Enter Mobile Number" required
                            icon="smartphone" />
                    </div>

                    <!-- Email & Password on same row -->
                    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input label="Email Address" name="email" type="email" placeholder="Enter Email Address"
                                required icon="mail" />
                        </div>
                        <div>
                            <x-input label="Temporary Password" name="password" type="password" placeholder="Enter Password"
                                required icon="lock" />
                        </div>
                    </div>

                    <!-- Upload Photo -->
                    <div class="sm:col-span-2">
                        <x-input label="Upload Photo" name="user_photo" type="file" icon="image" />
                    </div>

                    <!-- Role -->
                    <div>
                        <x-select label="Role" name="role" :options="['user' => 'User', 'admin' => 'Admin']" selected="user"
                            required />
                    </div>

                    <!-- Designation -->
                    <div>
                        <x-select label="Designation" name="designation" x-model="designation" required
                            :options="['' => 'Select Designation', 'CPO' => 'CPO', 'SCPO' => 'SCPO', 'ASI' => 'ASI', 'SI' => 'SI', 'IP' => 'IP', 'Others' => 'Others']" />
                    </div>

                    <!-- Other Designation -->
                    <div class="sm:col-span-2" x-show="designation === 'Others'" x-cloak x-transition>
                        <x-input label="Other Designation Details" name="other_designation"
                            placeholder="Specify designation" icon="briefcase" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                    <x-button variant="primary" icon="user-plus" size="lg"
                        class="shadow-md shadow-indigo-200 hover:shadow-lg transition-shadow">
                        Register User
                    </x-button>
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition-all bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 text-slate-600 hover:shadow-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        @section('scripts')
            @include('components.admin-form-validation-script')
        @endsection
    </div>

    <style>
        /* Catchy enhancements while preserving outline */
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #6366f1 !important;
            outline-offset: 2px !important;
            border-color: transparent !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        }

        /* Gradient animation for submit button */
        @keyframes subtlePulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.95;
            }
        }

        .shadow-indigo-200 {
            animation: subtlePulse 3s ease-in-out infinite;
        }

        /* Smooth transitions */
        input,
        select,
        textarea,
        button,
        a {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Hover effect on form container */
        .bg-white.border.border-slate-200 {
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .bg-white.border.border-slate-200:hover {
            box-shadow: 0 10px 40px -12px rgba(99, 102, 241, 0.25);
            transform: translateY(-2px);
        }

        /* Label enhancement */
        label {
            font-weight: 600 !important;
            letter-spacing: 0.3px;
        }

        /* Input field enhancement */
        input:not([type="file"]),
        select,
        textarea {
            background: linear-gradient(to bottom, #ffffff, #fafafa) !important;
        }

        input:hover:not([type="file"]):not(:focus),
        select:hover:not(:focus),
        textarea:hover:not(:focus) {
            border-color: #a5b4fc !important;
            background: #ffffff !important;
        }
    </style>
@endsection