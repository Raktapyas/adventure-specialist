<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton row seeded with the values that were previously hardcoded
     * in HomeController / home.blade.php / footer.blade.php.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->json('stats')->nullable();
            $table->string('cta_eyebrow')->nullable();
            $table->string('cta_title')->nullable();
            $table->string('cta_button_label')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->string('cta_image')->nullable();
            $table->string('contact_company')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('contact_phone_primary')->nullable();
            $table->string('contact_phone_secondary')->nullable();
            $table->string('contact_phone_owner')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_hours')->nullable();
            $table->string('contact_facebook_url')->nullable();
            $table->timestamps();
        });

        DB::table('site_settings')->insert([
            'stats' => json_encode([
                ['value' => 2013, 'suffix' => '', 'label' => 'Established Year'],
                ['value' => 1200, 'suffix' => '+', 'label' => 'Total Trekking'],
                ['value' => 1600, 'suffix' => '+', 'label' => 'Happy Trekkers'],
            ]),
            'cta_eyebrow' => 'Ready when you are',
            'cta_title' => 'Let us arrange your Himalayan holiday.',
            'cta_button_label' => 'Plan a Trip',
            'cta_button_url' => '/contact/#enquiry',
            'cta_image' => '/assets/images/banners/3.jpg',
            'contact_company' => 'ADVENTURE SPECIALIST TRAVEL',
            'contact_address' => 'Bungamati, Lalitpur, Nepal',
            'contact_phone_primary' => '+977 1 5173283',
            'contact_phone_secondary' => '+977 9851024546',
            'contact_phone_owner' => 'Raj K. Shrestha',
            'contact_email' => 'adventurespecialisttravel@gmail.com',
            'contact_hours' => "Sun – Fri 9:00 – 16:00\nSaturday – CLOSED",
            'contact_facebook_url' => 'https://www.facebook.com/Adventure-Specialist-Travel-PvtLtd-318003508387072',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
