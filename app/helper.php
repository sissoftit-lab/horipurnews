<?php 

if (!function_exists('slug_create') ) {
    function slug_create($val) {
        $slug = preg_replace('/\s+/u', '-', trim($val));
        return $slug;
    }
}

if(!function_exists('advertisement')){
    function advertisement(){
        $index_bottom = App\Models\Advertisement::inRandomOrder()
                                        ->where('add_placement','index_bottom')
                                        ->where('addSize','size_728')
                                        ->where('status',1)
                                        ->first(); 
        return $index_bottom;
    }
}

if(!function_exists('sidebar_banner')){
    function sidebar_banner(){
        $sidebar_banner = App\Models\Advertisement::inRandomOrder()
                                        ->where('add_placement','sidebar_bottom')
                                        ->where('addSize','size_468')
                                        ->where('status',1)
                                        ->first(); 
        return $sidebar_banner;
    }
}

if(!function_exists('sponsor_banner')){
    function sponsor_banner(){
        $sponsor_banners     = App\Models\Advertisement::inRandomOrder()
                                        ->where('add_placement','sponsor')
                                        ->where('addSize','size_468')
                                        ->where('status',1)
                                        ->take(2)
                                        ->get();
        return $sponsor_banners;
    }
}

if(!function_exists('header_ads')){

    function header_ads(){
        $header_ads   = App\Models\Advertisement::inRandomOrder()
                                ->where('add_placement','header')
                                ->where('addSize','size_728')
                                ->where('status',1)
                                ->inRandomOrder()
                                ->limit(1)
                                ->first(); 
        return $header_ads;
    }
}

if (! function_exists('convertUtf8')) {
    function convertUtf8( $value ) {
        return mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
    }
}

if(!function_exists('d_logo')){
    function d_logo($lid){
        $header_footer_logo = App\Models\Language::find($lid)->logos;
        return $header_footer_logo;
    }
}



/*
|--------------------------------------------------------------------------
| Frontend cache helpers
|--------------------------------------------------------------------------
| site_cache() keys include a version number that is bumped whenever a content
| model is saved/deleted (see AppServiceProvider), so admin edits show up
| immediately without needing cache tags (the file driver has none).
*/
if (! function_exists('site_cache_version')) {
    function site_cache_version() {
        return \Illuminate\Support\Facades\Cache::rememberForever('site_cache_version', fn () => time());
    }
}

if (! function_exists('site_cache_bump')) {
    function site_cache_bump() {
        \Illuminate\Support\Facades\Cache::forever('site_cache_version', microtime(true));
    }
}

if (! function_exists('site_cache')) {
    function site_cache(string $key, $ttl, \Closure $callback) {
        return \Illuminate\Support\Facades\Cache::remember('site:'.site_cache_version().':'.$key, $ttl, $callback);
    }
}

if (! function_exists('front_language')) {
    /** Current frontend language (session choice or default), resolved once per request. */
    function front_language() {
        static $lang = false;
        if ($lang === false) {
            $lang = null;
            if (session()->has('language')) {
                $id = session('language');
                $lang = site_cache('language:'.$id, 3600, fn () => \App\Models\Language::find($id));
            }
            if (!$lang) {
                $lang = site_cache('language:default', 3600, fn () => \App\Models\Language::where('is_default', 1)->first());
            }
        }
        return $lang;
    }
}

if (! function_exists('published_posts_query')) {
    function published_posts_query() {
        return \App\Models\Post::where('status', true)->where('is_pending', 0)->where('schedule_post', 0);
    }
}

if (! function_exists('cat_recent_posts')) {
    /** Latest published posts of a (parent) category, newest first. Cached; category relation preloaded. */
    function cat_recent_posts($categoryId, int $limit = 6) {
        return site_cache("cat_posts:$categoryId:$limit", 300, function () use ($categoryId, $limit) {
            return published_posts_query()->where('category_id', $categoryId)->with('category')
                ->orderBy('id', 'desc')->limit($limit)->get();
        });
    }
}

if (! function_exists('subcat_recent_posts')) {
    /** Latest published posts of a subcategory, newest first. Cached; category relation preloaded. */
    function subcat_recent_posts($subcategoryId, int $limit = 4) {
        return site_cache("subcat_posts:$subcategoryId:$limit", 300, function () use ($subcategoryId, $limit) {
            return published_posts_query()->where('subcategories_id', $subcategoryId)->with('category')
                ->orderBy('id', 'desc')->limit($limit)->get();
        });
    }
}

if (! function_exists('cat_post_count')) {
    function cat_post_count($categoryId) {
        return site_cache("cat_count:$categoryId", 300, fn () => published_posts_query()->where('category_id', $categoryId)->count());
    }
}

if (! function_exists('top_view_posts')) {
    /** Posts for the "most viewed" widgets, in rank order, for the current language. Cached. */
    function top_view_posts($topViews) {
        $lid = front_language()->id ?? 0;
        return site_cache('top_posts:'.$lid.':'.md5(json_encode($topViews)), 900, function () use ($topViews, $lid) {
            $ids = collect($topViews)->pluck('post_id')->all();
            $posts = \App\Models\Post::whereIn('id', $ids)->where('language_id', $lid)->with('category')->get()->keyBy('id');
            return collect($ids)->map(fn ($id) => $posts->get($id))->filter()->values();
        });
    }
}

if (! function_exists('footer_pages')) {
    function footer_pages() {
        return site_cache('footer_pages', 3600, fn () => \App\Models\Page::where('placement', 'footer')->where('status', 1)->get());
    }
}
