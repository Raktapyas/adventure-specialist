<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed a default admin account so the Filament panel is reachable.
     *
     * Credentials come from the environment (ADMIN_EMAIL / ADMIN_PASSWORD)
     * and fall back to a local-only default. Idempotent: an existing user
     * with the same email is left untouched.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password123');

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_admin' => true,
                'role' => 'admin',
            ]
        )->assignRole('super_admin');
    }
}
