@extends('layouts.app')

@section('meta')
    <x-seo
        :title="$video->title"
        :description="\Illuminate\Support\Str::limit(strip_tags((string) $video->description), 155)"
        :image="$video->thumbnail_url"
        type="video.other" />
@endsection

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $video->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $video->description), 500),
            'thumbnailUrl' => $video->thumbnail_url,
            'uploadDate' => $video->youtube_published_at?->toAtomString(),
            'duration' => $video->duration_seconds > 0
                ? 'PT'.intdiv($video->duration_seconds, 60).'M'.($video->duration_seconds % 60).'S'
                : null,
            'contentUrl' => $video->youtubeWatchUrl(),
            'embedUrl' => 'https://www.youtube.com/embed/'.$video->youtube_video_id,
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8">

    <nav class="text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:underline">Home</a>
        <span class="px-2">/</span>
        <a href="{{ route('videos.index') }}" class="hover:underline">Videos</a>
        @if ($video->category)
            <span class="px-2">/</span>
            @if ($video->category->slug)
                <a href="{{ route('categories.show', $video->category) }}" class="hover:underline">
                    {{ $video->category->name }}
                </a>
            @else
                <span class="text-slate-700">{{ $video->category->name }}</span>
            @endif
        @endif
    </nav>

    <div class="mt-4 grid gap-8 lg:grid-cols-3">
        {{-- Player + meta --}}
        <div class="lg:col-span-2">

            <div class="overflow-hidden rounded-2xl bg-black shadow-sm">
                <div class="aspect-video">
                    <iframe
                        id="yt-player"
                        src="https://www.youtube.com/embed/{{ $video->youtube_video_id }}?rel=0&modestbranding=1&enablejsapi=1{{ $resumeAt > 0 ? '&start='.$resumeAt : '' }}"
                        title="{{ $video->title }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        class="h-full w-full"
                        data-video-id="{{ $video->id }}"
                        data-duration="{{ $video->duration_seconds }}"
                        data-resume-at="{{ $resumeAt }}"
                    ></iframe>
                </div>
            </div>

            <h1 class="mt-5 text-xl font-semibold text-slate-900 md:text-2xl">{{ $video->title }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                @if ($video->channel_name)
                    <span>{{ $video->channel_name }}</span>
                    <span>·</span>
                @endif
                @if ($video->category)
                    @if ($video->category->slug)
                        <a href="{{ route('categories.show', $video->category) }}" class="hover:underline">
                            {{ $video->category->name }}
                        </a>
                    @else
                        <span>{{ $video->category->name }}</span>
                    @endif
                    <span>·</span>
                @endif
                <span>{{ $video->durationForHumans() }}</span>
                @if ($video->published_at)
                    <span>·</span>
                    <span>Published {{ $video->published_at->diffForHumans() }}</span>
                @endif
            </div>

            {{-- Actions --}}
            <div class="mt-5 flex flex-wrap items-center gap-3">
                @auth
                    @php
                        $saved = app(\App\Services\Watch\WatchlistService::class)
                            ->isSaved(auth()->user(), $video);
                    @endphp
                    <button
                        type="button"
                        id="watchlist-toggle"
                        data-saved="{{ $saved ? '1' : '0' }}"
                        data-url="{{ route('watchlist.store', $video) }}"
                        data-method="{{ $saved ? 'DELETE' : 'POST' }}"
                        class="btn-ghost ring-1 ring-slate-300">
                        <span id="watchlist-label">{{ $saved ? 'Saved ✓' : 'Save' }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost ring-1 ring-slate-300">
                        Sign in to save
                    </a>
                @endauth

                @if ($progress && ! $progress->completed)
                    <span class="text-sm text-slate-500">
                        Resuming from {{ gmdate('i:s', $progress->current_position_seconds) }} —
                        {{ $progress->progress_percentage }}% watched
                    </span>
                @elseif ($progress && $progress->completed)
                    <span class="text-sm text-emerald-600">✓ Watched</span>
                @endif
            </div>

            {{-- Description --}}
            @if ($video->description)
                <div class="card mt-6 p-5">
                    <h2 class="text-sm font-medium text-slate-900">Description</h2>
                    <div class="prose prose-sm mt-2 max-w-none whitespace-pre-line text-slate-700">
                        {{ $video->description }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Related --}}
        <aside class="lg:col-span-1">
            <h2 class="text-sm font-medium text-slate-900">More in
                {{ $video->category?->name ?? 'FocusedTube' }}
            </h2>
            @if ($related->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No related videos yet.</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($related as $r)
                        <li>
                            <a href="{{ $r->watchUrl() }}" class="flex gap-3 rounded-lg p-1 hover:bg-slate-100">
                                <img src="{{ $r->thumbnail_url }}" alt="" loading="lazy"
                                     class="h-14 w-24 flex-none rounded object-cover ring-1 ring-slate-200">
                                <div class="min-w-0">
                                    <p class="line-clamp-2 text-sm font-medium text-slate-800">{{ $r->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $r->durationForHumans() }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- Watchlist toggle ----
    const toggle = document.getElementById('watchlist-toggle');
    if (toggle) {
        toggle.addEventListener('click', async function () {
            const url    = toggle.dataset.url;
            const method = toggle.dataset.method;
            toggle.disabled = true;

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (! res.ok) throw new Error('Request failed');

                const data = await res.json();
                const saved = data.saved;
                toggle.dataset.saved = saved ? '1' : '0';
                toggle.dataset.method = saved ? 'DELETE' : 'POST';
                document.getElementById('watchlist-label').textContent = saved ? 'Saved ✓' : 'Save';
            } catch (e) {
                console.error(e);
            } finally {
                toggle.disabled = false;
            }
        });
    }

    // ---- Resume + future progress tracking ----
    // (Step 10 will attach a progress beacon here.)
    const iframe = document.getElementById('yt-player');
    const resumeAt = iframe ? parseInt(iframe.dataset.resumeAt || '0', 10) : 0;
    // The `?start=` param already handles resume on load.
    // Nothing else is required for Step 9b.
    if (resumeAt > 0) {
        console.info('[FocusedTube] Resuming at', resumeAt, 'seconds');
    }
});
</script>
@endpush
@push('scripts')
<script>
    window.FT_PLAYER_CONFIG = {
        videoId:     {{ $video->id }},
        startedUrl:  @json(route('videos.started', $video)),
        progressUrl: @json(route('videos.progress', $video)),
        csrf:        @json(csrf_token()),
        resumeAt:    {{ $resumeAt }},
        duration:    {{ (int) $video->duration_seconds }},
    };
</script>
<script src="https://www.youtube.com/iframe_api" async></script>
<script src="{{ asset('js/player-tracker.js') }}" defer></script>
@endpush