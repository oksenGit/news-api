<?php

namespace App\Jobs;

use App\Services\NewsFetchService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewsFromSource implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $sourceClass,
        private string $fromDate
    ) {}

    public function handle(NewsFetchService $service): void
    {
        $sourceName = class_basename($this->sourceClass);
        Log::info("Starting job for {$sourceName} since {$this->fromDate}...");

        try {
            $source = new $this->sourceClass();
            $service->fetchFromSource($source, $this->fromDate);
            Log::info("{$sourceName} fetch completed successfully.");
        } catch (\Exception $e) {
            Log::error("Error in {$sourceName}: " . $e->getMessage());
            throw $e;
        }
    }
}
