@props([
    'label' => null,
    'name' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4
])

<div class="space-y-1.5" x-data="{ count: 0, max: 500 }" x-init="count = $refs.textarea.value.length">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="flex items-center justify-between text-sm font-semibold text-slate-700">
            {{ $label }}
            <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500 font-bold" x-text="count + '/' + max"></span>
        </label>
    @endif
    <textarea 
        {{ $attributes->merge([
            'class' => 'block w-full px-4 py-3 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 transition-all duration-200 placeholder:text-slate-400 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none group-hover:border-slate-300',
            'id' => $name,
            'name' => $name,
            'rows' => $rows,
            'placeholder' => $placeholder
        ]) }}
        x-ref="textarea"
        @input="count = $event.target.value.length"
        :class="count > max ? 'border-rose-500 ring-rose-500/10' : ''"
    >{{ $name ? old($name, $value) : $value }}</textarea>
</div>
