@props(['label', 'name', 'options' => [], 'selected' => ''])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center group">
        <select 
            {{ $attributes->merge(['class' => 'block w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 transition-all duration-200 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none appearance-none group-hover:border-slate-300 cursor-pointer']) }}
            id="{{ $name }}" 
            name="{{ $name }}"
        >
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ $value == old($name, $selected) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="absolute right-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
            <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </div>
    </div>
    @error($name)
        <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
    @enderror
</div>
