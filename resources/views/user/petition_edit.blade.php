@extends('layouts.user')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="petitionForm()">
    <!-- Page Header (Professional & Clean) -->
    <div class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
        <!-- Subtle top accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="edit-3" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Petition</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Update records for petition: {{ $petition->petition_no }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('petitions.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200 flex items-center gap-2 transition-all">
                Cancel
            </a>
            <a href="{{ route('petitions.show', $petition->petition_id) }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="eye" class="w-4 h-4"></i> View Current
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top-2">
            <div class="flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800 mb-1">Please fix the following validation errors:</h3>
                    <ul class="list-disc list-inside text-sm text-rose-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top-2">
            <div class="flex items-start gap-3">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800 mb-1">Update Failed</h3>
                    <p class="text-sm text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stepper Navigation -->
    <div class="py-4">
        <div class="relative max-w-3xl mx-auto">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-200 -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-indigo-600 -translate-y-1/2 transition-all duration-500 ease-out z-0" :style="`width: ${((step - 1) / 3) * 100}%`"></div>

            <div class="relative z-10 flex justify-between">
                <button type="button" @click="setStep(1)" class="flex flex-col items-center gap-2 focus:outline-none group bg-slate-50 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ring-4 ring-slate-50"
                        :class="step >= 1 ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400 border border-slate-300'">
                        <span x-show="step > 1"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step === 1">1</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap tracking-tight" :class="step >= 1 ? 'text-slate-900' : 'text-slate-400'">Details</span>
                </button>

                <button type="button" @click="setStep(2)" class="flex flex-col items-center gap-2 focus:outline-none group bg-slate-50 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ring-4 ring-slate-50"
                        :class="step >= 2 ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400 border border-slate-300'">
                        <span x-show="step > 2"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap tracking-tight" :class="step >= 2 ? 'text-slate-900' : 'text-slate-400'">Complainants</span>
                </button>

                <button type="button" @click="setStep(3)" class="flex flex-col items-center gap-2 focus:outline-none group bg-slate-50 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ring-4 ring-slate-50"
                        :class="step >= 3 ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400 border border-slate-300'">
                        <span x-show="step > 3"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap tracking-tight" :class="step >= 3 ? 'text-slate-900' : 'text-slate-400'">Respondents</span>
                </button>

                <button type="button" @click="setStep(4)" class="flex flex-col items-center gap-2 focus:outline-none group bg-slate-50 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ring-4 ring-slate-50"
                        :class="step >= 4 ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400 border border-slate-300'">
                        <span>4</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap tracking-tight" :class="step >= 4 ? 'text-slate-900' : 'text-slate-400'">Review</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Sections -->
    <form method="POST" action="{{ route('petitions.update', $petition->petition_id) }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200" id="petitionFormElement">
        @csrf
        @method('PUT')

        <!-- STEP 1: Details -->
        <div x-show="step === 1" x-transition class="p-8 sm:p-10">
            <div class="mb-8 border-b border-slate-100 pb-5">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i> Petition Overview
                </h2>
            </div>
            
            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-1">
                        <x-input label="Petition No *" name="petition_no" x-model="petitionDetails.petition_no" required />
                    </div>
                    <div class="lg:col-span-1">
                        <x-input type="date" label="Date of Receipt *" name="date_of_petition_received" x-model="petitionDetails.date" required />
                    </div>
                    <div class="lg:col-span-1">
                        <x-select label="Nature of Petition *" name="nature_of_petition" x-model="petitionDetails.nature" required :options="['' => 'Select Category', 'Bribery' => 'Bribery', 'Misuse of authority' => 'Misuse of authority', 'Fraud / financial irregularities' => 'Fraud / financial irregularities', 'Serious negligence' => 'Serious negligence', 'others' => 'Others']" />
                    </div>
                    <div class="lg:col-span-1">
                        <x-select label="Mode of Petition *" name="mode_of_petition_received" x-model="petitionDetails.mode" required :options="['' => 'Select Origin', 'Email' => 'Email', 'Whatsapp' => 'Whatsapp', 'Tollfree' => 'Tollfree', 'Direct' => 'Direct', 'Unit' => 'Unit', 'Tapal' => 'Tapal', 'iaps' => 'iAPS', 'others' => 'Others']" />
                    </div>
                </div>

                <div x-show="petitionDetails.mode === 'others'" class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <x-input label="Specify Mode *" name="mode_others" x-model="petitionDetails.mode_others" />
                </div>

                <div>
                    <x-textarea label="Detailed Description *" name="description" x-model="petitionDetails.description" rows="5" required />
                </div>

                <div class="pt-4 mt-6 border-t border-slate-100">
                    <label class="block text-sm font-semibold text-slate-700 mb-4">Supporting Evidence (Attachments)</label>

                    @if($petition->uploads->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            @foreach($petition->uploads as $upload)
                                <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center gap-3 truncate pr-4">
                                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg shrink-0">
                                            <i data-lucide="file" class="w-4 h-4"></i>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-medium text-slate-900 truncate">{{ $upload->original_filename }}</p>
                                            <p class="text-xs text-slate-500">Uploaded on {{ $upload->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::url($upload->file_path) }}" target="_blank" class="shrink-0 p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Document">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <x-file-upload 
                        name="evidence_files[]" 
                        label="Add Additional Evidence (Attachments)" 
                        :multiple="true" 
                    />
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-end">
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    Next: Complainants <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: Complainants -->
        <div x-show="step === 2" x-transition class="p-8 sm:p-10" style="display: none;">
            <div class="mb-8 border-b border-slate-100 pb-5 text-left">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                    <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i> Complainant Details
                </h2>
            </div>
            
            <div class="space-y-8">
                <template x-for="(comp, index) in complainants" :key="index">
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-6">
                        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-5 h-5 rounded-md flex items-center justify-center text-xs" x-text="index + 1"></span>
                                Complainant
                            </h3>
                            <button type="button" @click="removeComplainant(index)" x-show="complainants.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div><x-input label="Full Name" ::name="`complainants[${index}][name]`" x-model="comp.name" required /></div>
                                <div><x-input label="Phone" ::name="`complainants[${index}][phone]`" x-model="comp.phone" /></div>
                                <div><x-input label="Aadhar" ::name="`complainants[${index}][aadhar]`" x-model="comp.aadhar" /></div>
                            </div>
                            
                            <div class="space-y-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-50 pb-1">Addresses</p>
                                <template x-for="(addr, addrIndex) in comp.addresses" :key="addrIndex">
                                    <div class="bg-slate-50 p-4 rounded-lg relative border border-slate-200 mb-4">
                                        <button type="button" @click="removeComplainantAddress(index, addrIndex)" x-show="comp.addresses.length > 1" class="absolute top-2 right-2 text-slate-400 hover:text-rose-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Type</label>
                                                <select ::name="`complainants[${index}][addresses][${addrIndex}][address_type]`" x-model="addr.address_type" class="w-full text-sm border-slate-300 rounded-lg">
                                                    <option value="Permanent">Permanent</option>
                                                    <option value="Temporary">Temporary</option>
                                                    <option value="Office">Office</option>
                                                </select>
                                            </div>
                                            <div class="lg:col-span-2"><x-input label="Address" ::name="`complainants[${index}][addresses][${addrIndex}][address]`" x-model="addr.address" required /></div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div><x-input label="District" ::name="`complainants[${index}][addresses][${addrIndex}][district]`" x-model="addr.district" required /></div>
                                                <div><x-input label="Pin" ::name="`complainants[${index}][addresses][${addrIndex}][pincode]`" x-model="addr.pincode" required /></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" @click="addComplainantAddress(index)" x-show="comp.addresses.length < 3" class="text-xs font-bold text-indigo-600 flex items-center gap-1 hover:underline">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Another Address
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addComplainant()" class="w-full py-4 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 hover:border-indigo-300 hover:text-indigo-600 transition-all font-bold text-sm uppercase tracking-widest flex justify-center items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i> Add Complainant
                </button>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    Continue to Respondents <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 3: Respondents -->
        <div x-show="step === 3" x-transition class="p-8 sm:p-10" style="display: none;">
            <div class="mb-8 border-b border-slate-100 pb-5 text-left">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i> Respondent Details
                </h2>
            </div>
            
            <div class="space-y-8">
                <template x-for="(acc, index) in accused" :key="index">
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-6">
                        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span class="bg-rose-100 text-rose-700 w-5 h-5 rounded-md flex items-center justify-center text-xs" x-text="index + 1"></span>
                                Respondent
                            </h3>
                            <button type="button" @click="removeAccused(index)" x-show="accused.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div><x-input label="Full Name" ::name="`accused[${index}][name]`" x-model="acc.name" required /></div>
                                <div><x-input label="Phone" ::name="`accused[${index}][phone]`" x-model="acc.phone" /></div>
                                <div><x-input label="Aadhar" ::name="`accused[${index}][aadhar]`" x-model="acc.aadhar" /></div>
                            </div>
                            
                            <div class="space-y-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-50 pb-1">Known Addresses</p>
                                <template x-for="(addr, addrIndex) in acc.addresses" :key="addrIndex">
                                    <div class="bg-slate-50 p-4 rounded-lg relative border border-slate-200 mb-4">
                                        <button type="button" @click="removeAccusedAddress(index, addrIndex)" x-show="acc.addresses.length > 1" class="absolute top-2 right-2 text-slate-400 hover:text-rose-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Type</label>
                                                <select ::name="`accused[${index}][addresses][${addrIndex}][address_type]`" x-model="addr.address_type" class="w-full text-sm border-slate-300 rounded-lg">
                                                    <option value="Permanent">Permanent</option>
                                                    <option value="Temporary">Temporary</option>
                                                    <option value="Office">Office</option>
                                                </select>
                                            </div>
                                            <div class="lg:col-span-2"><x-input label="Address" ::name="`accused[${index}][addresses][${addrIndex}][address]`" x-model="addr.address" required /></div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div><x-input label="District" ::name="`accused[${index}][addresses][${addrIndex}][district]`" x-model="addr.district" required /></div>
                                                <div><x-input label="Pin" ::name="`accused[${index}][addresses][${addrIndex}][pincode]`" x-model="addr.pincode" required /></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" @click="addAccusedAddress(index)" x-show="acc.addresses.length < 3" class="text-xs font-bold text-indigo-600 flex items-center gap-1 hover:underline">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Known Address
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addAccused()" class="w-full py-4 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 hover:border-indigo-300 hover:text-indigo-600 transition-all font-bold text-sm uppercase tracking-widest flex justify-center items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i> Add Respondent
                </button>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    Review Changes <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 4: Review -->
        <div x-show="step === 4" x-transition class="p-8 sm:p-10" style="display: none;">
            <div class="text-center py-6">

                
                <div class="max-w-xl mx-auto text-left">
                    <x-textarea label="Updated Assessment / Action" name="proposed_action" x-model="petitionDetails.proposed_action" rows="4" />
                </div>

                <div class="mt-10 pt-8 border-t border-slate-200 flex justify-between">
                    <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                        Modify Details
                    </button>
                    <button type="submit" class="px-10 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-lg shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i> Authorize & Update Record
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function petitionForm() {
        return {
            step: 1,
            petitionDetails: {
                petition_no: '{{ old('petition_no', $petition->petition_no) }}',
                date: '{{ old('date_of_petition_received', $petition->date_of_petition_received) }}',
                nature: '{{ old('nature_of_petition', $petition->nature_of_petition) }}',
                mode: '{{ old('mode_of_petition_received', $petition->mode_of_petition_received) }}',
                mode_others: '{{ old('mode_others', $petition->mode_of_petition_received_others) }}',
                description: `{!! addslashes(old('description', $petition->description)) !!}`,
                proposed_action: `{!! addslashes(old('proposed_action', $petition->proposed_action)) !!}`
            },
            complainants: {!! json_encode(old('complainants', $complainants)) !!},
            accused: {!! json_encode(old('accused', $accused)) !!},

            nextStep() { if (this.step < 4) { this.step++; this.scrollToTop(); } },
            prevStep() { if (this.step > 1) { this.step--; this.scrollToTop(); } },
            setStep(target) { this.step = target; this.scrollToTop(); },
            scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); this.refreshIcons(); },
            
            addComplainant() {
                this.complainants.push({ name: '', phone: '', aadhar: '', addresses: [{ address_type: 'Permanent', address: '', district: '', pincode: '' }] });
                this.refreshIcons();
            },
            removeComplainant(index) { if(this.complainants.length > 1) this.complainants.splice(index, 1); },
            addComplainantAddress(idx) { 
                const types = ['Permanent', 'Temporary', 'Office'];
                const used = this.complainants[idx].addresses.map(a => a.address_type);
                const next = types.find(t => !used.includes(t)) || 'Temporary';
                this.complainants[idx].addresses.push({ address_type: next, address: '', district: '', pincode: '' }); 
                this.refreshIcons();
            },
            removeComplainantAddress(cIdx, aIdx) { if(this.complainants[cIdx].addresses.length > 1) this.complainants[cIdx].addresses.splice(aIdx, 1); },

            addAccused() {
                this.accused.push({ name: '', phone: '', aadhar: '', addresses: [{ address_type: 'Permanent', address: '', district: '', pincode: '' }] });
                this.refreshIcons();
            },
            removeAccused(index) { if(this.accused.length > 1) this.accused.splice(index, 1); },
            addAccusedAddress(idx) { 
                const types = ['Permanent', 'Temporary', 'Office'];
                const used = this.accused[idx].addresses.map(a => a.address_type);
                const next = types.find(t => !used.includes(t)) || 'Temporary';
                this.accused[idx].addresses.push({ address_type: next, address: '', district: '', pincode: '' }); 
                this.refreshIcons();
            },
            removeAccusedAddress(aIdx, adIdx) { if(this.accused[aIdx].addresses.length > 1) this.accused[aIdx].addresses.splice(adIdx, 1); },

            refreshIcons() { if (typeof lucide !== 'undefined') { this.$nextTick(() => { lucide.createIcons(); }); } }
        }
    }
</script>
@endsection
