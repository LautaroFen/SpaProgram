@props([
    'href',
    'active' => false,
])

@php
    $base = 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30 dark:focus-visible:ring-slate-100/30';
    $inactive = 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-900 dark:hover:text-white';
    $activeClasses = 'bg-slate-900 text-white shadow-sm hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white';
@endphp

<a href="{{ $href }}" {{ $attributes->class([$base, $active ? $activeClasses : $inactive]) }}>
    @if (isset($icon))
        <span class="shrink-0">{{ $icon }}</span>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
