@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-md';
@endphp

<div 
    x-data="{ show: false }" 
    x-show="show" 
    @open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    @close-modal.window="if ($event.detail === '{{ $name }}') show = false"
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
    x-cloak
>
    <!-- Overlay -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal Panel -->
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full overflow-hidden transition-all transform bg-white border border-slate-200 shadow-2xl rounded-3xl p-6 text-left {{ $maxWidthClass }}"
             @click.outside="show = false">
            
            <div class="flex items-center justify-between mb-6">
                @if($title)
                    <h3 class="text-xl font-bold text-slate-900">{{ $title }}</h3>
                @endif
                <button type="button" 
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all"
                        @click="show = false">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mb-8">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
