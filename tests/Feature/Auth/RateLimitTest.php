<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('login throttles after 5 failed attempts', function () {
    RateLimiter::clear('');

    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
    }

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
    $response->assertSessionHasErrors('email');
});

test('forgot password throttles after 3 requests per minute', function () {
    RateLimiter::clear('');

    for ($i = 0; $i < 3; $i++) {
        $this->post('/forgot-password', ['email' => 'anyone@example.com']);
    }

    $this->post('/forgot-password', ['email' => 'anyone@example.com'])
        ->assertStatus(429);
});