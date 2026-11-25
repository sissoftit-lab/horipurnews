<?php

namespace App\Support;

use SimplePie\SimplePie;

/**
 * Minimal replacement for willvincent/feeds (not compatible with Laravel 13).
 * Usage stays the same: \Feeds::make($url)->get_items()
 */
class Feeds
{
    public static function make($feedUrl, $limit = 0, $forceFeed = false): SimplePie
    {
        $feed = new SimplePie();
        $cacheDir = storage_path('framework/cache/feeds');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $feed->set_cache_location($cacheDir);
        $feed->set_feed_url($feedUrl);
        $feed->force_feed($forceFeed);
        $feed->enable_cache(true);
        $feed->set_cache_duration(3600);
        if ($limit > 0) {
            $feed->set_item_limit($limit);
        }
        $feed->init();
        $feed->handle_content_type();

        return $feed;
    }
}
