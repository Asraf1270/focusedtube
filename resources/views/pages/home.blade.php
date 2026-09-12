@extends('layouts.app')

@section('title', 'FocusedTube — Watch with focus')

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-10 md:py-16">
        <h1 class="text-3xl md:text-5xl font-semibold tracking-tight text-slate-900">
            Learn without the noise.
        </h1>
        <p class="mt-4 max-w-2xl text-slate-600">
            FocusedTube is a curated video library. Every video here was selected
            on purpose — no infinite feed, no autoplay rabbit holes.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('videos.index') }}" class="btn-primary">Browse videos</a>
            @guest
                <a href="{{ route('register') }}" class="btn-ghost">Create account</a>
            @endguest
        </div>
    </section>
@endsection