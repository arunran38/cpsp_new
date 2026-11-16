@props(['value', 'required' => false])
@php
    $text = $value ?? (string)$slot;
    $isRequired = $required || $attributes->has('required') || (is_string($text) && str_contains($text, '*'));
    $displayText = $text;
    if (is_string($displayText)) {
        $displayText = trim(str_replace('*', '', strip_tags($displayText)));
    }
@endphp

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {!! $displayText !!}@if($isRequired)<span class="required-mark text-red-600 font-bold ml-1" style="color: #dc2626 !important;">*</span>@endif
</label>
