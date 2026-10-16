@props([
    'label' => null,
    'name' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'max' => 5000
])

<div class="space-y-1.5" x-data="{ count: 0, max: {{ $max }} }" x-init="count = $refs.textarea.value ? $refs.textarea.value.length : 0">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="flex items-center justify-between text-sm font-bold text-slate-800">
            {{ $label }}
            <span class="text-xs bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-slate-700 font-bold" x-text="count + '/' + max"></span>
        </label>
    @endif
        <textarea 
        {{ $attributes->merge([
            'class' => 'block w-full px-4 py-3 text-sm font-medium bg-white border rounded-xl text-slate-900 transition-all duration-200 placeholder:text-slate-500 outline-none group-hover:border-slate-300 leading-relaxed',
            'id' => $name,
            'rows' => $rows,
            'placeholder' => $placeholder
        ]) }}
        :class="[
            count > max ? 'border-rose-500 ring-rose-500/10' : '',
            (typeof formErrors !== 'undefined' && formErrors && $refs.textarea && formErrors[$refs.textarea.name]) ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 focus:ring-4 focus:ring-primary/10 focus:border-primary'
        ]"
        @if(!($attributes->has(':name') || $attributes->has('::name') || $attributes->has('x-bind:name'))) name="{{ $name }}" @endif
        x-ref="textarea"
        @input="count = $event.target.value.length; clearError($event)"
        @blur="validateField($event)"
    >{{ $name ? old($name, $value) : $value }}</textarea>
    @if($name)
        @error($name)
            <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
        @enderror
    @endif
    <template x-if="typeof formErrors !== 'undefined' && formErrors && $refs.textarea && formErrors[$refs.textarea.name]">
        <p class="text-xs font-semibold text-rose-600 mt-1" x-text="formErrors[$refs.textarea.name]"></p>
    </template>
</div>
