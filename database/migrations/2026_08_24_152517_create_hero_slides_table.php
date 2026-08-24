<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hero slides seeded with the three slides that were previously
     * hardcoded in the $heroSlides array in home.blade.php.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('lede')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->string('effect')->default('animate-hero-zoom-in');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        DB::table('hero_slides')->insert([
            [
                'image_path' => '/assets/images/banners/1.jpg',
                'eyebrow' => 'Adventure Specialist Travel Pvt. Ltd.',
                'title' => 'The Himalayas, thoughtfully arranged.',
                'lede' => 'Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar.',
                'primary_cta_label' => 'Explore Services',
                'primary_cta_url' => '/ast-services/',
                'secondary_cta_label' => 'Plan a Trip',
                'secondary_cta_url' => '/contact/#enquiry',
                'effect' => 'animate-hero-zoom-in',
                'sort_order' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image_path' => '/assets/images/banners/2.jpg',
                'eyebrow' => 'Where we go',
                'title' => 'Five countries, one standard of care.',
                'lede' => 'From the Kathmandu Valley to the roof of the world — culture, adventure and wildlife.',
                'primary_cta_label' => 'Explore Destinations',
                'primary_cta_url' => '/destination/',
                'secondary_cta_label' => 'Plan a Trip',
                'secondary_cta_url' => '/contact/#enquiry',
                'effect' => 'animate-hero-pan-right',
                'sort_order' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image_path' => '/assets/images/banners/3.jpg',
                'eyebrow' => 'Signature programs',
                'title' => 'Treks and tours, arranged around you.',
                'lede' => 'Curated special packages for groups and individuals across the Himalaya.',
                'primary_cta_label' => 'View Packages',
                'primary_cta_url' => '/special-package/',
                'secondary_cta_label' => 'Plan a Trip',
                'secondary_cta_url' => '/contact/#enquiry',
                'effect' => 'animate-hero-zoom-out',
                'sort_order' => 3,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
