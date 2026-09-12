@extends('layouts.app')

@section('title', 'Verify your email — FocusedTube')

@section('content')
<div class="mx-auto max-w-md px-4 py-10">
    <div class="card p-6">
        <h1 class="text-xl font-semibold">Verify your email</h1>
        <p class="mt-2 text-sm text-slate-600">
            We sent a verification link to your inbox. Please verify your email to continue.
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="mt-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                A new verification link has been sent.
            </div>
        @endif

        <div class="mt-6 flex items-center gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn-primary">Resend email</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-ghost">Logout</button>
            </form>
        </div>
    </div>
</div>
@endsection