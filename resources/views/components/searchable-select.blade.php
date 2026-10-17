@props(['name' => null, 'label' => null, 'options' => [], 'selected' => null, 'placeholder' => 'Select an option...'])
@php
    $disabledExpr = $attributes->get('x-bind:disabled');
@endphp

<div class="w-full space-y-1.5" x-modelable="selected" {{ $attributes->whereStartsWith('x-model') }} x-data="{ 
        open: false, 
        search: '', 
        selected: @js($selected),
        selectedLabel: '',
        options: @js($options),
        get isDisabled() {
            @if($disabledExpr)
                return Boolean({{ $disabledExpr }});
            @else
                return false;
            @endif
        },
        get filteredOptions() {
            if (this.search === '') return this.options;
            return Object.fromEntries(
                Object.entries(this.options).filter(([val, label]) => 
                    String(label).toLowerCase().includes(this.search.toLowerCase())
                )
            );
        },
        init() {
            if (this.selected && this.options[this.selected]) {
                this.selectedLabel = this.options[this.selected];
            }
            this.$watch('selected', (value) => {
                if (value && this.options[value]) {
                    this.selectedLabel = this.options[value];
                } else {
                    this.selectedLabel = '';
                }
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            });
            @if($disabledExpr)
            this.$watch('isDisabled', (disabled) => {
                if (disabled) {
                    this.open = false;
                    if (this.selected) {
                        this.select('', '');
                    }
                }
            });
            @endif
        },
        select(val, label) {
            this.selected = val;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
            if (this.$refs.hiddenInput && typeof clearError === 'function') {
                clearError({ target: this.$refs.hiddenInput });
            }
        }
     }">

    @if($label)
        <label class="block text-sm font-bold text-slate-800 transition-colors duration-200"
            :class="open ? 'text-primary' : ''">
            {!! $label !!}
        </label>
    @endif

    <div class="relative group">
        <div @click="if (isDisabled) return; open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
            tabindex="0"
            :tabindex="isDisabled ? -1 : 0"
            @keydown.enter.prevent="if(!isDisabled) { open = !open; if(open) $nextTick(() => $refs.searchInput.focus()); }"
            @keydown.space.prevent="if(!isDisabled) { open = !open; if(open) $nextTick(() => $refs.searchInput.focus()); }"
            @keydown.escape="open = false"
            class="flex items-center justify-between w-full px-4 py-2.5 text-sm rounded-xl border transition-all duration-200 outline-none shadow-sm focus:outline-none"
            :class="[
                isDisabled
                    ? 'bg-slate-100 text-slate-500 border-slate-200 cursor-not-allowed select-none font-medium'
                    : 'bg-white text-slate-900 cursor-pointer group-hover:border-slate-300 ' + ((typeof formErrors !== 'undefined' && formErrors && $refs.hiddenInput && formErrors[$refs.hiddenInput.name]) 
                        ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' 
                        : (open 
                            ? 'border-primary ring-4 ring-primary/10' 
                            : 'border-slate-200 focus:border-primary focus:ring-4 focus:ring-primary/10'))
            ]">

            <div class="flex items-center gap-2 truncate">
                <span x-text="selectedLabel || (isDisabled ? 'Enter suspect name first' : '{{ $placeholder }}')" class="font-medium truncate transition-colors"
                    :class="(!selectedLabel || isDisabled) ? 'text-slate-500 font-medium' : 'text-slate-900 font-semibold'"></span>
            </div>

            <input type="hidden" x-ref="hiddenInput" @if($name) name="{{ $name }}" @endif {{ $attributes->whereStartsWith('x-bind:name') }}
                :value="selected">

            <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                <button type="button" x-show="selected && !isDisabled" @click.stop="select('', '')"
                    class="text-slate-400 hover:text-rose-500 p-0.5 rounded-full hover:bg-slate-100 transition-colors"
                    title="Clear selection">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-300"
                    :class="open && !isDisabled ? 'rotate-180 text-primary' : ''"></i>
            </div>
        </div>
        <template x-if="typeof formErrors !== 'undefined' && formErrors && $refs.hiddenInput && formErrors[$refs.hiddenInput.name]">
            <p class="text-xs font-semibold text-rose-600 mt-1" x-text="formErrors[$refs.hiddenInput.name]"></p>
        </template>

        <div x-show="open" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden flex flex-col">

            <div class="p-2 border-b border-slate-100 bg-slate-50/80">
                <div class="relative flex items-center group/search">
                    <i data-lucide="search"
                        class="absolute left-3 w-4 h-4 text-slate-400 group-focus-within/search:text-primary transition-colors"></i>
                    <input type="text" x-model="search" x-ref="searchInput"
                        class="w-full pl-9 pr-3 py-2 text-sm font-medium bg-white border border-slate-200 rounded-lg outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-slate-500 text-slate-900"
                        placeholder="Search..." @click.stop>
                </div>
            </div>

            <div class="p-1.5 max-h-[240px] overflow-y-auto custom-scrollbar">
                <template x-for="(label, val) in filteredOptions" :key="val">
                    <div @click="select(val, label)"
                        class="px-3 py-2 text-sm cursor-pointer rounded-lg transition-all duration-150 flex items-center justify-between group/item"
                        :class="selected == val 
                            ? 'bg-primary/10 text-primary font-bold' 
                            : 'text-slate-800 font-medium hover:bg-slate-100 hover:text-slate-900'">

                        <span x-text="label" class="truncate"></span>

                        <div x-show="selected == val" class="flex-shrink-0 text-primary ml-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                    </div>
                </template>

                <div x-show="Object.keys(filteredOptions).length === 0"
                    class="px-4 py-8 text-sm text-slate-400 text-center flex flex-col items-center justify-center gap-2">
                    <div
                        class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-inner mb-1">
                        <i data-lucide="search" class="w-5 h-5 text-slate-300"></i>
                    </div>
                    <p class="font-bold text-slate-800">No results found</p>
                    <p class="text-xs text-slate-500 font-medium">Try a different search term</p>
                </div>
            </div>
        </div>
    </div>
</div>
