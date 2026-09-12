<?php

use App\Models\User;

test('non-admin users cannot access admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('guests cannot access admin routes', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('admin users can access admin routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});