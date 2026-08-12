<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Link the "Alo" page (slug "Alo-sir") under the About Us page so it
     * appears in the public About Us navigation. Alo was created as a
     * top-level page (parent_id = null) even though it is an About Us child
     * living at /about-us/Alo-sir/.
     *
     * Additive and idempotent: it only updates when the expected record is
     * found with no parent yet, and it never touches any other page.
     */
    public function up(): void
    {
        $aboutId = DB::table('pages')->where('slug', 'about')->value('id');
        $alo = DB::table('pages')->where('slug', 'Alo-sir')->first(['id', 'parent_id']);

        if ($aboutId === null || $alo === null || $alo->parent_id !== null) {
            return;
        }

        DB::table('pages')->where('id', $alo->id)->update(['parent_id' => $aboutId]);
    }

    /**
     * Reverse: detach Alo from About Us (restore top-level) only when it is
     * currently linked to the About Us page.
     */
    public function down(): void
    {
        $aboutId = DB::table('pages')->where('slug', 'about')->value('id');

        if ($aboutId === null) {
            return;
        }

        DB::table('pages')
            ->where('slug', 'Alo-sir')
            ->where('parent_id', $aboutId)
            ->update(['parent_id' => null]);
    }
};
