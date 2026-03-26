@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'selected' => ''
])

<div class="space-y-1.5">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center group">
        <select 
            {{ $attributes->merge([
                'class' => 'block w-full px-4 py-2.5 text-sm font-medium bg-white border border-slate-200 rounded-xl text-gray-800 transition-all duration-200 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none appearance-none group-hover:border-slate-300 cursor-pointer shadow-sm',
                'id' => $name,
                'name' => $name
            ]) }}
        >
            @foreach($options as $value => $optionLabel)
                <option value="{{ $value }}" {{ $name && $value == old($name, $selected) ? 'selected' : '' }} class="py-2 text-base font-medium text-gray-800 bg-white">
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
        <div class="absolute right-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
            <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </div>
    </div>
    @if($name)
        @error($name)
            <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>
