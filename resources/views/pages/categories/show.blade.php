@extends('layouts.app')

@section('meta')
    <x-seo
        :title="$category->name"
        :description="$category->description ?: 'Videos in '.$category->name.' on FocusedTube.'" />
@endsection

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10">

        <nav class="text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <span class="px-2">/</span>
            <a href="{{ route('categories.index') }}" class="hover:underline">Categories</a>
            <span class="px-2">/</span>
            <span class="text-slate-700">{{ $category->name }}</span>
        </nav>

        <header class="mt-4">
            <h1 class="text-2xl font-semibold text-slate-900 md:text-3xl">
                {{ $category->icon ? $category->icon.' ' : '' }}{{ $category->name }}
            </h1>
            @if ($category->description)
                <p class="mt-2 max-w-2xl text-slate-600">{{ $category->description }}</p>
            @endif
            <p class="mt-2 text-sm text-slate-500">{{ $videos->total() }} videos</p>
        </header>

        <div class="mt-8">
            @if ($videos->isEmpty())
                <x-empty
                    title="No videos in this category yet"
                    description="Check back soon — the admin is curating." />
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($videos as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>

                <div class="mt-6">{{ $videos->links() }}</div>
            @endif
        </div>
    </div>
@endsection