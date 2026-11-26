<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NewsCacheClear extends Command
{
    protected $signature = 'news-cache:clear';

    protected $description = 'Invalidate all cached news data without flushing Redis';

    public function handle(): int
    {
        news_cache()->invalidateAll();
        $this->info('News cache invalidated.');

        return self::SUCCESS;
    }
}
