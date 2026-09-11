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
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Complaint</h1>
                </div>
            </div>
        </div>





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
                    <button type="button" @click="step >= 2 ? setStep(2) : null"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent"
                        :class="{ 'cursor-not-allowed': step < 2 }">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 2 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span x-show="step > 2"><i data-lucide="check" class="w-4 h-4"></i></span>
                            <span x-show="step <= 2">2</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 2 ? 'text-white' : 'text-blue-200'">Complainant</span>
                    </button>

                    <!-- Step 3 -->
                    <button type="button" @click="step >= 3 ? setStep(3) : null"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent"
                        :class="{ 'cursor-not-allowed': step < 3 }">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm"
                            :class="step >= 3 ? 'bg-teal-500 text-white' : 'bg-white text-blue-500 border border-blue-200'">
                            <span x-show="step > 3"><i data-lucide="check" class="w-4 h-4"></i></span>
                            <span x-show="step <= 3">3</span>
                        </div>
                        <span class="text-xs font-semibold whitespace-nowrap transition-colors"
                            :class="step >= 3 ? 'text-white' : 'text-blue-200'">Suspect</span>
                    </button>

                    <!-- Step 4 -->
                    <button type="button" @click="step >= 4 ? setStep(4) : null"
                        class="flex flex-col items-center gap-2 focus:outline-none group bg-transparent"
                        :class="{ 'cursor-not-allowed': step < 4 }">
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
        <form method="POST" action="{{ route('petitions.store') }}" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-slate-200" id="petitionFormElement"
            @focusout="validateField($event)" @input="clearError($event)" @change="clearError($event)">
            @csrf
            <input type="hidden" name="step" :value="step">


            <!-- STEP 1: Petition Details -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="p-8 sm:p-10">

                <div class="mb-8 border-b border-slate-100 pb-5">
                    {{-- <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i> Section 1: Petition Overview
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Provide the fundamental details of the petition received.</p>
                    --}}
                </div>

                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="lg:col-span-1">
                            <x-input label="Receipt No *" name="receipt_no" x-model="petitionDetails.receipt_no" required
                                placeholder="Ex. 12/DVACB/2026-CPSP 5" @input="count = $event.target.value.length"
                                @blur="validateField($event); checkPetitionNumberUniqueness($event.target.value)" />
                        </div>
                        <div class="lg:col-span-1">
                            <x-input type="date" placeholder="DD-MM-YYYY" label="Date of Receipt *"
                                name="date_of_petition_received" x-model="petitionDetails.date" required
                                max="{{ now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d') }}" />
                        </div>
                        <div class="lg:col-span-1">
                            <x-select label="Nature of Petition *" name="nature_of_petition"
                                x-model="petitionDetails.nature" required
                                :options="['' => 'Select Category', 'Amassment of Wealth' => 'Amassment of Wealth', 'Bribery' => 'Bribery', 'Misuse of authority' => 'Misuse of authority', 'Fraud / financial irregularities' => 'Fraud / financial irregularities', 'Serious negligence' => 'Serious negligence', 'others' => 'Others']" />
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

                    <x-file-upload name="evidence_files[]" label="Supporting Evidence (Attachments)" :multiple="true" />
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
                                        <x-input label="Name" name="comp_name" x-bind:name="`complainants[${index}][name]`"
                                            x-model="comp.name" placeholder="Full Name" />
                                    </div>
                                    <div>
                                        <x-input label="Phone Number" name="comp_phone"
                                            x-bind:name="`complainants[${index}][phone]`" x-model="comp.phone"
                                            placeholder="Mobile Number" />
                                    </div>
                                    <div>
                                        <x-input label="Email" name="comp_email"
                                            x-bind:name="`complainants[${index}][email]`" x-model="comp.email" type="email"
                                            placeholder="Email Address" />
                                    </div>
                                </div>
                            </div>

                            <!-- Nested Addresses -->
                            <div>
                                <h4
                                    class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Complainant Address
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
                                                    <x-select label="Address Type"
                                                        x-bind:name="`complainants[${index}][addresses][${addrIndex}][address_type]`"
                                                        x-model="addr.address_type">
                                                        <option value="Permanent"
                                                            :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')"
                                                            class="font-semibold text-slate-900 bg-white">Permanent</option>
                                                        <option value="Temporary"
                                                            :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')"
                                                            class="font-semibold text-slate-900 bg-white">Temporary</option>
                                                        <option value="Office"
                                                            :disabled="comp.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')"
                                                            class="font-semibold text-slate-900 bg-white">Office</option>
                                                    </x-select>
                                                </div>
                                                <div class="lg:col-span-2">
                                                    <x-input label="Address" name="comp_address"
                                                        x-bind:name="`complainants[${index}][addresses][${addrIndex}][address]`"
                                                        x-model="addr.address"
                                                        placeholder="House/Building Name, Locality" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <x-select label="District"
                                                            x-bind:name="`complainants[${index}][addresses][${addrIndex}][district_id]`"
                                                            x-model="addr.district_id" placeholder="Select District"
                                                            :options="$districts->pluck('district_name', 'district_id')->toArray()" />
                                                    </div>
                                                    <div>
                                                        <x-input label="Pincode" name="comp_pincode"
                                                            x-bind:name="`complainants[${index}][addresses][${addrIndex}][pincode]`"
                                                            x-model="addr.pincode" placeholder="Code" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-4"
                                    x-show="comp.addresses.length < 3 && comp.addresses[comp.addresses.length - 1].address.trim() !== ''">
                                    <button type="button" @click="addComplainantAddress(index)"
                                        class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                    </button>
                                </div>
                            </div>
                        </div>
                </div>
                </template>

                <div class="pt-2 text-center" x-show="complainants[complainants.length - 1].name.trim() !== ''">
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

    <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="p-8 sm:p-10" style="display: none;">

        <!-- Duplicate Detection Banner -->
        <div x-show="duplicates.length > 0 && !linkedPetitionId" x-cloak
            class="mb-6 p-4 border rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
            style="background-color: #fffbeb; border-color: #fde68a;" x-transition>
            <div class="flex items-start gap-3">
                <div class="mt-0.5" style="color: #d97706;">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold" style="color: #92400e;">Potential Duplicates Detected!</h4>
                    <p class="text-xs mt-1" style="color: #b45309;">We found <span x-text="duplicates.length"
                            class="font-bold"></span>
                        existing petition(s) that look similar to the details you entered.</p>
                </div>
            </div>
            <button type="button" @click="showDuplicateModal = true" style="background-color: #f59e0b; color: white;"
                class="whitespace-nowrap text-xs font-bold px-4 py-2 rounded-lg shadow-sm hover:opacity-90 transition-opacity">
                View & Link Duplicates
            </button>
        </div>

        <input type="hidden" name="linked_petition_id" :value="linkedPetitionId">

        <div class="mb-8 border-b border-slate-100 pb-5">
            {{-- <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i> Section 3: Suspect Details
            </h2>
            <p class="text-sm text-slate-500 mt-1">Add one or more accused individuals regarding this petition.</p> --}}
        </div>

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
                                        @change="changeEntityType(index, 'Firm')" class="sr-only">
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
                        <div class="space-y-6 mb-8">
                            <!-- Row 1: Name, Phone, and optional PEN -->
                            <div class="grid gap-6 grid-cols-1"
                                :class="acc.entity_type === 'Person' ? 'md:grid-cols-3' : 'md:grid-cols-2'">
                                <div x-show="acc.entity_type === 'Person'">
                                    <x-input label="Name" name="acc_name" x-bind:name="`accused[${index}][name]`"
                                        x-model="acc.name" placeholder="Full Name"
                                        x-bind:required="acc.entity_type === 'Person'" />
                                </div>
                                <div x-show="acc.entity_type === 'Firm'">
                                    <x-input label="Firm / Project Name" name="acc_firm_name"
                                        x-bind:name="`accused[${index}][name]`" x-model="acc.name"
                                        placeholder="Firm / Project Name" x-bind:required="acc.entity_type === 'Firm'" />
                                </div>
                                <div>
                                    <x-input label="Phone Number" name="acc_phone" x-bind:name="`accused[${index}][phone]`"
                                        x-model="acc.phone" placeholder="Mobile Number" />
                                </div>
                                <div x-show="acc.entity_type === 'Person'">
                                    <x-input label="PEN Number" name="acc_pen_number"
                                        x-bind:name="`accused[${index}][pen_number]`" x-model="acc.pen_number"
                                        placeholder="6 or 7 digit PEN" pattern="\d{6,7}"
                                        title="PEN must be 6 or 7 digits" />
                                </div>
                            </div>

                            <!-- Row 2: Designation and Department -->
                            <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
                                <div>
                                    <x-searchable-select label="Designation"
                                        x-bind:name="`accused[${index}][designation_id]`" x-model="acc.designation_id"
                                        :options="$designations->pluck('designation_name', 'id')->toArray()"
                                        placeholder="Select Designation" />
                                </div>
                                <div>
                                    <x-searchable-select label="Department" x-bind:name="`accused[${index}][department_id]`"
                                        x-model="acc.department_id" :options="$departments->pluck('department_name', 'id')->toArray()" placeholder="Select Department" />
                                </div>
                            </div>
                        </div>

                        <!-- Nested Addresses -->
                        <div>
                            <h4
                                class="text-sm font-semibold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-2">
                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i> Suspect Addresses
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
                                                <x-select label="Address Type"
                                                    x-bind:name="`accused[${index}][addresses][${addrIndex}][address_type]`"
                                                    x-model="addr.address_type"
                                                    x-bind:class="acc.entity_type === 'Firm' ? 'pointer-events-none bg-slate-50 text-slate-500' : ''"
                                                    x-bind:tabindex="acc.entity_type === 'Firm' ? '-1' : '0'">
                                                    <option value="Permanent"
                                                        :disabled="acc.entity_type === 'Firm' || acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Permanent')"
                                                        x-show="acc.entity_type !== 'Firm'"
                                                        class="font-semibold text-slate-900 bg-white">Permanent</option>
                                                    <option value="Temporary"
                                                        :disabled="acc.entity_type === 'Firm' || acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Temporary')"
                                                        x-show="acc.entity_type !== 'Firm'"
                                                        class="font-semibold text-slate-900 bg-white">Temporary</option>
                                                    <option value="Office"
                                                        :disabled="acc.entity_type !== 'Firm' && acc.addresses.some((a, i) => i !== addrIndex && a.address_type === 'Office')"
                                                        class="font-semibold text-slate-900 bg-white">Office</option>
                                                </x-select>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <x-input label="Address" name="acc_address"
                                                    x-bind:name="`accused[${index}][addresses][${addrIndex}][address]`"
                                                    x-model="addr.address" placeholder="House/Building Name, Locality" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <x-select label="District"
                                                        x-bind:name="`accused[${index}][addresses][${addrIndex}][district_id]`"
                                                        x-model="addr.district_id" placeholder="Select District"
                                                        :options="$districts->pluck('district_name', 'district_id')->toArray()" />
                                                </div>
                                                <div>
                                                    <x-input label="Pincode" name="acc_pincode"
                                                        x-bind:name="`accused[${index}][addresses][${addrIndex}][pincode]`"
                                                        x-model="addr.pincode" placeholder="Code" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4"
                                x-show="acc.entity_type !== 'Firm' && acc.addresses.length < 3 && acc.addresses[acc.addresses.length - 1].address.trim() !== ''">
                                <button type="button" @click="addAccusedAddress(index)"
                                    class="text-sm font-semibold text-indigo-600 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Additional Address
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="pt-2 text-center" x-show="accused[accused.length - 1].name.trim() !== ''">
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
                Finalize Details <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- STEP 4: Complete -->
    <div x-show="step === 4" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="p-8 sm:p-10" style="display: none;">

        <div x-show="!showPreview" class="relative">
            <div class="max-w-2xl mx-auto py-8">


                <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 mb-8">
                    <x-textarea label="Proposed Action" name="proposed_action" x-model="petitionDetails.proposed_action"
                        rows="4" placeholder="Enter the remarks/notes here..." />
                </div>

                <div class="flex gap-4 mb-4">
                    <button type="button" @click="togglePreview()"
                        class="w-full px-6 py-3 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="eye" class="w-4 h-4"></i> Full-Preview
                    </button>
                </div>

                <div class="flex justify-between items-center py-6 border-t border-slate-200 mt-6">
                    <button type="button" @click="prevStep()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Edit Petition
                    </button>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-slate-900 rounded-lg shadow-md hover:bg-slate-800 focus:ring-4 focus:ring-slate-900/20 transition-all flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i> Submit Petition
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Catchy Preview Section -->
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100" style="display: none;"
            class="text-left bg-white rounded-2xl shadow-2xl shadow-indigo-500/10 border border-slate-200 relative overflow-hidden mt-8">

            <!-- Premium Header -->
            <div
                class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-b border-indigo-900/50 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-indigo-500 opacity-20 blur-3xl rounded-full pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-32 h-32 bg-blue-500 opacity-20 blur-3xl rounded-full pointer-events-none">
                </div>

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
                    <button type="button" @click="togglePreview()"
                        class="p-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-full text-indigo-100 transition-all backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/20">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="p-8 sm:p-10 bg-slate-50/50 space-y-12">

                <!-- 1. Petition Overview Panel -->
                <div class="relative">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm shadow-sm border border-indigo-200 ring-4 ring-white z-10">
                            1</div>
                        <h4 class="text-lg font-bold text-slate-900">Petition Outline</h4>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                            <div class="p-5">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="hash" class="w-3.5 h-3.5"></i> Record Number
                                </p>
                                <p class="text-base font-bold text-slate-900 font-mono bg-slate-50 inline-block px-2 py-1 rounded"
                                    x-text="petitionDetails.receipt_no || 'Pending...'"></p>
                            </div>
                            <div class="p-5">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Log Date
                                </p>
                                <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.date || '-'"></p>
                            </div>
                            <div class="p-5">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Nature
                                </p>
                                <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.nature || '-'">
                                </p>
                            </div>
                            <div class="p-5">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="radio" class="w-3.5 h-3.5"></i> Origin Mode
                                </p>
                                <template x-if="petitionDetails.mode === 'others'">
                                    <p class="text-base font-semibold text-slate-900" x-text="petitionDetails.mode_others">
                                    </p>
                                </template>
                                <template x-if="petitionDetails.mode !== 'others'">
                                    <p class="text-base font-semibold text-slate-900 capitalize"
                                        x-text="petitionDetails.mode || '-'"></p>
                                </template>
                            </div>
                        </div>
                        <div class="p-6 bg-slate-50 border-t border-slate-100">
                            <p
                                class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <i data-lucide="align-left" class="w-3.5 h-3.5"></i> Registered Description
                            </p>
                            <p class="text-sm font-medium text-slate-700 leading-relaxed max-w-4xl"
                                x-text="petitionDetails.description || 'No description explicitly provided.'"></p>
                        </div>
                    </div>
                </div>

                <!-- 2. Parties Involved Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                    <!-- Complainants Column -->
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm shadow-sm border border-blue-200 ring-4 ring-white z-10">
                                2</div>
                            <h4 class="text-lg font-bold text-slate-900">Complainants</h4>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(comp, idx) in complainants" :key="idx">
                                <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden transition-all hover:border-blue-300"
                                    x-show="comp.name">
                                    <div
                                        class="bg-blue-50/50 px-5 py-3 border-b border-blue-50 flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex justify-center items-center text-xs font-bold ring-2 ring-white">
                                                C</div>
                                            <h5 class="font-bold text-slate-800 text-base" x-text="comp.name"></h5>
                                        </div>
                                        <span
                                            class="text-[10px] bg-white text-blue-600 border border-blue-200 px-2 py-1 rounded-md font-bold uppercase tracking-wider"
                                            x-text="`ID: 0${idx+1}`"></span>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex flex-wrap gap-x-8 gap-y-4 mb-4">
                                            <div>
                                                <p
                                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                                    Contact</p>
                                                <p class="text-sm font-semibold text-slate-900"
                                                    x-text="comp.phone || 'N/A'"></p>
                                                <p class="text-xs text-slate-500 font-medium">Email</p>
                                                <p class="text-sm font-semibold text-slate-900"
                                                    x-text="comp.email || 'N/A'"></p>
                                            </div>

                                        </div>
                                        <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                            <p
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                                                <i data-lucide="map-pin" class="w-3 h-3 text-blue-500"></i> Localities
                                            </p>
                                            <template x-for="(addr, aIdx) in comp.addresses" :key="aIdx">
                                                <div x-show="addr.address" class="flex gap-3 text-sm">
                                                    <span
                                                        class="bg-slate-100 text-slate-600 border border-slate-200 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded shadow-sm mt-0.5 w-16 text-center shrink-0"
                                                        x-text="addr.address_type"></span>
                                                    <span class="font-medium text-slate-700"
                                                        x-text="`${addr.address}, ${districtNames[addr.district_id] || ''} — PIN:${addr.pincode}`"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Suspects Column -->
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-700 font-bold text-sm shadow-sm border border-rose-200 ring-4 ring-white z-10">
                                3</div>
                            <h4 class="text-lg font-bold text-slate-900">Suspects</h4>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(acc, idx) in accused" :key="idx">
                                <div class="bg-white rounded-xl border border-rose-100 shadow-sm overflow-hidden transition-all hover:border-rose-300"
                                    x-show="acc.name">
                                    <div
                                        class="bg-rose-50/50 px-5 py-3 border-b border-rose-50 flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex justify-center items-center text-xs font-bold ring-2 ring-white">
                                                R</div>
                                            <h5 class="font-bold text-slate-800 text-base" x-text="acc.name"></h5>
                                        </div>
                                        <span
                                            class="text-[10px] bg-white text-rose-600 border border-rose-200 px-2 py-1 rounded-md font-bold uppercase tracking-wider"
                                            x-text="`ID: 0${idx+1}`"></span>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex flex-wrap gap-x-8 gap-y-4 mb-4">
                                            <div>
                                                <p
                                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                                    Contact</p>
                                                <p class="text-sm font-semibold text-slate-900" x-text="acc.phone || 'N/A'">
                                                </p>
                                            </div>

                                        </div>
                                        <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                            <p
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                                                <i data-lucide="map-pin" class="w-3 h-3 text-rose-500"></i> Localities
                                            </p>
                                            <template x-for="(addr, aIdx) in acc.addresses" :key="aIdx">
                                                <div x-show="addr.address" class="flex gap-3 text-sm">
                                                    <span
                                                        class="bg-slate-100 text-slate-600 border border-slate-200 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded shadow-sm mt-0.5 w-16 text-center shrink-0"
                                                        x-text="addr.address_type"></span>
                                                    <span class="font-medium text-slate-700"
                                                        x-text="`${addr.address}, ${districtNames[addr.district_id] || ''} — PIN:${addr.pincode}`"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 4. Proposed Action -->
                <div class="relative">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm shadow-sm border border-emerald-200 ring-4 ring-white z-10">
                            4</div>
                        <h4 class="text-lg font-bold text-slate-900">Proposed Action</h4>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="bg-emerald-50/30 rounded-2xl border border-emerald-100 shadow-sm p-6">
                        <p
                            class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> Assessment Notes
                        </p>
                        <p class="text-sm font-medium text-slate-700 leading-relaxed"
                            x-text="petitionDetails.proposed_action || 'No assessment notes provided yet.'"></p>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div
                class="bg-white border-t border-slate-200 px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-500 font-medium"></p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="button" @click="togglePreview()"
                        class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all focus:ring-4 focus:ring-slate-100 shadow-sm">
                        Make Edits
                    </button>
                    <button type="submit"
                        class="w-full sm:w-auto px-8 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/30 hover:from-emerald-500 hover:to-teal-500 transition-all flex justify-center items-center gap-2 transform hover:-translate-y-0.5">
                        <i data-lucide="shield-check" class="w-5 h-5"></i> Submit & Register
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Duplicate Detection Modal -->
    <div x-show="showDuplicateModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-transition>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]"
            @click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="background-color: #4f46e5;">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="copy" class="w-5 h-5 text-indigo-100"></i> Potential Duplicates
                </h3>
                <button type="button" @click="showDuplicateModal = false"
                    class="text-white hover:text-gray-200 transition-colors p-1.5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar bg-slate-50/50" style="max-height: calc(90vh - 70px);">
                <p class="text-sm text-slate-600 mb-5">The following petitions share similarities (Name, Phone, or PEN) with
                    your current entry. You can choose to link this new entry to one of them.</p>

                <div class="space-y-5">
                    <template x-for="dup in duplicates" :key="dup.petition_id">
                        <div
                            class="bg-white border border-slate-200 hover:border-indigo-300 rounded-2xl p-5 flex flex-col sm:flex-row gap-5 items-start sm:items-center justify-between shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex-1 w-full">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 mb-4">
                                    <!-- Receipt No -->
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Petition
                                            No:</span>
                                        <span
                                            class="inline-flex text-sm font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100"
                                            x-text="dup.receipt_no"></span>
                                    </div>
                                    <!-- Date -->
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Date:</span>
                                        <span
                                            class="text-sm font-semibold text-slate-700 flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                                            <i data-lucide="calendar" class="w-4 h-4 text-indigo-400"></i> <span
                                                x-text="dup.date"></span>
                                        </span>
                                    </div>
                                    <!-- Complainant -->
                                    <div class="flex items-start gap-3 sm:col-span-2">
                                        <span
                                            class="text-xs font-bold text-indigo-900 uppercase tracking-wider w-24 shrink-0 mt-0.5">Complainant:</span>
                                        <span class="text-sm font-medium text-slate-800" x-text="dup.complainant"></span>
                                    </div>
                                    <!-- Suspect -->
                                    <div class="flex items-start gap-3 sm:col-span-2">
                                        <span
                                            class="text-xs font-bold text-rose-900 uppercase tracking-wider w-24 shrink-0 mt-0.5">Suspect:</span>
                                        <span class="text-sm font-medium text-slate-800" x-text="dup.accused"></span>
                                    </div>
                                </div>

                                <div
                                    class="mt-2 bg-gradient-to-r from-slate-50 to-white p-3 rounded-xl border border-slate-100 shadow-inner">
                                    <p class="text-sm text-slate-600 italic line-clamp-2" x-text="dup.description"></p>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-md text-xs">
                                        <span class="text-slate-500 font-semibold uppercase tracking-wider">Status:</span>
                                        <span class="font-bold text-slate-700 ml-1" x-text="dup.status"></span>
                                    </span>
                                    <template x-if="dup.decision">
                                        <span
                                            class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-700 rounded-md text-xs">
                                            <span class="font-semibold uppercase tracking-wider">Decision:</span>
                                            <span class="font-bold ml-1" x-text="dup.decision"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <button type="button" @click="linkPetition(dup.petition_id, dup.receipt_no)"
                                style="background-color: #10b981; color: white;"
                                class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold rounded-xl shadow-md shrink-0 flex justify-center items-center gap-2 hover:opacity-90 transition-opacity border border-transparent">
                                <i data-lucide="link" class="w-4 h-4"></i> Link Petition
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    </form>
    </div>

    <script>
        function petitionForm() {
            return {
                step: {{ old('step', 1) }},
                districtNames: @json($districts->pluck('district_name', 'district_id')),
                formErrors: {},

                showPreview: false,
                petitionDetails: {
                    receipt_no: '{{ old('receipt_no') }}',
                    date: '{{ old('date_of_petition_received') }}',
                    nature: '{{ old('nature_of_petition') }}',
                    mode: '{{ old('mode_of_petition_received') }}',
                    mode_others: '{{ old('mode_others') }}',
                    description: `{!! addslashes(old('description')) !!}`,
                    proposed_action: `{!! addslashes(old('proposed_action')) !!}`
                },
                complainants: @json(old('complainants')) || [{ id: Date.now(), name: '', phone: '', email: '', addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }] }],
                accused: @json(old('accused')) || [{ id: Date.now() + 1, entity_type: 'Person', designation_id: '', department_id: '', name: '', phone: '', pen_number: '', addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }] }],

                // Duplicate Checking State
                duplicates: [],
                showDuplicateModal: false,
                linkedPetitionId: '{{ old('linked_petition_id') }}' || null,
                isCheckingDuplicates: false,
                debounceTimer: null,

                init() {
                    this.$watch('accused', () => {
                        if (this.step === 3) this.debouncedCheckDuplicates();
                    });
                    this.$watch('complainants', () => {
                        if (this.step === 3) this.debouncedCheckDuplicates();
                    });
                    this.$watch('step', (newStep) => {
                        if (newStep === 3) this.debouncedCheckDuplicates();
                    });
                },

                debouncedCheckDuplicates() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.fetchDuplicates();
                    }, 1000);
                },

                fetchDuplicates() {
                    this.isCheckingDuplicates = true;
                    fetch('{{ route('petitions.checkDuplicates') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            complainants: this.complainants,
                            accused: this.accused
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.duplicates = data;
                            this.isCheckingDuplicates = false;
                        })
                        .catch(err => {
                            console.error('Error checking duplicates', err);
                            this.isCheckingDuplicates = false;
                        });
                },

                linkPetition(id, number) {
                    this.linkedPetitionId = id;
                    this.showDuplicateModal = false;

                    Swal.fire({
                        title: 'Petition Linked!',
                        text: 'Petition is linked to ' + number,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10b981',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let form = document.getElementById('petitionFormElement');
                            let redirectInput = document.createElement('input');
                            redirectInput.type = 'hidden';
                            redirectInput.name = 'duplicate_link_number';
                            redirectInput.value = number;
                            form.appendChild(redirectInput);
                            form.submit();
                        }
                    });
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
                    let isValid = true;
                    const formElements = document.getElementById('petitionFormElement').querySelectorAll('input, select, textarea');
                    formElements.forEach(el => {
                        const stepContainer = el.closest(`[x-show="step === ${step}"]`);
                        if (stepContainer && el.name && !el.disabled) {
                            let customError = '';
                            if (el.value) {
                                if ((el.name.includes('[pincode]') || el.name === 'comp_pincode' || el.name === 'acc_pincode') && !/^\d{6}$/.test(el.value)) {
                                    customError = 'Pincode must be 6 digits.';
                                } else if ((el.name.includes('[phone]') || el.name === 'comp_phone' || el.name === 'acc_phone') && !/^\d{10}$/.test(el.value)) {
                                    customError = 'Phone number must be 10 digits.';
                                } else if ((el.name.includes('[pen_number]') || el.name === 'acc_pen_number') && !/^\d{6,7}$/.test(el.value)) {
                                    customError = 'PEN must be 6 or 7 digits.';
                                }
                            }

                            // For Step 2 and Step 3, skip required validation but still check format
                            if (step === 2 || step === 3) {
                                // Only validate format, not required status
                                if (customError) {
                                    isValid = false;
                                    this.formErrors[el.name] = customError;
                                } else if (el.value && !el.checkValidity()) {
                                    isValid = false;
                                    if (el.validity.patternMismatch) {
                                        this.formErrors[el.name] = 'Invalid format.';
                                    } else if (el.validity.typeMismatch) {
                                        this.formErrors[el.name] = 'Please enter a valid value.';
                                    } else {
                                        this.formErrors[el.name] = el.validationMessage;
                                    }
                                } else {
                                    this.formErrors[el.name] = '';
                                }
                            } else {
                                // For other steps, validate normally
                                if (customError) {
                                    isValid = false;
                                    this.formErrors[el.name] = customError;
                                } else if (!el.checkValidity()) {
                                    isValid = false;
                                    if (el.validity.valueMissing) {
                                        this.formErrors[el.name] = 'This field is required.';
                                    } else if (el.validity.rangeOverflow) {
                                        this.formErrors[el.name] = 'Date cannot be in the future.';
                                    } else if (el.validity.patternMismatch) {
                                        this.formErrors[el.name] = 'Invalid format.';
                                    } else if (el.validity.typeMismatch) {
                                        this.formErrors[el.name] = 'Please enter a valid value.';
                                    } else {
                                        this.formErrors[el.name] = el.validationMessage;
                                    }
                                } else {
                                    this.formErrors[el.name] = '';
                                }
                            }
                        }
                    });

                    // Check for duplicates in Step 2 (Complainants) - only if there are complainants
                    if (step === 2 && isValid && this.complainants.some(c => c.name.trim() || c.phone.trim())) {
                        const duplicates = this.checkDuplicateComplainants();
                        if (duplicates.length > 0) {
                            isValid = false;
                            duplicates.forEach(msg => alert(msg));
                        }
                    }

                    // Check for duplicates in Step 3 (Accused) - only if there are accused
                    if (step === 3 && isValid && this.accused.some(a => a.name.trim() || a.phone.trim())) {
                        const duplicates = this.checkDuplicateAccused();
                        if (duplicates.length > 0) {
                            isValid = false;
                            duplicates.forEach(msg => alert(msg));
                        }
                    }

                    return isValid;
                },
                checkDuplicateComplainants() {
                    const errors = [];
                    const names = new Set();
                    const phones = new Set();
                    this.complainants.forEach((comp, index) => {
                        const name = comp.name.trim().toLowerCase();
                        const phone = comp.phone.trim();

                        // Check for duplicate names
                        if (name && names.has(name)) {
                            errors.push(`Complainant #${index + 1}: Name "${comp.name}" is already used by another complainant.`);
                        }
                        if (name) names.add(name);

                        // Check for duplicate phone numbers
                        if (phone && phones.has(phone)) {
                            errors.push(`Complainant #${index + 1}: Phone number "${phone}" is already used by another complainant.`);
                        }
                        if (phone) phones.add(phone);
                    });

                    return errors;
                },
                checkDuplicateAccused() {
                    const errors = [];
                    const names = new Set();
                    const phones = new Set();
                    this.accused.forEach((acc, index) => {
                        const name = acc.name.trim().toLowerCase();
                        const phone = acc.phone.trim();

                        // Check for duplicate names
                        if (name && names.has(name)) {
                            errors.push(`Suspect #${index + 1}: Name "${acc.name}" is already used by another suspect.`);
                        }
                        if (name) names.add(name);

                        // Check for duplicate phone numbers
                        if (phone && phones.has(phone)) {
                            errors.push(`Suspect #${index + 1}: Phone number "${phone}" is already used by another suspect.`);
                        }
                        if (phone) phones.add(phone);
                    });

                    return errors;
                },
                validateField(event) {
                    const el = event.target;
                    if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) return;
                    if (!el.name) return;

                    // Get the actual field name (handles array names like complainants[0][phone])
                    const fieldName = el.name;
                    const fieldValue = el.value.trim();

                    if (fieldName === 'receipt_no') {
                        this.formErrors[fieldName] = '';
                        return;
                    }

                    // Check if this field is in Step 2 or Step 3
                    const stepContainer = el.closest('[x-show*="step === 2"], [x-show*="step === 3"]');
                    if (stepContainer) {
                        // For Step 2 and Step 3, only validate format if field has a value
                        if (fieldValue) {
                            if (fieldName.includes('[phone]') || fieldName === 'comp_phone' || fieldName === 'acc_phone') {
                                if (fieldValue && !/^\d{10}$/.test(fieldValue)) {
                                    this.formErrors[fieldName] = 'Phone number must be 10 digits.';
                                } else {
                                    this.formErrors[fieldName] = '';
                                }
                            } else if (fieldName.includes('[email]') || fieldName === 'comp_email' || fieldName === 'acc_email') {
                                if (fieldValue && !/^\S+@\S+\.\S+$/.test(fieldValue)) {
                                    this.formErrors[fieldName] = 'Please enter a valid email address.';
                                } else {
                                    this.formErrors[fieldName] = '';
                                }
                            } else if (fieldName.includes('[pincode]') || fieldName === 'comp_pincode' || fieldName === 'acc_pincode') {
                                if (!/^\d{6}$/.test(fieldValue)) {
                                    this.formErrors[fieldName] = 'Pincode must be 6 digits.';
                                } else {
                                    this.formErrors[fieldName] = '';
                                }
                            } else if (fieldName.includes('[pen_number]') || fieldName === 'acc_pen_number') {
                                if (!/^\d{6,7}$/.test(fieldValue)) {
                                    this.formErrors[fieldName] = 'PEN must be 6 or 7 digits.';
                                } else {
                                    this.formErrors[fieldName] = '';
                                }
                            } else {
                                // For other fields in Step 2/3, clear errors if they have valid input
                                this.formErrors[fieldName] = '';
                            }
                        } else {
                            // If field is empty in Step 2/3, clear any errors
                            this.formErrors[fieldName] = '';
                        }
                        return;
                    }

                    // For Step 1 and other steps, normal validation
                    // Custom validation rules by field
                    if (fieldName.includes('[phone]') || fieldName === 'comp_phone') {
                        if (el.hasAttribute('required') && !fieldValue) {
                            this.formErrors[fieldName] = 'Phone number is required.';
                        } else if (fieldValue && !/^\d{10}$/.test(fieldValue)) {
                            this.formErrors[fieldName] = 'Phone number must be 10 digits.';
                        } else {
                            this.formErrors[fieldName] = '';
                        }
                    } else if (fieldName.includes('[email]') || fieldName === 'comp_email') {
                        if (fieldValue && !/^\S+@\S+\.\S+$/.test(fieldValue)) {
                            this.formErrors[fieldName] = 'Please enter a valid email address.';
                        } else {
                            this.formErrors[fieldName] = '';
                        }
                    } else if (fieldName.includes('[pincode]') || fieldName === 'comp_pincode') {
                        if (fieldValue && !/^\d{6}$/.test(fieldValue)) {
                            this.formErrors[fieldName] = 'Pincode must be 6 digits.';
                        } else {
                            this.formErrors[fieldName] = '';
                        }
                    } else if (fieldName.includes('[address]') || fieldName === 'address') {
                        if (el.hasAttribute('required') && !fieldValue) {
                            this.formErrors[fieldName] = 'Address is required.';
                        } else {
                            this.formErrors[fieldName] = '';
                        }
                    } else if (fieldName === 'date_of_petition_received') {
                        if (!fieldValue) {
                            this.formErrors[fieldName] = 'This field is required.';
                        } else {
                            const [year, month, day] = fieldValue.split('-').map(Number);
                            const selected = new Date(year, month - 1, day);
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            if (selected > today) {
                                this.formErrors[fieldName] = 'Date cannot be in the future.';
                            } else {
                                this.formErrors[fieldName] = '';
                            }
                        }
                    } else {
                        // Default HTML5 validation
                        if (el.checkValidity()) {
                            this.formErrors[fieldName] = '';
                        } else {
                            if (el.validity.valueMissing) {
                                this.formErrors[fieldName] = 'This field is required.';
                            } else if (el.validity.rangeOverflow) {
                                this.formErrors[fieldName] = 'Date cannot be in the future.';
                            } else if (el.validity.patternMismatch) {
                                this.formErrors[fieldName] = 'Invalid format.';
                            } else if (el.validity.typeMismatch) {
                                this.formErrors[fieldName] = 'Please enter a valid value.';
                            } else {
                                this.formErrors[fieldName] = el.validationMessage;
                            }
                        }
                    }
                },
                async checkPetitionNumberUniqueness(petitionNo) {
                    if (!petitionNo.trim()) return;

                    try {
                        const response = await fetch(`/petitions/check-petition-no`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ receipt_no: petitionNo })
                        });

                        const data = await response.json();

                        if (data.exists) {
                            this.formErrors['receipt_no'] = 'Receipt number already exists';
                        } else {
                            this.formErrors['receipt_no'] = '';
                        }
                    } catch (error) {
                        console.error('Error checking petition number uniqueness:', error);
                        // Don't show error on network failure, let server validation handle it
                    }
                },
                clearError(event) {
                    const el = event.target;
                    if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) return;
                    if (!el.name) return;

                    const fieldName = el.name;
                    const fieldValue = el.value.trim();

                    // Special handling for petition number - don't clear server-side validation errors
                    if (fieldName === 'receipt_no') {
                        return; // Don't clear errors for petition number on input
                    }

                    // For Step 2 and Step 3 fields, only clear format errors, not required errors
                    const stepContainer = el.closest('[x-show*="step === 2"], [x-show*="step === 3"]');
                    if (stepContainer) {
                        // Only clear format validation errors for Step 2 and 3
                        if (fieldName.includes('[phone]') || fieldName === 'comp_phone') {
                            if (fieldValue && /^\d{10}$/.test(fieldValue)) {
                                this.formErrors[fieldName] = '';
                            }

                        } else if (fieldName.includes('[pincode]') || fieldName === 'comp_pincode' || fieldName === 'acc_pincode') {
                            if (!fieldValue || /^\d{6}$/.test(fieldValue)) {
                                this.formErrors[fieldName] = '';
                            } else {
                                this.formErrors[fieldName] = 'Pincode must be 6 digits.';
                            }
                        } else if (fieldName.includes('[pen_number]') || fieldName === 'acc_pen_number') {
                            if (!fieldValue || /^\d{6,7}$/.test(fieldValue)) {
                                this.formErrors[fieldName] = '';
                            }
                        } else if (fieldName.includes('[name]') || fieldName === 'comp_name') {
                            if (fieldValue) {
                                this.formErrors[fieldName] = '';
                            }
                        } else {
                            // For other fields in Step 2/3, clear if they have a value
                            if (fieldValue) {
                                this.formErrors[fieldName] = '';
                            }
                        }
                    } else {
                        // For Step 1 and other steps, normal clearing logic
                        if (fieldName.includes('[phone]') || fieldName === 'comp_phone') {
                            if (fieldValue && /^\d{10}$/.test(fieldValue)) {
                                this.formErrors[fieldName] = '';
                            } else if (!fieldValue && !el.hasAttribute('required')) {
                                this.formErrors[fieldName] = '';
                            }

                        } else if (fieldName.includes('[pincode]') || fieldName === 'comp_pincode' || fieldName === 'acc_pincode') {
                            if (!fieldValue || /^\d{6}$/.test(fieldValue)) {
                                this.formErrors[fieldName] = '';
                            } else {
                                this.formErrors[fieldName] = 'Pincode must be 6 digits.';
                            }
                        } else if (fieldName.includes('[name]') || fieldName === 'comp_name') {
                            if (fieldValue) {
                                this.formErrors[fieldName] = '';
                            }
                        } else {
                            // For other fields, clear on valid input
                            if (fieldValue || !el.hasAttribute('required')) {
                                this.formErrors[fieldName] = '';
                            }
                        }
                    }
                },
                setStep(target) {
                    // Allow navigating back and forth if already visited or current
                    if (target < this.step || this.validateStep(this.step)) {
                        if (target >= 1 && target <= 4) {
                            this.step = target;
                            this.scrollToTop();
                        }
                    }
                },
                togglePreview() {
                    if (this.validateStep(3)) {
                        this.showPreview = !this.showPreview;
                        this.scrollToTop();
                    }
                },
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.refreshIcons();
                },
                addComplainant() {
                    const last = this.complainants[this.complainants.length - 1];
                    if (last.name.trim() !== '') {
                        this.complainants.push({
                            id: Date.now(), name: '', phone: '', email: '',
                            addresses: [{ address_type: 'Permanent', address: '', district_id: '', pincode: '' }]
                        });
                        this.refreshIcons();
                    }
                },
                removeComplainant(index) {
                    if (this.complainants.length > 1) this.complainants.splice(index, 1);
                },
                addComplainantAddress(compIndex) {
                    if (this.complainants[compIndex].addresses.length < 3) {
                        // find an available type
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
                    if (last.name.trim() !== '') {
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
                        // find an available type
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