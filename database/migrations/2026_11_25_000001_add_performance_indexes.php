<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds indexes for the hottest frontend queries (home page, top-viewed list, category pages).
 * Safe to re-run: each index is only created if missing.
 */
return new class extends Migration
{
    private array $indexes = [
        'views' => ['views_post_id_index' => 'post_id'],
        'posts' => [
            'posts_category_listing_index' => 'category_id, status, is_pending, schedule_post',
            'posts_language_listing_index' => 'language_id, status, is_pending, schedule_post',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $list) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($list as $name => $cols) {
                if (!$this->exists($table, $name)) {
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
