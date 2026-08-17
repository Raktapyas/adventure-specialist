<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class PruneRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:prune-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Role-resource permissions so they never appear on the Shield checklist';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = Permission::query()
            ->where('name', 'like', '%_role')
            ->delete();

        $this->info("Deleted {$deleted} role permission(s).");

        return self::SUCCESS;
    }
}
