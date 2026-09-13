@extends('layouts.app')

@section('meta')
    <x-seo title="Your Watchlist" />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Saved videos</h1>
        <p class="mt-2 text-slate-600">Videos you've bookmarked for later.</p>

        <div class="mt-8">
            @if ($items->isEmpty())
                <x-empty title="Your watchlist is empty"
                         description="Save a video from its page and it'll appear here.">
                    <a href="{{ route('videos.index') }}" class="btn-primary">Browse videos</a>
                </x-empty>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($items as $row)
                        @if ($row->video)
                            <x-video-card :video="$row->video" />
                        @endif
                    @endforeach
                </div>
                <div class="mt-6">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
@endsection