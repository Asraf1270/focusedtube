@props([
    'title',
    'subtitle' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="mb-4 flex items-end justify-between gap-4">
    <div>
        <h2 class="text-lg font-semibold text-slate-900 md:text-xl">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="text-sm font-medium text-brand-600 hover:underline">
            {{ $actionLabel }} →
        </a>
    @endif
</div>