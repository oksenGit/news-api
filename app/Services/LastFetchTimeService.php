<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LastFetchTimeService
{
    private const CACHE_KEY = 'last_news_fetch_time';

    public function getLastFetchTime(): string
    {
        return Cache::get(self::CACHE_KEY, now()->subDay()->format('Y-m-d H:i:s'));
    }

    public function updateLastFetchTime(): void
    {
        Cache::put(self::CACHE_KEY, now()->format('Y-m-d H:i:s'), now()->addDays(7));
    }
}
