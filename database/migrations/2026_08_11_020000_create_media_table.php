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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('path', 500)->unique();
            $table->string('disk', 20)->default('public')->nullable();
            $table->string('storage_path', 500)->nullable();
            $table->string('mime_type', 120);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_legacy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_legacy');
            $table->index('extension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
