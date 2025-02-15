<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTimesNewsSource implements NewsSource
{
    private const PAGE_SIZE = 100;
    private array $validCategories;

    public function __construct()
    {
        $this->validCategories = Cache::remember('news_categories', 3600, function () {
            return Category::where('is_default', false)
                ->pluck('name')
                ->toArray();
        });
    }

    public function fetch(string $fromDate): array
    {
        $page = 0;
        $allArticles = [];

        while (true) {
            $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
                'api-key' => config('services.nytimes.key'),
                'page' => $page,
                'sort' => 'newest',
                'begin_date' => date('Ymd', strtotime($fromDate)),
                'fl' => 'headline,abstract,web_url,pub_date,byline,_id,news_desk,keywords'
            ]);

            if ($response->status() !== 200) {
                Log::error('NYTimes error:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'page' => $page,
                        'begin_date' => date('Ymd', strtotime($fromDate))
                    ]
                ]);
                break;
            }

            $data = $response->json()['response'] ?? [];
            $articles = $data['docs'] ?? [];
            $hits = $data['meta']['hits'] ?? 0;

            if (empty($articles)) {
                break;
            }

            $allArticles = array_merge($allArticles, $this->processArticles($articles));

            if ($page * self::PAGE_SIZE >= min($hits, 1000)) {
                break;
            }

            $page++;

            usleep(6500000);
        }

        return $allArticles;
    }

    private function processArticles(array $articles): array
    {
        return array_map(function ($article) {
            $categories = $this->extractCategories($article);

            return [
                'title' => $article['headline']['main'] ?? '',
                'content' => $article['abstract'] ?? '',
                'author' => $article['byline']['original'] ?? null,
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'external_id' => $article['_id'],
                'url' => $article['web_url'],
                'published_at' => $article['pub_date'],
                'categories' => $categories
            ];
        }, $articles);
    }

    private function extractCategories(array $article): array
    {
        $content = strtolower(
            ($article['headline']['main'] ?? '') . ' ' .
                ($article['abstract'] ?? '') . ' ' .
                ($article['news_desk'] ?? '') . ' ' .
                implode(' ', array_map(fn($keyword) => $keyword['value'], $article['keywords'] ?? []))
        );

        $foundCategories = [];

        foreach ($this->validCategories as $category) {
            if (str_contains($content, $category)) {
                $foundCategories[] = $category;
            }
        }

        return $foundCategories;
    }
}
