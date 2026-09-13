@extends('layouts.admin')

@section('title', 'Playlists')
@section('heading', 'Playlists')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ number_format($playlists->total()) }} playlists</p>
        <a href="{{ route('admin.playlists.create') }}" class="btn-primary">+ New Playlist</a>
    </div>

    <x-admin.filter-bar>
        <div class="flex-1">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="text" value="{{ $filters['q'] }}" class="input">
        </div>
        <div class="w-48">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">All</option>
                <option value="draft"     @selected($filters['status'] === 'draft')>Draft</option>
                <option value="published" @selected($filters['status'] === 'published')>Published</option>
                <option value="archived"  @selected($filters['status'] === 'archived')>Archived</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button class="btn-primary">Filter</button>
            <a href="{{ route('admin.playlists.index') }}" class="btn-ghost">Reset</a>
        </div>
    </x-admin.filter-bar>

    <div class="mt-4">
        @if ($playlists->isEmpty())
            <x-admin.empty-state title="No playlists yet" description="Group related videos into an ordered playlist.">
                <a href="{{ route('admin.playlists.create') }}" class="btn-primary">Create a playlist</a>
            </x-admin.empty-state>
        @else
            <x-admin.data-table :headers="['Title', 'Videos', 'Status', 'Updated', '']">
                @foreach ($playlists as $playlist)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $playlist->title }}</div>
                            <div class="text-xs text-slate-500"><code>{{ $playlist->slug }}</code></div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $playlist->videos_count }}</td>
                        <td class="px-4 py-3">
                            @php
                                $tone = [
                                    'draft'     => 'bg-slate-100 text-slate-700',
                                    'published' => 'bg-emerald-100 text-emerald-700',
                                    'archived'  => 'bg-amber-100 text-amber-700',
                                ][$playlist->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="badge {{ $tone }}">{{ ucfirst($playlist->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $playlist->updated_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.playlists.edit', $playlist) }}" class="text-brand-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>

            <div class="mt-4">{{ $playlists->links() }}</div>
        @endif
    </div>
@endsection