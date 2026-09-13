@extends('layouts.admin')

@section('title', "Edit — {$video->title}")
@section('heading', 'Edit Video')

@section('breadcrumbs')
    <span class="text-slate-400">Admin</span>
    <span class="px-2">/</span>
    <a href="{{ route('admin.videos.index') }}" class="text-slate-500 hover:underline">Videos</a>
    <span class="px-2">/</span>
    <span class="text-slate-700 truncate max-w-xs inline-block align-bottom">{{ $video->title }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.videos.update', $video) }}"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @method('PATCH')

        {{-- Left: preview + read-only YouTube metadata --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card overflow-hidden">
                @if ($video->thumbnail_url)
                    <img src="{{ $video->thumbnail_url }}" alt="" class="aspect-video w-full object-cover">
                @endif
                <div class="p-5 space-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="badge bg-slate-100 text-slate-600">{{ $video->youtube_video_id }}</span>
                        @if ($video->channel_name)
                            <span class="text-slate-500">{{ $video->channel_name }}</span>
                        @endif
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-500">{{ $video->durationForHumans() }}</span>
                        <a href="{{ $video->youtubeWatchUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="ml-auto text-brand-600 hover:underline">Open on YouTube ↗</a>
                    </div>
                    <p class="text-xs text-slate-400">
                        YouTube-owned fields (thumbnail, channel, duration, YouTube publish date)
                        are read-only here. They refresh via a separate sync flow.
                    </p>
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <div>
                    <label for="title" class="label">Title</label>
                    <input id="title" name="title" type="text" required
                           value="{{ old('title', $video->title) }}" class="input">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-500">
                        Changing the title regenerates the slug.
                        Current slug: <code class="text-slate-700">{{ $video->slug }}</code>
                    </p>
                </div>

                <div>
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="10" class="input">{{ old('description', $video->description) }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Right: classification + status controls --}}
        <div class="space-y-6">
            <div class="card p-5 space-y-4">
                <h3 class="text-sm font-medium text-slate-900">Classification</h3>

                <div>
                    <label for="category_id" class="label">Category</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                    @selected((int) old('category_id', $video->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="visibility" class="label">Visibility</label>
                    <select id="visibility" name="visibility" class="input" required>
                        <option value="public"  @selected(old('visibility', $video->visibility) === 'public')>Public</option>
                        <option value="private" @selected(old('visibility', $video->visibility) === 'private')>Private</option>
                    </select>
                </div>

                <div>
                    <label for="display_order" class="label">Display order</label>
                    <input id="display_order" name="display_order" type="number" min="0" max="100000"
                           value="{{ old('display_order', $video->display_order) }}" class="input">
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <h3 class="text-sm font-medium text-slate-900">Placement</h3>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1"
                           @checked(old('is_featured', $video->is_featured))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Featured video
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_daily_focus" value="1"
                           @checked(old('is_daily_focus', $video->is_daily_focus))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Today's Focus
                </label>
            </div>

            <div class="card p-5 space-y-3">
                <h3 class="text-sm font-medium text-slate-900">Status</h3>
                <div class="flex items-center gap-2">
                    @php
                        $tone = [
                            'draft'     => 'bg-slate-100 text-slate-700',
                            'published' => 'bg-emerald-100 text-emerald-700',
                            'archived'  => 'bg-amber-100 text-amber-700',
                        ][$video->status] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <span class="badge {{ $tone }}">{{ ucfirst($video->status) }}</span>
                    @if ($video->published_at)
                        <span class="text-xs text-slate-500">since {{ $video->published_at->diffForHumans() }}</span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    @if ($video->status !== 'published')
                        <form method="POST" action="{{ route('admin.videos.publish', $video) }}">
                            @csrf
                            <button class="btn-primary">Publish</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.videos.unpublish', $video) }}">
                            @csrf
                            <button class="btn-ghost">Move to drafts</button>
                        </form>
                    @endif

                    @if ($video->status !== 'archived')
                        <form method="POST" action="{{ route('admin.videos.archive', $video) }}">
                            @csrf
                            <button class="btn-ghost">Archive</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.videos.restore', $video) }}">
                            @csrf
                            <button class="btn-ghost">Restore</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between gap-2">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('admin.videos.index') }}" class="btn-ghost">Back</a>
            </div>

            <div class="card p-5 border-red-200">
                <h3 class="text-sm font-medium text-red-700">Danger zone</h3>
                <p class="mt-1 text-xs text-slate-600">
                    Deleting removes the video, its watch progress and watchlist entries
                    for all users. This cannot be undone.
                </p>
                <form method="POST" action="{{ route('admin.videos.destroy', $video) }}"
                      class="mt-3"
                      onsubmit="return confirm('Permanently delete this video? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger">Delete video</button>
                </form>
            </div>
        </div>
    </form>
@endsection