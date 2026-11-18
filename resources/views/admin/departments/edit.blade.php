@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-white">Update Department</h1>
        <p class="text-sky-400 font-medium text-xs sm:text-sm">Modify department details with automatic duplicate validation.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900"
         x-data="adminFormValidation({ departmentId: {{ $department->id }} })">
        <form method="POST" action="{{ route('admin.departments.update', $department->id) }}" class="p-8 space-y-6"
              @submit.prevent="submitForm($event)">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-2">
                    <label for="department_name" class="text-sm font-bold text-slate-700">
                        Department Name <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <div class="relative flex items-center group">
                        <div class="absolute z-10 left-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
                            <i data-lucide="building" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="department_name" 
                               id="department_name" 
                               value="{{ old('department_name', $department->department_name) }}" 
                               required
                               autocomplete="off"
                               @input="clearError($event); checkDepartmentLive($event)"
                               @blur="validateField($event)"
                               class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-4 outline-none transition-all font-medium text-slate-900"
                               :class="formErrors['department_name'] ? 'border-rose-500 ring-rose-500/10 focus:border-rose-500' : 'border-slate-200 focus:border-primary focus:ring-primary/10 focus:bg-white'"
                               placeholder="e.g. IT Department">
                    </div>
                    <template x-if="formErrors['department_name']">
                        <p class="text-xs font-semibold text-rose-600 mt-1 flex items-center gap-1.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span x-text="formErrors['department_name']"></span>
                        </p>
                    </template>
                    @error('department_name')
                        <div x-show="!formErrors['department_name']">
                            <p class="text-xs font-semibold text-rose-600 mt-1 flex items-center gap-1.5">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.departments.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="Boolean(formErrors['department_name'])"
                        :class="Boolean(formErrors['department_name']) ? 'opacity-60 cursor-not-allowed bg-slate-300 text-slate-600' : 'bg-primary hover:bg-primary-hover text-black shadow-lg shadow-primary/20'"
                        class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Update Department</span>
                </button>
            </div>
        </form>
    </div>
</div>
@section('scripts')
    @include('components.admin-form-validation-script')
@endsection
@endsection
