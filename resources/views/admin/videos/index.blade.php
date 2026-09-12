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
            <x-admin.data-table :headers="['Title', 'Channel', 'Category', 'Status', 'Views', 'Updated', '']">
                @foreach ($videos as $video)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $video->thumbnail_url }}"
                                     alt=""
                                     class="h-10 w-16 flex-none rounded object-cover ring-1 ring-slate-200"
                                     loading="lazy">
                                <div class="min-w-0">
                                    <div class="truncate font-medium text-slate-900">{{ $video->title }}</div>
                                    <div class="truncate text-xs text-slate-500">{{ $video->youtube_video_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $video->channel_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $video->category?->name ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $tone = [
                                    'draft'     => 'bg-slate-100 text-slate-700',
                                    'published' => 'bg-emerald-100 text-emerald-700',
                                    'archived'  => 'bg-amber-100 text-amber-700',
                                ][$video->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="badge {{ $tone }}">{{ ucfirst($video->status) }}</span>
                            @if ($video->is_featured)
                                <span class="badge bg-brand-100 text-brand-700 ml-1">Featured</span>
                            @endif
                            @if ($video->is_daily_focus)
                                <span class="badge bg-purple-100 text-purple-700 ml-1">Focus</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($video->views_count) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $video->updated_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="#" class="text-brand-600 hover:underline">Edit</a>
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