@extends('layouts.app')

@section('meta')
    <x-seo :title="$playlist->title"
           :description="$playlist->description ?: 'A FocusedTube playlist.'" />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">

        <nav class="text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <span class="px-2">/</span>
            <a href="{{ route('playlists.index') }}" class="hover:underline">Playlists</a>
            <span class="px-2">/</span>
            <span class="text-slate-700">{{ $playlist->title }}</span>
        </nav>

        <header class="mt-4">
            <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">{{ $playlist->title }}</h1>
            @if ($playlist->description)
                <p class="mt-2 max-w-2xl text-slate-600">{{ $playlist->description }}</p>
            @endif
            <p class="mt-2 text-sm text-slate-500">{{ $videos->count() }} videos</p>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">

            {{-- Player / first video --}}
            <div class="lg:col-span-2">
                @if ($firstVideo)
                    <a href="{{ $firstVideo->watchUrl() }}"
                       class="group block overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="aspect-video overflow-hidden bg-slate-100">
                            @if ($firstVideo->thumbnail_url)
                                <img src="{{ $firstVideo->thumbnail_url }}" alt=""
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                            @endif
                        </div>
                        <div class="p-5">
                            <span class="text-xs font-medium uppercase tracking-wide text-brand-600">Start here</span>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ $firstVideo->title }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $firstVideo->durationForHumans() }}</p>
                        </div>
                    </a>
                @else
                    <x-empty title="This playlist has no videos yet" />
                @endif
            </div>

            {{-- Ordered list --}}
            <aside class="lg:col-span-1">
                <h2 class="text-sm font-medium text-slate-900">Up next</h2>
                <ol class="mt-3 space-y-1">
                    @foreach ($videos as $i => $video)
                        <li>
                            <a href="{{ $video->watchUrl() }}"
                               class="flex items-start gap-3 rounded-lg p-2 hover:bg-slate-100">
                                <span class="w-5 flex-none pt-0.5 text-right text-xs text-slate-400">{{ $i + 1 }}</span>
                                <img src="{{ $video->thumbnail_url }}" alt=""
                                     class="h-10 w-16 flex-none rounded object-cover ring-1 ring-slate-200">
                                <div class="min-w-0">
                                    <p class="line-clamp-2 text-sm font-medium text-slate-800">{{ $video->title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $video->durationForHumans() }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </aside>
        </div>
    </div>
@endsection