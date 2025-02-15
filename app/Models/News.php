<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'author',
        'source',
        'source_name',
        'external_id',
        'url',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'news_categories');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['title'] ?? null, function ($query, $title) {
            $query->where('title', 'like', "%{$title}%");
        });

        $query->when($filters['author'] ?? null, function ($query, $author) {
            $query->where('author', 'like', "%{$author}%");
        });

        $query->when($filters['source'] ?? null, function ($query, $source) {
            $query->where('source', $source);
        });

        $query->when($filters['source_name'] ?? null, function ($query, $sourceName) {
            $query->where('source_name', 'like', "%{$sourceName}%");
        });

        $query->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
            $query->where('published_at', '>=', $dateFrom);
        });

        $query->when($filters['date_to'] ?? null, function ($query, $dateTo) {
            $query->where('published_at', '<=', $dateTo);
        });

        $query->when($filters['categories'] ?? null, function ($query, $categories) {
            $categoryIds = is_array($categories) ? $categories : [$categories];
            $query->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
        });
    }
}
