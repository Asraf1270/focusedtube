@extends('layouts.app')

@section('title', 'Reset password — FocusedTube')

@section('content')
<div class="mx-auto flex max-w-md flex-col px-4 py-10">
    <h1 class="text-2xl font-semibold text-slate-900">Reset your password</h1>
    <p class="mt-1 text-sm text-slate-600">
        Enter your email and we'll send you a reset link.
    </p>

    <div class="card mt-6 p-6">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" required autofocus class="input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Email reset link</button>
        </form>
    </div>
</div>
@endsection