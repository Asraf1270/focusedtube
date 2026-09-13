@props([
    'video',
    'showProgress' => false,
    'progress'     => null, // WatchProgress|null — used in Step 10/11
])

<a href="{{ $video->watchUrl() }}"
   class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">

    <div class="relative aspect-video overflow-hidden bg-slate-100">
        @if ($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}"
                 alt=""
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
        @endif

        @if ($video->duration_seconds > 0)
            <span class="absolute bottom-2 right-2 rounded bg-black/75 px-1.5 py-0.5 text-xs font-medium text-white">
                {{ $video->durationForHumans() }}
            </span>
        @endif

        @if ($showProgress && $progress && ! $progress->completed && $progress->progress_percentage > 0)
            <div class="absolute inset-x-0 bottom-0 h-1 bg-black/20">
                <div class="h-full bg-brand-500" style="width: {{ $progress->progress_percentage }}%"></div>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        <h3 class="line-clamp-2 text-sm font-medium text-slate-900 group-hover:text-brand-700">
            {{ $video->title }}
        </h3>
        @if ($video->channel_name)
            <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $video->channel_name }}</p>
        @endif
        @if ($video->category)
            <p class="mt-2 text-xs text-slate-400">{{ $video->category->name }}</p>
        @endif
    </div>
</a>