@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'My Profile')

@section('content')
<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl rounded-3xl border border-slate-800 bg-slate-950 p-8 shadow-2xl">
        @if (session('status') === 'profile-updated')
            <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-400">
                Profile synchronized successfully.
            </div>
        @endif

        <div class="flex flex-col items-center gap-4 text-center mb-8">
            <div class="relative w-28 h-28 rounded-full overflow-hidden border-2 border-slate-700 bg-slate-900 shadow-lg cursor-pointer" onclick="document.getElementById('photo').click()">
                @if($user->profilePhoto)
                    <img id="photo-preview" src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" class="w-full h-full object-cover">
                @else
                    <img id="photo-preview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1e293b&color=cbd5e1&size=256&bold=true" class="w-full h-full object-cover">
                @endif
                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/30 opacity-0 transition hover:opacity-100">
                    <i data-lucide="camera" class="w-8 h-8 text-white"></i>
                </div>
            </div>

            <div>
                <h1 class="text-3xl font-semibold text-white">{{ $user->name }}</h1>
                <p class="mt-1 text-sm uppercase tracking-[0.3em] text-slate-500">{{ $user->role }}</p>
                <p class="mt-2 text-xs text-slate-500 uppercase tracking-[0.3em]">PEN: {{ $user->pen ?? 'N/A' }}</p>
            </div>
        </div>

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('patch')

            <input type="file" id="photo" name="photo" class="hidden" accept="image/*" onchange="previewImage(this)">

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="name" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Name</label>
                    <input id="name" name="name" type="text" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('name', $user->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="space-y-2">
                    <label for="mobile_number" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Contact Number</label>
                    <input id="mobile_number" name="mobile_number" type="text" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('mobile_number', $user->mobile_number) }}" />
                    <x-input-error :messages="$errors->get('mobile_number')" />
                </div>
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Email Address</label>
                <input id="email" name="email" type="email" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-slate-400 outline-none cursor-not-allowed" value="{{ old('email', $user->email) }}" readonly />
            </div>

            <div class="space-y-2">
                <label for="designation" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Designation</label>
                <input id="designation" name="designation" type="text" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('designation', $user->designation) }}" />
                <x-input-error :messages="$errors->get('designation')" />
            </div>

       <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
    <button 
        type="submit"
        class="ml-auto inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        Save Changes
    </button>
</div>
                @if (session('status') === 'profile-updated')
                    <div class="text-sm text-emerald-400">Changes applied successfully.</div>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
