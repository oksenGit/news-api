<?php

namespace App\Services\NewsSources;

use App\Contracts\NewsSource;
use App\Contracts\NewsSourceAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTimesNewsSource implements NewsSource
{
    private const PAGE_SIZE = 100;

    public function fetch(string $fromDate, NewsSourceAdapter $adapter): array
    {
        $page = 0;
        $allArticles = [];

        while (true) {
            $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
                'api-key' => config('services.nytimes.key'),
                'page' => $page,
                'sort' => 'newest',
                'begin_date' => date('Ymd', strtotime($fromDate)),
                'fl' => 'headline,abstract,web_url,pub_date,byline,_id,news_desk,keywords'
            ]);

            if ($response->status() !== 200) {
                Log::error('NYTimes error:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'page' => $page,
                        'begin_date' => date('Ymd', strtotime($fromDate))
                    ]
                ]);
                break;
            }

            $data = $response->json()['response'] ?? [];
            $articles = $data['docs'] ?? [];
            $hits = $data['meta']['hits'] ?? 0;

            if (empty($articles)) {
                break;
            }

            $allArticles = array_merge($allArticles, $adapter->parse($articles));

            if ($page * self::PAGE_SIZE >= min($hits, 1000)) {
                break;
            }

            $page++;

            usleep(6500000);
        }

        return $allArticles;
    }
}
