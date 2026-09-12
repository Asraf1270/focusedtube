@extends('layouts.app')

@section('title', 'Create account — FocusedTube')

@section('content')
<div class="mx-auto flex max-w-md flex-col px-4 py-10">
    <h1 class="text-2xl font-semibold text-slate-900">Create your account</h1>
    <p class="mt-1 text-sm text-slate-600">Focused learning, on your terms.</p>

    <div class="card mt-6 p-6">
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text" autocomplete="name"
                       value="{{ old('name') }}" required autofocus class="input">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email"
                       value="{{ old('email') }}" required class="input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       autocomplete="new-password" required class="input">
            </div>

            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-slate-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Sign in</a>
    </p>
</div>
@endsection