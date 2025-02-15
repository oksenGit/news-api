<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianNewsSource implements NewsSource
{
    private const PAGE_SIZE = 50;
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
        $page = 1;
        $allArticles = [];

        while (true) {
            $response = Http::get('https://content.guardianapis.com/search', [
                'api-key' => config('services.guardian.key'),
                'page-size' => self::PAGE_SIZE,
                'page' => $page,
                'order-by' => 'newest',
                'from-date' => date('Y-m-d', strtotime($fromDate)),
                'show-fields' => 'headline,bodyText,byline,thumbnail',
                'show-tags' => 'keyword'
            ]);

            if ($response->status() !== 200) {
                Log::error('Guardian error:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'page' => $page,
                        'from_date' => $fromDate
                    ]
                ]);
                break;
            }

            $data = $response->json()['response'] ?? [];
            $articles = $data['results'] ?? [];
            $totalPages = $data['pages'] ?? 1;

            if (empty($articles)) {
                break;
            }

            $allArticles = array_merge($allArticles, $this->processArticles($articles));

            if ($page >= $totalPages) {
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
                'title' => $article['fields']['headline'] ?? $article['webTitle'],
                'content' => $article['fields']['bodyText'] ?? '',
                'author' => $article['fields']['byline'] ?? null,
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'external_id' => $article['id'],
                'url' => $article['webUrl'],
                'published_at' => $article['webPublicationDate'],
                'categories' => $categories
            ];
        }, $articles);
    }

    private function extractCategories(array $article): array
    {
        $content = strtolower(
            ($article['fields']['headline'] ?? '') . ' ' .
                ($article['fields']['bodyText'] ?? '') . ' ' .
                implode(' ', array_map(fn($tag) => $tag['webTitle'], $article['tags'] ?? []))
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
