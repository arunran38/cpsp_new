@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'submit',
    'loading' => false,
    'icon' => null
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover shadow-lg shadow-primary/25 focus:ring-primary/50',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-500',
        'success' => 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 focus:ring-emerald-500',
        'danger' => 'bg-rose-500 text-white hover:bg-rose-600 shadow-lg shadow-rose-500/25 focus:ring-rose-500',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-lg shadow-amber-500/25 focus:ring-amber-500',
        'light' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 shadow-sm focus:ring-slate-500',
        'dark' => 'bg-slate-900 text-white hover:bg-slate-950 focus:ring-slate-900',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-500',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-8 py-3.5 text-base',
    ];

    $classes = "{$baseClasses} " . ($variants[$variant] ?? $variants['primary']) . " " . ($sizes[$size] ?? $sizes['md']);
@endphp

<button 
    {{ $attributes->merge(['class' => $classes, 'type' => $type]) }}
    x-data="{ isLoading: false }" 
    @if($loading) 
        @click="isLoading = true" 
        :disabled="isLoading"
    @endif
>
    @if($loading)
        <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif

    @if($icon)
        <i data-lucide="{{ $icon }}" class="{{ $loading ? '' : 'sm:mr-2' }} w-4 h-4" x-show="!isLoading"></i>
    @endif

    <span :class="isLoading ? 'ml-1' : ''">{{ $slot }}</span>
</button>
