

@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto" x-data="adminFormValidation()">
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        User Registration
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Create a new user account with the required information.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.08)] relative border border-slate-100 overflow-hidden">
            <!-- Subtle gradient background -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50 rounded-full blur-[100px] pointer-events-none -z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-50 rounded-full blur-[100px] pointer-events-none -z-10"></div>
            
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">User Details</h2>
                    <p class="text-xs font-medium text-slate-500">Provide personal and professional information</p>
                </div>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="p-8 space-y-8 relative z-10" enctype="multipart/form-data"
                @submit.prevent="submitForm($event)">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <x-input label="Full Name" name="name" placeholder="Enter Full Name" required icon="user" />
                    </div>

                    <!-- PEN -->
                    <div class="sm:col-span-1">
                        <x-input label="PEN" name="pen" placeholder="Enter PEN" required icon="hash" maxlength="7" minlength="6" required/>
                    </div>

                    <!-- Mobile Number -->
                    <div class="sm:col-span-1">
                        <x-input label="Mobile Number" name="mobile_number" placeholder="Enter Mobile Number" maxlength="10" required
                            icon="smartphone" />
                    </div>

                    <!-- Email & Password on same row -->
                    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 p-6 rounded-2xl bg-slate-50 border border-slate-100/50">
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
                        <x-input label="Upload Photo" name="user_photo" type="file" icon="image" accept=".jpg,.jpeg,.png" />
                    </div>



                    <!-- Designation -->
                    <div class="sm:col-span-1">
                        <x-select label="Designation" name="designation" x-model="designation" required
                            :options="['' => 'Select Designation', 'CPO' => 'CPO', 'SCPO' => 'SCPO', 'ASI' => 'ASI', 'SI' => 'SI', 'IP' => 'IP', 'Others' => 'Others']" />
                    </div>

                    <!-- Other Designation -->
                    <div class="sm:col-span-2" x-show="designation === 'Others'" x-cloak x-transition>
                        <x-input label="Other Designation Details" name="other_designation"
                            placeholder="Specify designation" icon="briefcase" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 mt-8 border-t border-slate-100">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition-all bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 hover:shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3 text-sm font-bold text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-500 shadow-lg shadow-indigo-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-300">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Register User
                    </button>
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
