@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'Change Password')

@section('content')
<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-4xl">
        <!-- Premium Header Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1e293b] to-[#0f172a] border border-slate-700/50 shadow-lg mb-6">
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-72 h-72 bg-indigo-500/20 rounded-full blur-[60px]"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-72 h-72 bg-teal-500/20 rounded-full blur-[60px]"></div>
            </div>
            
            <div class="relative px-6 py-6 sm:px-8 flex items-center gap-4">
                <a href="{{ route('profile.edit') }}" class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-white rounded-xl shadow hover:bg-slate-50 transition z-10">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <div class="text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        Change Password
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Update your account credentials to maintain security.
                    </p>
                </div>
            </div>
        </div>

        @if (session('status') === 'password-updated')
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 font-semibold">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <span class="text-sm">Password updated successfully.</span>
            </div>
        @endif

        <!-- Security Form Section -->
        <div class="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-xl">
            <div class="p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>
@endsection
