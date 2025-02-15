<?php

namespace App\Services\NewsSources\Adapters;

use App\Contracts\NewsSourceAdapter;
use App\Services\NewsFiltersService;

class GuardianAdapter implements NewsSourceAdapter
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