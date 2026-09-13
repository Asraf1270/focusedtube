@extends('layouts.admin')

@section('title', 'Review Video')
@section('heading', 'Review Video')

@section('breadcrumbs')
    <span class="text-slate-400">Admin</span>
    <span class="px-2">/</span>
    <a href="{{ route('admin.videos.index') }}" class="text-slate-500 hover:underline">Videos</a>
    <span class="px-2">/</span>
    <a href="{{ route('admin.videos.create') }}" class="text-slate-500 hover:underline">Add</a>
    <span class="px-2">/</span>
    <span class="text-slate-700">Review</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.videos.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf
        <input type="hidden" name="youtube_video_id" value="{{ $data->videoId }}">

        {{-- Left: video preview --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="card overflow-hidden">
                @if ($data->thumbnailUrl)
                    <img src="{{ $data->thumbnailUrl }}" alt="" class="aspect-video w-full object-cover">
                @else
                    <div class="aspect-video w-full bg-slate-100 grid place-items-center text-slate-400">No thumbnail</div>
                @endif
                <div class="p-5">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $data->title }}</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                        @if ($data->channelName)
                            <span>{{ $data->channelName }}</span>
                        @endif
                        <span>·</span>
                        <span>{{ gmdate('H:i:s', $data->durationSeconds) }}</span>
                        @if ($data->publishedAt)
                            <span>·</span>
                            <span>Published {{ $data->publishedAt->diffForHumans() }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs">
                        <span class="badge bg-slate-100 text-slate-600">{{ $data->videoId }}</span>
                        <a href="https://www.youtube.com/watch?v={{ $data->videoId }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-brand-600 hover:underline">Open on YouTube ↗</a>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="text-sm font-medium text-slate-900">Description</h3>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-600 max-h-72 overflow-y-auto">
                    {{ $data->description ?: '—' }}
                </p>
                <p class="mt-3 text-xs text-slate-400">
                    Description is read-only here. Editable descriptions come in a later step.
                </p>
            </div>
        </div>

        {{-- Right: metadata controls --}}
        <div class="space-y-6">
            <div class="card p-5 space-y-4">
                <h3 class="text-sm font-medium text-slate-900">Classification</h3>

                <div>
                    <label for="category_id" class="label">Category</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input" required>
                        <option value="draft"     @selected(old('status', 'draft') === 'draft')>Save as draft</option>
                        <option value="published" @selected(old('status') === 'published')>Publish now</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="visibility" class="label">Visibility</label>
                    <select id="visibility" name="visibility" class="input" required>
                        <option value="public"  @selected(old('visibility', 'public') === 'public')>Public</option>
                        <option value="private" @selected(old('visibility') === 'private')>Private</option>
                    </select>
                    @error('visibility') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <h3 class="text-sm font-medium text-slate-900">Placement</h3>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Featured video
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_daily_focus" value="1" @checked(old('is_daily_focus'))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Set as Today's Focus
                </label>

                <div>
                    <label for="display_order" class="label">Display order</label>
                    <input id="display_order" name="display_order" type="number" min="0" max="100000"
                           value="{{ old('display_order', 0) }}" class="input">
                    <p class="mt-1 text-xs text-slate-500">Lower numbers appear first.</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2">
                <button type="submit" class="btn-primary">Save Video</button>
                <button
                    type="submit"
                    form="cancel-form"
                    class="btn-ghost">Discard</button>
            </div>
        </div>
    </form>

    <form id="cancel-form" method="POST" action="{{ route('admin.videos.cancel') }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection