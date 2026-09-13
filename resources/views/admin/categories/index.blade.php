@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Categories')

@section('breadcrumbs')
    <span class="text-slate-400">Admin</span><span class="px-2">/</span>
    <span class="text-slate-700">Categories</span>
@endsection

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ number_format($categories->total()) }} categories</p>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ New Category</a>
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
                <option value="active"   @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button class="btn-primary">Filter</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Reset</a>
        </div>
    </x-admin.filter-bar>

    <div class="mt-4">
        @if ($categories->isEmpty())
            <x-admin.empty-state title="No categories yet" description="Create your first category to organize videos.">
                <a href="{{ route('admin.categories.create') }}" class="btn-primary">Create a category</a>
            </x-admin.empty-state>
        @else
            <x-admin.data-table :headers="['', 'Name', 'Slug', 'Videos', 'Order', 'Status', '']">
                @foreach ($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-center text-xl">{{ $category->icon ?: '·' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-slate-500"><code>{{ $category->slug }}</code></td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->videos_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $category->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-brand-600 hover:underline">Edit</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  class="inline"
                                  onsubmit="return confirm('Delete this category? Its videos will become uncategorized.');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>

            <div class="mt-4">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection