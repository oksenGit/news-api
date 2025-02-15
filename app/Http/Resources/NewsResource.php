<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author,
            'source' => $this->source,
            'source_name' => $this->source_name,
            'url' => $this->url,
            'published_at' => $this->published_at,
            'categories' => $this->categories->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name
            ])
        ];
    }


}
