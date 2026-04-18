<section class="max-w-3xl mx-auto">
    <form method="post" action="{{ route('profile.update') }}" class="space-y-12" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Header -->
        <div class="flex items-center gap-10 pb-8 border-b border-slate-800">
            <div class="relative group" onclick="document.getElementById('photo').click()">
                <div class="w-40 h-40 rounded-full overflow-hidden border-2 border-slate-700 shadow-lg cursor-pointer transition-all hover:border-blue-500">
                    @if($user->profilePhoto)
                        <img id="photo-preview" src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" class="w-full h-full object-cover">
                    @else
                        <img id="photo-preview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1e293b&color=cbd5e1&size=256&bold=true" class="w-full h-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <i data-lucide="camera" class="w-10 h-10 text-white"></i>
                    </div>
                </div>
                <input type="file" id="photo" name="photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                <x-input-error :messages="$errors->get('photo')" class="mt-2" />
            </div>
            <div class="space-y-1">
                <h2 class="text-2xl font-bold text-white tracking-tight">{{ $user->name }}</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-blue-400 uppercase tracking-widest">{{ $user->role }}</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-800"></span>
                    <span class="text-xs font-medium text-slate-500">{{ $user->email }}</span>
                </div>
            </div>
        </div>

        <!-- Information Sections -->
        <div class="grid grid-cols-1 gap-10">
            <!-- Basic Details -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    Personal Identity
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-semibold text-slate-500">Name</label>
                        <input id="name" name="name" type="text" class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" value="{{ old('name', $user->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div class="space-y-2">
                        <label for="mobile_number" class="block text-xs font-semibold text-slate-500">Contact Number</label>
                        <input id="mobile_number" name="mobile_number" type="text" class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" value="{{ old('mobile_number', $user->mobile_number) }}" />
                        <x-input-error :messages="$errors->get('mobile_number')" />
                    </div>
                </div>
                <div class="space-y-2 max-w-md">
                    <label for="email" class="block text-xs font-semibold text-slate-500">Email Address (Primary)</label>
                    <div class="relative group">
                        <input id="email" name="email" type="email" class="w-full bg-slate-900 border-slate-800 text-slate-500 rounded-lg text-sm py-3 px-4 pr-10 cursor-not-allowed" value="{{ old('email', $user->email) }}" required readonly />
                        <i data-lucide="lock" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-700"></i>
                    </div>
                </div>
            </div>

            <!-- Professional Info -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    Professional Standing
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="designation" class="block text-xs font-semibold text-slate-500">Designation</label>
                        <input id="designation" name="designation" type="text" class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" value="{{ old('designation', $user->designation) }}" />
                        <x-input-error :messages="$errors->get('designation')" />
                    </div>
                    <div class="space-y-2">
                        <label for="pen" class="block text-xs font-semibold text-slate-500">PEN Number</label>
                        <div class="relative group">
                            <input id="pen" name="pen" type="text" class="w-full bg-slate-900 border-slate-800 text-slate-500 rounded-lg text-sm py-3 px-4 pr-10 cursor-not-allowed" value="{{ old('pen', $user->pen) }}" readonly />
                            <i data-lucide="lock" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-700"></i>
                        </div>
                        <x-input-error :messages="$errors->get('pen')" />
                    </div>
                </div>
            </div>

        </div>

        <!-- Form Actions -->
        <div class="flex items-center gap-6 pt-8 border-t border-slate-800">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-lg active:scale-95">
                Update Record
            </button>

            @if (session('status') === 'profile-updated')
                <div class="text-sm text-emerald-400 font-bold flex items-center gap-2 animate-in fade-in zoom-in-95">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Changes Applied
                </div>
            @endif
        </div>
    </form>
</section>

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
