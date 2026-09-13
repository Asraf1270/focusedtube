@extends('layouts.admin')

@section('title', 'Videos')
@section('heading', 'Videos')

@section('breadcrumbs')
    <span class="text-slate-400">Admin</span>
    <span class="px-2">/</span>
    <span class="text-slate-700">Videos</span>
@endsection

@section('content')
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
            {{ number_format($videos->total()) }} video{{ $videos->total() === 1 ? '' : 's' }}
        </p>
        <a href="{{ route('admin.videos.create') }}" class="btn-primary">
            + Add Video
        </a>
    </div>

    <x-admin.filter-bar>
        <div class="flex-1">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="text" value="{{ $filters['q'] }}"
                   placeholder="Title, channel, description…" class="input">
        </div>

        <div class="w-full sm:w-48">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">All</option>
                @foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-56">
            <label for="category" class="label">Category</label>
            <select id="category" name="category" class="input">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.videos.index') }}" class="btn-ghost">Reset</a>
        </div>
    </x-admin.filter-bar>

    <div class="mt-4">
        @if ($videos->isEmpty())
            <x-admin.empty-state
                title="No videos yet"
                description="Add a YouTube URL to bring your first video into FocusedTube."
            >
                <a href="{{ route('admin.videos.create') }}" class="btn-primary">Add your first video</a>
            </x-admin.empty-state>
        @else
        {{-- before the <x-admin.data-table>, add the bulk bar --}}
<form method="POST" action="{{ route('admin.videos.bulk') }}" id="bulk-form" class="hidden mb-3">
    @csrf
    <div class="card flex flex-wrap items-center gap-3 px-4 py-3">
        <span class="text-sm text-slate-600">
            <span id="bulk-count">0</span> selected
        </span>
        <select name="action" required class="input w-auto">
            <option value="">Choose action…</option>
            <option value="publish">Publish</option>
            <option value="unpublish">Move to drafts</option>
            <option value="feature">Mark featured</option>
            <option value="unfeature">Remove featured</option>
            <option value="archive">Archive</option>
            <option value="delete">Delete</option>
        </select>
        <button type="submit" class="btn-primary"
                onclick="return confirm('Apply this action to the selected videos?');">
            Apply
        </button>
    </div>
</form>
            <x-admin.data-table :headers="[
    '' => true, // checkbox column header placeholder
    'Title', 'Channel', 'Category', 'Status', 'Views', 'Updated', ''
]">
    @foreach ($videos as $video)
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
                <input type="checkbox" form="bulk-form" name="ids[]" value="{{ $video->id }}"
                       class="row-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            </td>
            <td class="px-4 py-3">
                {{-- existing title cell --}}
                ...
            </td>
            {{-- channel / category / status / views / updated as before --}}
            <td class="px-4 py-3 text-right whitespace-nowrap">
                <a href="{{ route('admin.videos.edit', $video) }}"
                   class="text-brand-600 hover:underline">Edit</a>

                <span class="mx-1 text-slate-300">|</span>

                @if ($video->status !== 'published')
                    <form method="POST" action="{{ route('admin.videos.publish', $video) }}" class="inline">
                        @csrf
                        <button class="text-emerald-600 hover:underline">Publish</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.videos.unpublish', $video) }}" class="inline">
                        @csrf
                        <button class="text-slate-600 hover:underline">Unpublish</button>
                    </form>
                @endif

                <span class="mx-1 text-slate-300">|</span>

                @if ($video->status !== 'archived')
                    <form method="POST" action="{{ route('admin.videos.archive', $video) }}" class="inline">
                        @csrf
                        <button class="text-amber-600 hover:underline">Archive</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.videos.restore', $video) }}" class="inline">
                        @csrf
                        <button class="text-slate-600 hover:underline">Restore</button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach
</x-admin.data-table>

            <div class="mt-4">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulkForm   = document.getElementById('bulk-form');
        const bulkCount  = document.getElementById('bulk-count');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        if (! bulkForm) return;

        function refresh() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if (bulkCount) bulkCount.textContent = checked;
            bulkForm.classList.toggle('hidden', checked === 0);
        }

        checkboxes.forEach(cb => cb.addEventListener('change', refresh));
        refresh();
    });
</script>
@endpush