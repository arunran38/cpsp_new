<section class="w-full">
    <form method="post" action="{{ route('password.update') }}" class="space-y-8">
        @csrf
        @method('put')

        <!-- Section Header -->
        <div class="pb-6 border-b border-slate-100">
            <div class="flex items-center gap-2 text-blue-500 font-semibold text-sm uppercase tracking-wider">
                <i data-lucide="lock" class="w-4 h-4"></i>
                <span>Security Credentials</span>
            </div>
            <p class="mt-2 text-sm text-slate-500">
                Update your account password to maintain high security standards.
            </p>
        </div>

        <!-- Password Fields Grid -->
        <div class="space-y-6">
            <div class="space-y-2 max-w-md">
                <label for="update_password_current_password" class="block text-sm font-semibold text-blue-900">Current Password</label>
                <div class="relative">
                    <input id="update_password_current_password" name="current_password" type="password" 
                        class="w-full bg-white border border-blue-200 rounded-xl text-slate-700 placeholder:text-blue-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4 pr-10" 
                        autocomplete="current-password" placeholder="Enter Current Password" />
                    <i data-lucide="key" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300"></i>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="update_password_password" class="block text-sm font-semibold text-blue-900">New Password</label>
                    <input id="update_password_password" name="password" type="password" 
                        class="w-full bg-white border border-blue-200 rounded-xl text-slate-700 placeholder:text-blue-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" 
                        autocomplete="new-password" placeholder="Enter New Password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" />
                </div>

                <div class="space-y-2">
                    <label for="update_password_password_confirmation" class="block text-sm font-semibold text-blue-900">Confirm New Password</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                        class="w-full bg-white border border-blue-200 rounded-xl text-slate-700 placeholder:text-blue-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all py-3 px-4" 
                        autocomplete="new-password" placeholder="Re-enter New Password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-end gap-4">
            <a href="{{ route('profile.edit') }}" class="px-6 py-2.5 text-sm font-semibold text-blue-600 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Update Password
            </button>

            @if (session('status') === 'password-updated')
                <div class="text-sm text-emerald-600 font-bold flex items-center gap-2 animate-in fade-in zoom-in-95 ml-4">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Security Updated
                </div>
            @endif
        </div>
    </form>
</section>
