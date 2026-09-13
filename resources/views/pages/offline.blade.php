@extends('layouts.app')

@section('meta')
    <x-seo title="Offline" />
@endsection

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <h1 class="text-2xl font-semibold text-slate-900">You're offline</h1>
        <p class="mt-3 text-slate-600">
            FocusedTube needs an internet connection to load videos.
            Your saved pages and static content are still available.
        </p>
        <button onclick="location.reload()" class="btn-primary mt-6">Try again</button>
    </div>
@endsection