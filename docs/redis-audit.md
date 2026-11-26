# Redis integration: audit and proposed design

Date: 2026-11-26. Status: **audit only, no application code changed. Waiting for approval.**

> This file describes unfixed security holes. Do not upload it to the server or push it to a public repository.

## 0. Urgent: fix before any caching work

These come from reading the code. I did not run any of them against a server.

| # | Problem | Where | Effect |
|---|---|---|---|
| S1 | `POST /the/genius/ocean/2441139` writes any file path with any content (`p1`, `v1`) or deletes any file (`p2`). No login. CSRF applies, but any visitor can get a token. | `routes/web.php:709`, `FrontendController::subscription` | Full server takeover |
| S2 | `/clear-cache` is public. | `routes/web.php:685` | Anyone can wipe the cache repeatedly. With Redis this runs `FLUSHDB`. |
| S3 | `/finalize` is public and deletes an `install` directory. `/rss-feed/cronJob/Update` is public and imports posts. | `routes/web.php:710, 748` | Unwanted deletes, load and publishing |
| S4 | `activation_submit` writes a file whose path and content come from a third-party server (`geniusocean.com`). | `Admin/DashboardController` | Remote file write |

Recommendation: remove S1 now. Then check the production server for unknown `.php` files and review access logs for that URL, because it may already have been used.

## 1. Current architecture

- Laravel 13.44.1, old-style skeleton (`App\Http\Kernel`, `routes/web.php` string controllers). MariaDB locally.
- Hosting layout: app in `public_html/project`, public path is its parent (`bootstrap/app.php`).
- `bootstrap/app.php` loads `.env` from `vendor/markury/src/` **if that directory exists**. It does not exist locally. If it exists on production, the root `.env` is ignored there.
- Config: cache `file`, session `file`, queue `sync`. `config/cache.php` reads `CACHE_DRIVER`, **not** `CACHE_STORE`, so adding `CACHE_STORE=redis` alone does nothing.
- Redis config exists in `config/database.php` (`default` DB 0, `cache` DB 1). Client defaults to `phpredis`.
- No scheduled tasks (`Console/Kernel` is empty), no queued jobs, no news API (only the default `/api/user`).
- Services: only tracking/SEO (`app/Services/Tracking`). Front queries live in `FrontendController`, `app/helper.php`, `AppServiceProvider` and Blade views.

### Data model (actual)

| Item | Reality |
|---|---|
| Posts | `Post`, 10,726 rows. `status` is enum `true/false/draft`, plus `is_pending`, `schedule_post`, `schedule_post_date` |
| Categories / subcategories | One `categories` table with `parent_id`; posts have `category_id` and `subcategories_id` |
| Tags | Comma-separated text in `posts.tags`. No tag model |
| Authors | `Admin` (`admin_id`) or `User` (`user_id`) |
| Media | `posts.image_big`, `rss_image`, `video`, `audio`; `galleries` table |
| Views | `views(post_id, ip_address)`, **2.2M rows, no timestamp** |
| Soft deletes | None. Delete is permanent |
| SEO | `SeoMetaService`; `posts.meta_tag` is the description, `tags` the keywords; canonical is `/p/{id}` |

### Features you listed that do not exist

- **Related news** and **previous/next post**: not implemented. I will not add them unless you ask.
- **Daily/weekly popular or trending**: impossible today because `views` has no date. "Popular" is all-time.
- **Trending** and **breaking** are the same flag, `is_trending`.
- **Restore post**: no soft deletes.
- **Search caching**: none today.

### Existing cache

| Key | TTL | Invalidation |
|---|---|---|
| `site:{version}:home:{lang}`, `shared`, `menu`, `cat_posts`, `subcat_posts`, `cat_count`, `top_posts`, `footer_pages`, `language` | 5–60 min | Any save of 14 models bumps one global version |
| `top_views_v1` | 15 min | TTL only |
| `home_ads_v1` | 5 min | TTL only |
| `sitemap_xml_v1` | 60 min | TTL only |

So the app already caches. Moving the same keys from files to Redis will bring only a modest gain on its own. The larger gains are in section 3.

## 2. Baseline (local, before any change)

Windows XAMPP, PHP 8.5 CLI, MariaDB with a 16 MB buffer pool, pages rendered in-process with an empty temporary file cache. Production is PHP 8.4 on different hardware, so **compare these only with later local runs**. "Cold" means empty application cache.

| Page | Cold: time / queries / DB | Warm: time / queries / DB | Duplicates (warm) | HTML | Peak memory |
|---|---|---|---|---|---|
| Home | 9626 ms / 179 / 3607 ms | 525 ms / 107 / 65 ms | 89 | 545 KB | 34 MB |
| Post `/p/{id}` | 1139 / 38 / 506 | 374 / 14 / 21 | 2 | 80 KB | 30 MB |
| Category | 1325 / 44 / 516 | 369 / 20 / 21 | 10 | 135 KB | 30 MB |
| Category `?page=100` | 1012 / 34 / 571 | 477 / 10 / 107 | 2 | 81 KB | 28 MB |
| Search | 1547 / 44 / 589 | 404 / 20 / 34 | 10 | 93 KB | 30 MB |
| Tag | 2164 / 34 / 1642 | 1319 / 10 / 973 | 2 | 60 KB | 28 MB |
| Author | 1397 / 43 / 836 | 728 / 19 / 375 | 9 | 100 KB | 30 MB |
| Sitemap | 2386 / 1 / 1057 | 271 / 0 / 0 | 0 | 2.9 MB | 52 MB |

Reading the numbers:

- The cold home time is far larger than its DB time. It includes first-time view compilation and cache writes.
- Warm pages spend most of their time outside the database (framework boot and rendering, CLI without a persistent OPcache). Redis will not remove that part.
- The home cache for one language is about 940 KB, because it stores full posts including `description`.
- Not measured: production, Core Web Vitals, Redis hit/miss.

## 3. Findings

### 3.1 Expensive and duplicate queries

| Query | Cost seen | Cause | Fix |
|---|---|---|---|
| Top viewed: `GROUP BY post_id` over `views` | ~450 ms on every cold page | Scans 2.2M rows; runs in the layout | Stale-while-revalidate so no visitor waits; longer TTL |
| Home `slider_left` | 1172 ms | No matching index | Index I1 |
| Home video (run twice) | ~1000 + 770 ms | Scans ~3,800 rows | Index I3; one query, split in PHP |
| Tag `LIKE '%x%'` | ~1100 ms, uncached | Comma-text tags cannot be indexed | Cache by tag; real fix is a tag table (separate decision) |
| Author page count | 325 ms | Full table scan | Index I4 |
| `admins` by id, 80+ times on home | N+1, even when warm | Header menu reads `$post->admin->name`; helpers load only `category` | Eager load `admin:id,name` |
| `subcat_recent_posts()` | Called repeatedly (desktop and mobile menu, home tabs) | One cache read per call | Memoize per request |

Also: footer queries categories with `inRandomOrder()` and queries pages directly although `footer_pages()` exists; ad helpers use `ORDER BY RAND()` per request; `d_logo()` runs twice; poll results are queried per poll in `index` and `all_poll`; image albums load `categories` one by one; `generalsettings` and `seotools` are read twice per post page; `site_cache_version()` is read before every cached lookup.

Category pages are not cached at all. Post, tag, author, date and search pages cache only the layout.

Large offset pagination: `paginate(9)` is acceptable at the current size. The category view links only the first 6 pages. The sitemap lists every post in one 2.9 MB file.

### 3.2 Index recommendations (checked against actual indexes)

| ID | Index | Serves |
|---|---|---|
| I1 | `posts(language_id, slider_left, status, created_at)` | Home left slider |
| I2 | `posts(language_id, slider_right, status, id)` | Home right slider |
| I3 | `posts(language_id, post_type, is_videoGallery, id)` | Home videos |
| I4 | `posts(admin_id, status, is_pending, schedule_post, created_at)` | Author page |
| I5 | `galleries(post_id)` | Post gallery (small table today) |

Existing slug, category, subcategory, language and `views` indexes are already there. `posts` also has FULLTEXT indexes on `description` and `slug` that no front query uses; leave them unless you confirm nothing else needs them. Verify production indexes with `SHOW INDEX` before migrating.

### 3.3 Content consistency (affects SEO now, without Redis)

1. **Unpublished posts are public.** `productDetails`, `productDetails2` and `details` look up by id or slug with no status filter. Confirmed: scheduled post `/p/76` returns 200. Drafts and pending posts take the same path.
2. `postByDate` and the subcategory page do not filter `is_pending` (the subcategory one does check status and schedule). The front sitemap does not filter `is_pending`.
3. `top_view_posts()` does not filter by publication status.
4. Scheduled posts publish only when an admin visits `/admin/schedule/postApprove`.

### 3.4 Invalidation problems

| Problem | Effect |
|---|---|
| One global version for everything | One save (or each RSS import row) empties all caches; stampede on a busy site |
| `Admin`, `ImageGallery`, `ImageCategory`, `PollAnswer` not watched | Stale author names, galleries, poll options |
| Sitemap, top views, ads use fixed keys | Deleted posts stay in the sitemap up to 1 hour |
| Events fire immediately, not after commit | `destroyPosts` deletes inside a transaction, so a request can re-cache the old data before commit |
| Mass updates fire no events | `bulkFlag` bumps manually; any future mass update must too |
| `Cache::remember` without locks | Concurrent requests all rebuild the same expensive entry |

### 3.5 Redis and security risks

- **Flush:** admin "Clear cache", user "Clear cache" and `/clear-cache` call `cache:clear`, which on Redis is `FLUSHDB`. It ignores the prefix.
- **Shared server:** `127.0.0.1` with no password on shared hosting may mean other accounts can read or write your keys, and `FLUSHDB` could erase theirs. Cached PHP objects are unserialized, so a writable shared Redis is a code-execution risk. **Ask your host whether the instance is private to your account.** If it is not and they cannot add authentication, I advise staying on the file cache.
- **Web vs CLI PHP:** a successful CLI test does not prove the web PHP has the extension. `public/.htaccess` still selects `ea-php81`, which cannot run Laravel 13, so check which PHP and `.htaccess` production really uses.
- `APP_DEBUG` must be `false` on production.

## 4. Proposed design

### 4.1 Principles

- Cache **data**, not full HTML pages. Views contain CSRF tokens, login state and per-IP poll state.
- Keep server rendering, URLs, canonical tags, JSON-LD and social tags unchanged.
- Extend the existing helpers into `app/Services/NewsCache.php`; old helper names stay as thin wrappers.
- Store **version numbers in the file store** and data in Redis. If Redis goes down during an edit, the version still changes, so old Redis data cannot reappear when it returns. This assumes one web server.
- Fallback: Laravel's `failover` store (`redis`, then `file`) with a log entry, plus a command that tests Redis directly so failover cannot hide a bad setup.

### 4.2 Scoped versions instead of one global version

| Scope | Bumped by |
|---|---|
| `lists:{lang}` (home, latest, breaking, sliders, sitemap, popular) | Post create/delete, or change to publication, flags, title, slug, image, category, date |
| `post:{id}` | That post or its galleries/quiz/sort items |
| `cat:{id}` | A post entering, leaving or changing in that category or subcategory (old and new) |
| `author:{id}` | Author profile, or their posts (old and new author) |
| `tags:{lang}` | Changes to tags, publication or post title/image |
| `layout:{lang}` | Category, page, widget, logo, font, social link, settings, SEO, language |

Content-only edits bump `post:{id}` plus the lists that show an excerpt. All bumps run after commit (`DB::afterCommit`), through observers, with explicit calls for mass updates. Slug changes are covered because lookups are keyed by post id; old slug keys expire with the version.

### 4.3 Keys and TTLs (`config/news_cache.php`)

Final Redis key = `horipurnews_` + key. `{v}` is the scope version.

| Key | TTL |
|---|---|
| `news:home:{section}:{lang}:{v}` (sliders, latest, featured, videos) | 2 min |
| `news:home:breaking:{lang}:{v}` | 30 s |
| `news:cat:{id}:{v}:page:{n}` | 5 min |
| `news:subcat:{id}:{lang}:{v}` | 5 min |
| `news:post:{id}:{v}` (post, category, author, galleries) | 10 min |
| `news:author:{id}:{v}:page:{n}` | 10 min |
| `news:tag:{lang}:{hash}:{v}` (only tags that return results) | 10 min |
| `news:popular:{lang}:{v}` (stale-while-revalidate) | fresh 15 min, stale 60 min |
| `news:menu:{lang}:{v}`, `news:settings:{v}` | 30 / 60 min |
| `news:page:{slug}:{v}` | 60 min |
| `news:sitemap:{v}` | 60 min |

- Search is **not** cached: unlimited key variety and the full-text query is cheap (~70 ms).
- Missing posts and categories are not cached.
- Page numbers beyond the last page are not cached.
- Lists select only the columns and excerpt they display, which shrinks entries considerably.
- Stampede protection: `Cache::flexible()` for popular posts, a short lock for home rebuilds, plain `remember` elsewhere.
- Stale-while-revalidate is never used for post content.

### 4.4 Environment

```env
CACHE_DRIVER=failover      # config/cache.php reads this name
CACHE_PREFIX=              # avoid a double prefix
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_PREFIX=horipurnews_
```

Session and queue settings stay as they are. "Clear cache" buttons will bump versions instead of flushing.

## 5. Implementation order

| Step | Content | Risk |
|---|---|---|
| 0 | Remove S1; protect or remove S2, S3 | Low; check whether a cron calls the RSS URL |
| 1 | Hide unpublished posts (404); fix missing filters | Changes behaviour, intentionally |
| 2 | N+1 and duplicate query fixes | Low |
| 3 | Cache service, scoped invalidation, safe clear buttons, tests (still on file cache) | Medium |
| 4 | Scheduled-post command plus a cPanel cron (no workers) | Low |
| 5 | Index migration (I1–I5), run in a quiet period after a backup | Low to medium |
| 6 | Switch to Redis after production checks pass | Reversible by setting `CACHE_DRIVER=file` |
| 7 | Re-measure, deployment and rollback notes | None |

## 6. Images (separate, later)

919 MB, about 10,900 post images. Uploads are forced to 780×438 JPG (distorts other aspect ratios), the same file serves thumbnails, there is no WebP, and many `<img>` tags lack `width`/`height`. Recommended: generate thumbnail and WebP variants on upload, add `srcset` and dimensions, keep originals' URLs. Nothing binary goes into Redis.

## 7. Production checks needed (read-only)

1. cPanel → Select PHP Version: web PHP version, and is `redis` ticked?
2. Does `vendor/markury/src/.env` exist? Which `.env` is live?
3. Ask the host: is Redis private to this account? Can it have a password?
4. `SHOW INDEX FROM posts;` and `SELECT @@version;`
5. Existing cron jobs in cPanel (especially any that call the RSS URL).

## 8. Decisions needed

1. Approve step 0 (security) immediately?
2. Should drafts, pending and scheduled posts return 404 to the public?
3. Approve steps 2–7 as described?
4. Do you want related / previous-next news (new feature) or daily/weekly popular (needs a `views` date column)? Default: no to both.
