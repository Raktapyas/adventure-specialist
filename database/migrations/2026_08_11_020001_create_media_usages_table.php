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
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('model_type', 120);
            $table->unsignedBigInteger('model_id');
            $table->string('field', 60);
            $table->timestamps();

            $table->unique(['media_id', 'model_type', 'model_id', 'field'], 'media_usage_unique');
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
