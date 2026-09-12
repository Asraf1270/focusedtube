@extends('layouts.app')

@section('title', 'Confirm password — FocusedTube')

@section('content')
<div class="mx-auto max-w-md px-4 py-10">
    <div class="card p-6">
        <h1 class="text-xl font-semibold">Confirm your password</h1>
        <p class="mt-2 text-sm text-slate-600">This is a secure area. Please confirm before continuing.</p>

        <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required class="input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="btn-primary w-full">Confirm</button>
        </form>
    </div>
</div>
@endsection