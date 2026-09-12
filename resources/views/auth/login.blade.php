@extends('layouts.app')

@section('title', 'Login — FocusedTube')

@section('content')
<div class="mx-auto flex max-w-md flex-col px-4 py-10">
    <h1 class="text-2xl font-semibold text-slate-900">Sign in</h1>
    <p class="mt-1 text-sm text-slate-600">Welcome back. Let's get focused.</p>

    <div class="card mt-6 p-6">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email"
                       value="{{ old('email') }}" required autofocus class="input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="current-password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-brand-600 hover:underline">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-slate-600">
        No account?
        <a href="{{ route('register') }}" class="text-brand-600 hover:underline">Create one</a>
    </p>
</div>
@endsection