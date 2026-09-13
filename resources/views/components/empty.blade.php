@props(['title' => 'Nothing here yet', 'description' => null])

<div class="rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center">
    <h3 class="text-base font-medium text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>