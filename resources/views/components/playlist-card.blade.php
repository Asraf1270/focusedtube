@props(['playlist'])

<a href="{{ route('playlists.show', $playlist) }}"
   class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
    <div class="aspect-video overflow-hidden bg-slate-100">
        @if ($playlist->thumbnail_url)
            <img src="{{ $playlist->thumbnail_url }}" alt="" loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
        @else
            <div class="grid h-full w-full place-items-center text-slate-300">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/>
                </svg>
            </div>
        @endif
    </div>
    <div class="p-4">
        <h3 class="text-sm font-medium text-slate-900 group-hover:text-brand-700">{{ $playlist->title }}</h3>
        <p class="mt-1 text-xs text-slate-500">
            {{ $playlist->videos_count ?? $playlist->videos->count() }} videos
        </p>
    </div>
</a>