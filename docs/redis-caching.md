# News cache: operation, deployment and measurements

Code: `app/Services/NewsCacheService.php` (logic), `app/Providers/NewsCacheServiceProvider.php` (invalidation), `app/helper.php` (helpers), `config/news_cache.php` (lifetimes).

## How invalidation works

Every key embeds the version of the things it depends on. A change gives that scope a new version, so old keys are never read again and expire by TTL. Nothing is flushed or scanned. Versions are kept in files (`storage/framework/cache/news-versions`), so an edit made while Redis is down still counts when Redis returns. This assumes a single web server.

| Change | Scopes bumped |
|---|---|
| Post created, updated, published, unpublished, scheduled, deleted, flags (single or bulk) | `content`, `post:{id}`, old and new `cat:{id}`, old and new `author:{id}` |
| Post gallery image | `post:{id}` |
| Category, language, settings, SEO, social link, font, widget, page, logo, poll, image album/category/image | `layout` (everything) |
| Author name or photo; author deleted | `layout` |
| Advertisement (not click counts) | `ads` |
| "Clear cache" buttons, `/clear-cache`, `php artisan news-cache:clear` | `layout` |

Bumps run after the database transaction commits and are dropped on rollback. Posts have no soft delete, so there is no restore.

**Writes that skip Eloquent events** (query-builder `update`/`delete`, direct SQL, phpMyAdmin) are not detected. In code call `news_cache()->postsChanged($posts)`; after manual SQL run `news-cache:clear`.

## Keys and lifetimes

Redis key = `REDIS_PREFIX` + `CACHE_PREFIX` + key + `:v<versions>`.

| Key | Content | TTL (`config/news_cache.php`) |
|---|---|---|
| `news:home:breaking:{lang}` | Breaking/trending ticker | 30 s |
| `news:home:news:{lang}` | Sliders, featured, latest, videos | 2 min |
| `news:home:blocks:{lang}` | Home categories, polls, widgets, albums | 30 min |
| `news:cat:{id}:recent:*`, `:count` | Home category blocks, sidebar counts | 5 min |
| `news:cat:{id}:list:total`, `:page:{n}` | Category page | 5 min |
| `news:subcat:{id}:recent:*`, `:list:{lang}` | Subcategory menu and page | 5 min |
| `news:category:slug:{hash}` | Category lookup | 5 min |
| `news:post:{id}` | Published post, category, author name/photo, gallery | 10 min |
| `news:post:slug:{hash}` | Slug to id | 10 min |
| `news:author:{id}:list:*` | Author page | 10 min |
| `news:tag:{lang}:{hash}` | Tag results (only if non-empty) | 10 min |
| `news:date:{lang}:{day}:*` | Date archive | 5 min |
| `news:popular:view-ids` | Most-viewed ids (no version) | fresh 5 min, then served stale while refreshing, up to 30 more |
| `news:popular:posts:{lang}:{hash}` | Most-viewed posts | 5 min |
| `news:menu:{lang}`, `news:categories:all:{lang}` | Navigation, footer | 30 min |
| `news:shared`, `news:settings:*`, `news:language:*`, `news:logos:{lang}` | Global and SEO settings | 60 min |
| `news:page:{hash}`, `news:pages:footer` | Static pages | 60 min |
| `news:sitemap` | Sitemap XML | 60 min |
| `news:ads:active` | Active ads (picked randomly per request) | 5 min |

Not cached: search, load-more, poll results, per-visitor vote state, missing records, admin screens. Related and previous/next posts do not exist in this application.

Settings are cached without `smtp_user`, `smtp_pass` and `captcha_secret_key`; authors only with id, name and photo.

Stampede protection: a lock around the home news block and sitemap rebuilds; stale-while-revalidate for the most-viewed query.

## Behaviour changes

- Draft, pending and scheduled posts now return **404** to the public. A logged-in admin, or the post's own author, can still preview them.
- Date archive, subcategory page and sitemap no longer list pending posts.
- Date archive page 2 and later now work (the `date` parameter was being dropped).
- "Clear cache" no longer empties the cache store; it invalidates news data.
- `/p/<non-number>` returns 404.

## Deployment (cPanel)

Hosted layout: the application is in `public_html/project/`; run commands from there.

1. **Back up** the files and database.
2. Upload the changed and new files (`git status` lists them). Do not upload `docs/`.
3. Delete `bootstrap/cache/config.php` and `bootstrap/cache/routes*.php` if they exist. A stale config cache would leave invalidation switched off.
4. Check that `storage/framework/cache/` is writable.
5. **Deploy first with the existing file cache** (`.env` unchanged) and click through the site and admin.
6. Confirm with your host that Redis is private to your account. With no password on a shared instance, other accounts could read or alter cached data. If it is not private, stay on the file cache.
7. Add to `.env` (keep a copy of the old file):

```env
CACHE_STORE=failover
CACHE_PREFIX=
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_PREFIX=horipurnews_
```

Session and queue settings are not changed.

8. Verify:

```bash
php artisan config:clear
php artisan news-cache:check
```

It must end with `Redis OK`. If your host allows only database 0, set `REDIS_CACHE_DB=0`; the prefix keeps keys apart.

9. Open the home page and a post, then check that keys exist (if `redis-cli` is available):

```bash
redis-cli -n 1 --scan --pattern 'horipurnews_news:*' | head
redis-cli info stats | grep keyspace
```

10. Watch `storage/logs` for `Cache store [redis] failed`. That means the site is running on the file fallback. `news-cache:check` tests the CLI PHP, so this log is the check for the web PHP.

Never run `php artisan cache:clear` against a shared Redis: it flushes the whole database. Use `news-cache:clear`.

## Database indexes (optional, recommended)

`database/migrations/2026_11_26_000001_add_news_cache_query_indexes.php` has **not** been run. It adds indexes only; no data changes. Needed because a cache miss still ran queries scanning thousands of rows.

```bash
php artisan migrate:status          # confirm which migrations are pending
php artisan migrate --path=database/migrations/2026_11_26_000001_add_news_cache_query_indexes.php --force
```

Run it at a quiet time. Undo with `php artisan migrate:rollback --path=<same path>`.

## Testing

```bash
php vendor/bin/phpunit tests/Feature/NewsCacheTest.php   # needs pdo_sqlite and dev packages; not for production
php artisan news-cache:check
php artisan news-cache:clear
```

## Rollback

- **Redis only:** remove `CACHE_STORE` (or set `CACHE_STORE=file`), then `php artisan config:clear`.
- **All code:** restore the backup (or `git revert` the commit), delete `bootstrap/cache/config.php`, then delete `storage/framework/cache/data`.
- **Indexes:** the rollback command above.

## Measurements

Local machine (Windows, PHP 8.5 CLI, MariaDB with a 16 MB buffer pool), pages rendered in-process, **file cache**, indexes **not** applied. Single runs, so small differences are noise. These are not production or Redis figures.

| Page | Cold queries (before → after) | Cold DB ms | Warm queries | Warm time ms |
|---|---|---|---|---|
| Home | 179 → 114 | 3607 → 2739 | 107 → 3 | 525 → 400 |
| Post | 38 → 36 | 506 → 492 | 14 → 0 | 374 → 310 |
| Category | 44 → 36 | 516 → 491 | 20 → 0 | 369 → 325 |
| Search | 44 → 35 | 589 → 501 | 20 → 5 | 404 → 375 |
| Tag | 34 → 34 | 1642 → 1661 | 10 → 0 | 1319 → 332 |
| Author | 43 → 36 | 836 → 830 | 19 → 1 | 728 → 345 |
| Sitemap | 1 → 1 | 1057 → 1046 | 0 → 0 | 271 → 270 |

Duplicate queries on a warm home page: 89 → 0. HTML size is unchanged.

Index effect, measured on a temporary copy of `posts`: left slider 1111 → 0.5 ms, videos 852 → 0.4 ms, author count 1074 → 3 ms.

Not measured: production timings, Redis hit rate and memory, Core Web Vitals.

## Verification done

- HTML of 12 public URLs identical before and after (ignoring CSRF tokens and the shuffled footer order). The local database has no ads, so ad output was not compared.
- 11 PHPUnit tests for invalidation, rollback, pagination, fallback and no-flush.
- Admin create, edit, slug change, flags, bulk flags, unpublish, category move, schedule and approve, category rename, delete and subcategories, run on a throwaway copy of the database. Image uploads were not exercised.
- Fallback to files with no Redis extension.
- **A real Redis server was not available locally**, so the Redis path is unverified until steps 8–10.
