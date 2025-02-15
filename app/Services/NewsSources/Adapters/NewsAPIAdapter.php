<?php

namespace App\Services\NewsSources\Adapters;

use App\Contracts\NewsSourceAdapter;
use App\Services\NewsFiltersService;

class NewsAPIAdapter implements NewsSourceAdapter
{

    private array $validCategories;

    public function __construct()
    {
        $this->validCategories = NewsFiltersService::getCachedCategories();
    }
    

    public function parse(array $articles): array
    {
        if (!$articles) {
            return [];
        }
        return $this->processArticles($articles);
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