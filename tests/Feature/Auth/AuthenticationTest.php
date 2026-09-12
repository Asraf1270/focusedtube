<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('users can authenticate', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
});

test('users cannot authenticate with wrong password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('suspended users cannot log in', function () {
    $user = User::factory()->suspended()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login is rate limited after five attempts', function () {
    RateLimiter::clear('');

    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');
});

test('users can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('suspended users are logged out on next request', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $user->update(['status' => User::STATUS_SUSPENDED]);

    $this->get('/profile')->assertRedirect(route('login'));
    $this->assertGuest();
});