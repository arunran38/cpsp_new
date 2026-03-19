@extends('layouts.user')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white px-8 py-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                <i data-lucide="edit-3" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Petition: {{ $petition->petition_no }}</h1>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Modify the primary attributes of this record</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('petitions.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm flex items-center gap-2 transition-all">
                Cancel
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl">
            <div class="font-bold flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i> Please correct the following errors:</div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('petitions.update', $petition->petition_id) }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-10">
        @csrf
        @method('PUT')

        <div class="mb-8 border-b border-slate-100 pb-5">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i> Primary Information
            </h2>
            <p class="text-sm text-slate-500 mt-1">Note: Modifying attached complainants or accused requires dedicated verification flows.</p>
        </div>
        
        <div class="space-y-8" x-data="{ mode: '{{ $petition->mode_of_petition_received }}' }">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input label="Petition No *" name="petition_no" value="{{ old('petition_no', $petition->petition_no) }}" required />
                </div>
                <div>
                    <x-input type="date" label="Date of Receipt *" name="date_of_petition_received" value="{{ old('date_of_petition_received', $petition->date_of_petition_received) }}" required />
                </div>
                <div>
                    <x-select label="Nature of Petition *" name="nature_of_petition" :options="['' => 'Select Category', 'Bribery' => 'Bribery', 'Misuse of authority' => 'Misuse of authority', 'Fraud / financial irregularities' => 'Fraud / financial irregularities', 'Serious negligence' => 'Serious negligence', 'others' => 'Others']" :value="old('nature_of_petition', $petition->nature_of_petition)" required />
                </div>
                <div>
                    <select name="mode_of_petition_received" x-model="mode" required class="block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition-all">
                        <option value="">Select Origin</option>
                        <option value="Email">Email</option>
                        <option value="Whatsapp">Whatsapp</option>
                        <option value="Tollfree">Tollfree</option>
                        <option value="Direct">Direct</option>
                        <option value="Unit">Unit</option>
                        <option value="Tapal">Tapal</option>
                        <option value="iaps">iAPS</option>
                        <option value="others">Others</option>
                    </select>
                </div>
            </div>

            <div x-show="mode === 'others'" x-transition class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                <x-input label="Specify Mode of Petition *" name="mode_others" value="{{ old('mode_others', $petition->mode_of_petition_received_others) }}" />
            </div>

            <div>
                <x-textarea label="Detailed Description *" name="description" rows="5" required>{{ old('description', $petition->description) }}</x-textarea>
            </div>

            <div>
                <x-textarea label="Proposed Action / Initial Assessment" name="proposed_action" rows="3">{{ old('proposed_action', $petition->proposed_action) }}</x-textarea>
            </div>
            
            <div class="pt-6 border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-8 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Record Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
