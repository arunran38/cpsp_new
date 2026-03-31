@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'placeholder' => 'Select an option...'])

<div class="w-full group/wrapper" 
     x-data="{ 
        open: false, 
        search: '', 
        selected: @js($selected),
        selectedLabel: '',
        options: @js($options),
        get filteredOptions() {
            if (this.search === '') return this.options;
            return Object.fromEntries(
                Object.entries(this.options).filter(([val, label]) => 
                    label.toLowerCase().includes(this.search.toLowerCase())
                )
            );
        },
        init() {
            if (this.selected && this.options[this.selected]) {
                this.selectedLabel = this.options[this.selected];
            }
        },
        select(val, label) {
            this.selected = val;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
        }
     }">
    
    @if($label)
        <label class="block text-sm font-bold text-slate-700 mb-2 transition-colors duration-300"
               :class="open ? 'text-primary' : 'text-slate-700'">
            {{ $label }}
        </label>
    @endif

    <div class="relative group">
        <div @click="open = !open" 
             class="flex items-center justify-between w-full px-4 py-3 text-sm bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 transition-all duration-300 focus:ring-4 focus:ring-primary/10 focus:border-primary group-hover:border-slate-300 cursor-pointer outline-none shadow-sm"
             :class="open ? 'ring-4 ring-primary/10 border-primary bg-white' : 'group-hover/wrapper:bg-white'">
            
            <div class="flex items-center gap-3">
                <template x-if="selectedLabel">
                    <div class="w-2 h-2 rounded-full bg-primary shadow-sm shadow-primary/50"></div>
                </template>
                <span x-text="selectedLabel || '{{ $placeholder }}'" 
                      class="font-medium transition-colors"
                      :class="!selectedLabel ? 'text-slate-400' : 'text-slate-700'"></span>
            </div>

            <input type="hidden" name="{{ $name }}" :value="selected">
            
            <div class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 group-hover:bg-slate-200/50 transition-colors">
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-primary' : ''"></i>
            </div>
        </div>

        <div x-show="open" 
             @click.outside="open = false"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 scale-[0.95]"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-[0.95]"
             class="absolute z-[100] w-full mt-3 bg-white border border-slate-100 rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] overflow-hidden flex flex-col backdrop-blur-xl">
            
            <div class="p-3 border-b border-slate-100 bg-slate-50/50">
                <div class="relative flex items-center group/search">
                    <i data-lucide="search" class="absolute left-3.5 w-4 h-4 text-slate-400 group-focus-within/search:text-primary transition-colors"></i>
                    <input type="text" 
                           x-model="search" 
                           class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all placeholder:text-slate-400 font-medium" 
                           placeholder="Search options..."
                           @click.stop>
                </div>
            </div>

            <div class="p-2 space-y-0.5 max-h-[280px] overflow-y-auto custom-scrollbar">
                <template x-for="(label, val) in filteredOptions" :key="val">
                    <div @click="select(val, label)" 
                         class="group/item px-4 py-3 text-sm cursor-pointer rounded-xl transition-all duration-200 flex items-center justify-between"
                         :class="selected === val 
                            ? 'bg-primary/5 text-primary font-bold border border-primary/20 shadow-sm' 
                            : 'text-slate-600 hover:bg-slate-50 border border-transparent hover:border-slate-100'">
                        
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-1.5 rounded-full transition-all"
                                 :class="selected === val ? 'bg-primary scale-110 shadow-sm shadow-primary/50' : 'bg-slate-200 group-hover/item:bg-slate-300'"></div>
                            <span x-text="label" class="transition-transform duration-200" :class="selected === val ? '' : 'group-hover:translate-x-1'"></span>
                        </div>

                        <div x-show="selected === val" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-50"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="flex items-center justify-center w-6 h-6 rounded-lg bg-primary text-white shadow-lg shadow-primary/20">
                            <i data-lucide="check" class="w-3.5 h-3.5 stroke-[4]"></i>
                        </div>
                    </div>
                </template>
                
                <div x-show="Object.keys(filteredOptions).length === 0" class="px-4 py-12 text-sm text-slate-400 text-center flex flex-col items-center justify-center gap-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-inner">
                        <i data-lucide="search" class="w-7 h-7 text-slate-300"></i>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold text-slate-900">No results found</p>
                        <p class="text-[13px]">Try a different search term</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

