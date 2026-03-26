@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="petitionForm()">
    <!-- Page Header (Professional & Clean) -->
    <div class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
        <!-- Subtle top accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Complaint</h1>
            </div>
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
                    <h3 class="text-sm font-bold text-rose-800 mb-1">Submission Failed</h3>
                    <p class="text-sm text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stepper Navigation (Elegant Line-based) -->
    <div class="py-4">
        <div class="relative max-w-3xl mx-auto px-4 md:px-0">
            <!-- Background Line -->
            <div class="absolute top-4 left-0 w-full h-0.5 bg-indigo-300/20 -translate-y-1/2 z-0"></div>
            <!-- Progress Line -->
            <div class="absolute top-4 left-0 h-0.5 bg-teal-500 -translate-y-1/2 transition-all duration-500 ease-out z-0" :style="`width: ${((step - 1) / 3) * 100}%`"></div>

            <div class="relative z-10 flex justify-between">
                <!-- Step 1 -->
                <button type="button" @click="setStep(1)" class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 1 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                        <span x-show="step > 1"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step === 1">1</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap transition-colors" :class="step >= 1 ? 'text-white' : 'text-blue-200'">Petition Details</span>
                </button>

                <!-- Step 2 -->
                <button type="button" @click="step >= 2 ? setStep(2) : null" class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent" :class="{ 'cursor-not-allowed': step < 2 }">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 2 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                        <span x-show="step > 2"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap transition-colors" :class="step >= 2 ? 'text-white' : 'text-blue-200'">Complainant</span>
                </button>

                <!-- Step 3 -->
                <button type="button" @click="step >= 3 ? setStep(3) : null" class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent" :class="{ 'cursor-not-allowed': step < 3 }">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 3 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                        <span x-show="step > 3"><i data-lucide="check" class="w-4 h-4"></i></span>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap transition-colors" :class="step >= 3 ? 'text-white' : 'text-blue-200'">Respondent</span>
                </button>

                <!-- Step 4 -->
                <button type="button" @click="step >= 4 ? setStep(4) : null" class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent" :class="{ 'cursor-not-allowed': step < 4 }">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                        :class="step >= 4 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                        <span>4</span>
                    </div>
                    <span class="text-xs font-semibold whitespace-nowrap transition-colors" :class="step >= 4 ? 'text-white' : 'text-blue-200'">Complete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Sections (Minimalist Cards) -->
    <form method="POST" action="{{ route('petitions.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200" id="petitionFormElement">
        @csrf

        <!-- STEP 1: Petition Details -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 sm:p-10">
            
            <div class="mb-8 border-b border-slate-100 pb-5">
                {{-- <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i> Section 1: Petition Overview
                </h2>
                <p class="text-sm text-slate-500 mt-1">Provide the fundamental details of the petition received.</p> --}}
            </div>
            
            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-1">
                        <x-input label="Petition No *" name="petition_no" x-model="petitionDetails.petition_no" required placeholder="Ex. PT-2026-001" />
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

                <div x-show="petitionDetails.mode === 'others'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <x-input label="Specify Mode of Petition *" name="mode_others" x-model="petitionDetails.mode_others" ::required="petitionDetails.mode === 'others'" placeholder="Type the custom mode of receipt..." />
                </div>

                <div>
                    <x-textarea label="Detailed Description *" name="description" x-model="petitionDetails.description" rows="5" required placeholder="Type the complete factual description of the incident..." />
                </div>

                <x-file-upload 
                    name="evidence_files[]" 
                    label="Supporting Evidence (Attachments)" 
                    :multiple="true" 
                />
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-end">
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm transition-all flex items-center gap-2">
                    Continue to Complainant <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: Complainant Details -->
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 sm:p-10" style="display: none;">
            
            <div class="mb-8 border-b border-slate-100 pb-5">
                {{-- <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i> Section 2: Complainant Details
                </h2>
                <p class="text-sm text-slate-500 mt-1">Add one or more primary applicants filing this petition.</p> --}}
            </div>
            
            <div class="space-y-8">
                <template x-for="(comp, index) in complainants" :key="comp.id">
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <!-- Header for the section -->
                        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-5 h-5 rounded-md flex items-center justify-center text-xs" x-text="index + 1"></span>
                                Complainant Information
                            </h3>
                            <button type="button" @click="removeComplainant(index)" x-show="complainants.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                            </button>
                        </div>

                        <div class="p-6">
                            <!-- Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div>
                                    <x-input label="Full Name" name="comp_name" \:name="`complainants[${index}][name]`" x-model="comp.name" ::required="step === 2" placeholder="Legal Name" />
                                </div>
                                <div>
                                    <x-input label="Phone Number" name="comp_phone" \:name="`complainants[${index}][phone]`" x-model="comp.phone" ::required="step === 2" placeholder="10-digit Mobile" />
                                </div>
                                <div>
                                    <x-input label="Aadhar Number" name="comp_aadhar" \:name="`complainants[${index}][aadhar]`" x-model="comp.aadhar" placeholder="12-digit Aadhar" />
                                </div>
                            </div>

                            <!-- Nested Addresses -->
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Address Book
                                </h4>
                                
                                <div class="space-y-4">
                                    <template x-for="(addr, addrIndex) in comp.addresses" :key="addrIndex">
                                        <div class="bg-slate-50 p-5 border border-slate-200 rounded-lg relative">
                                            <button type="button" @click="removeComplainantAddress(index, addrIndex)" x-show="comp.addresses.length > 1" class="absolute top-4 right-4 z-10 text-slate-400 hover:text-rose-600 transition-colors">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                                                <div>
                                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address Type</label>
                                                    <select \:name="`complainants[${index}][addresses][${addrIndex}][address_type]`" x-model="addr.address_type" class="block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition-all">
                                                        <option value="Permanent" :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')">Permanent</option>
                                                        <option value="Temporary" :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')">Temporary</option>
                                                        <option value="Office" :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')">Office</option>
                                                    </select>
                                                </div>
                                                <div class="lg:col-span-2">
                                                    <x-input label="Street Address *" name="comp_address" \:name="`complainants[${index}][addresses][${addrIndex}][address]`" x-model="addr.address" ::required="step === 2" placeholder="House No, Street, Locality" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <x-input label="District *" name="comp_district" \:name="`complainants[${index}][addresses][${addrIndex}][district]`" x-model="addr.district" ::required="step === 2" placeholder="District" />
                                                    </div>
                                                    <div>
                                                        <x-input label="Pincode *" name="comp_pincode" \:name="`complainants[${index}][addresses][${addrIndex}][pincode]`" x-model="addr.pincode" ::required="step === 2" placeholder="Code" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="mt-4" x-show="comp.addresses.length < 3 && comp.addresses[comp.addresses.length - 1].address.trim() !== ''">
                                    <button type="button" @click="addComplainantAddress(index)" class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div class="pt-2 text-center" x-show="complainants[complainants.length - 1].name.trim() !== ''">
                    <button type="button" @click="addComplainant()" class="px-6 py-3 border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 hover:border-slate-400 transition-all flex items-center justify-center gap-2 w-full">
                        <i data-lucide="user-plus" class="w-5 h-5"></i> Register Another Complainant
                    </button>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-center">
                <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    Continue to Respondent <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 3: Respondent Details -->
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 sm:p-10" style="display: none;">
            
            <div class="mb-8 border-b border-slate-100 pb-5">
                {{-- <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i> Section 3: Respondent Details
                </h2>
                <p class="text-sm text-slate-500 mt-1">Add one or more accused individuals regarding this petition.</p> --}}
            </div>
            
            <div class="space-y-8">
                <template x-for="(acc, index) in accused" :key="acc.id">
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <!-- Header for the section -->
                        <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-5 h-5 rounded-md flex items-center justify-center text-xs" x-text="index + 1"></span>
                                Respondent Information
                            </h3>
                            <button type="button" @click="removeAccused(index)" x-show="accused.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                            </button>
                        </div>

                        <div class="p-6">
                            <!-- Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div>
                                    <x-input label="Full Name *" name="acc_name" \:name="`accused[${index}][name]`" x-model="acc.name" ::required="step === 3" placeholder="Legal Name" />
                                </div>
                                <div>
                                    <x-input label="Phone Number" name="acc_phone" \:name="`accused[${index}][phone]`" x-model="acc.phone" placeholder="Optional Mobile" />
                                </div>
                                <div>
                                    <x-input label="Aadhar Number" name="acc_aadhar" \:name="`accused[${index}][aadhar]`" x-model="acc.aadhar" placeholder="Optional 12-digit" />
                                </div>
                            </div>

                            <!-- Nested Addresses -->
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Known Addresses
                                </h4>
                                
                                <div class="space-y-4">
                                    <template x-for="(addr, addrIndex) in acc.addresses" :key="addrIndex">
                                        <div class="bg-slate-50 p-5 border border-slate-200 rounded-lg relative">
                                            <button type="button" @click="removeAccusedAddress(index, addrIndex)" x-show="acc.addresses.length > 1" class="absolute top-4 right-4 z-10 text-slate-400 hover:text-rose-600 transition-colors">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                                                <div>
                                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address Type</label>
                                                    <select \:name="`accused[${index}][addresses][${addrIndex}][address_type]`" x-model="addr.address_type" class="block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition-all">
                                                        <option value="Permanent" :disabled="acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')">Permanent</option>
                                                        <option value="Temporary" :disabled="acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')">Temporary</option>
                                                        <option value="Office" :disabled="acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')">Office</option>
                                                    </select>
                                                </div>
                                                <div class="lg:col-span-2">
                                                    <x-input label="Street Address" name="acc_address" \:name="`accused[${index}][addresses][${addrIndex}][address]`" x-model="addr.address" ::required="step === 3" placeholder="House No, Street, Locality" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <x-input label="District" name="acc_district" \:name="`accused[${index}][addresses][${addrIndex}][district]`" x-model="addr.district" ::required="step === 3" placeholder="District" />
                                                    </div>
                                                    <div>
                                                        <x-input label="Pincode" name="acc_pincode" \:name="`accused[${index}][addresses][${addrIndex}][pincode]`" x-model="addr.pincode" ::required="step === 3" placeholder="Code" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="mt-4" x-show="acc.addresses.length < 3 && acc.addresses[acc.addresses.length - 1].address.trim() !== ''">
                                    <button type="button" @click="addAccusedAddress(index)" class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div class="pt-2 text-center" x-show="accused[accused.length - 1].name.trim() !== ''">
                    <button type="button" @click="addAccused()" class="px-6 py-3 border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 hover:border-slate-400 transition-all flex items-center justify-center gap-2 w-full">
                        <i data-lucide="user-plus" class="w-5 h-5"></i> Register Another Respondent
                    </button>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-center">
                <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    Finalize Details <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- STEP 4: Complete -->
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 sm:p-10" style="display: none;">
            
            <div x-show="!showPreview" class="relative">
                <div class="max-w-2xl mx-auto py-8">

                    
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 mb-8">
                        <x-textarea label="Initial Assessment/Action Note" name="proposed_action" rows="4" placeholder="Enter the remarks/notes here..." />
                    </div>

                    <div class="flex gap-4 mb-4">
                        <button type="button" @click="togglePreview()" class="w-full px-6 py-3 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="eye" class="w-4 h-4"></i> Full-Preview
                        </button>
                    </div>
                    
                    <div class="flex justify-between items-center py-6 border-t border-slate-200 mt-6">
                        <button type="button" @click="prevStep()" class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Edit Petition
                        </button>
                        <button type="submit" class="px-8 py-2.5 text-sm font-semibold text-white bg-slate-900 rounded-lg shadow-md hover:bg-slate-800 focus:ring-4 focus:ring-slate-900/20 transition-all flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4"></i> Submit Petition
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modern Catchy Preview Section -->
            <div x-show="showPreview" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100" style="display: none;" class="text-left bg-white rounded-2xl shadow-2xl shadow-indigo-500/10 border border-slate-200 relative overflow-hidden mt-8">
                
                <!-- Premium Header -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-b border-indigo-900/50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-indigo-500 opacity-20 blur-3xl rounded-full pointer-events-none"></div>
                    <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-blue-500 opacity-20 blur-3xl rounded-full pointer-events-none"></div>
                    
                    <div class="px-8 py-8 relative z-10 flex justify-between items-center text-white">
                        <div class="flex items-center gap-5">
                            <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                                <i data-lucide="shield-check" class="w-8 h-8 text-indigo-200"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-tight drop-shadow-sm">Verification Summary</h3>
                                <p class="text-indigo-200 text-sm font-medium mt-1 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Ready for Final Authorization
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="togglePreview()" class="p-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-full text-indigo-100 transition-all backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/20">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-8 sm:p-10 bg-slate-50/50 space-y-12">
                    
                    <!-- 1. Petition Overview Panel -->
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm shadow-sm border border-indigo-200 ring-4 ring-white z-10">1</div>
                            <h4 class="text-lg font-bold text-slate-900">Petition Outline</h4>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                                <div class="p-5">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5"><i data-lucide="hash" class="w-3.5 h-3.5"></i> Record Number</p>
                                    <p class="text-base font-bold text-slate-900 font-mono bg-slate-50 inline-block px-2 py-1 rounded" x-text="petitionDetails.petition_no || 'Pending...'"></p>
                                </div>
                                <div class="p-5">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> Log Date</p>
                                    <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.date || '-'"></p>
                                </div>
                                <div class="p-5">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Nature</p>
                                    <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.nature || '-'"></p>
                                </div>
                                <div class="p-5">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5"><i data-lucide="radio" class="w-3.5 h-3.5"></i> Origin Mode</p>
                                    <template x-if="petitionDetails.mode === 'others'">
                                        <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.mode_others"></p>
                                    </template>
                                    <template x-if="petitionDetails.mode !== 'others'">
                                        <p class="text-base font-semibold text-slate-900 capitalize" x-text="petitionDetails.mode || '-'"></p>
                                    </template>
                                </div>
                            </div>
                            <div class="p-6 bg-slate-50 border-t border-slate-100">
                                <p class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-1.5"><i data-lucide="align-left" class="w-3.5 h-3.5"></i> Registered Description</p>
                                <p class="text-sm font-medium text-slate-700 leading-relaxed max-w-4xl" x-text="petitionDetails.description || 'No description explicitly provided.'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Parties Involved Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        
                        <!-- Complainants Column -->
                        <div class="relative">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm shadow-sm border border-blue-200 ring-4 ring-white z-10">2</div>
                                <h4 class="text-lg font-bold text-slate-900">Complainants</h4>
                                <div class="flex-1 h-px bg-slate-200"></div>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="(comp, idx) in complainants" :key="idx">
                                    <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden transition-all hover:border-blue-300" x-show="comp.name">
                                        <div class="bg-blue-50/50 px-5 py-3 border-b border-blue-50 flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex justify-center items-center text-xs font-bold ring-2 ring-white">C</div>
                                                <h5 class="font-bold text-slate-800 text-base" x-text="comp.name"></h5>
                                            </div>
                                            <span class="text-[10px] bg-white text-blue-600 border border-blue-200 px-2 py-1 rounded-md font-bold uppercase tracking-wider" x-text="`ID: 0${idx+1}`"></span>
                                        </div>
                                        <div class="p-5">
                                            <div class="flex flex-wrap gap-x-8 gap-y-4 mb-4">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Contact</p>
                                                    <p class="text-sm font-semibold text-slate-900" x-text="comp.phone || 'N/A'"></p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Verification (Aadhar)</p>
                                                    <p class="text-sm font-mono font-semibold text-slate-900" x-text="comp.aadhar || 'Pending'"></p>
                                                </div>
                                            </div>
                                            <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 text-blue-500"></i> Localities</p>
                                                <template x-for="(addr, aIdx) in comp.addresses" :key="aIdx">
                                                    <div x-show="addr.address" class="flex gap-3 text-sm">
                                                        <span class="bg-slate-100 text-slate-600 border border-slate-200 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded shadow-sm mt-0.5 w-16 text-center shrink-0" x-text="addr.address_type"></span>
                                                        <span class="font-medium text-slate-700" x-text="`${addr.address}, ${addr.district} — PIN:${addr.pincode}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Respondents Column -->
                        <div class="relative">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-700 font-bold text-sm shadow-sm border border-rose-200 ring-4 ring-white z-10">3</div>
                                <h4 class="text-lg font-bold text-slate-900">Respondents</h4>
                                <div class="flex-1 h-px bg-slate-200"></div>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="(acc, idx) in accused" :key="idx">
                                    <div class="bg-white rounded-xl border border-rose-100 shadow-sm overflow-hidden transition-all hover:border-rose-300" x-show="acc.name">
                                        <div class="bg-rose-50/50 px-5 py-3 border-b border-rose-50 flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex justify-center items-center text-xs font-bold ring-2 ring-white">R</div>
                                                <h5 class="font-bold text-slate-800 text-base" x-text="acc.name"></h5>
                                            </div>
                                            <span class="text-[10px] bg-white text-rose-600 border border-rose-200 px-2 py-1 rounded-md font-bold uppercase tracking-wider" x-text="`ID: 0${idx+1}`"></span>
                                        </div>
                                        <div class="p-5">
                                            <div class="flex flex-wrap gap-x-8 gap-y-4 mb-4">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Contact</p>
                                                    <p class="text-sm font-semibold text-slate-900" x-text="acc.phone || 'N/A'"></p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Verification (Aadhar)</p>
                                                    <p class="text-sm font-mono font-semibold text-slate-900" x-text="acc.aadhar || 'Pending'"></p>
                                                </div>
                                            </div>
                                            <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 text-rose-500"></i> Localities</p>
                                                <template x-for="(addr, aIdx) in acc.addresses" :key="aIdx">
                                                    <div x-show="addr.address" class="flex gap-3 text-sm">
                                                        <span class="bg-slate-100 text-slate-600 border border-slate-200 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded shadow-sm mt-0.5 w-16 text-center shrink-0" x-text="addr.address_type"></span>
                                                        <span class="font-medium text-slate-700" x-text="`${addr.address}, ${addr.district} — PIN:${addr.pincode}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Bar -->
                <div class="bg-white border-t border-slate-200 px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-500 font-medium">Please ensure accuracy before confirming via cryptographic stamp.</p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="button" @click="togglePreview()" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all focus:ring-4 focus:ring-slate-100 shadow-sm">
                            Make Edits
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-8 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/30 hover:from-emerald-500 hover:to-teal-500 transition-all flex justify-center items-center gap-2 transform hover:-translate-y-0.5">
                            <i data-lucide="shield-check" class="w-5 h-5"></i> Submit & Register
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    function petitionForm() {
        return {
            step: 1,
            showPreview: false,
            petitionDetails: {
                petition_no: '',
                date: '',
                nature: '',
                mode: '',
                mode_others: '',
                description: ''
            },
            complainants: [{ 
                id: Date.now(), 
                name: '', phone: '', aadhar: '', 
                addresses: [
                    { address_type: 'Permanent', address: '', district: '', pincode: '' }
                ] 
            }],
            accused: [{ 
                id: Date.now()+1, 
                name: '', phone: '', aadhar: '', 
                addresses: [
                    { address_type: 'Permanent', address: '', district: '', pincode: '' }
                ] 
            }],
            
            nextStep() {
                if (this.step < 4) {
                    this.step++;
                    this.scrollToTop();
                }
            },
            prevStep() {
                if (this.step > 1) {
                    this.step--;
                    this.scrollToTop();
                }
            },
            setStep(target) {
                if (target >= 1 && target <= 4) {
                    this.step = target;
                    this.scrollToTop();
                }
            },
            togglePreview() {
                this.showPreview = !this.showPreview;
                this.scrollToTop();
            },
            scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                this.refreshIcons();
            },
            addComplainant() {
                const last = this.complainants[this.complainants.length - 1];
                if(last.name.trim() !== '') {
                    this.complainants.push({ 
                        id: Date.now(), name: '', phone: '', aadhar: '', 
                        addresses: [{ address_type: 'Permanent', address: '', district: '', pincode: '' }] 
                    });
                    this.refreshIcons();
                }
            },
            removeComplainant(index) {
                if(this.complainants.length > 1) this.complainants.splice(index, 1);
            },
            addComplainantAddress(compIndex) {
                if (this.complainants[compIndex].addresses.length < 3) {
                    // find an available type
                    const existingTypes = this.complainants[compIndex].addresses.map(a => a.address_type);
                    const allTypes = ['Permanent', 'Temporary', 'Office'];
                    const nextType = allTypes.find(t => !existingTypes.includes(t)) || 'Temporary';
                    
                    this.complainants[compIndex].addresses.push({ address_type: nextType, address: '', district: '', pincode: '' });
                    this.refreshIcons();
                }
            },
            removeComplainantAddress(compIndex, addrIndex) {
                if (this.complainants[compIndex].addresses.length > 1) {
                    this.complainants[compIndex].addresses.splice(addrIndex, 1);
                }
            },
            addAccused() {
                const last = this.accused[this.accused.length - 1];
                if(last.name.trim() !== '') {
                    this.accused.push({ 
                        id: Date.now(), name: '', phone: '', aadhar: '', 
                        addresses: [{ address_type: 'Permanent', address: '', district: '', pincode: '' }] 
                    });
                    this.refreshIcons();
                }
            },
            removeAccused(index) {
                if(this.accused.length > 1) this.accused.splice(index, 1);
            },
            addAccusedAddress(accIndex) {
                if (this.accused[accIndex].addresses.length < 3) {
                     // find an available type
                    const existingTypes = this.accused[accIndex].addresses.map(a => a.address_type);
                    const allTypes = ['Permanent', 'Temporary', 'Office'];
                    const nextType = allTypes.find(t => !existingTypes.includes(t)) || 'Temporary';

                    this.accused[accIndex].addresses.push({ address_type: nextType, address: '', district: '', pincode: '' });
                    this.refreshIcons();
                }
            },
            removeAccusedAddress(accIndex, addrIndex) {
                if (this.accused[accIndex].addresses.length > 1) {
                    this.accused[accIndex].addresses.splice(addrIndex, 1);
                }
            },
            refreshIcons() {
                if (typeof lucide !== 'undefined') {
                    this.$nextTick(() => { lucide.createIcons(); });
                }
            }
        }
    }
</script>
@endsection