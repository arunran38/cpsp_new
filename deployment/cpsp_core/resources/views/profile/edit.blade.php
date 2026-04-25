@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <!-- Header -->
    <div class="flex items-center gap-5 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-xl transition-all hover:bg-slate-900/80">
        <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-inner">
            <i data-lucide="user-cog" class="w-8 h-8"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white uppercase tracking-wider">My Profile</h1>
            <p class="text-slate-400 text-sm font-medium">Manage your personal and professional identity</p>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 animate-in fade-in slide-in-from-top-4 font-semibold">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            <span class="text-sm">Profile synchronized successfully.</span>
        </div>
    @endif

    <!-- Profile Info Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-[2rem] overflow-hidden shadow-2xl">
        <div class="p-8 md:p-12">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>
</div>
@endsection
