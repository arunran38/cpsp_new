<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-visible text-slate-900"
    x-data="adminFormValidation()">
    <form method="POST" action="{{ route('admin.departments.store') }}" class="p-8"
        @submit.prevent="submitForm($event)">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <x-input label="Department Name" name="department_name" placeholder="Name of Department" required />
            </div>

            <div class="flex flex-col justify-end md:col-span-1">
                <label class="block text-sm font-bold text-transparent mb-1.5 select-none" aria-hidden="true">&nbsp;</label>
                <x-button icon="save" class="w-full">
                    Save Department
                </x-button>
            </div>
        </div>
    </form>
</div>
