<?php

namespace App\Services\NewsSources\Adapters;

use App\Contracts\NewsSourceAdapter;
use App\Services\NewsFiltersService;

class NYTimesAdapter implements NewsSourceAdapter
{
    private array $validCategories;

    public function __construct()
    {
        $this->validCategories = NewsFiltersService::getCachedCategories();
    }


    public function parse(array $articles): array
    {
        if (empty($articles)) {
            return [];
        }
        return $this->processArticles($articles);
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
