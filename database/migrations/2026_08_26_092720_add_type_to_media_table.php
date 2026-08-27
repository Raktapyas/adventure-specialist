<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the media kind (image / video) derived from the sniffed MIME type.
     *
     * Existing rows are backfilled in place: everything that is not a video
     * stays an image, so no media record is touched beyond gaining its type.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('type', 10)->default('image')->after('mime_type');

            $table->index('type');
        });

        DB::table('media')
            ->where('mime_type', 'like', 'video/%')
            ->update(['type' => 'video']);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
