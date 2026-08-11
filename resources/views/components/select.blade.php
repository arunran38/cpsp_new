@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'selected' => '',
    'placeholder' => null
])
<div class="space-y-1.5" x-data="{}">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center group">
        <select 
            {{ $attributes->merge([
                'class' => 'block w-full px-4 py-2.5 text-sm font-semibold bg-white border rounded-xl text-slate-900 transition-all duration-200 outline-none appearance-none group-hover:border-slate-300 cursor-pointer shadow-sm !bg-none',
                'id' => $name,
            ]) }}
            style="background-image: none !important;"
            :class="(typeof formErrors !== 'undefined' && formErrors && $refs.select && formErrors[$refs.select.name]) ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 focus:ring-4 focus:ring-primary/10 focus:border-primary'"
            x-ref="select"
            @if(!($attributes->has(':name') || $attributes->has('::name') || $attributes->has('x-bind:name'))) name="{{ $name }}" @endif
            @change="validateField($event)"
        >
            @if($placeholder)
                <option value="" {{ !$selected && !old($name) ? 'selected' : '' }} disabled class="py-2 text-base font-semibold text-slate-400 bg-white">
                    {{ $placeholder }}
                </option>
            @endif
            @if(count($options) > 0)
                @foreach($options as $value => $optionLabel)
                    <option value="{{ $value }}" {{ $name && $value == old($name, $selected) ? 'selected' : '' }} class="py-2 text-base font-semibold text-slate-900 bg-white">
                        {{ $optionLabel }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
        <div class="absolute right-4 pointer-events-none text-slate-400 group-hover:text-slate-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="m6 9 6 6 6-6"/></svg>
        </div>
    </div>
    @if($name)
        @error($name)
            <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
    <template x-if="typeof formErrors !== 'undefined' && formErrors && $refs.select && formErrors[$refs.select.name]">
        <p class="text-xs font-medium text-rose-500 mt-1" x-text="formErrors[$refs.select.name]"></p>
    </template>
</div>
