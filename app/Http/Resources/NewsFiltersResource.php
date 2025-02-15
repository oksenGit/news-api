<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsFiltersResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'categories' => $this->resource['categories'],
            'sources' => $this->resource['sources'],
            'source_names' => $this->resource['source_names'],
            'authors' => $this->resource['authors'],
            'date_range' => [
                'min' => $this->resource['date_range']['min'],
                'max' => $this->resource['date_range']['max']
            ]
        ];
    }
}
