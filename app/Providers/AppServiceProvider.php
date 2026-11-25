<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\GeneralSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App;
use App\Models\Font;
use App\Models\Language;
use App\Models\Post;
use App\Models\SocialLink;
use Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Models whose changes invalidate the cached frontend data.
     * (Advertisement is deliberately absent: click counters update it on every click;
     * ads use a short TTL instead. View is absent for the same reason.)
     */
    private array $invalidators = [
        \App\Models\Post::class,
        \App\Models\Category::class,
        \App\Models\Language::class,
        \App\Models\GeneralSettings::class,
        \App\Models\SocialLink::class,
        \App\Models\Font::class,
        \App\Models\Widget::class,
        \App\Models\WidgetSetiings::class,
        \App\Models\Page::class,
        \App\Models\Seo::class,
        \App\Models\Logo::class,
        \App\Models\PollQuestion::class,
        \App\Models\ImageAlbum::class,
        \App\Models\Gallery::class,
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        foreach ($this->invalidators as $model) {
            if (class_exists($model)) {
                $model::saved(fn () => site_cache_bump());
                $model::deleted(fn () => site_cache_bump());
            }
        }

        // Shared view data is resolved once per request (not once per rendered view/partial),
        // and the heavy parts are cached.
        $shared = null;

        view()->composer(['*'], function ($view) use (&$shared) {

            if ($shared === null) {
                $isAdmin = \Request::is('admin/*');

                if ($isAdmin) {
                    $default_language = Language::where('is_default', 1)->first();
                    $locale = DB::table('admin_languages')->where('is_default', '=', 1)->first();
                } else {
                    $default_language = front_language();
                    $locale = $default_language;
                }
                if ($locale && !empty($locale->name)) {
                    App::setlocale($locale->name);
                }

                if ($isAdmin) {
                    // Admin must always see fresh data.
                    $gs = GeneralSettings::find(1);
                    $shared = [
                        'gs'               => $gs,
                        'seo'              => DB::table('seotools')->first(),
                        'categories'       => collect(),
                        'social_links'     => SocialLink::orderBy('id', 'desc')->get(),
                        'top_views'        => [],
                        'tags'             => explode(',', (string) ($gs->tags ?? '')),
                        'default_language' => $default_language,
                        'default_font'     => Font::where('is_default', 1)->first(),
                        'languages'        => Language::orderBy('id', 'desc')->get(),
                    ];
                    if ($default_language) {
                        $shared['categories'] = Category::orderBy('category_order', 'asc')
                            ->where('language_id', '=', $default_language->id)
                            ->whereNull('parent_id')
                            ->where('show_on_menu', '=', 1)
                            ->get();
                    }
                } else {
                    $lid = $default_language->id ?? 0;
                    $common = site_cache('shared', 3600, function () {
                        $gs = GeneralSettings::find(1);
                        return [
                            'gs'           => $gs,
                            'seo'          => DB::table('seotools')->first(),
                            'social_links' => SocialLink::orderBy('id', 'desc')->get(),
                            'tags'         => explode(',', (string) ($gs->tags ?? '')),
                            'default_font' => Font::where('is_default', 1)->first(),
                            'languages'    => Language::orderBy('id', 'desc')->get(),
                        ];
                    });

                    $categories = $default_language
                        ? site_cache('menu:'.$lid, 3600, function () use ($lid) {
                            // Eager load children + their posts count to avoid per-item queries in the header.
                            return Category::with('child')
                                ->orderBy('category_order', 'asc')
                                ->where('language_id', '=', $lid)
                                ->whereNull('parent_id')
                                ->where('show_on_menu', '=', 1)
                                ->get();
                        })
                        : collect();

                    // The expensive GROUP BY over the whole `views` table: cached for 15 min, independent of content edits.
                    $top_views = Cache::remember('top_views_v1', 900, function () {
                        return DB::table('views')
                            ->select(DB::raw('count(*) as top_viwes, post_id'))
                            ->groupBy('post_id')
                            ->orderBy('top_viwes', 'desc')
                            ->take(6)
                            ->get();
                    });

                    $shared = $common + [
                        'categories'       => $categories,
                        'top_views'        => $top_views,
                        'default_language' => $default_language,
                    ];
                }
            }

            $view->with($shared);
        });
    }
}
