<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Post;
use App\Services\NewsCacheService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Invalidation rules of NewsCacheService, on an in-memory SQLite database. */
class NewsCacheTest extends TestCase
{
    private NewsCacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'cache.stores.news_versions' => ['driver' => 'array'],
        ]);

        Schema::create('posts', function (Blueprint $t) {
            $t->increments('id');
            foreach (['title', 'slug', 'status'] as $c) {
                $t->string($c)->nullable();
            }
            foreach (['category_id', 'subcategories_id', 'admin_id', 'is_pending', 'schedule_post', 'is_feature'] as $c) {
                $t->integer($c)->nullable()->default(0);
            }
            $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
        });
        Schema::create('advertisements', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('status')->default(1);
            $t->integer('click_count')->default(0);
            $t->string('link')->nullable();
        });

        $this->cache = $this->app->make(NewsCacheService::class);
    }

    private function makePost(array $attributes = []): Post
    {
        $post = new Post;
        $post->forceFill($attributes + ['title' => 'First', 'slug' => 'first', 'status' => 'true', 'category_id' => 1, 'admin_id' => 1, 'is_pending' => 0, 'schedule_post' => 0])->save();

        return $post->fresh();
    }

    private function keys(Post $post): array
    {
        return [
            'post' => $this->cache->key('p', ["post:{$post->id}"]),
            'content' => $this->cache->key('home', ['content']),
            'cat1' => $this->cache->key('c', ['cat:1']),
            'cat2' => $this->cache->key('c', ['cat:2']),
            'cat3' => $this->cache->key('c', ['cat:3']),
            'author1' => $this->cache->key('a', ['author:1']),
            'author2' => $this->cache->key('a', ['author:2']),
        ];
    }

    private function changed(array $before, array $after): array
    {
        return array_keys(array_diff_assoc($before, $after));
    }

    public function test_editing_a_post_invalidates_only_what_depends_on_it(): void
    {
        $post = $this->makePost();
        $before = $this->keys($post);

        $post->update(['title' => 'Renamed', 'slug' => 'renamed']);

        $this->assertSame(['post', 'content', 'cat1', 'author1'], $this->changed($before, $this->keys($post)));
    }

    public function test_moving_a_post_invalidates_old_and_new_category_and_author(): void
    {
        $post = $this->makePost();
        $before = $this->keys($post);

        $post->update(['category_id' => 2, 'admin_id' => 2]);

        $this->assertSame(['post', 'content', 'cat1', 'cat2', 'author1', 'author2'], $this->changed($before, $this->keys($post)));
    }

    public function test_deleting_a_post_invalidates_it(): void
    {
        $post = $this->makePost();
        $before = $this->keys($post);

        $post->delete();

        $this->assertContains('post', $this->changed($before, $this->keys($post)));
        $this->assertContains('content', $this->changed($before, $this->keys($post)));
    }

    public function test_unpublished_post_leaves_the_cache(): void
    {
        $post = $this->makePost();
        $read = fn () => $this->cache->remember("post:{$post->id}", ["post:{$post->id}"], 'post', fn () => Post::published()->find($post->id));
        $this->assertNotNull($read());

        foreach ([['status' => 'draft'], ['is_pending' => 1], ['schedule_post' => 1]] as $change) {
            $post->update($change);
            $this->assertNull($read());
            $post->update(['status' => 'true', 'is_pending' => 0, 'schedule_post' => 0]);
            $this->assertNotNull($read());
        }
    }

    public function test_invalidation_waits_for_commit_and_is_dropped_on_rollback(): void
    {
        $post = $this->makePost();
        $before = $this->keys($post);

        DB::beginTransaction();
        $post->update(['title' => 'Not yet']);
        $this->assertSame([], $this->changed($before, $this->keys($post)));
        DB::rollBack();
        $this->assertSame([], $this->changed($before, $this->keys($post)));

        DB::transaction(fn () => $post->fresh()->update(['title' => 'Saved']));
        $this->assertContains('post', $this->changed($before, $this->keys($post)));
    }

    public function test_category_change_invalidates_everything(): void
    {
        $post = $this->makePost();
        $before = $this->keys($post);

        (new Category)->forceFill(['title' => 'World'])->save();

        $this->assertCount(count($before), $this->changed($before, $this->keys($post)));
    }

    public function test_ad_clicks_do_not_invalidate_but_edits_do(): void
    {
        $ad = new Advertisement;
        $ad->forceFill(['status' => 1])->save();
        $ad = $ad->fresh();
        $key = $this->cache->key('ads', ['ads']);

        $ad->increment('click_count');
        $ad->update();
        $this->assertSame($key, $this->cache->key('ads', ['ads']));

        $ad->update(['link' => 'https://example.test']);
        $this->assertNotSame($key, $this->cache->key('ads', ['ads']));
    }

    public function test_missing_records_are_not_cached(): void
    {
        $calls = 0;
        $read = function () use (&$calls) {
            return $this->cache->remember('post:999', ['post:999'], 'post', function () use (&$calls) {
                $calls++;

                return null;
            });
        };
        $read();
        $read();

        $this->assertSame(2, $calls);
    }

    public function test_pagination_does_not_cache_pages_past_the_end(): void
    {
        $this->makePost();
        $this->makePost(['slug' => 'second']);
        request()->merge(['page' => 50]);

        $page = $this->cache->paginate('list', ['content'], 'category', fn () => Post::published()->orderBy('id'), 1);

        $this->assertSame(2, $page->total());
        $this->assertCount(0, $page->items());
        $this->assertFalse(Cache::has($this->cache->key('list:page:50', ['content'])));
    }

    public function test_site_keeps_working_when_redis_is_unavailable(): void
    {
        config([
            'cache.default' => 'failover',
            'cache.stores.failover' => ['driver' => 'failover', 'stores' => ['redis', 'array']],
            'database.redis.cache.port' => 1, // nothing listens here
        ]);

        $value = $this->cache->rememberLocked('fallback', ['content'], 60, fn () => 'from database');

        $this->assertSame('from database', $value);
    }

    public function test_clearing_never_flushes_the_cache_store(): void
    {
        Cache::put('unrelated', 'keep me', 60);
        $key = $this->cache->key('home', ['content']);

        $this->artisan('news-cache:clear')->assertExitCode(0);

        $this->assertSame('keep me', Cache::get('unrelated'));
        $this->assertNotSame($key, $this->cache->key('home', ['content']));
    }
}
