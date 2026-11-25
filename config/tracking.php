<?php

return [

    /*
    | Meta (Facebook) Pixel + Conversions API. Leave META_PIXEL_ID empty to disable tracking.
    */
    'meta' => [
        'enabled'         => (bool) env('META_PIXEL_ID'),
        'pixel_id'        => env('META_PIXEL_ID'),
        'access_token'    => env('META_CAPI_ACCESS_TOKEN'),    // Conversions API token (server only)
        'test_event_code' => env('META_CAPI_TEST_EVENT_CODE'), // e.g. TEST1234 while testing in Events Manager
        'api_version'     => env('META_GRAPH_VERSION', 'v21.0'),
        'timeout'         => 3,
    ],

    // Events the browser is allowed to forward to the server endpoint.
    'allowed_events' => [
        'PageView', 'ViewContent', 'Search', 'Lead', 'CompleteRegistration',
        'Subscribe', 'Contact', 'Share',
    ],

    /*
    | Home page SEO. Anything left null falls back to the general settings.
    */
    'seo' => [
        'home_title'       => env('SEO_HOME_TITLE'),
        'home_description' => env('SEO_HOME_DESCRIPTION'),
        'home_keywords'    => env('SEO_HOME_KEYWORDS'),
        'twitter_site'     => env('SEO_TWITTER_SITE'), // e.g. @livenews
        'locale'           => env('SEO_LOCALE', 'en_US'),
    ],
];
