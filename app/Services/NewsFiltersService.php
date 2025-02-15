<?php

namespace App\Services;

use App\Models\Category;
use App\Models\News;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NewsFiltersService
{
    private const CACHE_KEY = 'news_filters';

    public function getFilters(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                'categories' => $this->getCategories(),
                'sources' => $this->getSources(),
                'source_names' => $this->getSourceNames(),
                'authors' => $this->getAuthors(),
                'date_range' => $this->getDateRange(),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getCategories(): array
    {
        return Category::select('id', 'name')
            ->orderBy('is_default')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function getSources(): array
    {
        return News::select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source')
            ->toArray();
    }

    private function getSourceNames(): array
    {
        return News::select('source_name')
            ->distinct()
            ->orderBy('source_name')
            ->pluck('source_name')
            ->toArray();
    }

    private function getAuthors(): array
    {
        return News::select('author')
            ->whereNotNull('author')
            ->distinct()
            ->orderBy('author')
            ->pluck('author')
            ->toArray();
    }

    private function getDateRange(): array
    {
        $range = News::select(
            DB::raw('MIN(published_at) as min_date'),
            DB::raw('MAX(published_at) as max_date')
        )->first();

        return [
            'min' => $range->min_date,
            'max' => $range->max_date
        ];
    }
}
