<?php

namespace App\Services;

use App\Models\Post;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for frontend caching.
 *
 * Invalidation is version based: every key embeds the current version of the "scopes" it
 * depends on. Bumping a scope makes the old keys unreachable (they expire by TTL), so no
 * key scanning and never a flush. The "layout" scope is part of every versioned key.
 *
 * Scopes:
 *   layout       settings, menu, categories, pages, widgets ... and the "clear cache" button
 *   content      any list of posts (home, latest, tags, search by date, sitemap)
 *   post:{id}    one post and its gallery
 *   cat:{id}     one category or subcategory listing
 *   author:{id}  one author's listing
 *   ads          advertisements
 *
 * Null results are never stored, so a missing post/category is not cached.
 */
class NewsCacheService
{
    /** @var array<string, mixed> values already resolved during this request */
    private array $memo = [];

    /** @var array<string, string> */
    private array $versions = [];

    /**
     * @param  string[]  $scopes
     * @param  string|int  $ttl  a name from config('news_cache.ttl') or seconds
     */
    public function remember(string $key, array $scopes, string|int $ttl, Closure $callback): mixed
    {
        $full = $this->key($key, $scopes);

        if (array_key_exists($full, $this->memo)) {
            return $this->memo[$full];
        }

        $value = Cache::remember($full, $this->ttl($ttl), $callback);

        if ($value !== null) {
            $this->memo[$full] = $value;
        }

        return $value;
    }

    /**
     * remember() for expensive entries: on a miss only one request rebuilds, the others wait.
     */
    public function rememberLocked(string $key, array $scopes, string|int $ttl, Closure $callback): mixed
    {
        $full = $this->key($key, $scopes);

        if (array_key_exists($full, $this->memo)) {
            return $this->memo[$full];
        }
        if (($value = Cache::get($full)) !== null) {
            return $this->memo[$full] = $value;
        }

        $lock = null;
        try {
            $lock = Cache::lock('lock:'.$full, 30);
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            $lock = null; // the builder is slow; build our own copy rather than fail
        } catch (\Throwable $e) {
            report($e);
            $lock = null;
        }

        try {
            return $this->remember($key, $scopes, $ttl, $callback);
        } finally {
            $lock?->release();
        }
    }

    /**
     * Stale-while-revalidate for slow aggregates that may lag (popular posts).
     * Not versioned, never use it for post content.
     */
    public function staleWhileRevalidate(string $key, string $freshTtl, string $staleTtl, Closure $callback): mixed
    {
        $full = 'news:'.$key;

        if (array_key_exists($full, $this->memo)) {
            return $this->memo[$full];
        }

        $fresh = $this->ttl($freshTtl);
        $keep = $fresh + $this->ttl($staleTtl);
        $store = function () use ($full, $callback, $fresh, $keep) {
            $value = $callback();
            Cache::put($full, ['value' => $value, 'fresh_until' => time() + $fresh], $keep);

            return $value;
        };

        $entry = Cache::get($full);

        if (! is_array($entry)) {
            return $this->memo[$full] = $store();
        }

        if ($entry['fresh_until'] < time() && Cache::add($full.':refreshing', 1, 60)) {
            // Runs after the response has been sent (public/index.php calls terminate()).
            app()->terminating(function () use ($store) {
                try {
                    $store();
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }

        return $this->memo[$full] = $entry['value'];
    }

    /**
     * Cached equivalent of ->paginate(). The total and each page are cached separately;
     * pages past the end are not cached and an empty listing is not stored.
     *
     * @param  Closure  $query  returns a fresh Eloquent builder (with ordering)
     */
    public function paginate(string $key, array $scopes, string $ttl, Closure $query, int $perPage): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        $total = (int) $this->remember("$key:total", $scopes, $ttl, fn () => $query()->toBase()->getCountForPagination() ?: null);

        $items = $page <= (int) ceil($total / $perPage)
            ? $this->remember("$key:page:$page", $scopes, $ttl, fn () => $query()->forPage($page, $perPage)->get())
            : (new Post)->newCollection();

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    /* ------------------------------------------------------------------
     |  Invalidation
     * ------------------------------------------------------------------ */

    /** Bump scopes once the surrounding database transaction has committed (immediately if none). */
    public function invalidate(string ...$scopes): void
    {
        DB::afterCommit(fn () => $this->bump(...$scopes));
    }

    /** Invalidate every cached frontend entry. Safe on a shared Redis: nothing is flushed. */
    public function invalidateAll(): void
    {
        $this->invalidate('layout');
    }

    /**
     * Invalidate everything that depends on the given posts. Call this for mass updates,
     * which fire no model events.
     *
     * @param  iterable<Post>  $posts  need id, category_id, subcategories_id, admin_id
     */
    public function postsChanged(iterable $posts): void
    {
        $scopes = ['content'];

        foreach ($posts as $post) {
            $scopes[] = 'post:'.$post->id;

            // Old and new values, so a moved post leaves its previous listing too.
            foreach (['category_id' => 'cat', 'subcategories_id' => 'cat', 'admin_id' => 'author'] as $column => $scope) {
                foreach ([$post->getOriginal($column), $post->getAttribute($column)] as $id) {
                    if ($id) {
                        $scopes[] = $scope.':'.$id;
                    }
                }
            }
        }

        $this->invalidate(...array_values(array_unique($scopes)));
    }

    private function bump(string ...$scopes): void
    {
        $store = Cache::store(config('news_cache.version_store'));

        foreach ($scopes as $scope) {
            $store->forever('v:'.$scope, $this->newVersion());
        }

        $this->versions = [];
        $this->memo = [];
    }

    /* ------------------------------------------------------------------
     |  Keys
     * ------------------------------------------------------------------ */

    public function key(string $key, array $scopes = []): string
    {
        $versions = array_map(fn ($s) => $this->version($s), array_unique(['layout', ...$scopes]));

        return 'news:'.$key.':v'.implode('.', $versions);
    }

    /**
     * Only "layout" is written on read, so requests for ids that do not exist create no files.
     * Because every key contains the layout version, deleting the version store still
     * invalidates everything (other scopes simply restart at "0").
     */
    private function version(string $scope): string
    {
        $store = Cache::store(config('news_cache.version_store'));

        return $this->versions[$scope] ??= (string) ($scope === 'layout'
            ? $store->rememberForever('v:layout', fn () => $this->newVersion())
            : $store->get('v:'.$scope, '0'));
    }

    private function newVersion(): string
    {
        return bin2hex(random_bytes(4));
    }

    private function ttl(string|int $ttl): int
    {
        return is_int($ttl) ? $ttl : (int) config('news_cache.ttl.'.$ttl, 300);
    }
}
