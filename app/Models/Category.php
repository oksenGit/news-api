<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_categories');
    }

    public static function getDefaultCategory(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
