<?php 

if (!function_exists('slug_create') ) {
    function slug_create($val) {
        $slug = preg_replace('/\s+/u', '-', trim($val));
        return $slug;
    }
}

if(!function_exists('active_ads')){
    /** Active advertisements, optionally for a placement and size, from cache. */
    function active_ads(?string $placement = null, ?string $size = null){
        $ads = news_cache()->remember('ads:active', ['ads'], 'ads', fn () => App\Models\Advertisement::where('status', 1)->get());

        return $ads->when($placement, fn ($c) => $c->where('add_placement', $placement))
            ->when($size, fn ($c) => $c->where('addSize', $size))
            ->values();
    }
}

if(!function_exists('advertisement')){
    function advertisement(){
        return active_ads('index_bottom', 'size_728')->shuffle()->first();
    }
}

if(!function_exists('sidebar_banner')){
    function sidebar_banner(){
        return active_ads('sidebar_bottom', 'size_468')->shuffle()->first();
    }
}

if(!function_exists('sponsor_banner')){
    function sponsor_banner(){
        return active_ads('sponsor', 'size_468')->shuffle()->take(2)->values();
    }
}

if(!function_exists('header_ads')){
    function header_ads(){
        return active_ads('header', 'size_728')->shuffle()->first();
    }
}

if (! function_exists('convertUtf8')) {
    function convertUtf8( $value ) {
        return mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
    }
}

if(!function_exists('d_logo')){
    function d_logo($lid){
        return news_cache()->remember("logos:$lid", [], 'settings', fn () => App\Models\Logo::where('language_id', $lid)->get());
    }
}



/*
|--------------------------------------------------------------------------
| Frontend cache helpers
|--------------------------------------------------------------------------
| Thin wrappers around App\Services\NewsCacheService, which handles keys,
| lifetimes and invalidation.
*/
if (! function_exists('news_cache')) {
    function news_cache(): \App\Services\NewsCacheService {
        return app(\App\Services\NewsCacheService::class);
    }
}

if (! function_exists('front_language')) {
    /** Current frontend language (session choice or default). */
    function front_language() {
        $lang = null;
        if (session()->has('language')) {
            $id = (int) session('language');
            $lang = news_cache()->remember("language:$id", [], 'settings', fn () => \App\Models\Language::find($id));
        }

        return $lang ?: news_cache()->remember('language:default', [], 'settings', fn () => \App\Models\Language::where('is_default', 1)->first());
    }
}

if (! function_exists('public_settings')) {
    /** General settings without credentials, safe to cache and share with frontend views. */
    function public_settings() {
        return news_cache()->remember('settings:general', [], 'settings', function () {
            $gs = \App\Models\GeneralSettings::find(1);

            return $gs?->setRawAttributes(\Illuminate\Support\Arr::except($gs->getAttributes(), ['smtp_user', 'smtp_pass', 'captcha_secret_key']), true);
        });
    }
}

if (! function_exists('seo_settings')) {
    function seo_settings() {
        return news_cache()->remember('settings:seo', [], 'settings', fn () => \Illuminate\Support\Facades\DB::table('seotools')->first());
    }
}

if (! function_exists('widget_settings')) {
    function widget_settings() {
        return news_cache()->remember('settings:widgets', [], 'settings', fn () => \App\Models\WidgetSetiings::find(1));
    }
}

if (! function_exists('category_by_slug')) {
    function category_by_slug($slug) {
        return news_cache()->remember('category:slug:'.md5($slug), [], 'category', fn () => \App\Models\Category::where('slug', $slug)->first());
    }
}

if (! function_exists('published_posts_query')) {
    function published_posts_query() {
        return \App\Models\Post::published();
    }
}

if (! function_exists('post_card_relations')) {
    /** Relations the post cards read. Only public author columns, so nothing private is cached. */
    function post_card_relations() {
        return ['category', 'admin:id,name,photo', 'user:id,name'];
    }
}

if (! function_exists('cat_recent_posts')) {
    /** Latest published posts of a (parent) category, newest first. */
    function cat_recent_posts($categoryId, int $limit = 6) {
        return news_cache()->remember("cat:$categoryId:recent:$limit", ["cat:$categoryId"], 'category', function () use ($categoryId, $limit) {
            return published_posts_query()->where('category_id', $categoryId)->with(post_card_relations())
                ->orderBy('id', 'desc')->limit($limit)->get();
        });
    }
}

if (! function_exists('subcat_recent_posts')) {
    /** Latest published posts of a subcategory, newest first. */
    function subcat_recent_posts($subcategoryId, int $limit = 4) {
        return news_cache()->remember("subcat:$subcategoryId:recent:$limit", ["cat:$subcategoryId"], 'category', function () use ($subcategoryId, $limit) {
            return published_posts_query()->where('subcategories_id', $subcategoryId)->with(post_card_relations())
                ->orderBy('id', 'desc')->limit($limit)->get();
        });
    }
}

if (! function_exists('cat_post_count')) {
    function cat_post_count($categoryId) {
        return news_cache()->remember("cat:$categoryId:count", ["cat:$categoryId"], 'category', fn () => published_posts_query()->where('category_id', $categoryId)->count());
    }
}

if (! function_exists('top_view_posts')) {
    /** Posts for the "most viewed" widgets, in rank order, for the current language. */
    function top_view_posts($topViews) {
        $lid = front_language()->id ?? 0;
        $ids = collect($topViews)->pluck('post_id')->all();

        return news_cache()->remember("popular:posts:$lid:".md5(implode(',', $ids)), ['content'], 'popular', function () use ($ids, $lid) {
            $posts = published_posts_query()->whereIn('id', $ids)->where('language_id', $lid)->with(post_card_relations())->get()->keyBy('id');

            return collect($ids)->map(fn ($id) => $posts->get($id))->filter()->values();
        });
    }
}

if (! function_exists('footer_pages')) {
    function footer_pages() {
        return news_cache()->remember('pages:footer', [], 'page', fn () => \App\Models\Page::where('placement', 'footer')->where('status', 1)->get());
    }
}

if (! function_exists('footer_categories')) {
    function footer_categories($languageId) {
        return news_cache()->remember("categories:all:$languageId", [], 'menu', fn () => \App\Models\Category::where('language_id', $languageId)->get());
    }
}
