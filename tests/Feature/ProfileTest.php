<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee($user->name);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name'  => 'New Name',
        'email' => 'new@example.com',
    ]);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com');
});

test('changing email unverifies the account', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->patch('/profile', [
        'name'  => $user->name,
        'email' => 'changed@example.com',
    ]);

    expect($user->refresh()->email_verified_at)->toBeNull();
});

test('password can be updated with the correct current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profile/password', [
        'current_password'      => 'password',
        'password'              => 'new-password1',
        'password_confirmation' => 'new-password1',
    ]);

    expect(Hash::check('new-password1', $user->refresh()->password))->toBeTrue();
});

test('password cannot be updated with the wrong current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profile/password', [
        'current_password'      => 'wrong',
        'password'              => 'new-password1',
        'password_confirmation' => 'new-password1',
    ])->assertSessionHasErrors('current_password');
});

test('account can be deleted with the correct password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'password']);

    $this->assertGuest();
    expect(User::find($user->id))->toBeNull();
});