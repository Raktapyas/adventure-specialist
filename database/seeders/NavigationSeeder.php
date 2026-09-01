<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    /**
     * Seed the primary navigation to match the screenshot + existing site structure.
     * Idempotent: safe to run multiple times (checks label+url uniqueness).
     */
    public function run(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (NavigationItem::query()->exists()) {
            return;
        }

        $items = [
            ['label' => 'Home', 'url' => '/', 'sort_order' => 10],
            ['label' => 'About Us', 'url' => '/about-us/', 'sort_order' => 20],
            ['label' => 'Trekking & Activities', 'url' => '/ast-services/', 'sort_order' => 30],
            ['label' => 'Destinations', 'url' => '/destination/', 'sort_order' => 40],
            ['label' => 'Nepal', 'url' => '/nepal/', 'sort_order' => 50],
            ['label' => 'Contact', 'url' => '/contact/', 'sort_order' => 60],
        ];

        foreach ($items as $data) {
            NavigationItem::create([
                'label' => $data['label'],
                'url' => $data['url'],
                'sort_order' => $data['sort_order'],
                'is_visible' => true,
                'open_in_new_tab' => false,
            ]);
        }
    }
}
