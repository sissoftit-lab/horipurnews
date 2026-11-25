<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4 indexes. Each index is created only if missing, so it is safe to re-run.
 *
 *  posts.slug / categories.slug  : slug lookups (/{category}/{slug}) only had FULLTEXT indexes => full scans
 *  posts.subcategories_id        : subcategory listings and header menu
 *  posts.is_* flags              : homepage slider / trending / feature lists
 *  posts.created_at              : archive-by-date range queries
 *  views(post_id, ip_address)    : "has this IP viewed this post" check on every post view
 *  views.created_at is not indexed: the table has no such column requirement
 */
return new class extends Migration
{
    private array $indexes = [
        'posts' => [
            'posts_slug_index'               => 'slug',
            'posts_subcategory_listing_index'=> 'subcategories_id, status, is_pending, schedule_post, id',
            'posts_created_at_index'         => 'created_at',
            'posts_slider_index'             => 'language_id, is_slider, status, id',
            'posts_trending_index'           => 'language_id, is_trending, status, id',
            'posts_feature_index'            => 'language_id, is_feature, status, id',
        ],
        'categories' => [
            'categories_slug_index'          => 'slug',
            'categories_menu_index'          => 'language_id, parent_id, show_on_menu, category_order',
            'categories_parent_index'        => 'parent_id',
        ],
        'views' => [
            'views_post_ip_index'            => 'post_id, ip_address',
        ],
    ];

    /** Redundant duplicates of the same single column (harmless to drop, they only slow writes). */
    private array $redundant = [
        'posts' => ['title_2', 'title_3', 'description_2', 'slug_2'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $list) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($list as $name => $cols) {
                if (!$this->exists($table, $name)) {
                    try {
                        DB::statement("ALTER TABLE `$table` ADD INDEX `$name` ($cols)");
                    } catch (\Throwable $e) {
                        // A missing column on a customised schema must not abort the whole migration.
                        logger()->warning("Index $name skipped: ".$e->getMessage());
                    }
                }
            }
        }

        foreach ($this->redundant as $table => $names) {
            foreach ($names as $name) {
                if (Schema::hasTable($table) && $this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `$table` DROP INDEX `$name`");
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
