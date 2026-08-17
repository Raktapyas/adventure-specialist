<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PageSeeder::class,
            ServiceSeeder::class,
            DestinationSeeder::class,
            PackageSeeder::class,
            GallerySeeder::class,
            SyncLegacyMediaSeeder::class,
        ]);
    }
}
