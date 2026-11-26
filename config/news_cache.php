<?php

/*
| Frontend news cache. See docs/redis-caching.md.
*/

return [

    // Lifetimes in seconds. Admin edits invalidate immediately; these only bound
    // how long an entry lives when nothing changes.
    'ttl' => [
        'breaking'      => (int) env('NEWS_TTL_BREAKING', 30),     // breaking / trending ticker
        'home'          => (int) env('NEWS_TTL_HOME', 120),
        'latest'        => (int) env('NEWS_TTL_LATEST', 120),
        'category'      => (int) env('NEWS_TTL_CATEGORY', 300),
        'post'          => (int) env('NEWS_TTL_POST', 600),
        'popular'       => (int) env('NEWS_TTL_POPULAR', 300),     // served fresh for this long
        'popular_stale' => (int) env('NEWS_TTL_POPULAR_STALE', 1800), // then served stale while one request refreshes
        'author'        => (int) env('NEWS_TTL_AUTHOR', 600),
        'tag'           => (int) env('NEWS_TTL_TAG', 600),
        'date'          => (int) env('NEWS_TTL_DATE', 300),
        'menu'          => (int) env('NEWS_TTL_MENU', 1800),
        'settings'      => (int) env('NEWS_TTL_SETTINGS', 3600),   // global + SEO settings
        'page'          => (int) env('NEWS_TTL_PAGE', 3600),
        'sitemap'       => (int) env('NEWS_TTL_SITEMAP', 3600),
        'ads'           => (int) env('NEWS_TTL_ADS', 300),
    ],

    // Cache store holding the version numbers. Deliberately NOT Redis: if Redis is down
    // while an editor saves, the version still changes, so old Redis data cannot come back.
    'version_store' => 'news_versions',

];
