<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase as BaseTestCase;

class ImportLegacyDataTest extends BaseTestCase
{
    private string $sourceFile;

    private string $targetFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceFile = tempnam(sys_get_temp_dir(), 'legacy_source_').'.sqlite';
        $this->targetFile = tempnam(sys_get_temp_dir(), 'legacy_target_').'.sqlite';

        foreach ([$this->sourceFile, $this->targetFile] as $file) {
            file_put_contents($file, '');
        }
    }

    protected function tearDown(): void
    {
        @unlink($this->sourceFile);
        @unlink($this->targetFile);

        parent::tearDown();
    }

    public function test_it_imports_content_preserving_ids_and_relationships(): void
    {
        $this->createSchema('legacy_source', $this->sourceFile);
        $this->createSchema('legacy_target', $this->targetFile);

        DB::connection('legacy_source')->table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'hash', 'is_admin' => 1],
            ['id' => 2, 'name' => 'Guest', 'email' => 'guest@example.com', 'password' => 'hash', 'is_admin' => 0],
        ]);

        DB::connection('legacy_source')->table('pages')->insert([
            ['id' => 1, 'parent_id' => null, 'title' => 'About', 'slug' => 'about', 'sort_order' => 0, 'is_published' => 1],
            ['id' => 2, 'parent_id' => 1, 'title' => 'Child', 'slug' => 'child', 'sort_order' => 1, 'is_published' => 1],
        ]);

        DB::connection('legacy_source')->table('media')->insert([
            ['id' => 7, 'name' => 'photo.jpg', 'path' => '/assets/images/photo.jpg', 'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'size' => 1024, 'is_legacy' => 1, 'created_by' => null],
            ['id' => 9, 'name' => 'hero.png', 'path' => '/storage/hero.png', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => 2048, 'is_legacy' => 0, 'created_by' => 1],
        ]);

        DB::connection('legacy_source')->table('media_usages')->insert([
            ['id' => 1, 'media_id' => 9, 'model_type' => 'App\Models\Page', 'model_id' => 2, 'field' => 'cover_image'],
        ]);

        // Pre-existing junk in the target that must be truncated away.
        DB::connection('legacy_target')->table('pages')->insert([
            ['id' => 99, 'parent_id' => null, 'title' => 'Stale', 'slug' => 'stale', 'sort_order' => 0, 'is_published' => 0],
        ]);

        $this->artisan('import:legacy-data', [
            '--source' => 'legacy_source',
            '--target' => 'legacy_target',
        ])->assertExitCode(0);

        $this->assertSame(2, DB::connection('legacy_target')->table('users')->count());
        $this->assertSame(2, DB::connection('legacy_target')->table('pages')->count());
        $this->assertSame(2, DB::connection('legacy_target')->table('media')->count());
        $this->assertSame(1, DB::connection('legacy_target')->table('media_usages')->count());

        $this->assertSame('Guest', DB::connection('legacy_target')->table('users')->where('id', 2)->value('name'));
        $this->assertSame(1, DB::connection('legacy_target')->table('pages')->where('id', 2)->value('parent_id'));
        $this->assertSame(1, DB::connection('legacy_target')->table('media')->where('id', 9)->value('created_by'));
        $this->assertSame(2, DB::connection('legacy_target')->table('media_usages')->where('id', 1)->value('model_id'));

        // The stale row must be gone.
        $this->assertNull(DB::connection('legacy_target')->table('pages')->where('slug', 'stale')->value('id'));
    }

    public function test_it_skips_ephemeral_tables_and_reports_missing_tables(): void
    {
        $this->createSchema('legacy_source', $this->sourceFile, includeEphemeral: false);
        $this->createSchema('legacy_target', $this->targetFile, includeEphemeral: true);

        DB::connection('legacy_source')->table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'hash', 'is_admin' => 1],
        ]);

        DB::connection('legacy_target')->table('sessions')->insert([
            'id' => 'abc123',
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => '{}',
            'last_activity' => 123,
        ]);

        $this->artisan('import:legacy-data', [
            '--source' => 'legacy_source',
            '--target' => 'legacy_target',
        ])->expectsOutputToContain('Imported users: 1 rows.')
            ->expectsOutputToContain('Skipping packages: not present in the source.')
            ->assertExitCode(0);

        // Ephemeral data is untouched.
        $this->assertSame(1, DB::connection('legacy_target')->table('sessions')->count());
        $this->assertSame(0, DB::connection('legacy_target')->table('jobs')->count());
        $this->assertSame(1, DB::connection('legacy_target')->table('users')->count());
    }

    private function createSchema(string $connectionName, string $file, bool $includeEphemeral = true): void
    {
        $this->app['config']->set("database.connections.{$connectionName}", [
            'driver' => 'sqlite',
            'database' => $file,
        ]);

        DB::purge($connectionName);

        $builder = Schema::connection($connectionName);

        $builder->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        $builder->create('pages', function ($table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        $builder->create('media', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('path')->unique();
            $table->string('disk')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('mime_type');
            $table->string('extension');
            $table->integer('size');
            $table->string('alt_text')->nullable();
            $table->boolean('is_legacy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $builder->create('media_usages', function ($table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('field');
            $table->timestamps();
        });

        if ($includeEphemeral) {
            $builder->create('sessions', function ($table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });

            $builder->create('jobs', function ($table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }
}
