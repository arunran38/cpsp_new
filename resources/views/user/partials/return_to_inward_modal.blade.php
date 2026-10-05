<!-- Return to Inward Modal -->
<div x-show="showReturnModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div x-show="showReturnModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showReturnModal = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Dialog Panel -->
        <div x-show="showReturnModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
            
            <form :action="'{{ url('petitions') }}/' + returnPetitionId + '/return-to-inward'" method="POST">
                @csrf
                <div class="bg-rose-50/70 px-6 py-4 border-b border-rose-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                            <i data-lucide="corner-up-left" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Return to Inward</h3>
                            <p class="text-xs text-rose-700 font-medium">ഹർജി Inward-ലേക്ക് തിരിച്ചയക്കുക</p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-white/80 transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Receipt No:</span>
                            <span class="font-bold text-indigo-900 text-sm font-mono" x-text="returnReceiptNo"></span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="return_reason" class="block text-sm font-bold text-slate-700">
                            Reason for Return (തിരിച്ചയക്കാനുള്ള കാരണം) <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="return_reason" 
                                  id="return_reason" 
                                  rows="3" 
                                  required 
                                  placeholder="ഉദാ: ഈ വിഷയം ഈ സീറ്റുമായി ബന്ധപ്പെട്ടതല്ല / തെറ്റായ സീറ്റിലേക്ക് അയച്ചതാണ്..."
                                  class="block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 outline-none transition-all placeholder:text-slate-400"></textarea>
                    </div>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <span>ഇത് തിരിച്ചയച്ചാൽ നിങ്ങളുടെ ലിസ്റ്റിൽ നിന്നും ഒഴിവാകുകയും Inward-ന്റെ "Returned Petitions" ലിസ്റ്റിൽ ശരിയായ സീറ്റ് നൽകുന്നതിനായി കാണിക്കുകയും ചെയ്യും.</span>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="showReturnModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md shadow-rose-600/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
