<section class="space-y-6">
    <header>
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="user-minus" class="w-5 h-5 text-rose-500"></i>
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-6 py-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 text-sm font-bold rounded-2xl border border-rose-500/20 transition-all flex items-center gap-2"
    >
        <i data-lucide="trash-2" class="w-4 h-4"></i>
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            @csrf
            @method('delete')

            <h2 class="text-2xl font-bold text-white mb-4">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="text-slate-400 text-sm mb-6 leading-relaxed">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="space-y-2 mb-8">
                <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full bg-slate-950/50 border-slate-800 rounded-2xl text-white focus:border-rose-500 focus:ring-rose-500/20 transition-all font-medium py-3"
                    placeholder="{{ __('Enter password to confirm') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="flex items-center gap-4 justify-end pt-6 border-t border-slate-800">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-2xl border border-slate-700 transition-all">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl shadow-lg shadow-rose-500/20 transition-all">
                    {{ __('Permanently Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
