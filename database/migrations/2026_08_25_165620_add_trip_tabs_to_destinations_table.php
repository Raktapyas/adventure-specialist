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
        Schema::table('destinations', function (Blueprint $table) {
            $table->longText('itinerary')->nullable()->after('content');
            $table->longText('includes')->nullable()->after('itinerary');
            $table->longText('excludes')->nullable()->after('includes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['itinerary', 'includes', 'excludes']);
        });
    }
};
