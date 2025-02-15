<?php

namespace App\Services;

use App\Contracts\NewsRepository;
use App\Contracts\NewsSource;
use App\Contracts\NewsSourceAdapter;
use App\Services\NewsSources\Adapters\GuardianAdapter;
use App\Services\NewsSources\Adapters\NewsAPIAdapter;
use App\Services\NewsSources\Adapters\NYTimesAdapter;
use App\Services\NewsSources\GuardianNewsSource;
use App\Services\NewsSources\NewsAPISource;
use App\Services\NewsSources\NYTimesNewsSource;

class NewsFetchService
{
    public function __construct(private NewsRepository $repository) {}

    public function fetchFromSource(NewsSource $source, string $fromDate): void
    {
        $newsItems = $source->fetch($fromDate, $this->getSourceAdapter($source));
        foreach ($newsItems as $newsItem) {
            $this->repository->store($newsItem);
        }
    }

    private function getSourceAdapter(NewsSource $source): NewsSourceAdapter
    {
        $sourceAdapterMap = [
            NewsAPISource::class => NewsAPIAdapter::class,
            GuardianNewsSource::class => GuardianAdapter::class,
            NYTimesNewsSource::class => NYTimesAdapter::class,
        ];

        if (!isset($sourceAdapterMap[get_class($source)])) {
            throw new \InvalidArgumentException('Invalid source');
        }

        return new ($sourceAdapterMap[get_class($source)]);
    }
}
