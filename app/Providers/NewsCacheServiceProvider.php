<?php

namespace App\Providers;

use App\Models;
use App\Services\NewsCacheService;
use Illuminate\Cache\Events\CacheFailedOver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the news cache and wires model events to cache invalidation.
 */
class NewsCacheServiceProvider extends ServiceProvider
{
    /** Changes to these appear on (nearly) every page, so they invalidate everything. */
    private const LAYOUT_MODELS = [
        Models\Category::class,
        Models\Language::class,
        Models\GeneralSettings::class,
        Models\SocialLink::class,
        Models\Font::class,
        Models\Widget::class,
        Models\WidgetSetiings::class,
        Models\Page::class,
        Models\Seo::class,
        Models\Logo::class,
        Models\PollQuestion::class,
        Models\PollAnswer::class,
        Models\ImageAlbum::class,
        Models\ImageCategory::class,
        Models\ImageGallery::class,
    ];

    public function register(): void
    {
        $this->app->scoped(NewsCacheService::class);
    }

    public function boot(): void
    {
        $cache = fn (): NewsCacheService => $this->app->make(NewsCacheService::class);

        // Posts: create, publish, unpublish, schedule, flags, slug, category, tags, author, delete.
        Models\Post::saved(function (Models\Post $post) use ($cache) {
            if ($post->wasRecentlyCreated || $post->wasChanged()) {
                $cache()->postsChanged([$post]);
            }
        });
        Models\Post::deleted(fn (Models\Post $post) => $cache()->postsChanged([$post]));

        $gallery = fn (Models\Gallery $g) => $cache()->invalidate('post:'.$g->post_id);
        Models\Gallery::saved($gallery);
        Models\Gallery::deleted($gallery);

        foreach (self::LAYOUT_MODELS as $model) {
            $model::saved(fn () => $cache()->invalidateAll());
            $model::deleted(fn () => $cache()->invalidateAll());
        }

        // Authors: only public fields matter (logins also save these models).
        foreach ([Models\Admin::class, Models\User::class] as $model) {
            $model::saved(function ($author) use ($cache) {
                if ($author->wasChanged(['name', 'photo'])) {
                    $cache()->invalidateAll();
                }
            });
            $model::deleted(fn () => $cache()->invalidateAll());
        }

        // Ads: click counting saves the row on every click and must not invalidate.
        Models\Advertisement::saved(function (Models\Advertisement $ad) use ($cache) {
            if ($ad->wasRecentlyCreated || array_diff(array_keys($ad->getChanges()), ['click_count'])) {
                $cache()->invalidate('ads');
            }
        });
        Models\Advertisement::deleted(fn () => $cache()->invalidate('ads'));

        // Redis outages are served by the fallback store; make them visible in the log
        // (at most once every 5 minutes).
        Event::listen(function (CacheFailedOver $event) {
            try {
                if (! cache()->store('news_versions')->add('failover_logged', 1, 300)) {
                    return;
                }
            } catch (\Throwable $e) {
                // log anyway
            }
            Log::warning('Cache store ['.$event->storeName.'] failed, using the fallback: '.$event->exception->getMessage());
        });
    }
}
