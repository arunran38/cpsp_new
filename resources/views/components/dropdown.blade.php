@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = [
    'left' => 'left-0',
    'right' => 'right-0',
    'top' => 'bottom-full mb-2',
][$align] ?? 'right-0';
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-2 rounded-2xl shadow-xl bg-white border border-slate-200 ring-1 ring-black ring-opacity-5 focus:outline-none {{ $alignmentClasses }}"
            style="display: none; min-width: {{ $width }}px;">
        <div class="py-1 divide-y divide-slate-100">
            {{ $content }}
        </div>
    </div>
</div>
