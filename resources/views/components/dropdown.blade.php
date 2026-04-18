@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-2 bg-white border border-slate-100'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute z-[100] mt-3 {{ $width }} rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] {{ $alignmentClasses }} overflow-hidden"
            style="display: none;"
            @click="open = false">
        <div class="rounded-2xl divide-y divide-slate-50 {{ $contentClasses }} backdrop-blur-xl">
            {{ $content }}
        </div>
    </div>
</div>

