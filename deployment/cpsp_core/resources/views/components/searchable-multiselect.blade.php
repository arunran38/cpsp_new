@props(['name', 'label' => null, 'options' => [], 'selected' => [], 'placeholder' => 'Select / Search'])

<div x-data="{ 
        open: false, 
        search: '', 
        selected: @js(is_array($selected) ? array_map('strval', $selected) : []),
        options: @js($options),
        get filteredOptions() {
            if (this.search === '') return this.options;
            return Object.fromEntries(
                Object.entries(this.options).filter(([val, label]) => 
                    label.toLowerCase().includes(this.search.toLowerCase())
                )
            );
        },
        toggle(val) {
            val = val.toString();
            const index = this.selected.indexOf(val);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(val);
                this.search = ''; 
            }
        },
        remove(val) {
            val = val.toString();
            this.selected = this.selected.filter(item => item !== val);
        },
        isSelected(val) {
            return this.selected.includes(val.toString());
        }
     }"
     class="relative w-full group/wrapper"
     @click.outside="open = false; search = ''">
    
    <!-- Label -->
    @if($label)
        <label class="block text-sm font-bold text-slate-700 mb-2 transition-colors duration-300"
               :class="open ? 'text-primary' : 'text-slate-700'">
            {{ $label }}
        </label>
    @endif

    <!-- Trigger Box -->
    <div @click="open = true; if ($root?.formErrors?.unit_ids) delete $root.formErrors.unit_ids" 
         class="flex flex-wrap items-center gap-2 w-full px-4 py-3 min-h-[58px] bg-slate-50/50 border rounded-2xl cursor-text transition-all duration-300 relative z-0 shadow-sm"
         :class="$root?.formErrors?.unit_ids ? 'border-rose-500 ring-4 ring-rose-500/10 bg-rose-50/5' : (open ? 'border-primary bg-white ring-4 ring-primary/10' : 'border-slate-200 hover:border-slate-300 group-hover/wrapper:bg-white')">
        
        <!-- Selected Items Tags -->
        <template x-for="val in selected" :key="val">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white text-slate-700 text-[13px] font-bold rounded-xl border border-slate-200 shadow-sm group/tag transition-all hover:border-primary/30 hover:shadow-md animate-in fade-in zoom-in duration-200">
                <span x-text="options[val]"></span>
                <button type="button" @click.stop="remove(val)" class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 group-hover/tag:bg-rose-50 group-hover/tag:text-rose-500 transition-all focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </span>
        </template>

        <!-- Search Input -->
        <input type="text" 
               x-model="search"
               class="flex-1 min-w-[120px] border-none p-0 focus:ring-0 text-slate-700 bg-transparent outline-none shadow-none text-sm placeholder:text-slate-400 font-medium"
               :placeholder="selected.length === 0 ? '{{ $placeholder }}' : ''"
               @focus="open = true">

        <!-- Hidden inputs for submission -->
        <template x-for="val in selected" :key="'input-' + val">
            <input type="hidden" name="{{ $name }}[]" :value="val">
        </template>

        <template x-if="$root?.formErrors?.unit_ids">
            <p class="text-xs text-rose-500 mt-1" x-text="$root.formErrors.unit_ids"></p>
        </template>

        <!-- Chevron / Indicator -->
        <div class="ml-auto w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 group-hover/wrapper:bg-slate-200/50 transition-colors" @click.stop="open = !open">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-primary' : ''">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>

    <!-- Dropdown List -->
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-[0.95]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-[0.95]"
         class="absolute z-[100] w-full mt-3 bg-white border border-slate-100 rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] overflow-hidden flex flex-col backdrop-blur-xl">
        
        <style>
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar {
                width: 6px;
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-track {
                background: transparent; 
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-thumb {
                background: #e2e8f0; 
                border-radius: 10px;
                border: 2px solid white;
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1; 
            }
        </style>

        <div class="p-2 space-y-1">
            <div class="px-3 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                <span>Available Options</span>
                <span x-text="Object.keys(filteredOptions).length + ' results'" class="bg-slate-100 px-2 py-0.5 rounded-lg"></span>
            </div>
            
            <div class="flex-1 max-h-64 overflow-y-auto custom-scrollbar-{{ Str::slug($name) }} space-y-0.5">
                <template x-for="(label, val) in filteredOptions" :key="val">
                    <div @click.stop="toggle(val)" 
                         class="group/item px-4 py-3 text-sm cursor-pointer rounded-xl transition-all duration-200 flex items-center justify-between"
                         :class="isSelected(val) 
                            ? 'bg-primary/5 text-primary font-bold border border-primary/20 shadow-sm' 
                            : 'text-slate-600 hover:bg-slate-50 border border-transparent hover:border-slate-100'">
                        
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full transition-all"
                                 :class="isSelected(val) ? 'bg-primary scale-110 shadow-sm shadow-primary/50' : 'bg-slate-200 group-hover/item:bg-slate-300'"></div>
                            <span x-text="label" class="transition-transform duration-200" :class="isSelected(val) ? '' : 'group-hover:translate-x-1'"></span>
                        </div>
                        
                        <div x-show="isSelected(val)" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-50"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="flex items-center justify-center w-6 h-6 rounded-lg bg-primary text-white shadow-lg shadow-primary/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                    </div>
                </template>
                
                <div x-show="Object.keys(filteredOptions).length === 0" class="px-4 py-12 text-sm text-slate-400 text-center flex flex-col items-center justify-center gap-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold text-slate-900">No matches found</p>
                        <p class="text-[13px]">Try adjusting your search query</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

