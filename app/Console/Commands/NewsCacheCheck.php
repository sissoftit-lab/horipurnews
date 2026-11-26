<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Verifies Redis directly (bypassing the failover store, which would hide a bad setup).
 * Writes and removes one short-lived key; never flushes.
 */
class NewsCacheCheck extends Command
{
    protected $signature = 'news-cache:check';

    protected $description = 'Check the Redis connection used by the news cache';

    public function handle(): int
    {
        $this->line('Default cache store : '.config('cache.default'));
        $this->line('Redis client        : '.config('database.redis.client'));
        $this->line('Redis cache database: '.config('database.redis.cache.database'));
        $this->line('Key prefix          : '.config('database.redis.options.prefix').config('cache.prefix'));

        if (config('database.redis.client') === 'phpredis' && ! extension_loaded('redis')) {
            $this->error('The redis PHP extension is not loaded for this PHP binary.');

            return self::FAILURE;
        }

        try {
            Redis::connection('cache')->ping();
            $store = Cache::store('redis');
            $token = bin2hex(random_bytes(8));
            $store->put('news:healthcheck', $token, 30);
            $ok = $store->get('news:healthcheck') === $token;
            $store->forget('news:healthcheck');
        } catch (\Throwable $e) {
            $this->error('Redis failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $ok) {
            $this->error('Redis accepted the connection but the value read back did not match.');

            return self::FAILURE;
        }

        $this->info('Redis OK: ping, write, read and delete succeeded.');

        return self::SUCCESS;
    }
}
