@extends('layouts.admin')

@section('title', 'New Playlist')
@section('heading', 'New Playlist')

@section('content')
    <form method="POST" action="{{ route('admin.playlists.store') }}" class="max-w-2xl space-y-6">
        @csrf
        @include('admin.playlists._form', ['playlist' => $playlist])
        <div class="flex items-center gap-2">
            <button class="btn-primary">Create playlist</button>
            <a href="{{ route('admin.playlists.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection