<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\GeneralSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App;
use App\Models\Font;
use App\Models\Language;
use App\Models\Post;
use App\Models\SocialLink;
use Session;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
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
                    $common = news_cache()->remember('shared', [], 'settings', function () {
                        return [
                            'social_links' => SocialLink::orderBy('id', 'desc')->get(),
                            'default_font' => Font::where('is_default', 1)->first(),
                            'languages'    => Language::orderBy('id', 'desc')->get(),
                        ];
                    });
                    $gs = public_settings();
                    $common += [
                        'gs'   => $gs,
                        'seo'  => seo_settings(),
                        'tags' => explode(',', (string) ($gs->tags ?? '')),
                    ];

                    $categories = $default_language
                        ? news_cache()->remember("menu:$lid", [], 'menu', function () use ($lid) {
                            // Eager load children to avoid per-item queries in the header.
                            return Category::with('child')
                                ->orderBy('category_order', 'asc')
                                ->where('language_id', '=', $lid)
                                ->whereNull('parent_id')
                                ->where('show_on_menu', '=', 1)
                                ->get();
                        })
                        : collect();

                    // GROUP BY over the whole `views` table: refreshed after the response, never in a visitor's way.
                    $top_views = news_cache()->staleWhileRevalidate('popular:view-ids', 'popular', 'popular_stale', function () {
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
