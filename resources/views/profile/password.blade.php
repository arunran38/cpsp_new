@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'Change Password')

@section('content')
<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('profile.edit') }}" class="flex items-center justify-center w-10 h-10 bg-white rounded-xl shadow hover:bg-slate-50 transition">
                <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Change Password</h1>
                <p class="text-slate-400 text-sm">Update your account credentials to maintain security.</p>
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
