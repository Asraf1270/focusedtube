<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LockAdminCommand extends Command
{
    protected $signature = 'focus:lock-admin
                            {--email=admin@focusedtube.test : The admin email to lock}
                            {--password= : Optional password (random if not provided)}';

    protected $description = 'Rotate the seeded admin password (or create one if missing) and require a strong password.';

    public function handle(): int
    {
        $email = (string) $this->option('email');

        $admin = User::where('email', $email)->first();

        $password = (string) ($this->option('password') ?: Str::password(20, letters: true, numbers: true, symbols: true));

        if (! $admin) {
            $admin = new User([
                'name'     => 'FocusedTube Admin',
                'email'    => $email,
                'role'     => User::ROLE_ADMIN,
                'status'   => User::STATUS_ACTIVE,
                'password' => Hash::make($password),
            ]);
            $admin->save();

            $this->info('Created admin user.');
        } else {
            $admin->forceFill(['password' => Hash::make($password)])->save();
            $this->info('Rotated password.');
        }

        $this->line('');
        $this->line('  Email:    <fg=yellow>'.$email.'</>');
        $this->line('  Password: <fg=yellow>'.$password.'</>');
        $this->line('');
        $this->warn('  Store this password now. It will not be shown again.');
        $this->warn('  Log in and change it immediately from the profile page.');

        return self::SUCCESS;
    }
}