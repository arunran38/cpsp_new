@extends(auth()->user()->canAccess('access admin dashboard') && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')

@section('page-title', 'My Profile')

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
                <a href="{{ route('dashboard') }}" class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-white rounded-xl shadow hover:bg-slate-50 transition z-10">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <div class="text-left z-10">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-sm mb-1">
                        Edit Profile
                    </h1>
                    <p class="text-slate-300 font-medium max-w-2xl text-sm">
                        Update your personal account information.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden">
            <!-- Card Header -->
            <div class="px-8 py-6 border-b border-slate-100">
                <div class="flex items-center gap-2 text-blue-500 font-semibold text-sm uppercase tracking-wider">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Profile Details</span>
                </div>
            </div>

            <div class="p-8">
                @if (session('status') === 'profile-updated')
                    <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-600">
                        Profile synchronized successfully.
                    </div>
                @endif

                <div class="flex flex-col items-center gap-4 text-center mb-8">
                    <div class="relative w-28 h-28 rounded-full overflow-hidden border-4 border-white bg-slate-100 shadow-lg cursor-pointer group" onclick="document.getElementById('photo').click()">
                        @if($user->profilePhoto)
                            <img id="photo-preview" src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" class="w-full h-full object-cover">
                        @else
                            <img id="photo-preview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=eff6ff&color=3b82f6&size=256&bold=true" class="w-full h-full object-cover">
                        @endif
                        <div class="absolute inset-0 flex items-center justify-center bg-slate-900/30 opacity-0 transition group-hover:opacity-100">
                            <i data-lucide="camera" class="w-8 h-8 text-white"></i>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm uppercase tracking-[0.2em] text-blue-600 font-semibold">{{ $user->getRoleNames()->first() ?? 'User' }}</p>
                        <p class="mt-1 text-xs text-slate-500 uppercase tracking-[0.1em]">PEN: {{ $user->pen ?? 'N/A' }}</p>
                    </div>
                </div>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('patch')

                    <input type="file" id="photo" name="photo" class="hidden" accept=".jpg,.jpeg,.png" onchange="previewImage(this)">

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-semibold text-blue-900">Name</label>
                            <input id="name" name="name" type="text" class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-blue-300 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('name', $user->name) }}" placeholder="Enter Name" required />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div class="space-y-2">
                            <label for="mobile_number" class="block text-sm font-semibold text-blue-900">Contact Number</label>
                            <input id="mobile_number" name="mobile_number" type="text" class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-blue-300 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('mobile_number', $user->mobile_number) }}" placeholder="Enter Contact Number" />
                            <x-input-error :messages="$errors->get('mobile_number')" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-blue-900">Email Address</label>
                        <input id="email" name="email" type="email" class="w-full rounded-xl border border-blue-100 bg-slate-50 px-4 py-3 text-sm text-slate-500 outline-none cursor-not-allowed" value="{{ old('email', $user->email) }}" readonly />
                    </div>

                    <div class="space-y-2">
                        <label for="designation" class="block text-sm font-semibold text-blue-900">Designation</label>
                        <input id="designation" name="designation" type="text" class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-blue-300 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('designation', $user->designation) }}" placeholder="Enter Designation" />
                        <x-input-error :messages="$errors->get('designation')" />
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 text-sm font-semibold text-blue-600 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Save Changes
                        </button>
                        
                        @if (session('status') === 'profile-updated')
                            <div class="text-sm text-emerald-600 ml-4">Changes applied.</div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
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
