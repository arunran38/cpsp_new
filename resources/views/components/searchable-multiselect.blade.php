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
     class="relative w-full mt-3 font-sans group/wrapper"
     @click.outside="open = false; search = ''">
    
    <!-- Floating Label -->
    @if($label)
        <label class="absolute -top-2.5 left-3 px-1.5 text-[13px] font-semibold bg-white z-10 transition-colors duration-300"
               :class="open ? 'text-[#1c448a]' : 'text-slate-500 group-hover/wrapper:text-slate-700'">
            {{ $label }}
        </label>
    @endif

    <!-- Trigger Box -->
    <div @click="open = true" 
         class="flex flex-wrap items-center gap-2 w-full px-4 py-2.5 min-h-[52px] bg-white border rounded-xl cursor-text transition-all duration-300 relative z-0 shadow-sm"
         :class="open ? 'border-[#1c448a] ring-4 ring-[#1c448a]/10' : 'border-slate-200 hover:border-slate-300'">
        
        <!-- Selected Items Tags -->
        <template x-for="val in selected" :key="val">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200/60 shadow-[0_1px_2px_rgba(0,0,0,0.02)] group/tag transition-all hover:bg-white hover:border-slate-300 hover:shadow-sm">
                <span x-text="options[val]"></span>
                <button type="button" @click.stop="remove(val)" class="text-slate-400 group-hover/tag:text-rose-500 transition-colors focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </span>
        </template>

        <!-- Search Input -->
        <input type="text" 
               x-model="search"
               class="flex-1 min-w-[120px] border-none p-0 focus:ring-0 text-slate-700 bg-transparent outline-none shadow-none text-[15px] placeholder:text-slate-400 placeholder:font-normal"
               :placeholder="selected.length === 0 ? '{{ $placeholder }}' : ''"
               @focus="open = true">

        <!-- Hidden inputs for submission -->
        <template x-for="val in selected" :key="'input-' + val">
            <input type="hidden" name="{{ $name }}[]" :value="val">
        </template>

        <!-- Chevron -->
        <div class="ml-auto flex-shrink-0 cursor-pointer text-slate-400 group-hover/wrapper:text-slate-600 transition-colors" @click.stop="open = !open">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" class="transition-transform duration-300" :class="open ? 'rotate-180 text-[#1c448a]' : ''">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>

    <!-- Dropdown List -->
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
         class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] overflow-hidden flex flex-col backdrop-blur-sm">
        
        <style>
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar {
                width: 5px;
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-track {
                background: transparent; 
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-thumb {
                background: #cbd5e1; 
                border-radius: 10px;
            }
            .custom-scrollbar-{{ Str::slug($name) }}::-webkit-scrollbar-thumb:hover {
                background: #94a3b8; 
            }
        </style>

        <div class="flex-1 max-h-64 overflow-y-auto custom-scrollbar-{{ Str::slug($name) }} p-2 space-y-1">
            <template x-for="(label, val) in filteredOptions" :key="val">
                <div @click.stop="toggle(val)" 
                     class="px-4 py-3 text-[15px] cursor-pointer rounded-xl transition-all duration-200 flex items-center justify-between group/item"
                     :class="isSelected(val) ? 'bg-[#f0f7ff] text-[#1c448a] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    
                    <span x-text="label" class="transition-transform duration-200" :class="!isSelected(val) ? 'group-hover/item:translate-x-1' : ''"></span>
                    
                    <div x-show="isSelected(val)" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-50"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="flex items-center justify-center w-6 h-6 rounded-full bg-white shadow-sm border border-[#1c448a]/20 text-[#1c448a]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                </div>
            </template>
            
            <div x-show="Object.keys(filteredOptions).length === 0" class="px-4 py-8 text-sm text-slate-400 text-center flex flex-col items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <span>No matching results found</span>
            </div>
        </div>
    </div>
</div>
