@extends('layouts.admin')

@section('title', "Edit — {$category->name}")
@section('heading', 'Edit Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PATCH')
        @include('admin.categories._form', ['category' => $category])
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button class="btn-primary">Save changes</button>
                <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Back</a>
            </div>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                  onsubmit="return confirm('Delete this category? Its videos will become uncategorized.');">
                @csrf
                @method('DELETE')
                <button class="btn-danger">Delete category</button>
            </form>
        </div>
    </form>
@endsection