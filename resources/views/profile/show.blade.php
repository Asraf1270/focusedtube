@extends('layouts.app')

@section('title', 'Your profile — FocusedTube')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10 space-y-6">

    <h1 class="text-2xl font-semibold">Your profile</h1>

    @if (session('status') === 'profile-updated')
        <div class="rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">Profile updated.</div>
    @elseif (session('status') === 'password-updated')
        <div class="rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">Password updated.</div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-medium">Account details</h2>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $user->name) }}" required class="input">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}" required class="input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button class="btn-primary">Save changes</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="text-lg font-medium">Change password</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="label">Current password</label>
                <input id="current_password" name="current_password" type="password" required class="input">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">New password</label>
                <input id="password" name="password" type="password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="input">
            </div>

            <button class="btn-primary">Update password</button>
        </form>
    </div>

    <div class="card p-6 border-red-200">
        <h2 class="text-lg font-medium text-red-700">Danger zone</h2>
        <p class="mt-1 text-sm text-slate-600">Deleting your account is permanent.</p>

        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-4"
              onsubmit="return confirm('Delete your account permanently?');">
            @csrf
            @method('DELETE')

            <div>
                <label for="delete_password" class="label">Confirm with your password</label>
                <input id="delete_password" name="password" type="password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button class="btn-danger">Delete account</button>
        </form>
    </div>

</div>
@endsection