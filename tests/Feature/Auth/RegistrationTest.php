<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $this->get('/register')->assertOk();
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));

    $user = User::where('email', 'ada@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(User::ROLE_USER)
        ->and($user->status)->toBe(User::STATUS_ACTIVE);
});

test('registration rejects weak passwords', function () {
    $this->post('/register', [
        'name'                  => 'Weak',
        'email'                 => 'weak@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->post('/register', [
        'name'                  => 'Dup',
        'email'                 => 'dup@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});