<?php

use App\Models\User;

test('admin dashboard is forbidden to regular users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('admin dashboard renders for admins', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Total users');
});

test('guests are redirected from admin dashboard', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});