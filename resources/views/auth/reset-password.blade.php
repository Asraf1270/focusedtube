@extends('layouts.app')

@section('title', 'Set a new password — FocusedTube')

@section('content')
<div class="mx-auto flex max-w-md flex-col px-4 py-10">
    <h1 class="text-2xl font-semibold text-slate-900">Set a new password</h1>

    <div class="card mt-6 p-6">
        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $request->email) }}" required class="input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">New password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       autocomplete="new-password" required class="input">
            </div>

            <button type="submit" class="btn-primary w-full">Reset password</button>
        </form>
    </div>
</div>
@endsection