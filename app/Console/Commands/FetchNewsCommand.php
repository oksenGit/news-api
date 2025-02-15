<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsFromSource;
use App\Services\LastFetchTimeService;
use App\Services\NewsFiltersService;
use App\Services\NewsSources\GuardianNewsSource;
use App\Services\NewsSources\NewsAPISource;
use App\Services\NewsSources\NYTimesNewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {--from= : Date to fetch news from (format: Y-m-d H:i:s)}';
    protected $description = 'Fetch news from all configured sources';

    public function __construct(
        private LastFetchTimeService $lastFetchTimeService,
        private NewsFiltersService $filtersService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fromDate = $this->option('from') ?? $this->lastFetchTimeService->getLastFetchTime();

        $sources = [
            NewsAPISource::class,
            GuardianNewsSource::class,
            NYTimesNewsSource::class,
        ];

        $jobs = [];

        foreach ($sources as $sourceClass) {
            $sourceName = class_basename($sourceClass);
            Log::info("Dispatching job for {$sourceName} since {$fromDate}...");
            $this->info("Dispatching job for {$sourceName} since {$fromDate}...");

            $jobs[] = [new FetchNewsFromSource($sourceClass, $fromDate)];
        }

        Bus::batch($jobs)
            ->allowFailures()
            ->dispatch();

        $this->filtersService->clearCache();

        $this->lastFetchTimeService->updateLastFetchTime();
        $this->info('All news fetch jobs dispatched successfully!');
        return Command::SUCCESS;
    }
}
