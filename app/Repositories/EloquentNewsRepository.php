<?php

namespace App\Repositories;

use App\Contracts\NewsRepository;
use App\Models\Category;
use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EloquentNewsRepository implements NewsRepository
{
    public function __construct(private News $model) {}

    public function store(array $newsData): void
    {
        DB::transaction(function () use ($newsData) {
            
            $categories = $newsData['categories'] ?? [];
            unset($newsData['categories']);

            
            $news = $this->model->updateOrCreate(
                [
                    'external_id' => $newsData['external_id'],
                    'source' => $newsData['source']
                ],
                $newsData
            );

            $categoryIds = Category::whereIn('name', $categories)->pluck('id')->toArray();

            if (empty($categoryIds)) {
                if ($defaultCategory = Category::getDefaultCategory()) {
                    $categoryIds[] = $defaultCategory->id;
                }
            }

            $news->categories()->sync($categoryIds);
        });
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        return News::query()
            ->when($filters['title'] ?? null, function (Builder $query, string $title) {
                $query->where('title', 'like', "%{$title}%");
            })
            ->when($filters['author'] ?? null, function (Builder $query, string $author) {
                $query->where('author', 'like', "%{$author}%");
            })
            ->when($filters['source'] ?? null, function (Builder $query, string $source) {
                $query->where('source', $source);
            })
            ->when($filters['source_name'] ?? null, function (Builder $query, string $sourceName) {
                $query->where('source_name', $sourceName);
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $dateFrom) {
                $query->where('published_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $dateTo) {
                $query->where('published_at', '<=', $dateTo);
            })
            ->when($filters['categories'] ?? null, function (Builder $query, array $categories) {
                $query->whereHas('categories', function (Builder $query) use ($categories) {
                    $query->whereIn('categories.id', $categories);
                });
            })
            ->orderByDesc('published_at')
            ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);
    }
}
