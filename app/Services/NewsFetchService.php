<?php

namespace App\Services;

use App\Contracts\NewsRepository;
use App\Contracts\NewsSource;

class NewsFetchService
{
    public function __construct(private NewsRepository $repository) {}

    public function fetchFromSource(NewsSource $source, string $fromDate): void
    {
        $newsItems = $source->fetch($fromDate);

        foreach ($newsItems as $newsItem) {
            $this->repository->store($newsItem);
        }
    }
}
