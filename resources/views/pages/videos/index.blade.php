@extends('layouts.app')

@section('meta')
    <x-seo title="Videos" description="Browse every published video on FocusedTube." />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">

        <header class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Videos</h1>
            <p class="mt-2 text-slate-600">
                {{ number_format($videos->total()) }} published videos — no drafts, no noise.
            </p>
        </header>

        <x-admin.filter-bar>
            <div class="flex-1">
                <label for="q" class="label">Search</label>
                <input id="q" name="q" type="text" value="{{ $filters['q'] }}"
                       placeholder="Title, channel, description…" class="input">
            </div>
            <div class="w-full md:w-56">
                <label for="category" class="label">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) $filters['category'] === (string) $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-44">
                <label for="sort" class="label">Sort</label>
                <select id="sort" name="sort" class="input">
                    <option value="recent"  @selected($filters['sort'] === 'recent')>Newest</option>
                    <option value="popular" @selected($filters['sort'] === 'popular')>Most viewed</option>
                    <option value="oldest"  @selected($filters['sort'] === 'oldest')>Oldest</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="btn-primary">Filter</button>
                <a href="{{ route('videos.index') }}" class="btn-ghost">Reset</a>
            </div>
        </x-admin.filter-bar>

        <div class="mt-6">
            @if ($videos->isEmpty())
                <x-empty title="No videos match your filters"
                         description="Try a different keyword or clear the filters.">
                    <a href="{{ route('videos.index') }}" class="btn-ghost">Clear filters</a>
                </x-empty>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($videos as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $videos->links() }}</div>
            @endif
        </div>
    </div>
@endsection