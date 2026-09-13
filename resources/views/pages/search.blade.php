@extends('layouts.app')

@section('meta')
    <x-seo
        :title="$term ? 'Search: '.$term : 'Search'"
        description="Search FocusedTube's curated video library." />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">

        <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Search</h1>
        <p class="mt-2 text-slate-600">
            Search only FocusedTube's curated library — never all of YouTube.
        </p>

        {{-- Search bar --}}
        <form method="GET" action="{{ route('search') }}" class="mt-6 card p-4 flex flex-col gap-3 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="q" class="label">Search</label>
                <input id="q" name="q" type="text"
                       value="{{ $term }}"
                       placeholder="Try: Laravel, Physics, Grammar…"
                       class="input">
            </div>
            <div class="w-full md:w-56">
                <label for="category" class="label">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-44">
                <label for="sort" class="label">Sort</label>
                <select id="sort" name="sort" class="input">
                    <option value="recent"  @selected($sort === 'recent')>Newest</option>
                    <option value="popular" @selected($sort === 'popular')>Most viewed</option>
                    <option value="oldest"  @selected($sort === 'oldest')>Oldest</option>
                </select>
            </div>
            <button class="btn-primary">Search</button>
        </form>

        {{-- Results --}}
        <div class="mt-8">
            @if ($term === '' && ! $categoryId)
                <x-empty title="Start typing to search"
                         description="Results only include videos published to FocusedTube." />
            @elseif ($videos->isEmpty())
                <x-empty
                    title="No results"
                    description="Try a different keyword or clear the filters.">
                    <a href="{{ route('search') }}" class="btn-ghost">Clear filters</a>
                </x-empty>
            @else
                <p class="mb-4 text-sm text-slate-500">
                    {{ $videos->total() }} result{{ $videos->total() === 1 ? '' : 's' }}
                    @if ($term) for <span class="font-medium text-slate-700">"{{ $term }}"</span> @endif
                </p>

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