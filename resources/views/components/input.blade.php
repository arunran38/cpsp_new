@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'icon' => null
])

<div class="space-y-1.5" x-data="{ count: 0 }" x-init="count = $refs.input.value.length">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center group">
        @if($icon)
            <div class="absolute left-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </div>
        @endif
        <input 
            {{ $attributes->merge([
                'class' => 'block w-full ' . ($icon ? 'pl-11' : 'px-4') . ' py-2.5 text-sm bg-white border rounded-xl text-slate-900 transition-all duration-200 placeholder:text-slate-400 outline-none group-hover:border-slate-300',
                'type' => $type,
                'id' => $name,
                'placeholder' => $placeholder
            ]) }}
            :class="(typeof formErrors !== 'undefined' && formErrors && $refs.input && formErrors[$refs.input.name]) ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 focus:ring-4 focus:ring-primary/10 focus:border-primary'"
            @if(!($attributes->has(':name') || $attributes->has('::name') || $attributes->has('x-bind:name'))) name="{{ $name }}" @endif
            @if($name && $type !== 'file') value="{{ old($name, $value) }}" @elseif($value) value="{{ $value }}" @endif
            x-ref="input"
            @input="count = $event.target.value.length; clearError($event)"
            @blur="validateField($event)"
        >
        <button type="button" 
                x-show="count > 0" 
                @click="$refs.input.value = ''; count = 0; $refs.input.focus()" 
                class="absolute right-3 p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all"
                style="display: none;">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @if($name)
        @error($name)
            <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
    <template x-if="typeof formErrors !== 'undefined' && formErrors && $refs.input && formErrors[$refs.input.name]">
        <p class="text-xs font-medium text-rose-500 mt-1" x-text="formErrors[$refs.input.name]"></p>
    </template>
</div>
