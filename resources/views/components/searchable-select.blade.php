@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'placeholder' => 'Select an option...'])

<div class="space-y-1.5 min-w-[200px]" 
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
        <label class="block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <div class="relative group">
        <div @click="open = !open" 
             class="flex items-center justify-between w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 transition-all duration-200 focus:ring-4 focus:ring-primary/10 focus:border-primary group-hover:border-slate-300 cursor-pointer outline-none shadow-sm"
             :class="open ? 'ring-4 ring-primary/10 border-primary' : ''">
            <span x-text="selectedLabel || '{{ $placeholder }}'" :class="!selectedLabel ? 'text-slate-400' : ''"></span>
            <input type="hidden" name="{{ $name }}" :value="selected">
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors"></i>
        </div>

        <div x-show="open" 
             @click.outside="open = false"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute z-[60] w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden ring-1 ring-black ring-opacity-5">
            <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                <div class="relative flex items-center">
                    <i data-lucide="search" class="absolute left-3 w-4 h-4 text-slate-400"></i>
                    <input type="text" 
                           x-model="search" 
                           class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-lg outline-none focus:border-primary transition-all placeholder:text-slate-400" 
                           placeholder="Search options..."
                           @click.stop>
                </div>
            </div>
            <div class="max-height-[240px] overflow-y-auto custom-scrollbar">
                <template x-for="(label, val) in filteredOptions" :key="val">
                    <div @click="select(val, label)" 
                         class="px-4 py-2.5 text-sm cursor-pointer transition-colors flex items-center justify-between"
                         :class="selected === val ? 'bg-primary/5 text-primary font-semibold' : 'text-slate-600 hover:bg-slate-50'">
                        <span x-text="label"></span>
                        <i data-lucide="check" x-show="selected === val" class="w-4 h-4"></i>
                    </div>
                </template>
                <div x-show="Object.keys(filteredOptions).length === 0" class="px-4 py-8 text-center text-slate-400">
                    <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 opacity-20"></i>
                    <p class="text-xs">No matching results found</p>
                </div>
            </div>
        </div>
    </div>
</div>
