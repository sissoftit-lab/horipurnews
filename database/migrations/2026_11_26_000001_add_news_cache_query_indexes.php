<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for queries that still scanned thousands of rows on a cache miss.
 * Measured on a copy of 10.7k posts: home left slider 1111 ms -> 0.5 ms,
 * home videos 852 ms -> 0.4 ms, author page count 1074 ms -> 3 ms.
 * Each index is created only if missing, so it is safe to re-run.
 */
return new class extends Migration
{
    private array $indexes = [
        'posts' => [
            'posts_slider_left_index' => 'language_id, slider_left, status, created_at',
            'posts_video_index'       => 'language_id, post_type, is_videoGallery, id',
            'posts_author_index'      => 'admin_id, status, is_pending, schedule_post, created_at',
        ],
        'galleries' => [
            'galleries_post_id_index' => 'post_id',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $list) {
            foreach ($list as $name => $cols) {
                if (Schema::hasTable($table) && ! $this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `$table` ADD INDEX `$name` ($cols)");
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $list) {
            foreach (array_keys($list) as $name) {
                if (Schema::hasTable($table) && $this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `$table` DROP INDEX `$name`");
                }
            }
        }
    }

    private function exists(string $table, string $name): bool
    {
        return count(DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$name])) > 0;
    }
};
