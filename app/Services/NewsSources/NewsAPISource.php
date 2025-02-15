<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NewsAPISource implements NewsSource
{
    private const PAGE_SIZE = 100;
    private const MAX_SOURCES = 20;
    private array $validCategories;
    private array $sources;

    public function __construct()
    {
        $this->validCategories = Cache::remember('news_categories', 3600, function () {
            return Category::where('is_default', false)
                ->pluck('name')
                ->toArray();
        });

        $this->sources = $this->getSources();
    }

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

    public function fetch(string $fromDate): array
    {
        $page = 1;
        $allArticles = [];
        $selectedSources = Arr::random($this->sources, min(self::MAX_SOURCES, count($this->sources)));

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

            $allArticles = array_merge($allArticles, $this->processArticles($articles));

            if (count($articles) < self::PAGE_SIZE) {
                break;
            }

            $page++;
            usleep(100000);
        }

        return $allArticles;
    }

    private function processArticles(array $articles): array
    {
        return array_map(function ($article) {
            $categories = $this->extractCategories($article);

            return [
                'title' => $article['title'],
                'content' => $article['description'],
                'author' => $article['author'],
                'source' => 'newsapi',
                'source_name' => $article['source']['name'] ?? 'Unknown',
                'external_id' => md5($article['url']),
                'url' => $article['url'],
                'published_at' => $article['publishedAt'],
                'categories' => $categories
            ];
        }, $articles);
    }

    private function extractCategories(array $article): array
    {
        $content = strtolower($article['title'] . ' ' . ($article['description'] ?? ''));
        $foundCategories = [];

        foreach ($this->validCategories as $category) {
            if (str_contains($content, $category)) {
                $foundCategories[] = $category;
            }
        }

        return $foundCategories;
    }
}
