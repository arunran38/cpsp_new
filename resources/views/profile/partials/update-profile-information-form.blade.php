<div class="grid gap-4">
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

    <div class="space-y-2">
        <label for="email" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Email Address</label>
        <input id="email" name="email" type="email" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-slate-400 outline-none cursor-not-allowed" value="{{ old('email', $user->email) }}" readonly />
    </div>

    <div class="space-y-2">
        <label for="designation" class="block text-xs font-semibold uppercase tracking-widest text-slate-500">Designation</label>
        <input id="designation" name="designation" type="text" class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500" value="{{ old('designation', $user->designation) }}" />
        <x-input-error :messages="$errors->get('designation')" />
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
