@extends('layouts.app')

@section('meta')
    <x-seo
        title="FocusedTube"
        description="A focused, distraction-free video learning platform. Curated by hand, watched on purpose." />
@endsection

@section('content')

    {{-- Hero --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                Learn without the noise.
            </h1>
            <p class="mt-3 max-w-2xl text-slate-600">
                Every video here was chosen on purpose. No infinite feed,
                no autoplay rabbit holes — just the videos you actually
                want to watch.
            </p>
            @guest
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary">Create an account</a>
                    <a href="{{ route('videos.index') }}" class="btn-ghost">Browse videos</a>
                </div>
            @endguest
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-12 px-4 py-10">

        {{-- Continue Watching --}}
        @if (isset($continueWatching) && $continueWatching->isNotEmpty())
            <section>
                <x-section-heading
                    title="Continue watching"
                    subtitle="Pick up exactly where you left off." />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($continueWatching as $row)
                        @if ($row->video)
                            <x-video-card
                                :video="$row->video"
                                :show-progress="true"
                                :progress="$row" />
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Daily Focus --}}
        @if (! empty($dailyFocus))
            <section>
                <x-section-heading
                    title="Today's Focus 🎯"
                    subtitle="A single video worth your full attention." />
                <a href="{{ $dailyFocus->watchUrl() }}"
                   class="group grid overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md md:grid-cols-2">
                    <div class="aspect-video overflow-hidden bg-slate-100 md:aspect-auto md:h-full">
                        @if ($dailyFocus->thumbnail_url)
                            <img src="{{ $dailyFocus->thumbnail_url }}" alt=""
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center p-6 md:p-8">
                        <span class="text-xs font-medium uppercase tracking-wide text-brand-600">Daily Focus</span>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900 md:text-2xl">
                            {{ $dailyFocus->title }}
                        </h3>
                        @if ($dailyFocus->channel_name)
                            <p class="mt-2 text-sm text-slate-500">{{ $dailyFocus->channel_name }}</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-500">{{ $dailyFocus->durationForHumans() }}</p>
                        <span class="btn-primary mt-6 self-start">Start watching</span>
                    </div>
                </a>
            </section>
        @endif

        {{-- Featured --}}
        @if (! empty($featured) && $featured->isNotEmpty())
            <section>
                <x-section-heading
                    title="Featured"
                    subtitle="Hand-picked by the admin."
                    action-label="See all videos"
                    :action-url="route('videos.index')" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Categories --}}
        @if (! empty($categories) && $categories->isNotEmpty())
            <section>
                <x-section-heading
                    title="Browse by category"
                    action-label="All categories"
                    :action-url="route('categories.index')" />
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($categories as $category)
                        <x-category-card :category="$category" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Recently added --}}
        @if (! empty($recent) && $recent->isNotEmpty())
            <section>
                <x-section-heading
                    title="Recently added"
                    action-label="See more"
                    :action-url="route('videos.index')" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recent as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Popular --}}
        @if (! empty($popular) && $popular->isNotEmpty())
            <section>
                <x-section-heading
                    title="Popular on FocusedTube"
                    subtitle="Most-watched this month." />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($popular as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Empty state for a brand-new install --}}
        @php
            $hasAnyContent = (! empty($featured) && $featured->isNotEmpty())
                || (! empty($recent) && $recent->isNotEmpty())
                || (! empty($popular) && $popular->isNotEmpty())
                || ! empty($dailyFocus);
        @endphp

        @unless ($hasAnyContent)
            <x-empty
                title="FocusedTube is empty right now"
                description="Once an admin adds some videos they'll show up here." />
        @endunless

    </div>
@endsection