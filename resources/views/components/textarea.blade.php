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
        <label @if($name) for="{{ $name }}" @endif class="flex items-center justify-between text-sm font-semibold text-slate-700">
            {{ $label }}
            <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-black font-bold" x-text="count + '/' + max"></span>
        </label>
    @endif
        <textarea 
        {{ $attributes->merge([
            'class' => 'block w-full px-4 py-3 text-sm bg-white border rounded-xl text-black transition-all duration-200 placeholder:text-slate-400 outline-none group-hover:border-slate-300',
            'id' => $name,
            'rows' => $rows,
            'placeholder' => $placeholder
        ]) }}
        :class="[
            count > max ? 'border-rose-500 ring-rose-500/10' : '',
            ($data.formErrors && $refs.textarea && $data.formErrors[$refs.textarea.name]) ? 'border-rose-500 ring-4 ring-rose-500/10 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 focus:ring-4 focus:ring-primary/10 focus:border-primary'
        ]"
        @if(!($attributes->has(':name') || $attributes->has('::name') || $attributes->has('x-bind:name'))) name="{{ $name }}" @endif
        x-ref="textarea"
        @input="count = $event.target.value.length; clearError($event)"
        @blur="validateField($event)"
    >{{ $name ? old($name, $value) : $value }}</textarea>
    @if($name)
        @error($name)
            <p class="text-xs font-medium text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
    <template x-if="$data.formErrors && $refs.textarea && $data.formErrors[$refs.textarea.name]">
        <p class="text-xs font-medium text-rose-500 mt-1" x-text="$data.formErrors[$refs.textarea.name]"></p>
    </template>
</div>
