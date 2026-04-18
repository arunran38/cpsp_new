@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'Change Password')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <!-- Header -->
    <div class="flex items-center gap-5 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-xl transition-all hover:bg-slate-900/80">
        <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-inner">
            <i data-lucide="shield-lock" class="w-8 h-8"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white uppercase tracking-wider">Security Access</h1>
            <p class="text-slate-400 text-sm font-medium">Update your account credentials to maintain protocol</p>
        </div>
    </div>

    @if (session('status') === 'password-updated')
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 animate-in fade-in slide-in-from-top-4 font-semibold">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
            <span class="text-sm">Password updated successfully.</span>
        </div>
    @endif

    <!-- Security Form Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2rem] overflow-hidden shadow-2xl">
        <div class="p-8 md:p-12">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</div>
@endsection
