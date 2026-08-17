@extends(auth()->user()->role === 'admin' && !session('is_impersonating_seat') ? 'layouts.admin' : 'layouts.user')
@section('container_width', 'max-w-full')

@section('content')
    <div class="space-y-6" x-data="petitionForm()">
        <!-- Page Header (Professional & Clean) -->
        <div
            class="bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
            <!-- Subtle top accent line -->
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>

            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <i data-lucide="edit-3" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Petition</h1>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Ref No: {{ $petition->petition_no }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('petitions.index') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 focus:outline-none transition-all">
                    Cancel
                </a>
                <a href="{{ route('petitions.show', $petition->petition_id) }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none transition-all flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4"></i> View Current
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div
                class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top-2">
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



        <!-- Stepper Navigation (Elegant Line-based) -->
        <div class="py-4">
            <div class="relative max-w-3xl mx-auto px-4 md:px-0">
                <!-- Background Line -->
                <div class="absolute top-4 left-0 w-full h-0.5 bg-indigo-300/20 -translate-y-1/2 z-0"></div>
                <!-- Progress Line -->
                <div class="absolute top-4 left-0 h-0.5 bg-teal-500 -translate-y-1/2 transition-all duration-500 ease-out z-0"
                    :style="`width: ${((step - 1) / 3) * 100}%`"></div>

                <div class="relative z-10 flex justify-between">
                    <!-- Step 1 -->
                    <button type="button" @click="setStep(1)"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 1 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span x-show="step > 1"><i data-lucide="check" class="w-4 h-4"></i></span>
                            <span x-show="step === 1">1</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 1 ? 'text-white' : 'text-blue-200'">Petition Details</span>
                    </button>

                    <!-- Step 2 -->
                    <button type="button" @click="setStep(2)"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 2 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span x-show="step > 2"><i data-lucide="check" class="w-4 h-4"></i></span>
                            <span x-show="step <= 2">2</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 2 ? 'text-white' : 'text-blue-200'">Complainant</span>
                    </button>

                    <!-- Step 3 -->
                    <button type="button" @click="setStep(3)"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 3 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span x-show="step > 3"><i data-lucide="check" class="w-4 h-4"></i></span>
                            <span x-show="step <= 3">3</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 3 ? 'text-white' : 'text-blue-200'">Suspect</span>
                    </button>

                    <!-- Step 4 -->
                    <button type="button" @click="setStep(4)"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 4 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span>4</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 4 ? 'text-white' : 'text-blue-200'">Complete</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Sections (Minimalist Cards) -->
        <form method="POST" action="{{ route('petitions.update', $petition->petition_id) }}" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-slate-200" id="petitionFormElement">
            @csrf
            @method('PUT')

            <!-- STEP 1: Petition Details -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="p-8 sm:p-10">

                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="lg:col-span-1">
                            <x-input label="Petition No *" name="petition_no" x-model="petitionDetails.petition_no" required
                                placeholder="Ex. PT-2026-001" />
                        </div>
                        <div class="lg:col-span-1">
                            <x-input type="date" placeholder="DD-MM-YYYY" label="Date of Receipt *"
                                name="date_of_petition_received" x-model="petitionDetails.date" required
                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}" />
                        </div>
                        <div class="lg:col-span-1">
                            <x-select label="Nature of Petition *" name="nature_of_petition"
                                x-model="petitionDetails.nature" required
                                :options="['' => 'Select Category', 'Bribery' => 'Bribery', 'Misuse of authority' => 'Misuse of authority', 'Fraud / financial irregularities' => 'Fraud / financial irregularities', 'Serious negligence' => 'Serious negligence', 'others' => 'Others']" />
                        </div>
                        <div class="lg:col-span-1">
                            <x-select label="Mode of Petition *" name="mode_of_petition_received"
                                x-model="petitionDetails.mode" required
                                :options="['' => 'Select Origin', 'Email' => 'Email', 'Whatsapp' => 'Whatsapp', 'Tollfree' => 'Tollfree', 'Direct' => 'Direct', 'Unit' => 'Unit', 'Tapal' => 'Tapal', 'iaps' => 'iAPS', 'others' => 'Others']" />
                        </div>
                    </div>

                    <div x-show="petitionDetails.mode === 'others'" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0" style="display: none;"
                        class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                        <x-input label="Specify Mode of Petition *" name="mode_others" x-model="petitionDetails.mode_others"
                            x-bind:required="petitionDetails.mode === 'others'"
                            placeholder="Type the custom mode of receipt..." />
                    </div>

                    <div>
                        <x-textarea label="Detailed Description *" name="description" x-model="petitionDetails.description"
                            rows="5" required placeholder="Type the complete factual description of the incident..." />
                    </div>

                    <div class="pt-4 mt-6 border-t border-slate-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-4">Supporting Evidence
                            (Attachments)</label>

                        @if($petition->uploads->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                                @foreach($petition->uploads as $upload)
                                    <div x-show="!deleted_uploads.includes({{ $upload->upload_id }})"
                                        class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl group transition-all hover:bg-white hover:shadow-sm">
                                        <div class="flex items-center gap-3 truncate pr-4">
                                            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg shrink-0">
                                                <i data-lucide="file" class="w-4 h-4"></i>
                                            </div>
                                            <div class="truncate">
                                                <p class="text-xs font-semibold text-slate-900 truncate">
                                                    {{ $upload->original_filename }}</p>
                                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">
                                                    {{ $upload->created_at->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('petitions.download', $upload->upload_id) }}" target="_blank"
                                                class="shrink-0 p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                                title="View Document">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                            </a>
                                            <button type="button" @click="removeExistingUpload({{ $upload->upload_id }})"
                                                class="shrink-0 p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                                title="Remove Document">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Hidden fields for deleted uploads -->
                            <template x-for="id in deleted_uploads" :key="id">
                                <input type="hidden" name="deleted_attachments[]" :value="id">
                            </template>
                        @endif

                        <x-file-upload name="evidence_files[]" label="Add Additional Evidence (Attachments)"
                            :multiple="true" />
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-200 flex justify-end">
                    <button type="button" @click="nextStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm transition-all flex items-center gap-2">
                        Continue to Complainant <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Complainant Details -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="p-8 sm:p-10" style="display: none;">

                <div class="space-y-8">
                    <template x-for="(comp, index) in complainants" :key="comp.id">
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                            <!-- Header for the section -->
                            <div class="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span
                                        class="bg-indigo-100 text-indigo-700 w-5 h-5 rounded-md flex items-center justify-center text-xs"
                                        x-text="index + 1"></span>
                                    Complainant Information
                                </h3>
                                <button type="button" @click="removeComplainant(index)" x-show="complainants.length > 1"
                                    class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                                </button>
                            </div>

                            <div class="p-6">
                                <!-- Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                    <div>
                                        <x-input label="Full Name" name="comp_name"
                                            x-bind:name="`complainants[${index}][name]`" x-model="comp.name"
                                            x-bind:required="step === 2" placeholder="Legal Name" />
                                    </div>
                                    <div>
                                        <x-input label="Phone Number" name="comp_phone"
                                            x-bind:name="`complainants[${index}][phone]`" x-model="comp.phone"
                                            x-bind:required="step === 2" placeholder="10-digit Mobile" />
                                    </div>
                                    <div>
                                        <x-input label="Email (Optional)" name="comp_email"
                                            x-bind:name="`complainants[${index}][email]`" x-model="comp.email"
                                            type="email" placeholder="Email Address" />
                                    </div>
                                </div>

                                <!-- Nested Addresses -->
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Address Book
                                    </h4>

                                    <div class="space-y-4">
                                        <template x-for="(addr, addrIndex) in comp.addresses" :key="addrIndex">
                                            <div class="bg-slate-50 p-5 border border-slate-200 rounded-lg relative">
                                                <button type="button" @click="removeComplainantAddress(index, addrIndex)"
                                                    x-show="comp.addresses.length > 1"
                                                    class="absolute top-4 right-4 z-10 text-slate-400 hover:text-rose-600 transition-colors">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-semibold text-slate-700 mb-1.5">Address
                                                            Type</label>
                                                        <div class="relative flex items-center">
                                                            <select
                                                                :name="`complainants[${index}][addresses][${addrIndex}][address_type]`"
                                                                x-model="addr.address_type"
                                                                class="block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition-all appearance-none pr-10"
                                                                style="background-image: none !important;">
                                                                <option value="Permanent"
                                                                    :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')">
                                                                    Permanent</option>
                                                                <option value="Temporary"
                                                                    :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')">
                                                                    Temporary</option>
                                                                <option value="Office"
                                                                    :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')">
                                                                    Office</option>
                                                            </select>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="w-4 h-4 text-slate-400 absolute right-2 pointer-events-none">
                                                                <path d="m6 9 6 6 6-6" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="lg:col-span-2">
                                                        <x-input label="Street Address *" name="comp_address"
                                                            x-bind:name="`complainants[${index}][addresses][${addrIndex}][address]`"
                                                            x-model="addr.address" x-bind:required="step === 2"
                                                            placeholder="House No, Street, Locality" />
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <x-select label="District *"
                                                                x-bind:name="`complainants[${index}][addresses][${addrIndex}][district_id]`"
                                                                x-model="addr.district_id" x-bind:required="step === 2"
                                                                placeholder="Select District"
                                                                :options="$districts->pluck('district_name', 'district_id')->toArray()" />
                                                        </div>
                                                        <div>
                                                            <x-input label="Pincode *" name="comp_pincode"
                                                                x-bind:name="`complainants[${index}][addresses][${addrIndex}][pincode]`"
                                                                x-model="addr.pincode" x-bind:required="step === 2"
                                                                placeholder="Code" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="mt-4" x-show="comp.addresses.length < 3">
                                        <button type="button" @click="addComplainantAddress(index)"
                                            class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                            <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="pt-2 text-center">
                        <button type="button" @click="addComplainant()"
                            class="px-6 py-3 border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 hover:border-slate-400 transition-all flex items-center justify-center gap-2 w-full">
                            <i data-lucide="user-plus" class="w-5 h-5"></i> Register Another Complainant
                        </button>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-center">
                    <button type="button" @click="prevStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                    </button>
                    <button type="button" @click="nextStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                        Continue to Suspect <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Suspect Details -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="p-8 sm:p-10" style="display: none;">

                <div class="space-y-8">
                    <template x-for="(acc, index) in accused" :key="acc.id">
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                            <!-- Header for the section -->
                            <div
                                class="flex flex-wrap justify-between items-center px-6 py-4 bg-slate-50/80 border-b border-slate-200 gap-4">
                                <div class="flex flex-wrap items-center gap-6">
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span
                                            class="bg-indigo-100 text-indigo-700 w-5 h-5 rounded-md flex items-center justify-center text-xs"
                                            x-text="index + 1"></span>
                                        Suspect Information
                                    </h3>

                                    <!-- Catchy Entity Type Selection -->
                                    <div class="inline-flex p-1 bg-indigo-600 rounded-full shadow-inner">
                                        <label class="relative flex-1 cursor-pointer min-w-[150px] text-center mb-0">
                                            <input type="radio" x-model="acc.entity_type" value="Person"
                                                x-bind:name="`accused[${index}][entity_type]`"
                                                @change="changeEntityType(index, 'Person')" class="sr-only">
                                            <div class="px-4 py-1.5 rounded-full text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2"
                                                :class="acc.entity_type === 'Person' ? 'bg-white text-indigo-600 shadow-sm' : 'text-indigo-100 hover:text-white'">
                                                <i data-lucide="user" class="w-4 h-4"></i> Person
                                            </div>
                                        </label>
                                        <label class="relative flex-1 cursor-pointer min-w-[150px] text-center mb-0">
                                            <input type="radio" x-model="acc.entity_type" value="Firm"
                                                x-bind:name="`accused[${index}][entity_type]`"
                                                @change="changeEntityType(index, 'Firm')"
                                                class="sr-only">
                                            <div class="px-4 py-1.5 rounded-full text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2"
                                                :class="acc.entity_type === 'Firm' ? 'bg-white text-indigo-600 shadow-sm' : 'text-indigo-100 hover:text-white'">
                                                <i data-lucide="building-2" class="w-4 h-4"></i> Firm / Project
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <button type="button" @click="removeAccused(index)" x-show="accused.length > 1"
                                    class="text-rose-600 hover:text-rose-700 text-xs font-semibold flex items-center gap-1 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                                </button>
                            </div>

                            <div class="p-6 pt-7">
                                <!-- Fields -->
                                 <div class="space-y-6 mb-8">
                                     <!-- Row 1: Name, Phone, and optional PEN -->
                                     <div class="grid gap-6 grid-cols-1" :class="acc.entity_type === 'Person' ? 'md:grid-cols-3' : 'md:grid-cols-2'">
                                         <div x-show="acc.entity_type === 'Person'">
                                             <x-input label="Full Name *" name="acc_name" x-bind:name="`accused[${index}][name]`"
                                                 x-model="acc.name" x-bind:required="step === 3 && acc.entity_type === 'Person'"
                                                 placeholder="Legal Name" />
                                         </div>
                                         <div x-show="acc.entity_type === 'Firm'">
                                             <x-input label="Firm / Project Name *" name="acc_firm_name"
                                                 x-bind:name="`accused[${index}][name]`" x-model="acc.name"
                                                 x-bind:required="step === 3 && acc.entity_type === 'Firm'"
                                                 placeholder="Firm / Project Name" />
                                         </div>
                                         <div>
                                             <x-input label="Phone" name="acc_phone" x-bind:name="`accused[${index}][phone]`"
                                                 x-model="acc.phone" placeholder="Mobile Number" />
                                         </div>
                                         <div x-show="acc.entity_type === 'Person'">
                                             <x-input label="PEN Number" name="acc_pen_number" x-bind:name="`accused[${index}][pen_number]`"
                                                 x-model="acc.pen_number" placeholder="6 or 7 digit PEN" pattern="\d{6,7}" title="PEN must be 6 or 7 digits" />
                                         </div>
                                     </div>

                                     <!-- Row 2: Designation and Department -->
                                     <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
                                         <div>
                                             <x-searchable-select label="Designation"
                                                 x-bind:name="`accused[${index}][designation_id]`" x-model="acc.designation_id"
                                                 :options="$designations->pluck('designation_name', 'id')->toArray()" placeholder="Select Designation" />
                                         </div>
                                         <div>
                                             <x-searchable-select label="Department"
                                                 x-bind:name="`accused[${index}][department_id]`" x-model="acc.department_id"
                                                 :options="$departments->pluck('department_name', 'id')->toArray()" placeholder="Select Department" />
                                         </div>
                                     </div>
                                 </div>

                                <!-- Nested Addresses -->
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Known Addresses
                                    </h4>

                                    <div class="space-y-4">
                                        <template x-for="(addr, addrIndex) in acc.addresses" :key="addrIndex">
                                            <div class="bg-slate-50 p-5 border border-slate-200 rounded-lg relative">
                                                <button type="button" @click="removeAccusedAddress(index, addrIndex)"
                                                    x-show="acc.addresses.length > 1"
                                                    class="absolute top-4 right-4 z-10 text-slate-400 hover:text-rose-600 transition-colors">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-semibold text-slate-700 mb-1.5">Address
                                                            Type</label>
                                                        <div class="relative flex items-center">
                                                            <select
                                                                :name="`accused[${index}][addresses][${addrIndex}][address_type]`"
                                                                x-model="addr.address_type"
                                                                :class="acc.entity_type === 'Firm' ? 'pointer-events-none bg-slate-50 text-slate-500 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg outline-none transition-all appearance-none pr-10' : 'block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition-all appearance-none pr-10'"
                                                                style="background-image: none !important;">
                                                                <option value="Permanent"
                                                                    :disabled="acc.entity_type === 'Firm' || acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')"
                                                                    x-show="acc.entity_type !== 'Firm'">Permanent</option>
                                                                <option value="Temporary"
                                                                    :disabled="acc.entity_type === 'Firm' || acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')"
                                                                    x-show="acc.entity_type !== 'Firm'">Temporary</option>
                                                                <option value="Office"
                                                                    :disabled="acc.entity_type !== 'Firm' && acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')">
                                                                    Office</option>
                                                            </select>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="w-4 h-4 text-slate-400 absolute right-2 pointer-events-none">
                                                                <path d="m6 9 6 6 6-6" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="lg:col-span-2">
                                                        <x-input label="Street Address" name="acc_address"
                                                            x-bind:name="`accused[${index}][addresses][${addrIndex}][address]`"
                                                            x-model="addr.address" x-bind:required="step === 3"
                                                            placeholder="House No, Street, Locality" />
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <x-select label="District"
                                                                x-bind:name="`accused[${index}][addresses][${addrIndex}][district_id]`"
                                                                x-model="addr.district_id" x-bind:required="step === 3"
                                                                placeholder="Select District"
                                                                :options="$districts->pluck('district_name', 'district_id')->toArray()" />
                                                        </div>
                                                        <div>
                                                            <x-input label="Pincode"
                                                                x-bind:name="`accused[${index}][addresses][${addrIndex}][pincode]`"
                                                                x-model="addr.pincode" x-bind:required="step === 3"
                                                                placeholder="Code" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="mt-4" x-show="acc.entity_type !== 'Firm' && acc.addresses.length < 3">
                                        <button type="button" @click="addAccusedAddress(index)"
                                            class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                            <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="pt-2 text-center" x-show="accused.length > 0 && accused[accused.length - 1].name.trim() !== ''">
                        <button type="button" @click="addAccused()"
                            class="px-6 py-3 border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 hover:border-slate-400 transition-all flex items-center justify-center gap-2 w-full">
                            <i data-lucide="user-plus" class="w-5 h-5"></i> Register Another Suspect
                        </button>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-center">
                    <button type="button" @click="prevStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                    </button>
                    <button type="button" @click="nextStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                        Review Changes <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Review & Finalize -->
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="p-8 sm:p-10" style="display: none;">

                <div class="max-w-2xl mx-auto py-8">
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 mb-8">
                        <x-textarea label="Current Assessment/Action Note" name="proposed_action"
                            x-model="petitionDetails.proposed_action" rows="4"
                            placeholder="Enter the remarks/notes here..." />
                    </div>

                    <div class="flex justify-between items-center py-6 border-t border-slate-200 mt-6">
                        <button type="button" @click="prevStep()"
                            class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Edit Details
                        </button>
                        <button type="submit"
                            class="px-8 py-3 text-sm font-bold text-white bg-slate-900 rounded-xl shadow-md hover:bg-slate-800 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-5 h-5"></i> Update Petition Record
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
                    petition_no: '{!! addslashes($petition->petition_no) !!}',
                    date: '{{ $petition->date_of_petition_received }}',
                    nature: '{{ $petition->nature_of_petition }}',
                    mode: '{{ $petition->mode_of_petition_received }}',
                    mode_others: '{!! addslashes($petition->mode_of_petition_received_others) !!}',
                    description: `{!! addslashes($petition->description) !!}`,
                    proposed_action: `{!! addslashes($petition->proposed_action) !!}`
                },
                complainants: {!! json_encode($complainants) !!}.length > 0 ? {!! json_encode($complainants) !!} : [{ id: Date.now(), name: '', phone: '', email: '', addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }] }],
                accused: {!! json_encode($accused) !!}.length > 0 ? {!! json_encode($accused) !!} : [{ id: Date.now() + 1, entity_type: 'Person', designation_id: '', department_id: '', name: '', phone: '', pen_number: '', addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }] }],
                deleted_uploads: [],

                removeExistingUpload(id) {
                    if (confirm('Are you sure you want to remove this document? It will be permanently deleted once you update the petition.')) {
                        this.deleted_uploads.push(id);
                    }
                },

                nextStep() {
                    if (this.validateStep(this.step)) {
                        if (this.step < 4) {
                            this.step++;
                            this.scrollToTop();
                        }
                    }
                },
                prevStep() {
                    if (this.step > 1) {
                        this.step--;
                        this.scrollToTop();
                    }
                },
                validateStep(step) {
                    if (step === 1) {
                        if (!this.petitionDetails.petition_no || !this.petitionDetails.date || !this.petitionDetails.nature || !this.petitionDetails.mode || !this.petitionDetails.description) {
                            alert("Please fill all required petition details.");
                            return false;
                        }
                    } else if (step === 2) {
                        const validComp = this.complainants.every(c => c.name.trim() !== '' && c.phone.trim() !== '');
                        if (!validComp) {
                            alert("Please provide at least Name and Phone for all complainants.");
                            return false;
                        }
                    } else if (step === 3) {
                        const validAcc = this.accused.every(a => a.name.trim() !== '');
                        if (!validAcc) {
                            alert("Please provide the name of the suspect.");
                            return false;
                        }
                    }
                    return true;
                },
                setStep(target) {
                    if (target < this.step || this.validateStep(this.step)) {
                        if (target >= 1 && target <= 4) {
                            this.step = target;
                            this.scrollToTop();
                        }
                    }
                },
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.refreshIcons();
                },
                addComplainant() {
                    this.complainants.push({
                        id: Date.now(), name: '', phone: '', email: '',
                        addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }]
                    });
                    this.refreshIcons();
                },
                removeComplainant(index) {
                    if (this.complainants.length > 1) this.complainants.splice(index, 1);
                },
                addComplainantAddress(compIndex) {
                    if (this.complainants[compIndex].addresses.length < 3) {
                        const existingTypes = this.complainants[compIndex].addresses.map(a => a.address_type);
                        const allTypes = ['Permanent', 'Temporary', 'Office'];
                        const nextType = allTypes.find(t => !existingTypes.includes(t)) || 'Temporary';

                        this.complainants[compIndex].addresses.push({ address_type: nextType, address: '', district_id: '', pincode: '' });
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
                    if (!last || last.name.trim() !== '') {
                        this.accused.push({
                            id: Date.now(), entity_type: 'Person', designation_id: '', department_id: '', name: '', phone: '', pen_number: '',
                            addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }]
                        });
                        this.refreshIcons();
                    }
                },
                removeAccused(index) {
                    if (this.accused.length > 1) this.accused.splice(index, 1);
                },
                changeEntityType(accIndex, newType) {
                    let acc = this.accused[accIndex];
                    acc.entity_type = newType;
                    if (newType === 'Firm') {
                        if (acc.addresses.length > 0 && acc.addresses[0].address_type === 'Permanent') {
                            acc.addresses[0].address_type = 'Office';
                        }
                    } else {
                        if (acc.addresses.length > 0 && acc.addresses[0].address_type === 'Office') {
                            acc.addresses[0].address_type = 'Permanent';
                        }
                    }
                    this.refreshIcons();
                },
                addAccusedAddress(accIndex) {
                    if (this.accused[accIndex].addresses.length < 3) {
                        const existingTypes = this.accused[accIndex].addresses.map(a => a.address_type);
                        const allTypes = ['Permanent', 'Temporary', 'Office'];
                        const nextType = allTypes.find(t => !existingTypes.includes(t)) || 'Temporary';

                        this.accused[accIndex].addresses.push({ address_type: nextType, address: '', district_id: '', pincode: '' });
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