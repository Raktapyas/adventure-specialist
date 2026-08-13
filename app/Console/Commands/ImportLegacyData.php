<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportLegacyData extends Command
{
    protected $signature = 'import:legacy-data
        {--source=sqlite : Connection to read the legacy data from}
        {--target=mysql : Connection to write the data into}';

    protected $description = 'Import legacy content data from the SQLite database into the MySQL database';

    /**
     * Content tables imported in FK-safe order (parents before children).
     *
     * Ephemeral tables (sessions, cache, cache_locks, job_batches, jobs,
     * failed_jobs, password_reset_tokens, migrations) are intentionally left out.
     */
    protected array $tables = [
        'users',
        'pages',
        'services',
        'destinations',
        'packages',
        'gallery_images',
        'inquiries',
        'redirects',
        'media',
        'media_usages',
    ];

    public function handle(): int
    {
        $source = $this->option('source');
        $target = $this->option('target');

        if ($source === 'sqlite') {
            config()->set('database.connections.sqlite.database', database_path('database.sqlite'));
            DB::purge('sqlite');
        }

        $this->truncateTarget($target);

        foreach ($this->tables as $table) {
            $this->importTable($table, $source, $target);
        }

        $this->syncAutoIncrement($target);
        $this->info('Legacy data imported successfully.');

        return self::SUCCESS;
    }

    /**
     * Empty the content tables in reverse FK order so constraints never block the truncate.
     */
    private function truncateTarget(string $target): void
    {
        Schema::connection($target)->disableForeignKeyConstraints();

        foreach (array_reverse($this->tables) as $table) {
            if (Schema::connection($target)->hasTable($table)) {
                DB::connection($target)->table($table)->truncate();
            }
        }

        Schema::connection($target)->enableForeignKeyConstraints();
    }

    /**
     * Copy every row from the source connection into the target connection,
     * preserving the original IDs so foreign keys stay intact.
     */
    private function importTable(string $table, string $source, string $target): void
    {
        if (! Schema::connection($source)->hasTable($table)) {
            $this->warn("Skipping {$table}: not present in the source.");

            return;
        }

        if (! Schema::connection($target)->hasTable($table)) {
            $this->warn("Skipping {$table}: not present in the target.");

            return;
        }

        $rows = DB::connection($source)
            ->table($table)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        if ($rows !== []) {
            DB::connection($target)->table($table)->insert($rows);
        }

        $this->info("Imported {$table}: ".count($rows).' rows.');
    }

    /**
     * Advance MySQL AUTO_INCREMENT past the highest imported ID so new records
     * never collide with the preserved legacy IDs.
     */
    private function syncAutoIncrement(string $target): void
    {
        $connection = DB::connection($target);

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ($this->tables as $table) {
            if (! Schema::connection($target)->hasTable($table)) {
                continue;
            }

            $maxId = $connection->table($table)->max('id');

            if ($maxId !== null) {
                $connection->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = ".((int) $maxId + 1));
            }
        }
    }
}
