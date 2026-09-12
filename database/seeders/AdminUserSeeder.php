<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@focusedtube.test'],
            [
                'name'     => 'FocusedTube Admin',
                'password' => Hash::make('password'),
                'role'     => User::ROLE_ADMIN,
                'status'   => User::STATUS_ACTIVE,
            ]
        );
    }
}