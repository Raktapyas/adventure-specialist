<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('homepage_services_eyebrow')->nullable();
            $table->string('homepage_services_title')->nullable();
            $table->string('homepage_services_lede')->nullable();
            $table->boolean('homepage_services_visible')->default(true);
            $table->string('homepage_destinations_eyebrow')->nullable();
            $table->string('homepage_destinations_title')->nullable();
            $table->string('homepage_destinations_lede')->nullable();
            $table->boolean('homepage_destinations_visible')->default(true);
            $table->string('homepage_packages_eyebrow')->nullable();
            $table->string('homepage_packages_title')->nullable();
            $table->string('homepage_packages_lede')->nullable();
            $table->boolean('homepage_packages_visible')->default(true);
            $table->string('homepage_gallery_eyebrow')->nullable();
            $table->string('homepage_gallery_title')->nullable();
            $table->string('homepage_gallery_lede')->nullable();
            $table->boolean('homepage_gallery_visible')->default(true);
            $table->string('homepage_why_eyebrow')->nullable();
            $table->string('homepage_why_title')->nullable();
            $table->string('homepage_why_lede')->nullable();
            $table->boolean('homepage_why_visible')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'homepage_services_eyebrow',
                'homepage_services_title',
                'homepage_services_lede',
                'homepage_services_visible',
                'homepage_destinations_eyebrow',
                'homepage_destinations_title',
                'homepage_destinations_lede',
                'homepage_destinations_visible',
                'homepage_packages_eyebrow',
                'homepage_packages_title',
                'homepage_packages_lede',
                'homepage_packages_visible',
                'homepage_gallery_eyebrow',
                'homepage_gallery_title',
                'homepage_gallery_lede',
                'homepage_gallery_visible',
                'homepage_why_eyebrow',
                'homepage_why_title',
                'homepage_why_lede',
                'homepage_why_visible',
            ]);
        });
    }
};
