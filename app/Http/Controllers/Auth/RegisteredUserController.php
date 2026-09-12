<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            return User::create([
                'name'     => $request->validated('name'),
                'email'    => $request->validated('email'),
                'password' => $request->validated('password'),
                'role'     => User::ROLE_USER,
                'status'   => User::STATUS_ACTIVE,
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home')
            ->with('status', 'Welcome to FocusedTube!');
    }
}