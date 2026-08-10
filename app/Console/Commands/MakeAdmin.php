<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email} {--name=Administrator} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create (or promote) a user to an administrator account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $password = $this->option('password') ?: Str::password(16);

        $user = User::firstOrNew(['email' => $email], [
            'name' => $name,
            'password' => Hash::make($password),
        ]);

        $user->is_admin = true;
        $user->save();

        $this->info("Admin account ready: {$user->email}");

        if (! $this->option('password')) {
            $this->warn("Password: {$password}");
        }

        return self::SUCCESS;
    }
}
