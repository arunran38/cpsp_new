@props(['label', 'name', 'value' => 1, 'checked' => false])

<div class="flex items-center" x-data="{ isChecked: @js($checked) }">
    <div class="relative flex items-center">
        <input 
            {{ $attributes->merge(['class' => 'w-5 h-5 text-primary border-slate-300 rounded-lg focus:ring-primary/20 focus:ring-offset-0 transition-all duration-200 cursor-pointer accent-primary']) }}
            type="checkbox" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ value }}"
            x-model="isChecked"
        >
    </div>
    @if($label)
        <label class="ml-3 text-sm font-medium text-slate-700 cursor-pointer select-none" for="{{ $name }}">
            {{ $label }}
        </label>
    @endif
</div>
