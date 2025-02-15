<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Contracts\NewsSourceAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianNewsSource implements NewsSource
{
    private const PAGE_SIZE = 50;

    public function fetch(string $fromDate, NewsSourceAdapter $adapter): array
    {
        $page = 1;
        $allArticles = [];

        while (true) {
            $response = Http::get('https://content.guardianapis.com/search', [
                'api-key' => config('services.guardian.key'),
                'page-size' => self::PAGE_SIZE,
                'page' => $page,
                'order-by' => 'newest',
                'from-date' => date('Y-m-d', strtotime($fromDate)),
                'show-fields' => 'headline,bodyText,byline,thumbnail',
                'show-tags' => 'keyword'
            ]);

            if ($response->status() !== 200) {
                Log::error('Guardian error:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'page' => $page,
                        'from_date' => $fromDate
                    ]
                ]);
                break;
            }

            $data = $response->json()['response'] ?? [];
            $articles = $data['results'] ?? [];
            $totalPages = $data['pages'] ?? 1;

            if (empty($articles)) {
                break;
            }

            $allArticles = array_merge($allArticles, $adapter->parse($articles));

            if ($page >= $totalPages) {
                break;
            }

            $page++;
            usleep(100000);
        }

        return $allArticles;
    }
}
