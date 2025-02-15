<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Contracts\NewsSourceAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NewsAPISource implements NewsSource
{
    private const PAGE_SIZE = 100;
    private const MAX_SOURCES = 20;

    private function getSources(): array
    {
        return Cache::remember('newsapi_sources', 3600 * 24, function () {
            $response = Http::get('https://newsapi.org/v2/top-headlines/sources', [
                'apiKey' => config('services.newsapi.key'),
                'language' => 'en'
            ]);

            $sources = $response->json()['sources'] ?? [];

            return array_map(function ($source) {
                return $source['id'];
            }, $sources);
        });
    }

    public function fetch(string $fromDate, NewsSourceAdapter $adapter): array
    {
        $page = 1;
        $allArticles = [];        
        $sources = $this->getSources();
        $selectedSources = Arr::random($sources, min(self::MAX_SOURCES, count($sources)));

        while (true) {
            $response = Http::get('https://newsapi.org/v2/everything', [
                'apiKey' => config('services.newsapi.key'),
                'language' => 'en',
                'sources' => implode(',', $selectedSources),
                'pageSize' => self::PAGE_SIZE,
                'page' => $page,
                'sortBy' => 'publishedAt',
                'from' => date('Y-m-d\TH:i:s', strtotime($fromDate))
            ]);

            if ($response->status() !== 200) {
                Log::error('NewsAPI error:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'sources' => $selectedSources,
                        'page' => $page,
                        'from' => $fromDate
                    ]
                ]);
                break;
            }

            $articles = $response->json()['articles'] ?? [];

            if (empty($articles)) {
                break;
            }

            $allArticles = array_merge($allArticles, $adapter->parse($articles));

            if (count($articles) < self::PAGE_SIZE) {
                break;
            }

            $page++;
            usleep(100000);
        }

        return $allArticles;
    }
}
