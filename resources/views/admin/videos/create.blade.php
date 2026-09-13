@extends('layouts.admin')

@section('title', 'Add Video')
@section('heading', 'Add Video')

@section('breadcrumbs')
    <span class="text-slate-400">Admin</span>
    <span class="px-2">/</span>
    <a href="{{ route('admin.videos.index') }}" class="text-slate-500 hover:underline">Videos</a>
    <span class="px-2">/</span>
    <span class="text-slate-700">Add</span>
@endsection

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <h2 class="text-lg font-medium text-slate-900">Paste a YouTube URL</h2>
            <p class="mt-1 text-sm text-slate-500">
                We'll fetch the title, channel, thumbnail and duration from YouTube.
                Nothing is saved until you review and confirm on the next screen.
            </p>

            <form method="POST" action="{{ route('admin.videos.fetch') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="url" class="label">YouTube URL</label>
                    <input
                        id="url"
                        name="url"
                        type="text"
                        inputmode="url"
                        autocomplete="off"
                        placeholder="https://www.youtube.com/watch?v=…"
                        value="{{ old('url') }}"
                        required
                        autofocus
                        class="input">
                    @error('url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        Supports <code>watch?v=</code>, <code>youtu.be/</code>,
                        <code>shorts/</code>, <code>embed/</code>, and raw 11-character IDs.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Fetch Video</button>
                    <a href="{{ route('admin.videos.index') }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>

        @if ($pending)
            <div class="card mt-6 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">A video is still pending review</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $pending->title }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.videos.review') }}" class="btn-primary">Continue review</a>
                        <form method="POST" action="{{ route('admin.videos.cancel') }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-ghost">Discard</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection