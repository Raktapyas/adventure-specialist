<?php

use App\Models\NavigationItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (Schema::hasTable('navigation_items') && NavigationItem::query()->count() === 0) {
            DB::table('navigation_items')->insert([
                ['label' => 'Home', 'url' => '/', 'sort_order' => 10, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'About Us', 'url' => '/about-us/', 'sort_order' => 20, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Trekking & Activities', 'url' => '/ast-services/', 'sort_order' => 30, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Destinations', 'url' => '/destination/', 'sort_order' => 40, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Nepal', 'url' => '/nepal/', 'sort_order' => 50, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Contact', 'url' => '/contact/', 'sort_order' => 60, 'is_visible' => true, 'open_in_new_tab' => false, 'parent_id' => null, 'type' => 'custom', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
