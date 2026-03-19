@props(['label', 'name', 'type' => 'text', 'value' => '', 'placeholder' => ''])

<div class="space-y-1.5" x-data="{ count: 0 }" x-init="count = $refs.input.value.length">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center group">
        <input 
            {{ $attributes->merge(['class' => 'block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 transition-all duration-200 placeholder:text-slate-400 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none group-hover:border-slate-300']) }}
            type="{{ $type }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            x-ref="input"
            @input="count = $event.target.value.length"
        >
        <button type="button" 
                x-show="count > 0" 
                @click="$refs.input.value = ''; count = 0; $refs.input.focus()" 
                class="absolute right-3 p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all"
                style="display: none;">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @error($name)
        <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
    @enderror
</div>
