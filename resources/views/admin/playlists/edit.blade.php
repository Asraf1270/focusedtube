@extends('layouts.admin')

@section('title', "Edit — {$playlist->title}")
@section('heading', 'Edit Playlist')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Left: details form --}}
        <div class="lg:col-span-1">
            <form method="POST" action="{{ route('admin.playlists.update', $playlist) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                @include('admin.playlists._form', ['playlist' => $playlist])
                <div class="flex items-center gap-2">
                    <button class="btn-primary">Save</button>
                    <a href="{{ route('admin.playlists.index') }}" class="btn-ghost">Back</a>
                </div>
            </form>

            <div class="card p-5 mt-6 border-red-200">
                <h3 class="text-sm font-medium text-red-700">Danger zone</h3>
                <p class="mt-1 text-xs text-slate-600">Deleting the playlist removes the video associations, but not the videos themselves.</p>
                <form method="POST" action="{{ route('admin.playlists.destroy', $playlist) }}" class="mt-3"
                      onsubmit="return confirm('Delete this playlist?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger">Delete playlist</button>
                </form>
            </div>
        </div>

        {{-- Right: video management --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-5">
                <h3 class="text-sm font-medium text-slate-900">Add a video</h3>
                <form method="POST" action="{{ route('admin.playlists.videos.attach', $playlist) }}"
                      class="mt-3 flex items-end gap-2">
                    @csrf
                    <div class="flex-1">
                        <label for="video_id" class="label">Video</label>
                        <select id="video_id" name="video_id" required class="input">
                            <option value="">Select a video…</option>
                            @foreach ($candidateVideos as $v)
                                <option value="{{ $v->id }}">{{ $v->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn-primary">Add</button>
                </form>
                @if ($candidateVideos->isEmpty())
                    <p class="mt-2 text-xs text-slate-500">Every video is already in this playlist (or none exist yet).</p>
                @endif
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-900">
                        Videos in this playlist ({{ $playlist->videos->count() }})
                    </h3>
                    <button type="button" id="save-order" class="btn-ghost text-brand-600 hidden">
                        Save order
                    </button>
                </div>

                @if ($playlist->videos->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">No videos yet. Add one above.</p>
                @else
                    <form method="POST" action="{{ route('admin.playlists.reorder', $playlist) }}" id="reorder-form">
                        @csrf
                        <ul id="playlist-videos" class="mt-3 divide-y divide-slate-100">
                            @foreach ($playlist->videos as $video)
                                <li class="flex items-center gap-3 py-2" draggable="true" data-id="{{ $video->id }}">
                                    <span class="cursor-move select-none text-slate-400" aria-hidden="true">⋮⋮</span>
                                    <img src="{{ $video->thumbnail_url }}" alt=""
                                         class="h-10 w-16 flex-none rounded object-cover ring-1 ring-slate-200">
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-medium text-slate-900">{{ $video->title }}</div>
                                        <div class="truncate text-xs text-slate-500">
                                            {{ $video->category?->name ?: 'Uncategorized' }}
                                        </div>
                                    </div>
                                    <input type="hidden" name="ids[]" value="{{ $video->id }}">
                                    <button type="submit"
                                            form="detach-{{ $video->id }}"
                                            class="text-red-600 hover:underline">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="ids[]" disabled> {{-- placeholder to keep validation happy if empty --}}
                    </form>

                    {{-- Separate delete forms — one per video --}}
                    @foreach ($playlist->videos as $video)
                        <form id="detach-{{ $video->id }}" method="POST"
                              action="{{ route('admin.playlists.videos.detach', [$playlist, $video]) }}"
                              class="hidden"
                              onsubmit="return confirm('Remove this video from the playlist?');">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list     = document.getElementById('playlist-videos');
    const saveBtn  = document.getElementById('save-order');
    const reorder  = document.getElementById('reorder-form');
    if (! list || ! reorder) return;

    let dragged = null;

    list.querySelectorAll('li').forEach(function (li) {
        li.addEventListener('dragstart', function (e) {
            dragged = li;
            e.dataTransfer.effectAllowed = 'move';
            li.classList.add('opacity-50');
        });
        li.addEventListener('dragend', function () {
            dragged?.classList.remove('opacity-50');
            dragged = null;
        });
        li.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (! dragged || dragged === li) return;
            const rect = li.getBoundingClientRect();
            const after = (e.clientY - rect.top) > rect.height / 2;
            li.parentNode.insertBefore(dragged, after ? li.nextSibling : li);
            syncInputs();
            saveBtn.classList.remove('hidden');
        });
    });

    function syncInputs() {
        const inputs = reorder.querySelectorAll('input[name="ids[]"]:not([disabled])');
        inputs.forEach(function (input, i) {
            input.value = list.children[i].dataset.id;
        });
    }

    saveBtn.addEventListener('click', function () {
        reorder.submit();
    });

    syncInputs();
});
</script>
@endpush