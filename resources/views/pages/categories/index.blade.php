@extends('layouts.app')

@section('meta')
    <x-seo title="Categories" description="Browse FocusedTube videos by category." />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">Categories</h1>
        <p class="mt-2 text-slate-600">Pick a topic and dive in.</p>

        @if ($categories->isEmpty())
            <div class="mt-8">
                <x-empty title="No categories yet"
                         description="Categories appear here once an admin adds them." />
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <x-category-card :category="$category" />
                @endforeach
            </div>
        @endif
    </div>
@endsection