@extends('layouts.app')

@section('meta')
    <x-seo title="Watch History" />
@endsection

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">

    <header class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Watch history</h1>
            <p class="mt-2 text-slate-600">Everything you've watched, grouped by day.</p>
        </div>

        @if ($grouped->isNotEmpty())
            <form method="POST" action="{{ route('history.clear') }}"
                  onsubmit="return confirm('Clear your entire watch history? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="btn-ghost text-red-600">Clear all</button>
            </form>
        @endif
    </header>

    <div class="mt-8 space-y-8">
        @if ($grouped->isEmpty())
            <x-empty title="No watch history yet"
                     description="Videos you watch while signed in appear here.">
                <a href="{{ route('videos.index') }}" class="btn-primary">Find something to watch</a>
            </x-empty>
        @else
            @foreach ($grouped as $day => $entries)
                <section>
                    <h2 class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ $day }}</h2>
                    <ul class="mt-3 divide-y divide-slate-100 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                        @foreach ($entries as $entry)
                            @php $video = $entry->video; @endphp
                            @if ($video)
                                <li class="flex items-center gap-3 p-3">
                                    <a href="{{ $video->watchUrl() }}" class="flex min-w-0 flex-1 items-center gap-3">
                                        <img src="{{ $video->thumbnail_url }}" alt="" loading="lazy"
                                             class="h-14 w-24 flex-none rounded object-cover ring-1 ring-slate-200">
                                        <div class="min-w-0">
                                            <p class="line-clamp-2 text-sm font-medium text-slate-900">{{ $video->title }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $video->category?->name ?: 'Uncategorized' }}
                                                · {{ $entry->watched_at->format('H:i') }}
                                            </p>
                                        </div>
                                    </a>

                                    <form method="POST" action="{{ route('history.destroy', $entry->id) }}"
                                          onsubmit="return confirm('Remove this entry from your history?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-slate-500 hover:text-red-600">Remove</button>
                                    </form>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </section>
            @endforeach
        @endif
    </div>
</div>
@endsection