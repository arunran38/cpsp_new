<!-- Return to Inward Modal -->
<div x-show="showReturnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div x-show="showReturnModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showReturnModal = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Dialog Panel -->
        <div x-show="showReturnModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-[0_20px_60px_-15px_rgba(0,0,0,0.2)] transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">

            <form :action="'{{ url('petitions') }}/' + returnPetitionId + '/return-to-inward'" method="POST">
                @csrf
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between rounded-t-2xl relative overflow-hidden bg-white">
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-rose-100 text-rose-600 flex items-center justify-center">
                            <i data-lucide="corner-up-left" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-indigo-900 tracking-tight">Return to Inward</h3>
                            <p class="text-sm text-rose-600 font-semibold mt-0.5">Send petition back for reassignment</p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnModal = false"
                        class="text-blue-400 hover:text-blue-600 p-2 rounded-xl hover:bg-blue-50 transition-all relative z-10 focus:outline-none">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6 space-y-6 bg-white">
                    <div class="bg-teal-50/50 p-4 rounded-xl border border-teal-100 flex justify-between items-center group transition-colors">
                        <div class="flex items-center gap-2 text-teal-800">
                            <i data-lucide="hash" class="w-4 h-4 text-teal-500"></i>
                            <span class="text-sm font-bold">Receipt Number</span>
                        </div>
                        <span class="font-bold text-teal-700 text-base font-mono bg-white px-4 py-1.5 rounded-lg border border-teal-200 shadow-sm" x-text="returnReceiptNo"></span>
                    </div>

                    <div class="space-y-2.5">
                        <label for="return_reason" class="block text-[15px] font-bold text-indigo-900">
                            Reason for Return <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea name="return_reason" id="return_reason" rows="3" required
                                placeholder="Please explain why this petition is being returned..."
                                class="block w-full px-4 py-3 text-sm bg-blue-50 border border-blue-200 rounded-xl text-blue-900 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:text-blue-400 resize-none shadow-inner"></textarea>
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-xl border border-amber-200 flex items-start gap-3 shadow-sm">
                        <div class="mt-0.5 bg-amber-100 p-1.5 rounded-full shrink-0">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        </div>
                        <p class="text-[13px] text-amber-800 font-semibold leading-relaxed">
                            Returning this petition will remove it from your list. It will appear in Inward's "Returned Petitions" list for reassignment to the correct seat.
                        </p>
                    </div>

                </div>

                <div class="px-6 py-5 bg-slate-50/80 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showReturnModal = false"
                        class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-blue-600 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all focus:ring-4 focus:ring-blue-100 outline-none">
                        Cancel
                    </button>
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-lg shadow-rose-600/20 transition-all flex items-center justify-center gap-2 focus:ring-4 focus:ring-rose-500/20 outline-none hover:-translate-y-0.5 active:translate-y-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>