<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsFiltersResource;
use App\Services\NewsFiltersService;
use Illuminate\Http\JsonResponse;

class NewsFiltersController extends Controller
{
    public function __construct(
        private NewsFiltersService $filtersService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            new NewsFiltersResource($this->filtersService->getFilters())
        );
    }
}
