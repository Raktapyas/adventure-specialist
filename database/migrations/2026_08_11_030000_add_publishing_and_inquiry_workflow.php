<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Additive hardening only: publishing states, inquiry workflow columns,
     * and the indexes the public queries actually use. Existing rows are
     * left untouched (all new columns default to current behavior).
     */
    public function up(): void
    {
        foreach (['pages', 'services', 'destinations', 'packages'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('is_published')->default(true);
                $table->index('is_published');
            });
        }

        Schema::table('inquiries', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
            $table->string('status', 20)->default('new');
            $table->index('is_read');
            $table->index('status');
        });

        foreach (['pages', 'services', 'destinations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->index('parent_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['pages', 'services', 'destinations', 'packages'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['is_published']);
                $table->dropColumn('is_published');
            });
        }

        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['is_read']);
            $table->dropIndex(['status']);
            $table->dropColumn('status');
            $table->dropColumn('is_read');
        });

        foreach (['pages', 'services', 'destinations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['parent_id']);
            });
        }
    }
};
