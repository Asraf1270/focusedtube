@extends('layouts.app')

@section('meta')
    <x-seo title="Playlists" description="Curated playlists on FocusedTube." />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Playlists</h1>
        <p class="mt-2 text-slate-600">Ordered collections — start at the top and work down.</p>

        <div class="mt-8">
            @if ($playlists->isEmpty())
                <x-empty title="No playlists yet"
                         description="Playlists appear here once an admin publishes one." />
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($playlists as $playlist)
                        <x-playlist-card :playlist="$playlist" />
                    @endforeach
                </div>

                <div class="mt-6">{{ $playlists->links() }}</div>
            @endif
        </div>
    </div>
@endsection