<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900"
    x-data="adminFormValidation({ departmentId: {{ isset($department) ? $department->id : 'null' }} })">
    <form method="POST" action="{{ route('admin.departments.store') }}" class="p-6 sm:p-8"
        @submit.prevent="submitForm($event)">
        @csrf
        <div class="space-y-2">
            <label for="department_name" class="block text-sm font-bold text-slate-800">
                Department Name <span class="required-mark text-red-600 font-bold ml-1" style="color: #dc2626 !important;">*</span>
            </label>
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="relative flex-1 w-full" x-data="{ count: 0 }" x-init="count = $refs.deptInput ? $refs.deptInput.value.length : 0">
                    <div class="relative flex items-center group">
                        <div class="absolute z-10 left-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
                            <i data-lucide="building" class="w-4 h-4"></i>
                        </div>
                        <input 
                            type="text"
                            name="department_name"
                            id="department_name"
                            x-ref="deptInput"
                            value="{{ old('department_name') }}"
                            placeholder="e.g. Health & Family Welfare Department"
                            required
                            autocomplete="off"
                            @input="count = $event.target.value.length; clearError($event); checkDepartmentLive($event)"
                            @blur="validateField($event)"
                            class="block w-full pl-11 pr-10 py-2.5 h-[44px] text-sm font-medium bg-white border rounded-xl text-slate-900 transition-all duration-200 placeholder:text-slate-400 outline-none group-hover:border-slate-300"
                            :class="formErrors['department_name'] ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 focus:ring-4 focus:ring-primary/10 focus:border-primary'"
                        />
                        <button type="button" 
                                x-show="count > 0" 
                                @click="$refs.deptInput.value = ''; count = 0; clearError({ target: $refs.deptInput }); $refs.deptInput.focus()" 
                                class="absolute right-3 p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all"
                                style="display: none;">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <template x-if="formErrors['department_name']">
                        <p class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span x-text="formErrors['department_name']"></span>
                        </p>
                    </template>
                    @error('department_name')
                        <div x-show="!formErrors['department_name']">
                            <p class="text-xs font-semibold text-rose-600 mt-1.5 flex items-center gap-1.5">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p>
                        </div>
                    @enderror
                </div>

                <button type="submit" 
                        :disabled="Boolean(formErrors['department_name'])"
                        :class="Boolean(formErrors['department_name']) ? 'opacity-60 cursor-not-allowed bg-slate-300 text-slate-600' : 'bg-primary hover:bg-primary-hover text-black shadow-lg shadow-primary/20'"
                        class="w-full sm:w-auto sm:min-w-[190px] shrink-0 inline-flex items-center justify-center gap-2 px-6 py-2.5 h-[44px] text-sm font-bold rounded-xl transition-all duration-200">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Department</span>
                </button>
            </div>
        </div>
    </form>
</div>
