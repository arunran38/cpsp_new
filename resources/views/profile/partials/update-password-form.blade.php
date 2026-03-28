<section class="max-w-3xl mx-auto">
    <form method="post" action="{{ route('password.update') }}" class="space-y-10">
        @csrf
        @method('put')

        <!-- Section Header -->
        <div class="pb-6 border-b border-slate-800">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                Security Credentials
            </h3>
            <p class="mt-2 text-sm text-slate-500">
                Update your account password to maintain high security standards.
            </p>
        </div>

        <!-- Password Fields Grid -->
        <div class="grid grid-cols-1 gap-8">
            <div class="space-y-2 max-w-md">
                <label for="update_password_current_password" class="block text-xs font-semibold text-slate-500">Current Password</label>
                <div class="relative">
                    <input id="update_password_current_password" name="current_password" type="password" 
                        class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" 
                        autocomplete="current-password" />
                    <i data-lucide="key" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-700"></i>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="update_password_password" class="block text-xs font-semibold text-slate-500">New Password</label>
                    <input id="update_password_password" name="password" type="password" 
                        class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" 
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" />
                </div>

                <div class="space-y-2">
                    <label for="update_password_password_confirmation" class="block text-xs font-semibold text-slate-500">Confirm New Password</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                        class="w-full bg-slate-950 border-slate-800 rounded-lg text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" 
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center gap-6 pt-8 border-t border-slate-800">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-slate-800 border border-slate-700 rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-slate-700 hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-lg active:scale-95">
                Commit Password Change
            </button>

            @if (session('status') === 'password-updated')
                <div class="text-sm text-emerald-400 font-bold flex items-center gap-2 animate-in fade-in zoom-in-95">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Security Updated
                </div>
            @endif
        </div>
    </form>
</section>
