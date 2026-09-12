@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div class="card flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 12h16M4 18h10"/>
        </svg>
    </div>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (trim(strip_tags($slot)))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>