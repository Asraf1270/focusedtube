@extends('layouts.admin')

@section('title', 'New Category')
@section('heading', 'New Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" class="max-w-2xl space-y-6">
        @csrf
        @include('admin.categories._form', ['category' => $category])
        <div class="flex items-center gap-2">
            <button class="btn-primary">Create category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection